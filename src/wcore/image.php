<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/image.php
 * 简述: 专门用于图像处理
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: image.php 1150 2017-07-24 02:10:33Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_image
{
	/**
	 * 要处理的图像文件路径与名称
	 *
	 * @var string
	 */
	private $_file = null;

	/**
	 * 要处理的图像文件对象
	 *
	 * @var images resource
	 */
	private $_image = null;

	/**
	 * 图像相信
	 *
	 * @var array
	 */
	private $_info = array();

	/**
	 * 构造函数
	 *
	 * @param string $filename 要操作的图像文件路径
	 */
	public function __construct($filename)
	{
		$this->open($filename);
	}

	/**
	 * 打开要操作的图像文件
	 *
	 * @param    string $filename 要操作的图像文件路径
	 * @return    boolan    如果文件不存在或无法打开则返回false
	 */
	public function open($filename)
	{
		if (!file_exists($filename))
		{
			return false;
		}
		$this->_file  = $filename;
		$info         = getimagesize($filename);
		$this->_info  = array(
			'width'  => $info[0],
			'height' => $info[1],
			'bits'   => $info['bits'],
			'mime'   => $info['mime']
		);
		$this->_image = $this->_create($filename);

		return true;
	}

	/**
	 * 保存已处理好的图像文件
	 *
	 * @param    string  $filename 要保存的图像文件路径
	 * @param    integer $quality  要只在的图像质量(jpg图才有效)
	 * @return    boolan    如果文件保存不成功则返回false
	 */
	public function save($filename, $quality = 90)
	{
		$info      = pathinfo($filename);
		$extension = strtolower($info['extension']);
		if (!is_resource($this->_image))
		{
			return false;
		}
		if ($extension == 'jpeg' || $extension == 'jpg')
		{
			$result = imagejpeg($this->_image, $filename, $quality);
		}
		elseif ($extension == 'png')
		{
			$result = imagepng($this->_image, $filename);
		}
		elseif ($extension == 'gif')
		{
			$result = imagegif($this->_image, $filename);
		}

		return $result;
	}

	/**
	 * 调整要操作的图像大小
	 *
	 * @param    integer $width  宽度
	 * @param    integer $height 高度
	 * @return    boolean    如果调整大小失败则返回false
	 */
	public function resize($width = 0, $height = 0)
	{
		if (!$this->_info['width'] || !$this->_info['height'])
		{
			return false;
		}
		$xpos  = 0;
		$ypos  = 0;
		$scale = min($width / $this->_info['width'], $height / $this->_info['height']);
		if ($scale == 1 && $this->_info['mime'] != 'image/png')
		{
			return false;
		}
		$new_width    = (int)($this->_info['width'] * $scale);
		$new_height   = (int)($this->_info['height'] * $scale);
		$xpos         = (int)(($width - $new_width) / 2);
		$ypos         = (int)(($height - $new_height) / 2);
		$filename_old = $this->_image;
		$this->_image = imagecreatetruecolor($width, $height);
		if (isset($this->_info['mime']) && $this->_info['mime'] == 'image/png')
		{
			imagealphablending($this->_image, false);
			imagesavealpha($this->_image, true);
			$background = imagecolorallocatealpha($this->_image, 255, 255, 255, 127);
			imagecolortransparent($this->_image, $background);
		}
		else
		{
			$background = imagecolorallocate($this->_image, 255, 255, 255);
		}
		imagefilledrectangle($this->_image, 0, 0, $width, $height, $background);
		imagecopyresampled($this->_image, $filename_old, $xpos, $ypos, 0, 0, $new_width, $new_height, $this->_info['width'], $this->_info['height']);
		imagedestroy($filename_old);
		$this->_info['width']  = $width;
		$this->_info['height'] = $height;

		return true;
	}

	/**
	 * 在要操作的图像上增加水印
	 *
	 * @param string  $filename 水印图像文件路径
	 * @param mixed   $position 打水印的位置 1左上角,2右上角,3左下角,4右下角
	 * @param integer $opacity  水印透明度
	 */
	public function watermark($filename, $position = 'bottomright', $opacity = 100)
	{
		$watermark        = $this->_create($filename);
		$watermark_width  = imagesx($watermark);
		$watermark_height = imagesy($watermark);
		switch ($position)
		{
			case 1:
			case 'topleft':
				$watermark_pos_x = 0;
				$watermark_pos_y = 0;
				break;
			case 2:
			case 'topright':
				$watermark_pos_x = $this->_info['width'] - $watermark_width;
				$watermark_pos_y = 0;
				break;
			case 3:
			case 'bottomleft':
				$watermark_pos_x = 0;
				$watermark_pos_y = $this->_info['height'] - $watermark_height;
				break;
			case 4:
			case 'bottomright':
				$watermark_pos_x = $this->_info['width'] - $watermark_width;
				$watermark_pos_y = $this->_info['height'] - $watermark_height;
				break;
		}
		imagecopymerge($this->_image, $watermark, $watermark_pos_x, $watermark_pos_y, 0, 0, 120, 40, $opacity);
		imagedestroy($watermark);
	}

	/**
	 * 在操作的图像上复制一块图像
	 *
	 * @param integer $top_x    复制图像开始TX标
	 * @param integer $top_y    复制图像开始TY标
	 * @param integer $bottom_x 复制图像开始BX标
	 * @param integer $bottom_y 复制图像开始BY标
	 */
	public function copy($top_x, $top_y, $bottom_x, $bottom_y)
	{
		$filename_old = $this->_image;
		$this->_image = imagecreatetruecolor($bottom_x - $top_x, $bottom_y - $top_y);
		imagecopy($this->_image, $filename_old, 0, 0, $top_x, $top_y, $this->_info['width'], $this->_info['height']);
		imagedestroy($filename_old);
		$this->_info['width']  = $bottom_x - $top_x;
		$this->_info['height'] = $bottom_y - $top_y;
	}

	/**
	 * 要操作的图像上旋转
	 *
	 * @param float  $degree 旋转的角度
	 * @param string $color  背景颜色
	 */
	public function rotate($degree, $color = 'FFFFFF')
	{
		$rgb                   = $this->_html2rgb($color);
		$this->_image          = imagerotate($this->_image, $degree, imagecolorallocate($this->_image, $rgb[0], $rgb[1], $rgb[2]));
		$this->_info['width']  = imagesx($this->_image);
		$this->_info['height'] = imagesy($this->_image);
	}

	/**
	 * 对要操作的图像使用过滤器
	 *
	 * @param    int $filter 详情请参见PHP手册此图像处理函数
	 * @return    boolan    如果成功则返回 true，失败则返回 false
	 */
	public function filter($filter = IMG_FILTER_EMBOSS)
	{
		return imagefilter($this->_image, $filter);
	}

	/**
	 * 在要操作的图像上增加字符
	 *
	 * @param     string  $text  字符串
	 * @param     integer $x     坐标X
	 * @param     integer $y     坐标Y
	 * @param     integer $size  字体大小 1-5
	 * @param     string  $color 字体颜色
	 * @return    boolan    如果成功则返回 true，失败则返回 false
	 */
	public function text($text, $x = 0, $y = 0, $size = 5, $color = '000000')
	{
		$rgb = $this->_html2rgb($color);

		return imagestring($this->_image, $size, $x, $y, $text, imagecolorallocate($this->_image, $rgb[0], $rgb[1], $rgb[2]));
	}

	/**
	 * 将指定图像合并到要操作的图像上
	 *
	 * @param string  $filename 要合并的图像路径
	 * @param integer $x        坐标X
	 * @param integer $y        坐标Y
	 * @param integer $opacity  透明度
	 */
	public function merge($filename, $x = 0, $y = 0, $opacity = 100)
	{
		$merge        = $this->_create($filename);
		$merge_width  = imagesx($merge);
		$merge_height = imagesy($merge);
		imagecopymerge($this->_image, $merge, $x, $y, 0, 0, $merge_width, $merge_height, $opacity);
		imagedestroy($merge);
	}

	/**
	 * 根据图像创建图像画布
	 *
	 * @param  string $filename 文件路径
	 * @return resource    图像对象
	 */
	private function _create($filename)
	{
		$mime = $this->_info['mime'];
		if ($mime == 'image/gif')
		{
			return imagecreatefromgif($filename);
		}
		elseif ($mime == 'image/png')
		{
			return imagecreatefrompng($filename);
		}
		elseif ($mime == 'image/jpeg')
		{
			return imagecreatefromjpeg($filename);
		}
	}

	/**
	 * 将HTML颜色值转换成RGB值
	 *
	 * @param    string $color HTML颜色值
	 * @return    array    RGB值
	 */
	private function _html2rgb($color)
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
			return array(
				0,
				0,
				0
			);
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