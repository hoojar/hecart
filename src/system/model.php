<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: system/model.php
 * 简述: 数据库模块处理基础库
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: model.php 1273 2017-09-28 06:18:03Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class Model extends modules_mem
{
	/**
	 * @var Url URL对象
	 */
	protected $url;

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
	 * @var Registry 全局对象
	 */
	protected $registry;

	/**
	 * @var int 系统语言编号
	 */
	public $language_id = 1;

	/**
	 * 构造函数
	 *
	 * @param $registry Registry
	 */
	public function __construct($registry)
	{
		parent::__construct();
		$this->registry    = $registry;
		$this->url         = $registry->get('url');
		$this->config      = $registry->get('config');
		$this->request     = $registry->get('request');
		$this->response    = $registry->get('response');
		$this->session     = $registry->get('session');
		$this->customer    = $registry->get('customer');
		$this->language    = $registry->get('language');
		$this->currency    = $registry->get('currency');
		$this->document    = $registry->get('document');
		$this->language_id = intval($this->config->get('config_language_id'));
	}

	/**
	 * 从全局中获取数据
	 *
	 * @param string $key 数据名称
	 * @return mixed 数据值
	 */
	public function __get($key)
	{
		return $this->registry->get($key);
	}

	/**
	 * 设置一个全局数据
	 *
	 * @param string $key   数据名称
	 * @param mixed  $value 数据值
	 */
	public function __set($key, $value)
	{
		$this->registry->set($key, $value);
	}

	/**
	 * 获取散列值与散列后密码
	 *
	 * @param string $password 用户密码
	 * @return array salt|pswd
	 */
	public function salt2pwd($password)
	{
		$salt = substr($password, 0, 9);
		$pswd = md5(md5($salt) . $password);

		return array(
			'salt' => $salt,
			'pwd'  => $pswd
		);
	}

	/**
	 * 用户姓名组合
	 *
	 * @param string $as 表别名
	 * @return string 组合后的姓名
	 */
	public function fullname($as = '')
	{
		/**
		 * 判断姓是否为汉字,中国人的姓是在前面,其他国家的是姓在后
		 */
		return "IF({$as}lastname != '',
					IF(LENGTH({$as}lastname)=CHAR_LENGTH({$as}lastname),
						CONCAT({$as}firstname, ' ', {$as}lastname),
						CONCAT({$as}lastname,{$as}firstname)),
					{$as}firstname) ";
	}

	/**
	 * 收货人姓名组合
	 *
	 * @param string $as 表别名
	 * @return string 组合后的姓名
	 */
	public function consignee($as = '')
	{
		/**
		 * 判断姓是否为汉字,中国人的姓是在前面,其他国家的是姓在后
		 */
		return "IF({$as}shipping_lastname != '',
					IF(LENGTH({$as}shipping_lastname)=CHAR_LENGTH({$as}shipping_lastname),
						CONCAT({$as}shipping_firstname, ' ', {$as}shipping_lastname),
						CONCAT({$as}shipping_lastname,{$as}shipping_firstname)),
					{$as}shipping_firstname) ";
	}
}
?>