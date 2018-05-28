<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: system/registry.php
 * 简述: 全局注册通用库
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: registry.php 1273 2017-09-28 06:18:03Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
final class Registry
{
	/**
	 * @var array 注册对象组
	 */
	private $_data = array();

	/**
	 * 获取注册数据
	 *
	 * @param string $key     名称
	 * @param mixed  $default 默认值
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		return isset($this->_data[$key]) ? $this->_data[$key] : $default;
	}

	/**
	 * 设置注册数据
	 *
	 * @param $key
	 * @param $value
	 */
	public function set($key, $value)
	{
		$this->_data[$key] = $value;
	}

	/**
	 * 判断注册数据是否存在
	 *
	 * @param $key
	 * @return bool
	 */
	public function has($key)
	{
		return isset($this->_data[$key]);
	}

	/**
	 * 获取注册对象
	 *
	 * @param string $key 对象名称
	 * @return object     对象值
	 */
	public function __get($key)
	{
		return $this->get($key);
	}

	/**
	 * 设置注册对象
	 *
	 * @param string $key   对象名称
	 * @param object $value 对象值
	 */
	public function __set($key, $value)
	{
		$this->set($key, $value);
	}

	/**
	 * 跳转地址
	 *
	 * @param string $url    跳转地址
	 * @param int    $status 输出状态
	 */
	public function redirect($url, $status = 302)
	{
		header('Status: ' . $status);
		header('Location: ' . str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), $url));
		exit();
	}

	/**
	 * 加载库文件
	 *
	 * @param string $library 系统加名称
	 */
	public function library($library)
	{
		$file = DIR_ROOT . "/system/library/{$library}.php";
		if (file_exists($file))
		{
			require($file);
		}
		else
		{
			exit('Error: Could not load library ' . $library . '!');
		}
	}

	/**
	 * 加载模块
	 *
	 * @param string $model 模块名称
	 */
	public function model($model)
	{
		$class     = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', $model);
		$model_cls = 'model_' . str_replace('/', '_', $model);
		if (!is_null($this->get($model_cls)))
		{
			return;
		}

		$file = DIR_SITE . '/model/' . $model . '.php';
		if (file_exists($file))
		{
			require($file);
			$this->set($model_cls, new $class($this));
		}
		else
		{
			exit('Error: Could not load model ' . $model . '!');
		}
	}

	/**
	 * 加载配置文件
	 *
	 * @param string $config 配置文件名称
	 */
	public function config($config)
	{
		$this->config->load($config);
	}

	/**
	 * 加载语言文件
	 *
	 * @param string $language 语言文件名称
	 */
	public function language($language)
	{
		$this->language->load($language);
	}

	/**
	 * 执行CDN处理
	 *
	 * @param string $fname 文件名
	 * @param string $path  附加路径
	 * @param string $fext  文件扩展名
	 * @return string 完整URL图片地址
	 */
	function execdn($fname, $path = '/', $fext = '')
	{
		if (strpos($fname, '://') !== false)
		{
			return $fname;
		}

		if ($fname && $fname[0] == '/')
		{
			$path = '';
		}

		$base_url = HTTP_STORE;
		if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == '1'))
		{
			$base_url = HTTPS_STORE;
		}

		/**
		 * 当使用本机时，就不用CDN处理了
		 */
		if (isset($_GET['local']))
		{
			return "{$base_url}{$path}{$fname}";
		}

		if (!USE_CDN_JS2CSS || !USE_CDN_IMAGES)
		{
			if (empty($fext))
			{
				$qlen = strpos($fname, '?');
				$fext = strrchr(($qlen !== false ? substr($fname, 0, $qlen) : $fname), '.');
			}

			$fext = strtolower($fext);
			if (!USE_CDN_JS2CSS && ($fext == '.js' || $fext == '.css'))
			{
				return "{$base_url}{$path}{$fname}";
			}

			if (!USE_CDN_IMAGES && ($fext == '.jpg' || $fext == '.jpeg' || $fext == '.gif' || $fext == '.swf' || $fext == '.png'))
			{
				return "{$base_url}{$path}{$fname}";
			}
		}

		/**
		 * 分服务器加载
		 */
		static $fname_hosts = null;
		static $fname_count = null;
		if (is_null($fname_hosts))
		{
			$fname_hosts = json_decode(CDN_URLS, true);
			$fname_count = count($fname_hosts);
		}

		$key = ($fname_count > 1) ? abs(crc32($fname)) % $fname_count : 0;

		return "{$fname_hosts[$key]}{$path}{$fname}";
	}

	/**
	 * 执行控制器
	 *
	 * @param string $route 路由地址
	 * @param array  $args  参数
	 * @return string       执行结果
	 */
	public function &exectrl($route, $args = array())
	{
		$parts    = explode('/', $route);
		$cls_file = (count($parts) > 1) ? (DIR_SITE . "/controller/{$parts[0]}/{$parts[1]}.php") : '';
		if ($cls_file && file_exists($cls_file))
		{
			require_once($cls_file);
			$cls_name   = 'Controller' . preg_replace('/[^a-zA-Z0-9]/', '', "{$parts[0]}{$parts[1]}");
			$controller = new $cls_name($this);
			$cls_act    = isset($parts[2]) ? $parts[2] : 'index';
			if (method_exists($controller, $cls_act))
			{
				$result = $controller->{$cls_act}($args);
			}
			else
			{
				$result = $this->exectrl('error/not_found');
			}
		}
		else
		{
			$result = $this->exectrl('error/not_found');
		}

		return $result;
	}
}
?>