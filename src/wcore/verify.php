<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/verify.php
 * 简述: 专门产生校验码
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: verify.php 1150 2017-07-24 02:10:33Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_verify
{
	/**
	 * 验证码字符串
	 *
	 * @var string 为空代表由程序自动产生字符码
	 */
	public $words = '';

	/**
	 * 背景图片地址
	 *
	 * @var string 为空代表没有前景图片合成
	 */
	public $background = '';

	/**
	 * 复制背景宽度多少到合成图片中
	 *
	 * @var integer 宽度 0代表自动计算宽度
	 */
	public $copy_bg_width = 0;

	/**
	 * 复制背景高度多少到合成图片中
	 *
	 * @var integer 高度 0代表自动计算高度
	 */
	public $copy_bg_height = 0;

	/**
	 * 设置字母对象顶边的外延边距
	 *
	 * @var integer 0代表对象顶边的外延边距自动计算
	 */
	public $text_margin_top = 0;

	/**
	 * 干扰数,验证码的识别困难度,数字越大越难识别
	 *
	 * @var integer
	 */
	public $disturb_size = 15;

	/**
	 * 背景颜色,不设置则随机产生背景颜色
	 *
	 * @var boolean
	 */
	public $bgcolor = '';

	/**
	 * 设置字母字体的大小
	 *
	 * @var int 0代表根据图片大小自动计算字体大小
	 */
	public $font_size = 0;

	/**
	 * 设置验证码图片的文件格式
	 *
	 * @var string {png jpg jpeg gif}
	 */
	public $image_type = 'png';

	/**
	 * 验证码字符串字体
	 *
	 * @var string 设置一个默认的字体
	 */
	public $font = 'britanic.ttf';

	/**
	 * 字符串只能包涵哪些字符
	 *
	 * @var string
	 */
	public $charsets = 'ABCDEFGHKLMNPRSTUVWYZabcdefghklmnprstuvwyz23456789';

	/**
	 * 构造函数
	 *
	 */
	public function __construct()
	{
		$this->font = dirname(__FILE__) . "/{$this->font}";
	}

	/**
	 * 生成验证码
	 *
	 * @param int $length 生成几位数的验证码
	 * @return string 验证码字符串
	 */
	public function generate_words($length = 4)
	{
		$this->words = '';
		$length      = intval($length);
		$cslen       = strlen($this->charsets) - 1;
		for ($i = 1; $i <= $length; ++$i)
		{
			$this->words .= $this->charsets[rand(0, $cslen)];
		}

		return $this->words;
	}

	/**
	 * 将HTML颜色值转换成RGB值
	 *
	 * @param    string $color HTML颜色值
	 * @return    array    RGB值
	 */
	public function html2rgb($color)
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

	/**
	 * 画出验证码图片
	 *
	 * @param int    $width  验证码图片宽度
	 * @param int    $height 验证码图片高度
	 * @param string $bg     附加背景图地址
	 * @return string 返回验证码
	 */
	function draw($width = 120, $height = 60, $bg = '')
	{
		/**
		 * 设置颜色
		 */
		$image = imagecreatetruecolor($width, $height) or die('Cannot initialize new GD image stream');
		$nscolor = imagecolorallocate($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)); //干扰颜色

		if (empty($this->bgcolor))
		{
			$bgcolor = imagecolorallocate($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)); //背景颜色
			imagecolortransparent($image, $bgcolor);//设置颜色为透明背景
		}
		else
		{
			$rgb     = $this->html2rgb($this->bgcolor);
			$bgcolor = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
		}

		imagefilledrectangle($image, 0, 0, $width, $height, $bgcolor); //设置背景颜色

		/**
		 * 背景处理
		 */
		$this->background = $bg ? $bg : $this->background;
		if ($this->background && file_exists($this->background))
		{
			$dat = getimagesize($this->background);
			switch ($dat[2])
			{
				case 1:
					$newim = imagecreatefromgif($this->background);
					break;
				case 2:
					$newim = imagecreatefromjpeg($this->background);
					break;
				case 3:
					$newim = imagecreatefrompng($this->background);
					break;
				case 15:
					$newim = imagecreatefromwbmp($this->background);
					break;
				case 16:
					$newim = imagecreatefromxbm($this->background);
					break;
				default:
					$newim = null;
			}

			/**
			 * 将背景合成到验证码图片中
			 */
			if ($newim)
			{
				if ($this->copy_bg_width <= 0)
				{
					$this->copy_bg_width = $width;
				}
				if ($this->copy_bg_height <= 0)
				{
					$this->copy_bg_height = $height;
				}
				imagecopyresampled($image, $newim, 0, 0, 0, 0, $this->copy_bg_width, $this->copy_bg_height, imagesx($newim), imagesy($newim));
			}
		}
		else
		{
			for ($i = 0; $i < $this->disturb_size; $i++) //产生背景干扰点
			{
				imagefilledellipse($image, mt_rand(0, $width), mt_rand(0, $height), 1, 1, $nscolor);
			}
			for ($i = 0; $i < $this->disturb_size; $i++) //随机生成干扰线条
			{
				imageline($image, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), $nscolor);
			}
		}

		/**
		 * 创建文本框与文本
		 */
		if (empty($this->words))
		{
			$this->generate_words();
		}

		$font_size = $this->font_size ? $this->font_size : ceil((($width + $height) / 2) * 0.36);
		$textbox = imagettfbbox($font_size, 0, $this->font, $this->words) or die('Error in imagettfbbox function');
		$x = ($width - $textbox[4]) / 2;
		if ($this->text_margin_top > 0)
		{
			$y = $this->text_margin_top; //固定字母对象顶边的外延边距
		}
		else
		{
			$y = ($height - $textbox[5]) / 2; //不固定字母对象顶边的外延边距
		}

		/**
		 * 生成字母并定位置
		 */
		$strlen = strlen($this->words);
		for ($i = 0; $i < $strlen; ++$i)
		{
			$text_color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
			imagettftext($image, $font_size, 0, $x, $y, $text_color, $this->font, $this->words[$i]);
			$x = $x + ($font_size / 1.3);
			if ($this->text_margin_top <= 0)
			{
				$y = rand($y - $height / 5, $y + $height / 5);
			}
		}

		/**
		 * 输出图片
		 */
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		switch ($this->image_type)
		{
			case 'jpg':
			case 'jpeg':
				header('Content-Type: image/jpeg');
				imagejpeg($image);
				break;
			case 'gif':
				header('Content-type: image/gif');
				imagegif($image);
				break;
			default:
				header('Content-Type: image/png');
				imagepng($image);
				break;
		}

		imagedestroy($image);

		return $this->words;
	}
}
?>