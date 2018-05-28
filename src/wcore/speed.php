<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/speed.php
 * 简述: 生成静态文件或缓冲MEMCACHED
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: speed.php 1150 2017-07-24 02:10:33Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_speed
{
	/**
	 * 采用哪种类型加速
	 *
	 * @var string {file:文件类型 mem:Memcached}
	 */
	private $_type = 'file';

	/**
	 * 生成哪个类型的文件
	 *
	 * @var string 此参数只有type=file才生效
	 */
	private $_ext = '.html';

	/**
	 * 缓冲在哪个目录当中
	 *
	 * @var string 如果type=file此处才生效
	 */
	private $_floder = '';

	/**
	 * 过滤哪些GET字段
	 *
	 * @var string 需要过滤的内容
	 */
	private $_filter = ',nocache,error,local,';

	/**
	 * MEM缓冲对象
	 *
	 * @var wcore_mem MEM缓冲对象
	 */
	private $_mem = null;

	/**
	 * 当前页面唯一编号 page unique id
	 *
	 * @var string 如果puid为空则代表不启动加速服务
	 */
	private $_puid = '';

	/**
	 * 加速数据有效期(单位分钟)
	 *
	 * @var int 默认为10分钟
	 */
	private $_expire = 10;

	/**
	 * 构造函数
	 *
	 * @param string $type   采用哪种类型加速{file:文件类型 mem:Memcached}
	 * @param int    $expire 加速数据有效期(单位分钟)默认为0是为了采用全局设置的有效期
	 * @param string $puid   缓冲时的唯一编号
	 * @param string $floder 如果type=file生效，缓冲在哪个目录当中
	 * @param string $ext    如果type=file生效，生成哪个类型的文件
	 */
	public function __construct($type = 'file', $expire = 0, $puid = '', $floder = '', $ext = '.html')
	{
		/**
		 * 判断是否启动加速服务,当有POST数据时则不加速内容或者是否设定了启动加速内容常量且为真
		 */
		if (!empty($_POST) || (defined('SPEED_DATA') && !SPEED_DATA))
		{
			return;
		}

		/**
		 * 初始化相关数据
		 */
		$this->_ext    = empty($ext) ? $this->_ext : $ext;
		$this->_type   = (strtolower($type) == 'mem') ? 'mem' : 'file';
		$this->_floder = empty($floder) ? $_SERVER['DOCUMENT_ROOT'] : $floder;
		$this->_expire = ($expire > 0) ? $expire : (defined('SPEED_DATA_EXPIRE') ? SPEED_DATA_EXPIRE : 10);
		$this->_puid   = !empty($puid) ? $puid : $this->generate_puid(); //缓冲时的唯一编号

		/**
		 * 判断采用哪种媒介存储,如果是采用MEM就创建MEM对象
		 */
		if ($this->_type == 'mem')
		{
			$this->_mem = (MEM_USTYPE == 'redis') ? wcore_object::mds() : wcore_object::mem();
		}
	}

	/**
	 * 增加需要过滤的GET字段
	 *
	 * @param string $str 需要过滤的GET字段
	 * @return bool        增加成功返回true失败为false
	 */
	public function add_filter($str)
	{
		if (empty($str))
		{
			return false;
		}
		$this->_filter .= "{$str},";

		return true;
	}

	/**
	 * 产生唯一编号若type类型为mem则在编号上加域名MD5
	 *
	 * @return string 唯一编号
	 */
	public function generate_puid()
	{
		/**
		 * 判断由GET数据组合而成的puid是否有数据,若没有的话就只以执行文件名编号
		 */
		if (!empty($_GET))
		{
			ksort($_GET); //对GET数组的KEY排序,尽量操持puid因GET数据前后而不同
			$puid = strtok($_SERVER['SCRIPT_NAME'], '.') . '/' . md5(var_export($_GET, true));
		}
		else
		{
			$puid = dirname($_SERVER['SCRIPT_NAME']) . strtok(basename($_SERVER['SCRIPT_NAME']), '.');
		}

		/**
		 * 根据存储类型与域名生成编号
		 */
		if ($this->_type == 'mem')
		{
			$domain      = defined(DOMAIN_NAME) ? DOMAIN_NAME : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''); //主机域名
			$this->_puid = md5("{$domain}{$puid}");
		}
		else
		{
			$this->_puid = "{$puid}{$this->_ext}";
		}
	}

	/**
	 * 获取先前加速已存储好的数据
	 *
	 * @return string 加速的内容
	 */
	public function &get_data()
	{
		$html = ''; //要返回的数据
		if (isset($_GET['nocache']) || empty($this->_puid) || !empty($_POST))
		{
			return $html;
		}

		/**
		 * 采用Memcached加速获取
		 */
		if ($this->_type == 'mem')
		{
			return $this->_mem->get('SPEED-DATA', $this->_puid);
		}

		/**
		 * 采用文件系统加速获取
		 */
		$filename = "{$this->_floder}/{$this->_puid}";
		if (file_exists($filename))
		{
			$lcft = filemtime($filename); //last create file time 上次创建时间
			if (($lcft + ($this->_expire * 60)) < time()) //加速内容过期了
			{
				return $html;
			}
			$html = file_get_contents($filename); //读取加速文件内容
		}

		return $html;
	}

	/**
	 * 存储要加速的数据到指定的媒介
	 *
	 * @param string $html 要存储的HTML内容
	 * @return bool        是否存储成功
	 */
	public function set_data(&$html = '')
	{
		if (empty($this->_puid))
		{
			return false;
		}

		if (empty($html))
		{
			$html = ob_get_flush();
		}

		/**
		 * 采用Memcached加速存储
		 */
		if ($this->_type == 'mem')
		{
			return $this->_mem->set('SPEED-DATA', $this->_puid, $html, $this->_expire);
		}

		/**
		 * 采用文件系统加速存储
		 */
		$filename = "{$this->_floder}/{$this->_puid}";
		$this->make_dir(dirname($filename));
		file_put_contents($filename, $html);

		return true;
	}

	/**
	 * 自动创建目录,可递归创建
	 *
	 * @param    string $path 要创建的目录地址
	 * @return    boolean    创建成功返回true失败为false
	 */
	public function make_dir($path)
	{
		if (empty($path))
		{
			return false;
		}

		if (!file_exists($path))
		{
			$this->make_dir(dirname($path));
			@mkdir($path, 0777);
		}

		return true;
	}
}
?>