<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/passport.php
 * 简述: 专门处理通行证的相关函数
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: passport.php 1150 2017-07-24 02:10:33Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_passport
{
	/**
	 * 生成密码
	 *
	 * @param string $str 要加密的字符串
	 * @return string    加密后的字符串
	 */
	public static function password($str)
	{
		return md5($str);
	}

	/**
	 * 生成经过加密码算法的门票
	 *
	 * @param integer $uid   用户编号
	 * @param string  $uname 用户名
	 * @return string    门票串
	 */
	public static function make_ticket($uid, $uname)
	{
		//判断是否定义了通行证密匙,若没有定义则定义为hoo!@#jar
		$key    = defined('SITE_MD5_KEY') ? SITE_MD5_KEY : 'hoo!@#jar';
		$ticket = md5("{$uid}-{$uname}-{$key}");
		$start  = substr($ticket, -12, 12);
		$end    = substr($ticket, 0, 20);

		return "{$start}{$end}";
	}

	/**
	 * 获取或设置门票到COOKIE当中
	 *
	 * @param string $tickey 门票串
	 * @return bool|string 若是获取门票则返回门票，或是设置门票才返回是否设置成功
	 */
	public static function ticket($tickey = '')
	{
		if (empty($tickey)) //获取门票
		{
			return isset($_COOKIE['ticket']) ? $_COOKIE['ticket'] : '';
		}

		return wcore_utils::set_cookie('ticket', $tickey); //存储门票
	}

	/**
	 * 获取或设置用户信息，当info为空时则获取用户信息，不为空且有数组时则设置用户到COOKIE中
	 *
	 * @param array $info 用户信息数组，可为空
	 * @return array|bool 若是获取门票则返回门票，或是设置门票才返回是否设置成功
	 */
	public static function user_info($info = null)
	{
		/**
		 * 获取用户信息
		 */
		if (empty($info))
		{
			$info            = array();
			$info['uid']     = isset($_COOKIE['uid']) ? intval($_COOKIE['uid']) : 0; //用户编号
			$info['uname']   = isset($_COOKIE['uname']) ? $_COOKIE['uname'] : ''; //用户名称
			$info['unick']   = isset($_COOKIE['unick']) ? $_COOKIE['unick'] : ''; //用户昵称
			$info['ticket']  = wcore_passport::ticket(); //用户门票
			$info['logined'] = false; //没有登录成功
			if ($info['uid'] != 0) //判断是否有登录用户编号
			{
				$my_ticket = wcore_passport::make_ticket($info['uid'], $info['uname']);
				if ($info['ticket'] === $my_ticket) //验证门票是否合法
				{
					$info['logined'] = true; //登录成功
				}
				unset($my_ticket);
			}

			return $info;
		}

		/*
		 * 不为数据则返回false
		 */
		if (!is_array($info))
		{
			return false;
		}

		/**
		 * 存储用户信息
		 */
		foreach ($info as $k => $v)
		{
			wcore_utils::set_cookie($k, $v);
		}
		if (isset($info['uid']) && isset($info['uname']))
		{
			$my_ticket = wcore_passport::make_ticket($info['uid'], $info['uname']);
			wcore_passport::ticket($my_ticket);
		}

		return true; //设置COOKIE数据成功
	}

	/**
	 * 清空会话，那就意味着退出系统
	 *
	 */
	public static function empty_session()
	{
		wcore_utils::set_cookie('uid', null, 0);
		wcore_utils::set_cookie('uname', null, 0);
		wcore_utils::set_cookie('unick', null, 0);
		wcore_utils::set_cookie('ticket', null, 0);
		wcore_utils::set_cookie('logined', null, 0);
	}
}
?>