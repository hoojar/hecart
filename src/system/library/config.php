<?php
/**
 * 网站系统配置处理类库
 */
class Config extends modules_mem
{
	/**
	 * @var array 配置数据组
	 */
	private $data = array();

	/**
	 * @var bool 是否拥有访问权限
	 */
	public $apermission = false;

	/**
	 * @var bool 是否拥有更改权限
	 */
	public $mpermission = false;

	/**
	 * 从数据库中获取网站列表数据并格式化以域名为数组KEY
	 */
	public function __construct()
	{
		parent::__construct();
		$res = $this->mem_sql('SELECT * FROM ' . DB_PREFIX . 'setting', DB_GET_ALL);
		foreach ($res as $setting)//设置系统参数到配置数据组中
		{
			$this->set($setting['key'], $setting['serialized'] ? unserialize($setting['value']) : $setting['value']);
		}
	}

	/**
	 * 获取配置
	 *
	 * @param string $key     配置名
	 * @param mixed  $default 无值时的默认值
	 * @return mixed 配置值
	 */
	public function get($key, $default = null)
	{
		return isset($this->data[$key]) ? $this->data[$key] : $default;
	}

	/**
	 * 设置配置
	 *
	 * @param string $key   配置名
	 * @param mixed  $value 配置值
	 */
	public function set($key, $value)
	{
		$this->data[$key] = $value;
	}

	/**
	 * 根据配置名检测是有配置
	 *
	 * @param string $key 配置名
	 * @return bool
	 */
	public function has($key)
	{
		return isset($this->data[$key]);
	}

	/**
	 * 加载配置文件
	 *
	 * @param string $filename 配置文件
	 */
	public function load($filename)
	{
		$file = DIR_ROOT . "/system/config/{$filename}.php";
		if (file_exists($file))
		{
			$_ = array();
			require($file);
			$this->data = array_merge($this->data, $_);
		}
		else
		{
			exit('Error: Could not load config file: /system/config/' . $filename . '.php');
		}
	}
}
?>