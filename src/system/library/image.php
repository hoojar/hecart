<?php
class Image
{
	private $file;

	private $image;

	private $info;

	public function __construct($file)
	{
		if (file_exists($file))
		{
			$this->file  = $file;
			$this->image = $this->create($file, $this->info);
		}
		else
		{
			exit('Error: Could not load image ' . $file . '!');
		}
	}

	private function create($image, &$info)
	{
		$info = getimagesize($image);
		$info = array(
			'width'  => $info[0],
			'height' => $info[1],
			'bits'   => $info['bits'],
			'mime'   => $info['mime']
		);

		if ($info['mime'] == 'image/gif')
		{
			return imagecreatefromgif($image);
		}
		elseif ($info['mime'] == 'image/png')
		{
			return imagecreatefrompng($image);
		}
		elseif ($info['mime'] == 'image/jpeg')
		{
			return imagecreatefromjpeg($image);
		}

		return imagecreatefromjpeg($image);
	}

	public function save($file, $quality = 90)
	{
		$info      = pathinfo($file);
		$extension = strtolower($info['extension']);
		if (is_resource($this->image))
		{
			if ($extension == 'jpeg' || $extension == 'jpg')
			{
				imagejpeg($this->image, $file, $quality);
			}
			elseif ($extension == 'png')
			{
				imagepng($this->image, $file);
			}
			elseif ($extension == 'gif')
			{
				imagegif($this->image, $file);
			}
			imagedestroy($this->image);
		}
	}

	public function resize($width = 0, $height = 0)
	{
		if (!$this->info['width'] || !$this->info['height'])
		{
			return;
		}

		$scale = min($width / $this->info['width'], $height / $this->info['height']);
		if ($scale == 1 && $this->info['mime'] != 'image/png')
		{
			return;
		}

		$new_width   = (int)($this->info['width'] * $scale);
		$new_height  = (int)($this->info['height'] * $scale);
		$xpos        = (int)(($width - $new_width) / 2);
		$ypos        = (int)(($height - $new_height) / 2);
		$image_old   = $this->image;
		$this->image = imagecreatetruecolor($width, $height);

		if (isset($this->info['mime']) && $this->info['mime'] == 'image/png')
		{
			imagealphablending($this->image, false);
			imagesavealpha($this->image, true);
			$background = imagecolorallocatealpha($this->image, 255, 255, 255, 127);
			imagecolortransparent($this->image, $background);
		}
		else
		{
			$background = imagecolorallocate($this->image, 255, 255, 255);
		}

		imagefilledrectangle($this->image, 0, 0, $width, $height, $background);
		imagecopyresampled($this->image, $image_old, $xpos, $ypos, 0, 0, $new_width, $new_height, $this->info['width'], $this->info['height']);
		imagedestroy($image_old);
		$this->info['width']  = $width;
		$this->info['height'] = $height;
	}

	public function watermark($file, $position = 'bottomright', $margin = 20)
	{
		$winfo     = array();
		$watermark = $this->create($file, $winfo);

		switch ($position)
		{
			case 'topleft':
				$watermark_pos_x = $margin;
				$watermark_pos_y = $margin;
				break;
			case 'topright':
				$watermark_pos_x = $this->info['width'] - $winfo['width'] - $margin;
				$watermark_pos_y = $margin;
				break;
			case 'bottomleft':
				$watermark_pos_x = $margin;
				$watermark_pos_y = $this->info['height'] - $winfo['height'] - $margin;
				break;
			case 'bottomright':
				$watermark_pos_x = $this->info['width'] - $winfo['width'] - $margin;
				$watermark_pos_y = $this->info['height'] - $winfo['height'] - $margin;
				break;
			case 'center':
			default:
				$watermark_pos_x = ($this->info['width']) / 3;
				$watermark_pos_y = ($this->info['height']) / 3;
				break;
		}

		imagecopy($this->image, $watermark, $watermark_pos_x, $watermark_pos_y, 0, 0, $winfo['width'], $winfo['height']);
		imagedestroy($watermark);
	}

	public function crop($top_x, $top_y, $bottom_x, $bottom_y)
	{
		$image_old   = $this->image;
		$this->image = imagecreatetruecolor($bottom_x - $top_x, $bottom_y - $top_y);

		imagecopy($this->image, $image_old, 0, 0, $top_x, $top_y, $this->info['width'], $this->info['height']);
		imagedestroy($image_old);
		$this->info['width']  = $bottom_x - $top_x;
		$this->info['height'] = $bottom_y - $top_y;
	}

	public function rotate($degree, $color = 'FFFFFF')
	{
		$rgb                  = $this->html2rgb($color);
		$this->image          = imagerotate($this->image, $degree, imagecolorallocate($this->image, $rgb[0], $rgb[1], $rgb[2]));
		$this->info['width']  = imagesx($this->image);
		$this->info['height'] = imagesy($this->image);
	}

	private function filter($filter)
	{
		imagefilter($this->image, $filter);
	}

	private function text($text, $x = 0, $y = 0, $size = 5, $color = '000000')
	{
		$rgb = $this->html2rgb($color);
		imagestring($this->image, $size, $x, $y, $text, imagecolorallocate($this->image, $rgb[0], $rgb[1], $rgb[2]));
	}

	private function merge($file, $x = 0, $y = 0, $opacity = 100)
	{
		$minfo = array();
		$merge = $this->create($file, $minfo);
		imagecopymerge($this->image, $merge, $x, $y, 0, 0, $minfo['width'], $minfo['height'], $opacity);
	}

	private function html2rgb($color)
	{
		if ($color[0] == '#')
		{
			$color = substr($color, 1);
		}

		if (strlen($color) == 6)
		{
			list($r, $g, $b) = array(
				$color[0] . $color[1],
				$color[2] . $color[3],
				$color[4] . $color[5]
			);
		}
		elseif (strlen($color) == 3)
		{
			list($r, $g, $b) = array(
				$color[0] . $color[0],
				$color[1] . $color[1],
				$color[2] . $color[2]
			);
		}
		else
		{
			return false;
		}

		$r = hexdec($r);
		$g = hexdec($g);
		$b = hexdec($b);

		return array(
			$r,
			$g,
			$b
		);
	}
}
?>