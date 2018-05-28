<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: $Id: start.php 1073 2017-06-29 10:04:58Z zhangsl $
 * 简述: 程序开始文件
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 *
 * 版权 2006-2014, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2014, Hoojar Studio All Rights Reserved.
 *
 * 设置输出是否压缩与加载相关库文件
 */
require(DIR_ROOT . '/config/setting.php');
require(DIR_ROOT . '/wcore/mem.php'); //加载设置与MEM库

/**
 * 自动加载类库
 *
 * @param string $class_name 类名
 */
spl_autoload_register(function ($class_name)
{
	if (false !== strpos($class_name, '_'))
	{
		$class_name = str_replace('_', '/', $class_name);
	}
	else
	{
		$class_name = 'system/' . str_replace('\\', '/', $class_name);
	}

	if (false === strpos($class_name, 'PHPExcel'))
	{
		require(DIR_ROOT . "/{$class_name}.php");
	}
});

/**
 *判断是否是通过手机访问
 *
 */
function is_mobile()
{
	/**
	 * 如果有HTTP_X_WAP_PROFILE则一定是移动设备
	 */
	if (isset($_SERVER['HTTP_X_WAP_PROFILE']))
	{
		return true;
	}

	/**
	 * 判断手机发送的客户端标志,兼容性有待提高
	 */
	if (isset($_SERVER['HTTP_USER_AGENT']))
	{
		$ua_keyword = array(
			'nokia',
			'sony',
			'ericsson',
			'mot',
			'samsung',
			'htc',
			'sgh',
			'lg',
			'sharp',
			'sie-',
			'philips',
			'panasonic',
			'alcatel',
			'lenovo',
			'iphone',
			'ipod',
			'blackberry',
			'meizu',
			'android',
			'netfront',
			'symbian',
			'ucweb',
			'windowsce',
			'palm',
			'operamini',
			'operamobi',
			'openwave',
			'nexusone',
			'cldc',
			'midp',
			'wap',
			'mobile'
		);
		if (preg_match('/(' . implode('|', $ua_keyword) . ')/i', strtolower($_SERVER['HTTP_USER_AGENT'])))
		{
			return true; //从HTTP_USER_AGENT中查找手机浏览器的关键字
		}
	}

	/**
	 * 协议法，因为有可能不准确，放到最后判断
	 */
	if (isset($_SERVER['HTTP_ACCEPT']))
	{
		if (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false)
		{
			return true; //如果支持wml说明是移动设备
		}
	}

	return false;
}

/**
 * 获取安全令牌
 *
 * @param string $site_key 网站密匙
 * @param bool   $check_ua 是否校对浏览器UA
 * @return string 安全令牌
 */
function security_token($site_key, $check_ua = true)
{
	if (empty($_SERVER['REMOTE_ADDR']))
	{
		exit('Can not get your ip address, This is not safe!');
	}

	$ua = ($check_ua && isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '';

	return strtolower(md5("{$ua}{$site_key}{$_SERVER['REMOTE_ADDR']}"));
}
?>