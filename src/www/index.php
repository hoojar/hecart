<?php
/**
 * 初始化系统配置
 */
define('VERSION', '1.1.3');//Version
define('DIR_SITE', empty($_SERVER['DOCUMENT_ROOT']) ? dirname(__FILE__) : $_SERVER['DOCUMENT_ROOT']);
define('DIR_ROOT', empty($_SERVER['DOCUMENT_ROOT']) ? dirname(dirname(__FILE__)) : dirname($_SERVER['DOCUMENT_ROOT']));
require(DIR_ROOT . '/config/start.php');//loading start for here
define('IS_MOBILE', is_mobile());//Determine whether for mobile access

/**
 * 缓存与静态文件
 */
if (true)
{
	//此处加速适合于多语言多货币
	$_GET['ismobile'] = IS_MOBILE;
	$_GET['language'] = isset($_COOKIE['language']) ? $_COOKIE['language'] : '';
	$_GET['currency'] = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : '';
	$speed            = new wcore_speed('mem');
	unset($_GET['ismobile'], $_GET['language'], $_GET['currency']);
}
else
{
	//此处加速仅适应于单语言单货币
	$puid  = ($_SERVER["REQUEST_URI"] == '/' || $_SERVER["REQUEST_URI"] == $_SERVER["SCRIPT_NAME"]) ? 'index.html' : $_SERVER["REQUEST_URI"];
	$speed = new wcore_speed(((strpos($puid, '?') === false) ? 'file' : 'mem'), 0, $puid);
}

/**
 * 记录订单来源域名
 */
$ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
if (!empty($ref) && stripos($ref, $_SERVER['HTTP_HOST']) === false)
{
	$out = wcore_utils::parse_url2($ref);//分析URL地址获取相关数据
	wcore_utils::set_cookie('ref', (isset($out['host']) ? $out['host'] : ''), 30);
	unset($ref, $out);
}

/**
 * 从缓存中获取数据
 */
$html = $speed->get_data();
if (!empty($html))
{
	exit($html);
}

/**
 * 加载系统所需类库
 */
require(DIR_ROOT . '/system/startup.php');
require(DIR_ROOT . '/system/library/customer.php');
require(DIR_ROOT . '/system/library/currency.php');

/**
 * 注册全局对象
 */
$registry           = new Registry();
$registry->config   = new Config();//注册全局与创建配置对象
$registry->request  = new Request();//注册全局与创建请求对象
$registry->response = new Response();//注册全局与创建响应对象
$registry->document = new Document();//注册全局与创建页面对象
$registry->session  = new wcore_session(SESSION_SAVE_TYPE);//注册全局与创建会话对象
$registry->language = new Language($registry->config->get('config_language'), $registry->request->get_var('language', 's', 'c'));//注册全局与创建语言对象
$registry->config->set('config_language', $registry->language->code);//设置网站语言代号
$registry->config->set('config_language_id', $registry->language->id);//设置网站语言编号
$registry->customer   = new Customer($registry);//Customer
$registry->currency   = new Currency($registry);//注册全局与创建货币对象
$registry->url        = new Url($registry->config->get('config_use_ssl'));//注册全局与创建URL对象
$registry->log        = new Log($registry->config->get('config_error_filename'));//注册全局与创建日志对象
$registry->encryption = new wcore_encryption($registry->config->get('config_encryption'));//注册全局与创建加密对象

/**
 * 接管错误处理
 */
set_error_handler(function ($errno, $errstr, $errfile, $errline)
{
	switch ($errno)
	{
		case E_NOTICE:
		case E_USER_NOTICE:
			$error = 'Notice';
			break;
		case E_WARNING:
		case E_USER_WARNING:
			$error = 'Warning';
			break;
		case E_ERROR:
		case E_USER_ERROR:
			$error = 'Fatal Error';
			break;
		default:
			$error = 'Unknown';
			break;
	}

	if ($GLOBALS['registry']->config->get('config_error_display'))
	{
		echo '<b>' . $error . '</b>: ' . $errstr . ' in <b>' . $errfile . '</b> on line <b>' . $errline . '</b>';
	}

	if ($GLOBALS['registry']->config->get('config_error_log'))
	{
		$GLOBALS['registry']->log->phplog('PHP ' . $error . ':  ' . $errstr . ' in ' . $errfile . ' on line ' . $errline);
	}
});

/**
 * 设置网站语言、使用的模板、字符集、输出页面内容压缩等级
 */
wcore_utils::set_cookie('language', $registry->language->code, 365);
$registry->tplname                     = $registry->config->get('config_template');
$registry->request->cookie['language'] = $registry->language->code;
$registry->response->addHeader('Content-Type: text/html; charset=utf-8');
$registry->response->setCompression($registry->config->get('config_compression'));

/**
 * 判断网站是否正在维护中
 */
if ($registry->config->get('config_maintenance'))
{
	if ($mhtml = $registry->exectrl('common/maintenance'))
	{
		exit($mhtml); //如果维护模式返回数据则显示并退出
	}
}

/**
 * 执行路由处理
 */
if (empty($registry->request->request['route']))
{
	$registry->request->request['route'] = 'common/home';
}
$html = $registry->exectrl($registry->request->request['route']);

/**
 * 判断页面是否启用了高速缓存处理
 */
if (defined('WCORE_SPEED'))
{
	$speed->set_data($html);
	unset($speed);
}

/**
 * 输出处理后的页面内容
 */
$registry->response->output($html);
?>