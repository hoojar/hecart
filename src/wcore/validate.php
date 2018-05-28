<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/validate.php
 * 简述: 专门用于表单的处理
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: validate.php 1150 2017-07-24 02:10:33Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_validate
{
	/**
	 * 判断是否为邮箱地址
	 *
	 * @param string $str 邮件地址
	 * @return boolean
	 */
	public static function email($str)
	{
		if (empty($str))
		{
			return false;
		}

		return (bool)preg_match('/^(?!\.)[-+_a-z0-9.]++(?<!\.)@(?![-.])[-a-z0-9.]+(?<!\.)\.[a-z]{2,6}$/iD', $str);
	}

	/**
	 * 决断是否为URL地址
	 *
	 * @param string $str URL地址
	 * @return boolean
	 */
	public static function url($str)
	{
		if (empty($str))
		{
			return false;
		}
		$regex = "/^((https|http|ftp|rtsp|mms):\/\/)" //协议头
			. "(([0-9a-z_!~*'().&=+$%-]+: )?[0-9a-z_!~*'().&=+$%-]+@)?" //ftp的user@
			. "(([0-9]{1,3}\.) {3}[0-9]{1,3}" //IP形式的URL- 199.194.52.184
			. "|" //允许IP和DOMAIN(域名)
			. "([0-9a-z_!~*'()-]+\.)*" //域名 www.
			. "([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\." //二级域名
			. "[a-z]{2,6})" //首层域名 .com or .museum
			. "(:[0-9]{1,4})?" //端口 :80
			. "((\/?)|" //a slash isn't required if there is no file name
			. "(\/[0-9a-z_!~*'().;?:@&=+$,%#-]+)+\/?)$/i";

		return (bool)preg_match($regex, $str);
	}

	/**
	 * 判断是否为固定电话
	 *
	 * @param string $str 　固定电话号码
	 * @return boolean
	 */
	public static function phone($str)
	{
		if (empty($str))
		{
			return false;
		}

		return (bool)preg_match('/(^\d{3,4},\d{7,8}(,\d{1,4})?$)|(^\d{3,4}\-\d{7,8}(\-\d{1,4})?$)|(^\d{3,4}\d{7,11}$)/', $str);
	}

	/**
	 * 判断是否为手机号码
	 *
	 * @param string $str 　手机号码
	 * @return boolean
	 */
	public static function mobile($str)
	{
		if (empty($str))
		{
			return false;
		}

		return (bool)preg_match('/^(\+86)?1[3,4,5,7,8](\d{9})$/', $str);
	}

	/**
	 * 判断是否为手机号码
	 *
	 * @param string $str 　手机号码
	 * @return boolean
	 */
	public static function handset($str)
	{
		if (empty($str))
		{
			return false;
		}

		return wcore_validate::mobile($str);
	}

	/**
	 * 判断是否为传真号码
	 *
	 * @param string $str 传真号码
	 * @return boolean
	 */
	public static function fax($str)
	{
		if (empty($str))
		{
			return false;
		}

		return (bool)preg_match('/(^\d{3,4},\d{7,8}(,\d{1,4})?$)|(^\d{3,4}\-\d{7,8}(\-\d{1,4})?$)|(^\d{3,4}\d{7,11}$)/', $str);
	}

	/**
	 * 判断是否为日期
	 *
	 * @param string $str 日期
	 * @return boolean
	 */
	public static function is_date(&$str)
	{
		if (empty($str))
		{
			return false;
		}
		$result = strtotime($str);
		if (false === $result)
		{
			return false;
		}
		$str = date('Y-m-d', $result);

		return true;
	}

	/**
	 * 校验IP地址是否正确
	 *
	 * @param string $str ip地址
	 * @return boolean
	 */
	public static function ip($str)
	{
		if (empty($str))
		{
			return false;
		}

		return (ip2long($str) == -1) ? false : true;
	}

	/**
	 * 判断是否为UTF8字符集
	 *
	 * @param string $str 要转的字符串
	 * @return boolean
	 */
	public static function utf8($str)
	{
		if (empty($str))
		{
			return false;
		}
		$curr_encoding = mb_detect_encoding($str);
		if ($curr_encoding == "UTF-8" && mb_check_encoding($str, "UTF-8"))
		{
			return true;
		}

		return false;
	}

	/**
	 * 校验输入内容是否全为字母(a-Z)
	 *
	 * @param string $str 　字符串
	 * @return boolean
	 */
	public static function letter($str)
	{
		if (empty($str))
		{
			return false;
		}

		return (bool)preg_match('/^[A-Za-z]+$/', $str);
	}

	/**
	 * 校验输入内容是否全为字母与数字
	 *
	 * @param string $str 　字符串
	 * @return boolean
	 */
	public static function letter2number($str)
	{
		if (empty($str))
		{
			return false;
		}

		return (bool)preg_match('/^[A-Za-z0-9]+$/', $str);
	}

	/**
	 * 校验输入内容是否由数字、26个英文字母或者下划线组成的字符串
	 *
	 * @param string $str 　字符串
	 * @return boolean
	 */
	public static function alphanumeric($str)
	{
		if (empty($str))
		{
			return false;
		}

		return (bool)preg_match('/^\w+$/', $str);
	}

	/**
	 * 校验输入内容是否为小写字母(a-z)
	 *
	 * @param string $str 　字符串
	 * @return boolean
	 */
	public static function lower_case($str)
	{
		if (empty($str))
		{
			return false;
		}

		return (bool)preg_match('/^[a-z]+$/', $str);
	}

	/**
	 * 校验输入内容是否为大写字母(A-Z)
	 *
	 * @param string $str 　字符串
	 * @return boolean
	 */
	public static function upper_case($str)
	{
		if (empty($str))
		{
			return false;
		}

		return (bool)preg_match('/^[A-Z]+$/', $str);
	}

	/**
	 * 校验输入内容是否全为字母与数字
	 *
	 * @param string $str 　字符串
	 * @return boolean
	 */
	public static function chinese($str)
	{
		if (empty($str))
		{
			return false;
		}

		return (bool)preg_match('/^[\x7f-\xff]+$/', $str);
	}
}
?>