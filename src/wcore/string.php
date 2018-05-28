<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/string.php
 * 简述: 专门用于提供各种操作字符串的函数
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: string.php 1150 2017-07-24 02:10:33Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_string
{
	/**
	 * 截取中文若取3个字符，则汉字也会取2个字，但长度为会6,是以字长度的为准
	 * 例: leftSubstr('abc树林woods', 4)则输出为: abc树 (得以utf-8编码)
	 *
	 * @param string  $string 要截取的字符串
	 * @param integer $length 要取的长度
	 * @return string
	 */
	public static function lcase($string, $length)
	{
		if (!$string || !is_numeric($length))
		{
			return '';
		}

		return mb_strcut($string, 0, $length);
		/**
		 * 以下是当没有安装mb_string模块时的处理方法
		 */
		$str_len = 0;
		if ($length >= strlen($string)) //如果要截取的长度大于或等于实际长度则直接返回
		{
			return $string;
		}
		else
		{
			while ($str_len < $length) //将$length换算成实际UTF8格式编码下字符串的长度
			{
				$str_len += (ord($string[$str_len]) > 127) ? 3 : 1; //当检测到一个中文字符时加3
			}

			return substr($string, 0, $str_len);
		}
	}

	/**
	 * 获取$cs分隔符前的字符
	 *
	 * @param string $str 要截取的字符串
	 * @param string $cs  截取的分隔符
	 * @return string
	 */
	public static function cleft_front($str, $cs)
	{
		if (!$str || !$cs)
		{
			return '';
		}
		$pos = strpos($str, $cs);

		return ($pos !== false) ? substr($str, 0, $pos) : $str;
	}

	/**
	 * 获取$cn分隔符后的字符
	 *
	 * @param string $str 要截取的字符串
	 * @param string $cs  截取的分隔符
	 * @return string
	 */
	public static function cleft_back($str, $cs)
	{
		if (!$str || !$cs)
		{
			return '';
		}
		$pos = mb_strpos($str, $cs);
		if ($pos === false)
		{
			return $str;
		}
		$pos++;

		return mb_substr($str, $pos);
	}

	/**
	 * 截取中文与英文相交的字符数，若取3个字符，则汉字也会取3个字，但长度为会9
	 *
	 * @param string  $string 要截取的字符串
	 * @param integer $length 长度
	 * @return string
	 */
	public static function cnsubstr($string, $length)
	{
		if (!$string || !is_numeric($length))
		{
			return '';
		}

		return mb_substr($string, 0, $length);

		/**
		 * 以下是当没有安装mb_string模块时的处理方法
		 */
		$str_len = 0;
		$slen    = strlen($string);
		if ($length >= $slen) //如果要截取的长度大于或等于实际长度则直接返回
		{
			return $string;
		}

		for ($i = 0; $i < $length; $i++) //将$length换算成实际UTF8格式编码下字符串的长度
		{
			if ($str_len >= $slen) //当检测到一个中文字符时
			{
				break;
			}
			$mb_len = 3; //GB2312占2字位，UTF-8占3字位
			$str_len += (ord($string[$str_len]) > 127) ? $mb_len : 1;
		}

		return substr($string, 0, $str_len);
	}

	/**
	 * 获取 $string 左边 $length 字符要是要取的字符数大于或等    于实际字符数就按原值返回否则按要取的多少为准
	 * 例: leftWord('abc树林woods', 5) 则输出: abc树林
	 *
	 * @param string  $string 要截取的字符串
	 * @param integer $length 长度
	 * @return string
	 */
	public static function lword($string, $length)
	{
		return wcore_string::cnsubstr($string, $length);
	}

	/**
	 * 获取 $string 右边 $length 字符要是要取的字符数大于或等于实际字符数就按原值返回否则按要取的多少为准
	 *
	 * @param string  $string
	 * @param integer $length
	 * @return string
	 */
	public static function rword($string, $length)
	{
		if (!$string || !is_numeric($length))
		{
			return '';
		}
		$slen = mb_strlen($string) - $length;
		if ($slen <= 0)
		{
			return $string;
		}

		return mb_substr($string, $slen, $length);

		/**
		 * 以下是当没有安装mb_string模块时的处理方法
		 */
		$slen = strlen($string);
		if ($slen <= $length)
		{
			return $string;
		}
		$slen    = $slen - $length; //确定从哪开始取
		$tmp_str = wcore_string::cnsubstr($string, $slen);

		return str_replace($tmp_str, '', $string);
	}
}
?>