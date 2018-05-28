<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/redis.php
 * 简述: 专门用于提供各种REDIS操作
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: redis.php 1150 2017-07-24 02:10:33Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_redis
{
	/**
	 * redis 的连接对象
	 *
	 * @var object|Redis
	 */
	public $object = null;

	/**
	 * Redis缓冲的有效期,以分钟为单位
	 *
	 * @var int
	 */
	public $expire = 30;

	/**
	 * 存储数据时KEY的前缀
	 *
	 * @var string
	 */
	public $prefix = '';

	/**
	 * 是否开启使用Redis功能
	 *
	 * @var boolean
	 */
	private $_use = true;

	/**
	 * 是否持久连接
	 *
	 * @var boolean
	 */
	private $_pconnect = false;

	/**
	 * 构造函数 初始化redis
	 *
	 * @param mixed   $host     服务器主机
	 * @param integer $port     端口号
	 * @param string  $pwd      访问密码
	 * @param boolean $use      是否使用Redis
	 * @param integer $expire   Redis的有效期,以分钟为单位
	 * @param string  $prefix   存储数据时KEY的前缀
	 * @param boolean $pconnect 连接服务器是否以长连接
	 */
	public function __construct($host, $port = 11211, $pwd = '', $use = true, $expire = 30, $prefix = '', $pconnect = false)
	{
		/**
		 * 是否可以使用Redis功能
		 */
		if (!$this->_use = $use)
		{
			return;
		}

		$this->expire    = (int)$expire; //有效期时间
		$this->prefix    = $prefix; //KEY的前缀
		$this->_pconnect = $pconnect; //是否为长连接
		$this->object    = new Redis(); //创建REDIS对象
		$timeout         = defined('MEM_TIMEOUT') ? MEM_TIMEOUT : 2;//连接MEM超时时间以秒为单位

		/**
		 * 判断是否为长连接
		 * 如果max_execution_time小于等于0代表SHELL下执行程序，永不超时，需采用长连接
		 */
		if (ini_get('max_execution_time') <= 0)
		{
			$timeout         = 0;
			$this->_pconnect = true;
		}
		if ($this->_pconnect)
		{
			$link = $this->object->pconnect($host, $port, $timeout);
		}
		else
		{
			$link = $this->object->connect($host, $port, $timeout);
		}
		if (!$link)
		{
			$this->_use = false;
			$this->halt("Can not connect redis host: {$host}");
		}

		/**
		 * Redis 授权验证
		 */
		if (!empty($pwd))
		{
			if (!$this->object->auth($pwd))
			{
				$this->_use = false;
				$this->halt('Redis security authentication failure');
			}
		}
	}

	/**
	 * 析构函数 关闭连接
	 */
	public function __destruct()
	{
		if (!$this->_pconnect)
		{
			$this->close(); //非持久连接将关闭连接
		}
	}

	/**
	 * 执行redis所提供的函数
	 *
	 * @param string $name      函数名
	 * @param mixed  $arguments 　参数
	 * @return bool|mixed　执行结果
	 */
	public function __call($name, $arguments)
	{
		if ($this->object && method_exists($this->object, $name))
		{
			return call_user_func_array(array(
				&$this->object,
				$name
			), $arguments);
		}

		return false;
	}

	/**
	 * 组合Redis的KEY
	 *
	 * @param string $t 数据类型字
	 * @param string $k 数据名称
	 * @return string 组合后的KEY
	 */
	private function _makey($t, $k)
	{
		return "{$this->prefix}{$t}-{$k}";
	}

	/**
	 * 存储数据
	 *
	 * @param string $type   数据类型说明
	 * @param string $key    数据名称
	 * @param mixed  $value  数据
	 * @param int    $expire 有效期以分钟为单位,为0时则永不过期只有当Redis服务器关闭才过期
	 * @return boolean 存储成功为true反知为false
	 */
	public function set($type, $key, $value, $expire = -1)
	{
		if (!$this->_use)
		{
			return false;
		}

		$prefix = $this->_makey($type, $key);
		$value  = is_array($value) ? json_encode($value) : $value;

		/**
		 * mt_rand(1, 120)为增加一个两分钟内的随机值，以避免对应缓存的同时更新
		 */
		if ($expire > 0)
		{
			$expire = $expire * 60 + mt_rand(1, 120);
		}
		elseif ($expire < 0)
		{
			$expire = $this->expire * 60 + mt_rand(1, 120);
		}

		/**
		 * 设置 Redis 数据
		 */
		if ($expire == 0)
		{
			$result = $this->object->set($prefix, $value); //永不过期
		}
		else
		{
			$result = $this->object->setex($prefix, $expire, $value);
		}

		return $result;
	}

	/**
	 * 获取数据
	 *
	 * @param string $type    数据类型说明
	 * @param string $key     数据名称
	 * @param mixed  $default 默认值
	 * @return mixed 获取的缓存值
	 */
	public function &get($type, $key, $default = null)
	{
		if (!$this->_use || (isset($_GET['nocache']) && $type != 'session'))
		{
			return $default;
		}

		$res = $this->object->get($this->_makey($type, $key));
		$len = strlen($res);
		if ($len >= 2 && (($res[0] === '{' && $res[$len - 1] === '}') || ($res[0] === '[' && $res[$len - 1] === ']')))
		{
			$res = @json_decode($res, true);
		}

		return $res;
	}

	/**
	 * 为某个数字类型的数据名称增值
	 *
	 * @param string $type  数据类型说明
	 * @param string $key   数据名称
	 * @param int    $value 要增加的数值
	 * @return mixed 成功为增加后的值失败则返回false
	 */
	public function increment($type, $key, $value = 1)
	{
		if (!$this->_use || !is_numeric($value))
		{
			return false;
		}

		return $this->object->incrBy($this->_makey($type, $key), $value);
	}

	/**
	 * 为某个数字类型的数据名称减值
	 *
	 * @param string $type  数据类型说明
	 * @param string $key   数据名称
	 * @param int    $value 要减的数值
	 * @return mixed 成功为减后的值失败则返回false
	 */
	public function decrement($type, $key, $value = 1)
	{
		if (!$this->_use || !is_numeric($value))
		{
			return false;
		}

		return $this->object->decrBy($this->_makey($type, $key), $value);
	}

	/**
	 * 将数据存储到消息队列
	 *
	 * @param string $type  数据类型说明
	 * @param string $key   数据名称
	 * @param mixed  $value 数据
	 * @return int|bool 存储成功返回存储长度失败为false
	 */
	public function push($type, $key, $value)
	{
		if (!$this->_use)
		{
			return false;
		}

		$value = is_array($value) ? json_encode($value) : $value;

		return $this->object->lPush($this->_makey($type, $key), $value);
	}

	/**
	 * 将数据弹出消息队列并删除key的值
	 *
	 * @param string $type    数据类型说明
	 * @param string $key     数据名称
	 * @param mixed  $default 默认值
	 * @return mixed 获取的缓存值
	 */
	public function &pop($type, $key, $default = false)
	{
		if (!$this->_use)
		{
			return $default;
		}

		$res = $this->object->rPop($this->_makey($type, $key));
		$len = strlen($res);
		if ($len >= 2 && (($res[0] === '{' && $res[$len - 1] === '}') || ($res[0] === '[' && $res[$len - 1] === ']')))
		{
			$res = @json_decode($res, true);
		}

		return $res;
	}

	/**
	 * 删除某个数据名称当中的数据
	 *
	 * @param string $type 数据类型说明
	 * @param string $key  数据名称
	 * @return boolean 删除成功为true反知为false
	 */
	public function del($type, $key)
	{
		if (!$this->_use)
		{
			return false;
		}

		return $this->object->delete($this->_makey($type, $key));
	}

	/**
	 * 删除某个数据名称当中的数据del的别名函数
	 *
	 * @param string $type 数据类型说明
	 * @param string $key  数据名称
	 * @return boolean 删除成功为true反知为false
	 */
	public function delete($type, $key)
	{
		return $this->del($type, $key);
	}

	/**
	 * 清空Redis当中的所有数据
	 *
	 * @return boolean 清空成功为true反知为false
	 */
	public function flush()
	{
		if (!$this->_use)
		{
			return false;
		}

		return $this->object->flushDB();
	}

	/**
	 * 关闭Redis对象
	 *
	 * @return boolean 成功为true反知为false
	 */
	public function close()
	{
		if ($this->_use && is_object($this->object))
		{
			return $this->object->close();
		}

		return true;
	}

	/**
	 * 获取是否开启Redis功能
	 *
	 * @return boolean 可用为true反知为false
	 */
	public function used()
	{
		return $this->_use;
	}

	/**
	 * 类与到严重错误时停执行
	 *
	 * @param string $msg 提示的信息
	 */
	public function halt($msg)
	{
		if (defined('DEBUG_LOG') && DEBUG_LOG)
		{
			file_put_contents(DIR_ROOT . '/logs/redis.log', date('Y-m-d H:i:s') . " - Error: {$msg}\n", FILE_APPEND);
		}
		exit($msg);
	}
}
?>