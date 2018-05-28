<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: modules/mem.php
 * 简述: 数据模块缓存
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: mem.php 1258 2017-09-06 07:24:21Z zhangsl $
 *
 * 版权 2006-2014, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2014, Hoojar Studio All Rights Reserved.
 */
class modules_mem extends wcore_object
{
	/**
	 * @var wcore_mem MEM对象
	 */
	public $mem = null;

	/**
	 * @var string SQL类型前缀
	 */
	public $mem_type_sql = 'MEM-SQL';

	/**
	 * @var string 资源类型前缀
	 */
	public $mem_type_res = 'MEM-RES';

	/**
	 * 构造函数
	 */
	public function __construct()
	{
		if (!is_object($this->mem))
		{
			$this->mem = (MEM_USTYPE == 'redis') ? $this->mds() : $this->mem();
		}
	}

	/**
	 * 获取数据
	 *
	 * @param string $mkey 获取码(mem key)
	 * @return mixed 返回数据
	 */
	public function &mem_get($mkey)
	{
		return $this->mem->get($this->mem_type_res, $mkey);
	}

	/**
	 * 设置数据
	 *
	 * @param string $mkey   获取码(mem key)
	 * @param mixed  $res    数据
	 * @param int    $expire 有效期以分钟为单位,为0时则永不过期只有当MEM服务器关闭才过期
	 * @return boolean 存储成功返回true反知为false
	 */
	public function mem_set($mkey, $res, $expire = 30)
	{
		return $this->mem->set($this->mem_type_res, $mkey, $res, $expire);
	}

	/**
	 * 删除数据
	 *
	 * @param string $mkey 获取码(mem key)
	 * @return boolean 存储成功返回true反知为false
	 */
	public function mem_del($mkey)
	{
		return $this->mem->del($this->mem_type_res, $mkey);
	}

	/**
	 * 获取SQL的数据,若MEM中有则从MEM中拿没有则查询数据库
	 *
	 * @param string  $sql    SQL语句
	 * @param string  $dtype  获取记录类型 (详情参数setting.php)
	 * @param boolean $sdb    选用从库读取
	 * @param int     $expire 有效期以分钟为单位,为0时则永不过期只有当MEM服务器关闭才过期
	 * @return mixed 返回数据
	 */
	public function mem_sql($sql, $dtype = 'fetch_row', $sdb = true, $expire = 30)
	{
		$mkey = md5($sql . $dtype);
		$res  = $this->mem->get($this->mem_type_sql, $mkey);
		if (!$res || isset($_GET['nocache']))
		{
			$db  = $sdb ? $this->sdb() : $this->mdb(); //用哪个数据库操作
			$res = $db->{$dtype}($sql);
			$this->mem->set($this->mem_type_sql, $mkey, $res, $expire); //存储数据到MEM
		}

		return $res;
	}

	/**
	 * hash 将指定字段设置为数组的KEY
	 *
	 * @param string  $sql    SQL语句
	 * @param string  $key    用哪个字段名的内容设置为KEY
	 * @param string  $pre    数组下标前缀
	 * @param boolean $sdb    选用从库读取
	 * @param int     $expire 有效期以分钟为单位,为0时则永不过期只有当MEM服务器关闭才过期
	 * @return mixed 返回数据
	 */
	public function hash_sql($sql, $key, $pre = '', $sdb = true, $expire = 30)
	{
		$mkey = md5($sql);
		$res  = $this->mem->get($this->mem_type_sql, $mkey);
		if (!empty($res))
		{
			return $res;
		}

		/**
		 * 从数据库中获取数据
		 */
		$res  = array();
		$db   = $sdb ? $this->sdb() : $this->mdb(); //用哪个数据库操作
		$arrs = $db->fetch_all($sql);

		/**
		 * 按指定key进行HASH
		 */
		if (!empty($arrs))
		{
			foreach ($arrs as $v)
			{
				if (isset($v[$key]))
				{
					$res["{$pre}{$v[$key]}"] = $v;
				}
			}
			unset($arrs);
		}

		$this->mem->set($this->mem_type_sql, $mkey, $res, $expire); //存储数据到MEM

		return $res;
	}

	/**
	 * hash数组，将数组中的某个字段值索引成数组的KEY
	 *
	 * @param array  $arr      需要HASH的数组
	 * @param string $key      要定的数组字段名
	 * @param array  $mem_type 需要HASH的数组
	 * @param int    $expire   有效期以分钟为单位,为0时则永不过期只有当MEM服务器关闭才过期
	 * @return array|mixed 返回数据
	 */
	public function &mem_hash($arr, $key, $mem_type, $expire = 30)
	{
		if (empty($arr) || !is_array($arr))
		{
			return $arr;
		}

		/**
		 * 从缓存中获取数据
		 */
		$mem_type = "{$mem_type}-{$key}";
		$res      = $this->mem->get(__FUNCTION__, $mem_type);
		if (!empty($res))
		{
			return $res;
		}

		/**
		 * 将数据进行hash
		 */
		$res = array();
		foreach ($arr as $v)
		{
			if (isset($v[$key]))
			{
				$res[$v[$key]] = $v;
			}
		}

		/**
		 * 存储数据并返回数据
		 */
		$this->mem->set(__FUNCTION__, $mem_type, $res, $expire);

		return $res;
	}
}
?>