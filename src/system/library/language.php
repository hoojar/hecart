<?php
/**
 * 网站语言处理
 */
class Language extends modules_mem
{
	/**
	 * @var int 语言编号
	 */
	public $id;

	/**
	 * @var string 语言代码
	 */
	public $code;

	/**
	 * @var string 语言所在目录
	 */
	public $directory;

	/**
	 * @var array 语言列表
	 */
	public $list = array();

	/**
	 * @var array 语言包数组
	 */
	public $data = array();

	/**
	 * 初始化语言
	 *
	 * @param string $code        系统语言代码
	 * @param string $cookie_code 用户COOKIE中的语言代码
	 * @param bool   $all         是否获取所有语言列表
	 */
	public function __construct($code, $cookie_code, $all = false)
	{
		parent::__construct();
		$sql        = 'SELECT * FROM ' . DB_PREFIX . 'language WHERE ' . ($all ? '1' : 'status = 1');
		$this->list = $this->hash_sql($sql, 'code');

		/**
		 * 判断是否用户选择了语言存在了COOKIE中
		 */
		if ($cookie_code && !empty($this->list[$cookie_code]['status']))
		{
			$code = $cookie_code;
		}
		else //自动检测语言
		{
			if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']))
			{
				foreach ($this->list as $key => $value)
				{
					if (stripos($_SERVER['HTTP_ACCEPT_LANGUAGE'], $key) !== false)
					{
						$code = $key;
					}
				}
			}
		}

		$this->code      = $code; //语言代码
		$this->id        = $this->list[$code]['language_id'];
		$this->directory = $this->list[$code]['directory'];
		$this->load($this->list[$this->code]['filename']);
	}

	/**
	 * 获取语言文字
	 *
	 * @param string $key 语言标识
	 * @return mixed 对应语言
	 */
	public function get($key)
	{
		return isset($this->data[$key]) ? $this->data[$key] : str_replace('_', ' ', $key);
	}

	/**
	 * 加载语言文件包
	 *
	 * @param string $filename 语言文件包名
	 * @return array           语言包
	 */
	public function load($filename)
	{
		/**
		 * 根据用户的选择加载语言
		 */
		$file = DIR_SITE . "/language/{$this->directory}/{$filename}.php";
		if (file_exists($file))
		{
			$_ = array();
			require($file);
			$this->data = array_merge($this->data, $_);

			return $this->data;
		}

		/**
		 * 找到需要加载的语言则加载默认语言
		 */
		$file = DIR_SITE . "/language/english/{$filename}.php";
		if (file_exists($file))
		{
			$_ = array();
			require($file);
			$this->data = array_merge($this->data, $_);

			return $this->data;
		}
	}
}
?>