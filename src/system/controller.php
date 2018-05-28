<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: system/controller.php
 * 简述: 系统控制层处理基础库
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: controller.php 1273 2017-09-28 06:18:03Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class Controller extends modules_mem
{
	/**
	 * @var Registry 全局注册对象
	 */
	protected $registry;

	/**
	 * @var Url URL地址对象
	 */
	protected $url;

	/**
	 * @var User 后台用户对象
	 */
	protected $user;

	/**
	 * @var Config 配置对象
	 */
	protected $config;

	/**
	 * @var Request 请求对象
	 */
	protected $request;

	/**
	 * @var Response 响应对象
	 */
	protected $response;

	/**
	 * @var Language 语言对象
	 */
	protected $language;

	/**
	 * @var Currency 货币对象
	 */
	protected $currency;

	/**
	 * @var wcore_session 会话对象
	 */
	protected $session;

	/**
	 * @var Document 页面对象
	 */
	protected $document;

	/**
	 * @var Customer 客户对象
	 */
	protected $customer;

	/**
	 * @var string 模板名称
	 */
	private $_tplname;

	/**
	 * @param Registry $registry 注册对象
	 */
	public function __construct(&$registry)
	{
		parent::__construct();
		$this->registry = $registry;
		$this->url      = $registry->get('url');
		$this->user     = $registry->get('user');
		$this->config   = $registry->get('config');
		$this->request  = $registry->get('request');
		$this->session  = $registry->get('session');
		$this->response = $registry->get('response');
		$this->language = $registry->get('language');
		$this->currency = $registry->get('currency');
		$this->customer = $registry->get('customer');
		$this->document = $registry->get('document');
		$this->_tplname = $registry->get('tplname');
	}

	/**
	 * 获取注册对象
	 *
	 * @param string $key 对象名称
	 * @return object     对象值
	 */
	public function __get($key)
	{
		return $this->registry->get($key);
	}

	/**
	 * 设置注册对象
	 *
	 * @param string $key   对象名称
	 * @param object $value 对象值
	 */
	public function __set($key, $value)
	{
		$this->registry->set($key, $value);
	}

	/**
	 * 根据模板提取生成后的内容
	 *
	 * @param string $template 模板名称
	 * @param array  $vrs      模板数据
	 * @return string          HTML
	 */
	protected function &view($template, &$vrs)
	{
		/**
		 * 判断模板文件是否存在，如不存在则使用默认模板
		 */
		$tpl_file = DIR_SITE . "/view/{$this->_tplname}/{$template}";
		if (!file_exists($tpl_file))
		{
			$tpl_file = DIR_SITE . "/view/default/{$template}";
		}

		/**
		 * 判断是否为手机访问，如果是手机访问则加载对应的手机模板
		 */
		if (IS_MOBILE)
		{
			$tpl_mobi = DIR_SITE . "/view/{$this->config->get('mhecart_theme_name', 'mhecart')}/{$template}";
			$tpl_file = file_exists($tpl_mobi) ? $tpl_mobi : $tpl_file;
		}

		/**
		 * 获取访问权限与修改权限,方便模板中做权限处理
		 */
		$vrs['apermission'] = $this->config->apermission; //访问权
		$vrs['mpermission'] = $this->config->mpermission; //修改权

		/**
		 * 组合数据生成HTML
		 */
		ob_start();
		extract($vrs);
		require($tpl_file);
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * 缓存 - 控制器内容
	 *
	 * @param string $route 路由地址
	 * @param array  $args  参数
	 * @return string       执行结果
	 */
	protected function mem_ctrl($route, $args = array())
	{
		$mkey    = md5(DOMAIN_NAME . $route);
		$content = $this->mem_get($mkey);

		if (empty($content))
		{
			$content = $this->registry->exectrl($route, $args);
			$this->mem_set($mkey, $content);
		}

		return $content;
	}

	/**
	 * 获取当前路由地址数组
	 *
	 * @return array 下标0:为二级地址,下标1:为三级地址
	 */
	public function get_route()
	{
		$route    = array();
		$route[0] = $route[1] = '';

		if (isset($this->request->request['route']))
		{
			$parts = explode('/', $this->request->request['route']);
			if (count($parts) > 1)
			{
				$route[0] = "{$parts[0]}/{$parts[1]}";
				$route[1] = "{$route[0]}/" . (isset($parts[2]) ? $parts[2] : 'index');
			}
		}

		return $route;
	}
}
?>