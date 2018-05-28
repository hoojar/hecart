<?php
/**
 * 请求数据类库
 */
class Request
{
	public $get = array();

	public $post = array();

	public $cookie = array();

	public $files = array();

	public $server = array();

	public function __construct()
	{
		$this->get     = &$_GET;
		$this->post    = &$_POST;
		$this->request = &$_REQUEST;
		$this->cookie  = &$_COOKIE;
		$this->files   = &$_FILES;
		$this->server  = &$_SERVER;
	}

	/**
	 * 安全获取变量
	 *
	 * @param string  $ob      为要取的数据名字
	 * @param string  $type    为要取的是什么数据类型 (i=整形, d=整形, f=浮点, s=字符, c=字符, b=布尔, a=数组, o=对象)
	 * @param string  $gpcs    为是取外部变量还是 get post request cookie session，或要取session变量则设置成s，取cookie则设置为c
	 * @param mixed   $default 当取不到数据时则将数据设置成默认值
	 * @param integer $length  如果是字符串则截取指定的长度
	 * @return mixed
	 */
	public function get_var($ob, $type = 'string', $gpcs = 'request', $default = null, $length = 0)
	{
		if (empty($ob))
		{
			return $default;
		}

		/**
		 * 从GET、POST、COOKIE、SESSION、REQUEST对象中获取数据
		 */
		switch (strtolower($gpcs))
		{
			case 'g':
			case 'get':
				$value = isset($this->get[$ob]) ? $this->get[$ob] : $default;
				break;
			case 'p':
			case 'post':
				$value = isset($this->post[$ob]) ? $this->post[$ob] : $default;
				break;
			case 'c':
			case 'cookie':
				$value = isset($this->cookie[$ob]) ? $this->cookie[$ob] : $default;
				break;
			case 's':
			case 'session':
				$value = isset($_SESSION[$ob]) ? $_SESSION[$ob] : $default;
				break;
			default:
				$value = isset($this->request[$ob]) ? $this->request[$ob] : $default;
				break;
		}
		$value = empty($value) ? $default : $value; //0非常的特殊，使用empty去判断0的结果为true

		/**
		 * 转换成指定的数据类型
		 */
		switch (strtolower($type))
		{
			case '': //字符类型
			case 'c':
			case 's':
			case 'char':
			case 'string':
				return (settype($value, 'string')) ? (($length === 0) ? $value : mb_strcut($value, 0, intval($length))) : '';
			case 'f': //浮点类型
			case 'd':
			case 'float':
			case 'double':
				return (settype($value, 'float')) ? $value : 0.0;
			case 'i': //整数类型
			case 'int':
			case 'integer':
				return (settype($value, 'integer')) ? $value : 0;
			case 'b': //布尔类型
			case 'bool':
			case 'boolean':
				return (settype($value, 'boolean')) ? $value : false;
			case 'a': //数组类型
			case 'array':
				return (settype($value, 'array')) ? $value : array();
			case 'o': //对象类型
			case 'object':
				return (settype($value, 'object')) ? $value : null;
			default:
				return $default;
		}
	}
}
?>