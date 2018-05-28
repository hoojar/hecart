<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: system/startup.php
 * 简述: 系统启动初始化处理
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: startup.php 1273 2017-09-28 06:18:03Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 *
 * 判断是否注册了全局对象，如果是则清空全局自动变量
 */
if (ini_get('register_globals'))
{
	$globals = array(
		$_FILES,
		$_SERVER,
		$_REQUEST,
		$_SESSION
	);
	foreach ($globals as $global)
	{
		foreach (array_keys($global) as $key)
		{
			unset(${$key});
		}
	}
}

/**
 * 将各种外部参数转义
 *
 * @param string $awrv 要转换值
 * @param string $awrk 转换好值
 */
function walk_var_clean(&$awrv, &$awrk)
{
	$awrv = htmlspecialchars(stripslashes($awrv), ENT_QUOTES);
}

if (!empty($_GET))
{
	@array_walk_recursive($_GET, 'walk_var_clean');
}
if (!empty($_POST))
{
	@array_walk_recursive($_POST, 'walk_var_clean');
}
if (!empty($_COOKIE))
{
	@array_walk_recursive($_COOKIE, 'walk_var_clean');
}
if (!empty($_REQUEST))
{
	@array_walk_recursive($_REQUEST, 'walk_var_clean');
}

@array_walk_recursive($_SERVER, 'walk_var_clean');

/**
 * 判断是否定义了网站文档ROOT目录,如果没有定义转根据脚本完整路径去定义
 */
if (!isset($_SERVER['DOCUMENT_ROOT']))
{
	if (isset($_SERVER['SCRIPT_FILENAME']))
	{
		$_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0 - strlen($_SERVER['PHP_SELF'])));
	}
}

/**
 * 判断是否定义了请求URI地址,如果没有定义则根据请求串去定义
 */
if (!isset($_SERVER['REQUEST_URI']))
{
	$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);
	if (isset($_SERVER['QUERY_STRING']))
	{
		$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
	}
}

/**
 * 加载系统核心对象库
 */
require(DIR_ROOT . '/system/model.php');
require(DIR_ROOT . '/system/registry.php');
require(DIR_ROOT . '/system/controller.php');

/**
 * 加载系统附加功能处理库
 */
require(DIR_ROOT . '/system/library/log.php');
require(DIR_ROOT . '/system/library/url.php');
require(DIR_ROOT . '/system/library/mail.php');
require(DIR_ROOT . '/system/library/image.php');
require(DIR_ROOT . '/system/library/config.php');
require(DIR_ROOT . '/system/library/request.php');
require(DIR_ROOT . '/system/library/response.php');
require(DIR_ROOT . '/system/library/language.php');
require(DIR_ROOT . '/system/library/document.php');
require(DIR_ROOT . '/system/library/pagination.php');
?>