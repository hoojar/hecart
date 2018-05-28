<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/ip.php
 * 简述: 专门用处理IP获取对应的实际地址(类型的处理经AB测试比单个函数的慢一点点)
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: ip.php 1150 2017-07-24 02:10:33Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_ip
{
	/**
	 * IP库文件名称
	 *
	 * @var string
	 */
	private $_fname = '';

	/**
	 * IP库文件指针
	 *
	 * @var object
	 */
	private $_fp = null;

	/**
	 * IP库当共有多少条记录
	 *
	 * @var integer
	 */
	private $_count = 0;

	/**
	 * 初始化并打开IP库
	 *
	 * @param string $fname IP库路径
	 */
	public function __construct($fname = 'ip-db.dat')
	{
		$this->_fp = @fopen($fname, 'rb');
		if ($this->_fp === false)
		{
			exit("{$fname} file not exists .");
		}

		$res          = unpack('Ncnt', fread($this->_fp, 4)); //获取IP库当中有多少条记录
		$this->_count = $res['cnt'];
		unset($res);
	}

	/**
	 * 关闭打开的IP库文件
	 *
	 */
	public function __destruct()
	{
		if ($this->_fp)
		{
			fclose($this->_fp);
		}
	}

	/**
	 * 获取真实的IP地址
	 *
	 * @return string IP地址
	 */
	public function get_ip()
	{
		if (getenv('HTTP_CLIENT_IP'))
		{
			$ip = getenv('HTTP_CLIENT_IP');
		}
		else if (getenv('HTTP_X_FORWARDED_FOR'))
		{
			list($ip) = explode(',', getenv('HTTP_X_FORWARDED_FOR'));
		}
		else if (getenv('REMOTE_ADDR'))
		{
			$ip = getenv('REMOTE_ADDR');
		}
		else
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return $ip;
	}

	/**
	 * 根据IP获取对应的数据(高效)
	 *
	 * @param string $ip IP地址
	 * @return array 未找到返回false，找到返回array(sIp=>开始IP数字 eIp=>结束IP数字 pId=>省份编号 cId=>城市编号)
	 */
	function get_address($ip)
	{
		/**
		 * IP转数字并判断是否为合法IP地址
		 */
		$ip = ip2long($ip); //将IP转换成数字
		if ($ip === false)
		{
			return false; //ip转数字无效返回false
		}

		$ip = sprintf('%u', $ip); //将转换成数字的IP再次转换成无符号的长整形

		/**
		 * 初始化二分查找参照数据
		 */
		$seek   = 12; //一条记录占几个字节
		$top    = $middle = 0; //低位//下标
		$bottom = $this->_count; //底(记录总数)

		/**
		 * 二分查找对应的数据
		 */
		while ($top <= $bottom)
		{
			$middle = floor(($top + $bottom) / 2);
			fseek($this->_fp, 4 + $middle * $seek);
			$data        = unpack('NsIp/NeIp/SpId/ScId', fread($this->_fp, $seek));
			$data['sIp'] = sprintf('%u', $data['sIp']);
			$data['eIp'] = sprintf('%u', $data['eIp']);

			if ($ip < $data['sIp'])
			{
				$bottom = $middle - 1;
			}
			elseif ($ip > $data['eIp'])
			{
				$top = $middle + 1;
			}
			else
			{
				$rdata = $data; //找到了
				$top   = $middle + 1; //当有多条记录符号时则尽量取最后一个值
			}
		}

		return isset($rdata) ? $rdata : false; //未找到
	}
}
?>