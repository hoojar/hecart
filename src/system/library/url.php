<?php
/**
 * 网站URL地址处理类库
 */
class Url
{
	/**
	 * @var string 是否使用SSL的总开关
	 */
	private $_ssl = false;

	/**
	 * @var string SSL安全连接地址
	 */
	private $_surl = '';

	/**
	 * URL构造函数
	 *
	 * @param bool $ssl 是否使用SSL的总开关
	 */
	public function __construct($ssl = false)
	{
		$this->_ssl  = $ssl;
		$this->_surl = ($ssl) ? HTTPS_STORE : '/';
	}

	/**
	 * 生成URL连接地址
	 *
	 * @param string $route 路由接口名称
	 * @param string $args  要连接的参数
	 * @param bool   $ssl   是否走SSL安全连接
	 * @return string
	 */
	public function link($route, $args = '', $ssl = false)
	{
		$url = (($ssl) ? $this->_surl : '/') . $route;
		if (!empty($args))
		{
			$url .= "?{$args}";
		}

		return $url;
	}

	/**
	 * 生成完整的URL连接地址
	 *
	 * @param string $route 路由接口名称
	 * @param string $args  要连接的参数
	 * @param bool   $ssl   是否走SSL安全连接
	 * @return string
	 */
	public function flink($route, $args = '', $ssl = false)
	{
		$url = (($this->_ssl && $ssl) ? $this->_surl : HTTP_STORE) . $route;
		if (!empty($args))
		{
			$url .= "?{$args}";
		}

		return $url;
	}
}
?>