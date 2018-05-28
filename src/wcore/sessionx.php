<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/sessionx.php
 * 简述: 超级SESSION处理库
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: session.php 1144 2016-07-22 06:31:59Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_sessionx
{
	/**
	 * @var array SESSION数组
	 */
	public $data = array();

	/**
	 * @var string IP地址
	 */
	private $_ip = '';

	/**
	 * @var string SESSION ID
	 */
	private $_sid = '';

	/**
	 * 将SESSION存储在哪种物质类型中
	 *
	 * @var string 存储方式如下
	 * db    max:65535    会话内容存储在数据库表中
	 * mdb   max:255      会话内容存储在数据库内存表中
	 * mem   max:unlimit  会话内容存储在Memcache缓存中
	 * file  max:unlimit  会话内容存储在文件中
	 * dir   max:unlimit  会话内容存储在分目录的文件中
	 */
	private $_type = 'file';

	/**
	 * 当存储方式为file或dir时SESSION文件所存储的路径
	 *
	 * @var string 会话文件存储路径
	 */
	private $_path = '/tmp';

	/**
	 * @var wcore_mysql 连接数据的模块对象
	 */
	private $_db = null;

	/**
	 * @var wcore_mem 连接MEM的模块对象
	 */
	private $_mem = null;

	/**
	 * 当SESSION存储在数据库中时要操作的数据表
	 *
	 * @var string 数据库表名称 (分普通表[session_wcore]与内存表[session_mem])
	 */
	private $_opt = 'session_wcore';

	/**
	 * @var string SESSION 前缀
	 */
	private $_prefix = 'ws';

	/**
	 * @var integer SESSION的寿命，默认为30分钟以秒为单位
	 */
	private $_ltime = 1800;

	/**
	 * 初始化SESSION
	 *
	 * @param string  $type   会话的存储方式
	 * @param integer $ltime  会话寿命时间以分钟为单位
	 * @param string  $path   会话文件存储的路径
	 * @param string  $prefix 会话文件前缀
	 * @param boolean $start  是否马上启用SESSION处理
	 */
	public function __construct($type = 'file', $ltime = 30, $path = '', $prefix = 'ws', $start = true)
	{
		$this->_prefix = $prefix;
		$this->_type   = strtolower($type);
		$this->_ltime  = ($ltime && is_numeric($ltime)) ? $ltime * 60 : ini_get('session.gc_maxlifetime');

		if ($this->_type == 'file' || $this->_type == 'dir')
		{
			$this->_path = ($path && file_exists($path)) ? $path : ini_get('session.save_path');
			wcore_fso::make_dir($this->_path); //处理SESSION存储的路径
		}

		if ($start) //是否马上启用SESSION处理
		{
			$this->start();
		}
	}

	/**
	 * 析构函数
	 */
	public function __destruct() { }

	/**
	 * 启动SESSION处理
	 */
	public function start()
	{
		static $_status = false;
		if ($_status)
		{
			return true;
		}

		/**
		 * 初始化操作SESSION的对象
		 */
		if ($this->_type == 'db' || $this->_type == 'mdb') //以数据库方式来处理SESSION
		{
			$this->_db = wcore_object::mdb();
			$this->_ip = wcore_utils::get_ip();
			if ($this->_type == 'mdb')
			{
				$this->_opt = 'session_mem';
			}
		}
		else if ($this->_type == 'mem') //以Memcache缓冲方式来处理SESSION
		{
			$this->_mem         = (MEM_USTYPE == 'redis') ? wcore_object::mds() : wcore_object::mem();
			$this->_mem->expire = $this->_ltime / 60;
		}

		register_shutdown_function(array(
			&$this,
			'commit'
		)); //程序结束时调用提交SESSION处理

		$_status    = true;
		$this->data = &$_SESSION; //将SESSION数据赋值给公共data成员变量
		$ssname     = ini_get('session.name');
		$this->_sid = !empty($_COOKIE[$ssname]) ? $_COOKIE[$ssname] : $this->sid();
		$this->data = $this->_read($this->_sid); //读取SESSION数据
		setcookie($ssname, $this->_sid, 0, '/', DOMAIN_NAME); //保存SESSION编号到COOKIE

		return $_status;
	}

	/**
	 * 提交SESSION
	 *
	 * @return boolean
	 */
	public function commit()
	{
		$this->_write($this->_sid, (array)$this->data);//保存SESSION数据到媒介中

		return $this->_gc(); //清除已过期的SESSION
	}

	/**
	 * 获取会话
	 *
	 * @param string $key     会话名
	 * @param string $default 默认值
	 * @return mixed null 会话值
	 */
	public function get($key, $default = null)
	{
		return isset($this->data[$key]) ? $this->data[$key] : $default;
	}

	/**
	 * 设置会话
	 *
	 * @param string $key   会话名
	 * @param mixed  $value 会话值
	 */
	public function set($key, $value)
	{
		$this->data[$key] = $value;
	}

	/**
	 * 删除会话
	 *
	 * @param string $key 会话名
	 */
	public function del($key)
	{
		unset($this->data[$key]);
	}

	/**
	 * 获取SESSION编号
	 *
	 * @param string $sid SESSION ID
	 * @return string SESSION ID
	 */
	public function sid($sid = '')
	{
		if (!empty($sid))
		{
			$this->_sid = $sid;
		}
		else
		{
			$this->_sid = $this->_prefix;
			$chars      = 'abcdefghijklmnopqrstuvwxyz0123456789';
			for ($i = 0; $i < 12; $i++)
			{
				$this->_sid .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
			}
		}

		return $this->_sid;
	}

	/**
	 * 读取SESSION内容
	 *
	 * @param string $sid 会话唯一标识
	 * @return string 会话值
	 */
	private function _read($sid)
	{
		/**
		 * 以数据库方式来处理SESSION
		 */
		if ($this->_type == 'db' || $this->_type == 'mdb')
		{
			$res = $this->_db->fetch_row("SELECT sData FROM {$this->_opt} WHERE sId = '{$sid}';");

			return !empty($res['sData']) ? @json_decode($res['sData'], true) : array();
		}

		/**
		 * 以Memcache缓冲方式来处理SESSION
		 */
		if ($this->_type == 'mem')
		{
			return (array)$this->_mem->get('session', $sid);
		}

		/**
		 * 以文件系统的方式来处理SESSION
		 */
		if ($this->_type == 'dir')
		{
			$sfile = "{$this->_path}/{$sid[0]}/{$this->_prefix}-{$sid}";
		}
		else
		{
			$sfile = "{$this->_path}/{$this->_prefix}-{$sid}";
		}

		if (!file_exists($sfile))
		{
			return array();
		}

		return @json_decode(file_get_contents($sfile), true);
	}

	/**
	 * 写入SESSION内容
	 *
	 * @param string $sid   会话唯一标识
	 * @param string $sdata 会话内容
	 * @return boolean
	 */
	private function _write($sid, $sdata)
	{
		/**
		 * SESSION数据为空则清除先前数据
		 */
		if (empty($sdata))
		{
			return $this->destroy($sid);
		}

		/**
		 * 以数据库方式来处理SESSION
		 */
		if ($this->_type == 'db' || $this->_type == 'mdb')
		{
			$expires = time() + $this->_ltime; //SESSION有效期时间戳
			$sdata   = json_encode($sdata); //将数据转换成JSON格式方便存储
			$sql     = "REPLACE INTO {$this->_opt} (sId, sData, sIp, sExpires) VALUES ('{$sid}', '{$sdata}', '{$this->_ip}', {$expires})";
			$this->_db->query($sql);

			return ($this->_db->affected_rows() > 0) ? true : false;
		}

		/**
		 * 以Memcache缓冲方式来处理SESSION
		 */
		if ($this->_type == 'mem')
		{
			$expires = $this->_ltime / 60; //SESSION的有效期
			return $this->_mem->set('session', $sid, $sdata, $expires);
		}

		/**
		 * 以文件系统的方式来处理SESSION
		 */
		if ($this->_type == 'dir')
		{
			$sfile = "{$this->_path}/{$sid[0]}";
			wcore_fso::make_dir($sfile); //处理SESSION存储的路径
			$sfile = "{$sfile}/{$this->_prefix}-{$sid}";
		}
		else
		{
			$sfile = "{$this->_path}/{$this->_prefix}-{$sid}";
		}
		$sdata = json_encode($sdata); //将数据转换成JSON格式方便存储

		return file_put_contents($sfile, $sdata);
	}

	/**
	 * 清除SESSION
	 *
	 * @param string $sid 会话唯一标识
	 * @return boolean 清除成功返回true否则为false
	 */
	public function destroy($sid = '')
	{
		if (empty($sid))
		{
			$sid = $this->_sid;
		}

		$this->data = array(); //清空当前SESSION数据防止复写

		/**
		 * 以数据库方式来处理SESSION
		 */
		if ($this->_type == 'db' || $this->_type == 'mdb')
		{
			$this->_db->query("DELETE FROM {$this->_opt} WHERE sId = '{$sid}'");

			return ($this->_db->affected_rows() > 0) ? true : false;
		}

		/**
		 * 以Memcache缓冲方式来处理SESSION
		 */
		if ($this->_type == 'mem')
		{
			return $this->_mem->del('session', $sid);
		}

		/**
		 * 以文件系统的方式来处理SESSION
		 */
		if ($this->_type == 'dir')
		{
			$sfile = "{$this->_path}/{$sid[0]}/{$this->_prefix}-{$sid}";
		}
		else
		{
			$sfile = "{$this->_path}/{$this->_prefix}-{$sid}";
		}

		return file_exists($sfile) ? @unlink($sfile) : true;
	}

	/**
	 * 清除过期的SESSION
	 *
	 * @return boolean
	 */
	private function _gc()
	{
		if ($this->_type == 'db' || $this->_type == 'mdb') //以数据库方式来处理SESSION
		{
			$this->_db->query("DELETE FROM {$this->_opt} WHERE sExpires < " . time());
		}
		else if ($this->_type == 'file') //以文件系统的方式来处理SESSION
		{
			$this->_del_sfile($this->_path);
		}
		else if ($this->_type == 'dir') //以目录分层文件的方式来处理SESSION
		{
			$dir = 'abcdefghijklmnopqrstuvwxyz';
			$len = strlen($dir);
			for ($i = 0; $i < $len; ++$i)
			{
				$this->_del_sfile("{$this->_path}/{$dir[$i]}");
			}
		}

		return true;
	}

	/**
	 * 删除session文件
	 *
	 * @param string  $dir      会话文件所在目录
	 * @param boolean $no_check 是否进行过期判断
	 * @return boolean
	 */
	private function _del_sfile($dir, $no_check = false)
	{
		if ($no_check) //直接删除SESSION文件不进行过期判断
		{
			foreach (glob("{$dir}/{$this->_prefix}-*") as $filename)
			{
				@unlink($filename);
			}

			return true;
		}

		foreach (glob("{$dir}/{$this->_prefix}-*") as $filename)
		{
			if (filemtime($filename) + $this->_ltime < time())
			{
				@unlink($filename);
			}
		}

		return true;
	}

	/**
	 * 清空所有SESSION
	 *
	 * @return boolean
	 */
	public function cleanup()
	{
		switch ($this->_type)
		{
			case 'mem':
				return $this->_mem->flush();
			case 'db':
			case 'mdb':
				return $this->_db->truncate($this->_opt);
			case 'file':
				return $this->_del_sfile($this->_path, true);
			case 'dir':
				$dir = 'abcdefghijklmnopqrstuvwxyz';
				$len = strlen($dir);
				for ($i = 0; $i < $len; ++$i)
				{
					wcore_fso::rm_dir($dir[$i]);
				}
			default:
				return true;
		}
	}
}
?>