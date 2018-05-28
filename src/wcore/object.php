<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/object.php
 * 简述: 全局对象操作接口
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: object.php 1 2012-11-20 05:55:12Z Administrator $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_object
{
	/**
	 * 常用函数接口
	 *
	 * @var wcore_utils
	 */
	private static $_utils = null;

	/**
	 * 提示函数接口
	 *
	 * @var wcore_tip
	 */
	private static $_tip = null;

	/**
	 * 操作数据库接口
	 *
	 * @var wcore_mysql
	 */
	private static $_db = array();

	/**
	 * 操作Redis接口
	 *
	 * @var wcore_redis
	 */
	private static $_rds = array();

	/**
	 * 操作MEMCACHED库接口
	 *
	 * @var wcore_mem
	 */
	private static $_mem = null;

	/**
	 * 常用函数接口
	 *
	 * @return wcore_utils 返回常用函数对象
	 */
	public static function &utils()
	{
		if (is_object(self::$_utils))
		{
			return self::$_utils;
		}
		self::$_utils = new wcore_utils();

		return self::$_utils;
	}

	/**
	 * 提示函数接口
	 *
	 * @return wcore_tip 返回常用函数对象
	 */
	public static function &tip()
	{
		if (is_object(self::$_tip))
		{
			return self::$_tip;
		}
		self::$_tip = new wcore_tip();

		return self::$_tip;
	}

	/**
	 * 操作数据库接口
	 *
	 * @param string $name
	 * @return wcore_mssql|wcore_mysql|wcore_mysqli|wcore_oci 返回操作数据的对象
	 */
	public static function &db($name = '')
	{
		/**
		 * 判断数据库连接是否已生成数组连接池,是则定位到要调用的连接对象
		 */
		if (isset(self::$_db[$name]))
		{
			return self::$_db[$name]; //数据库连接从连接池数组当中取
		}

		/**
		 * 生成数据连接数组池
		 */
		$db_servers = json_decode(DB_SERVERS, true);
		foreach ($db_servers as $k => $v)
		{
			if ($k != $name)
			{
				continue; //若$name不为空就只注册连接需要打开的数据库对象
			}

			switch (strtolower($v['dbtype']))
			{
				case 'mysqli':
					$db = new wcore_mysqli($v['host'], $v['user'], $v['pwd'], $v['dbname'], $v['charset'], $v['port'], $v['pconnect']);
					break;
				case 'oci':
					$db = new wcore_oci($v['host'], $v['user'], $v['pwd'], $v['dbname'], $v['charset'], $v['port'], $v['pconnect']);
					break;
				case 'mssql':
					$db = new wcore_mssql($v['host'], $v['user'], $v['pwd'], $v['dbname'], $v['charset'], $v['port'], $v['pconnect']);
					break;
				default:
					$db = new wcore_mysql($v['host'], $v['user'], $v['pwd'], $v['dbname'], $v['charset'], $v['port'], $v['pconnect']);
					break;
			}
			self::$_db[$k] = $db;

			return $db;
		}

		exit("System can not connect {$name} database server.");
	}

	/**
	 * 操作Redis接口
	 *
	 * @param string $name
	 * @return wcore_redis 返回操作Redis的对象
	 */
	public static function &rds($name = '')
	{
		/**
		 * 判断Redis连接是否已生成数组连接池,是则定位到要调用的连接对象
		 */
		if (isset(self::$_rds[$name]))
		{
			return self::$_rds[$name]; //Redis连接从连接池数组当中取
		}

		/**
		 * 生成Redis连接数组池
		 */
		$rds_servers = json_decode(RDS_SERVERS, true);
		foreach ($rds_servers as $k => $v)
		{
			if ($k != $name)
			{
				continue; //若$name不为空就只注册连接需要打开的Redis对象
			}

			self::$_rds[$k] = new wcore_redis($v['host'], $v['port'], $v['pwd'], MEM_USE, MEM_EXPIRE, MEM_PREFIX, $v['pconnect']);

			return self::$_rds[$k];
		}

		exit("System can not connect {$name} redis server.");
	}

	/**
	 * 操作MEMCACHED库接口
	 *
	 * @return wcore_mem 返回操作数据的对象
	 */
	public static function &mem()
	{
		if (is_object(self::$_mem))
		{
			return self::$_mem;
		}

		$mem_servers = json_decode(MEM_SERVERS, true);
		self::$_mem  = new wcore_mem($mem_servers, MEM_USE, MEM_EXPIRE, MEM_PREFIX, MEM_PCONNECT);

		return self::$_mem;
	}

	/**
	 * 主数据库连接操作(可写可读)
	 *
	 * @return wcore_mysql 返回操作数据的对象
	 */
	public static function mdb()
	{
		static $_db = null;
		if (is_null($_db))
		{
			$_db = wcore_object::db('master');
		}

		return $_db;
	}

	/**
	 * 从数据库连接操作(只读)
	 *
	 * @return wcore_mysql 返回操作数据的对象
	 */
	public static function sdb()
	{
		static $_db = null;
		if (is_null($_db))
		{
			$_db = wcore_object::db('slave');
		}

		return $_db;
	}

	/**
	 * 主Redis连接操作(可写可读)
	 *
	 * @return wcore_redis 返回操作数据的对象
	 */
	public static function mds()
	{
		static $_rds = null;
		if (is_null($_rds))
		{
			$_rds = wcore_object::rds('master');
		}

		return $_rds;
	}

	/**
	 * 从Redis连接操作(建议只读)
	 *
	 * @return wcore_redis 返回操作数据的对象
	 */
	public static function sds()
	{
		static $_rds = null;
		if (is_null($_rds))
		{
			$_rds = wcore_object::rds('slave');
		}

		return $_rds;
	}
}
?>