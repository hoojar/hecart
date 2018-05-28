<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/phplog.php
 * 简述: 专门用于处理PHP日志错误信息库
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: phplog.php 1150 2017-07-24 02:10:33Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_phplog
{
	/**
	 * 保存运行日志的路径
	 *
	 * @var string
	 */
	private $_err_path = '/tmp/';

	/**
	 * 错误类型
	 *
	 * @var integer
	 */
	private $_error_type = array(
		E_ERROR             => 'Error',
		E_WARNING           => 'Warning',
		E_PARSE             => 'Parsing Error',
		E_NOTICE            => 'Notice',
		E_CORE_ERROR        => 'Core Error',
		E_CORE_WARNING      => 'Core Warning',
		E_COMPILE_ERROR     => 'Compile Error',
		E_COMPILE_WARNING   => 'Compile Warning',
		E_USER_ERROR        => 'User Error',
		E_USER_WARNING      => 'User Warning',
		E_USER_NOTICE       => 'User Notice',
		E_STRICT            => 'Runtime Notice',
		E_RECOVERABLE_ERROR => 'Catchable Fatal Error'
	);

	/**
	 * 构造函数
	 *
	 * @param boolean $err_show 是否显示出错信息
	 * @param string  $err_path 如果不显示错误信息则保存运行日志的路径
	 */
	public function __construct($err_show = false, $err_path = '/tmp/')
	{
		if (ini_get('display_errors') || $err_show) //若开启了出错提示则输出错误信息
		{
			ini_set('error_reporting', E_ALL | E_STRICT);
			set_error_handler(array(
								   &$this,
								   'show_error_handler'
							  ));
			set_exception_handler(array(
									   &$this,
									   'show_error_handler'
								  ));
		}
		else //普通安全等级则记录下PHP运行的日志出错信息
		{
			$err_path        = ($err_path[strlen($err_path) - 1] == '/') ? $err_path : "{$err_path}/";
			$this->_err_path = $err_path;
			ini_set('error_log', "{$this->_err_path}sys-" . date('Y-m-d'));
			set_error_handler(array(
								   &$this,
								   'log_error_handler'
							  ));
			set_exception_handler(array(
									   &$this,
									   'log_error_handler'
								  ));
		}
	}

	/**
	 * 获取PHP运行中出错类型提示
	 *
	 * @param    integer $eno 出错编号
	 * @return    string    出错信息
	 */
	public function error_tip($eno)
	{
		return isset($this->_error_type[$eno]) ? $this->_error_type[$eno] : 'Error Tip';
	}

	/**
	 * 记录PHP运行中所产生的错误信息
	 *
	 * @param integer $eno  出错等级编号
	 * @param string  $msg  出错描述
	 * @param string  $file 出错文件
	 * @param integer $line 出错文件哪一行
	 * @param array   $vars 所有成员变量
	 */
	public function log_error_handler($eno, $msg, $file, $line, $vars)
	{
		$tnow      = date('Y-m-d H:i:s');
		$error_tip = $this->error_tip($eno);
		$err_msg   = "\n--------------------------------PHP ERROR INFORMATION--------------------------------\n";
		$err_msg .= "Rank: [{$error_tip}]\n";
		$err_msg .= "Note: [{$msg}]\n";
		$err_msg .= "File: [{$file}]\n";
		$err_msg .= "Line: [{$line}]\n";
		$err_msg .= "Time: [{$tnow}]\n";
		error_log($err_msg); //记录系统级错误
		if (strpos($error_tip, 'User') !== false)
		{
			error_log($err_msg, 3, "{$this->_err_path}user-" . date('Y-m-d')); //记录用户级错误
		}
	}

	/**
	 * 显示PHP运行中所产生的错误信息
	 *
	 * @param integer $eno  出错等级编号
	 * @param string  $msg  出错描述
	 * @param string  $file 出错文件
	 * @param integer $line 出错文件哪一行
	 * @param array   $vars 所有成员变量
	 */
	public function show_error_handler($eno, $msg, $file, $line, $vars)
	{
		$tnow      = date('Y-m-d H:i:s');
		$error_tip = $this->error_tip($eno);
		$err_msg   = '<table cellspacing="1" class="list-table"><caption>PHP ERROR INFORMATION</caption>';
		$err_msg .= "<tr class='bgc1'><td width='100'>Rank</td><td>{$error_tip}</td></tr>";
		$err_msg .= "<tr class='bgc2'><td>Note</td><td>{$msg}</td></tr>";
		$err_msg .= "<tr class='bgc1'><td>File</td><td>{$file}</td></tr>";
		$err_msg .= "<tr class='bgc2'><td>Line</td><td>{$line}</td></tr>";
		$err_msg .= "<tr class='bgc1'><td>Time</td><td>{$tnow}</td></tr></table>";
		wcore_tip::show($err_msg);
	}
}
?>