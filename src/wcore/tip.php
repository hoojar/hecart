<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/tip.php
 * 简述: 专门用于提供各种提示框的函数
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: tip.php 1150 2017-07-24 02:10:33Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_tip
{
	/**
	 * 弹出对话框显示要提示的信息发送到客户端
	 *
	 * @param string  $msg  提示内容
	 * @param boolean $quit 提示完是否关闭 true为是false为不关闭
	 */
	public static function alert($msg, $quit = false)
	{
		echo("<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">");
		echo("<title>系统提示</title></head><body><script>alert('{$msg}');</script><body></html>");
		if ($quit)
		{
			exit(0);
		}
	}

	/**
	 * 弹出对话框显示要提示的信息发送到客户端并反回前一个页面
	 *
	 * @param string $msg 提示内容
	 */
	public static function alert_back($msg)
	{
		echo("<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">");
		exit("<title >系统提示</title ></head><body><script>alert('{$msg}');history.go(-1);</script><body></html>");
	}

	/**
	 * 弹出对话框显示要提示的信息发送到客户端并加载指定页面 ->在自己的页面上加载
	 *
	 * @param string $msg    提示内容
	 * @param string $url    要跳转的URL地址
	 * @param string $target 在哪个窗口中打开,参数有: self top parent 或自定义的窗口
	 */
	public static function alert_url($msg, $url, $target = "self")
	{
		echo("<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><title>系统提示</title>");
		exit("</head><body><script>alert('{$msg}');{$target}.location=\"{$url}\";</script><body></html> ");
	}

	/**
	 * 采用JAVASCRIPT加载哪个页面,或跳转到哪个URL
	 *
	 * @param string $url    要跳转的URL地址
	 * @param string $target 在哪个窗口中打开,参数有: self top parent 或自定义的窗口
	 * @param bool   $quit
	 */
	public static function js_url($url, $target = "self", $quit = false)
	{
		echo("<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">");
		echo("<meta http-equiv=\"refresh\" content=\"10; url={$url}\"><title>Visit {$url} website</title></head><body>");
		echo("<script>{$target}.location=\"{$url}\";</script></body></html>");

		if ($quit)
		{
			exit(0);
		}
	}

	/**
	 * 重载指定页面 ->在自己的页面基础上重显示
	 *
	 * @param string $url 要加地载的的URL地址
	 */
	public static function location($url = '')
	{
		if (empty($url))
		{
			$url = wcore_utils::get_http_referer();
		}

		echo("<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">");
		echo("<meta http-equiv=\"refresh\" content=\"0; url={$url}\"><title>Visit {$url} website</title>");
		exit('</head><body></body></html>');
	}

	/**
	 * 手机浏览器提示并跳转指定URL地址
	 *
	 * @param string  $msg    提示的内容
	 * @param string  $url    要跳转的URL地址
	 * @param integer $second 显示多少秒
	 */
	public static function mtip($msg, $url = '', $second = 3)
	{
		if (empty($url))
		{
			$url = wcore_utils::get_http_referer();
		}

		if (strpos($_SERVER["HTTP_ACCEPT"], 'wap.wml') !== false)
		{
			$second = $second * 10;
			$msg    = strip_tags($msg);
			header('Content-Type: text/vnd.wap.wml; charset=utf-8');
			echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<!DOCTYPE wml PUBLIC \"-//WAPFORUM//DTD WML 1.3//EN\"");
			echo("\"http://www.wapforum.org/DTD/wml13.dtd\">\n<wml><card title=\"返回\" ontimer=\"{$url}\"><p>{$msg}</p>");
			exit("<p><a href=\"{$url}\">未自动跳转点击此处</a></p><timer value=\"{$second}\"/></card></wml>");
		}
		else
		{
			echo("<?xml version=\"1.0\" encoding=\"utf-8\"?><html><head>");
			echo("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">");
			echo("<meta http-equiv=\"refresh\" content=\"{$second}; url={$url}\"><title>系统提示</title></head><body>");
			echo("<div style='margin:5px;padding:5px;line-height:22px;border:1px solid #FFCCBF;background-color:#FFFFE6;'>");
			echo("{$msg}</div><br/>{$second}秒未跳转，请点击<a href=\"{$url}\">快速跳转</a>");
			exit("</body></html>");
		}
	}

	/**
	 * 在浏览器展示提示信息后并在指定时间后跳转指定URL地址
	 *
	 * @param string  $msg    提示的内容
	 * @param string  $url    要跳转的URL地址
	 * @param integer $second 显示多少秒
	 */
	public static function show($msg, $url = '', $second = 3)
	{
		if (empty($url)) //如果为空则返回上一页面或首页
		{
			$url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/';
		}

		echo('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
		echo('<html xmlns="http://www.w3.org/1999/xhtml">');
		echo('<head>');
		echo('<meta http-equiv="content-type" content="text/html; charset=utf-8" />');

		/**
		 * 分析是跳转到指定地址还是其他处理
		 */
		if (!empty($url))
		{
			$second = intval($second);
			if ($second <= 0)
			{
				$second = 3;
			}
			$msg = "{$msg} <b class='line'><b id='_sec'>{$second}</b> 秒后未自动跳转，请点击 ";
			if (strtolower($url) == '|back|')
			{
				$msg = "{$msg}<a href='javascript:history.go(-1)' target='_self'>返回上一页</a></b>";
				echo("<script language=\"javascript\">try{window.setInterval(\"history.go(-1)\",{$second}*1000);}catch(e){}</script>");
			}
			else
			{
				$msg = "{$msg}<a href='{$url}'>快速跳转</a></b>";
				echo("<meta http-equiv=\"refresh\" content=\"{$second}; url={$url}\">");
			}
		}

		echo('<title>系统提示</title>');
		echo('<style type="text/css">');
		echo('*{margin:0;padding:0;}.bgc1{background-color: #DCEBFE;}.bgc2{background-color: #F9F9F9;}');
		echo('html{font-size:12px;height:100%;margin-bottom:1px;background-color:#E4EDF0;}');
		echo('body{font-family:Arial,sans-serif;color:#536482;background:#E4EDF0;font-size:12px;margin:0;}');
		echo('a:link,a:active,a:visited{color:#006699;text-decoration:none;}a:hover{color:#DD6900;text-decoration:underline;}');
		echo('#header{clear:both;font-size:13px;text-align:center;height:30px;}');
		echo('#body{margin:10px auto;padding:20px;width:80%;background-color:#FFFFFF;border:1px solid #A9B8C2;border-radius:5px;');
		echo('-moz-border-radius:5px;-webkit-border-radius:5px;box-shadow:2px 2px 2px #B0AEA6;-moz-box-shadow:2px 2px 2px #B0AEA6;');
		echo('-webkit-box-shadow:2px 2px 2px #B0AEA6;}');
		echo('#body h1{line-height:25px;margin-bottom:0;color:#DF075C;text-shadow: 1px 1px 1px #FFFF00;}');
		echo('#body div{margin-top:20px;margin-bottom:5px;border-bottom:1px solid #CCCCCC;padding-bottom:5px;color:#333333;font-size:20px;}');
		echo('#body p{text-align:right;display:block;}');
		echo('#body div .line{margin-top:20px;border-top:1px dashed #DBD7D1;padding:20px 5px;color:#333333;font-size:12px;display:block;}');
		echo('.line b{color:#FF0000;}');
		echo('.list-table{width: 100%; background-color:#70AED3; margin-top: 5px;}');
		echo('.list-table caption{background-color:#70AED3;line-height:25px;font-weight:bold;color:#FFFF00;}.list-table td{padding:5px;}');
		echo('#footer{clear:both;font-size:12px;text-align:center;}');
		echo('</style>');

		echo('</head>');
		echo('<body>');
		echo('<div id="header"></div>');
		echo('<div id="body">');
		echo("	<h1>系统提示</h1><div>{$msg}</div>");
		echo('	<p>请通知系统管理员或网站管理者: <a href="mailto:hoojar@163.com">hoojar@163.com</a></p>');
		echo('</div>');
		echo('<div id="footer">Powered by hoojar &copy; 2004 - 2012 <a href="http://www.hoojar.com/">Design By Hoojar Studio</a></div>');
		echo("<script language=\"javascript\">var sec={$second};function run(){try{--sec;document.getElementById('_sec').innerHTML=sec;");
		echo("if(sec<=0){clearInterval(t);self.location.href='{$url}';}}catch(e){}}var t=window.setInterval('run()',1000);</script>");
		echo('</body>');
		exit('</html>');
	}
}
?>