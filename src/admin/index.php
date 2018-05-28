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
if (true) //此处加速适合于多语言多货币
{
	$_GET['islogged'] = 0;
	$_GET['ismobile'] = IS_MOBILE;
	$_GET['language'] = isset($_COOKIE['language']) ? $_COOKIE['language'] : '';
	$_GET['currency'] = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : '';
	if ($token = (isset($_COOKIE['token']) ? $_COOKIE['token'] : ''))
	{
		$_GET['islogged'] = ($token == security_token(SITE_MD5_KEY)) ? 1 : 0;
	}
	$speed = new wcore_speed('mem');
	unset($_GET['islogged'], $_GET['ismobile'], $_GET['language'], $_GET['currency'], $token);
}
else //此处加速仅适应于单语言单货币
{
	$puid  = ($_SERVER["REQUEST_URI"] == '/' || $_SERVER["REQUEST_URI"] == $_SERVER["SCRIPT_NAME"]) ? 'index.html' : $_SERVER["REQUEST_URI"];
	$speed = new wcore_speed(((strpos($puid, '?') === false) ? 'file' : 'mem'), 0, $puid);
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
require(DIR_ROOT . '/system/library/user.php');
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
$registry->language = new Language($registry->config->get('config_admin_language'), $registry->request->get_var('language', 's', 'c'), true);//注册全局与创建语言对象
$registry->config->set('config_language_id', $registry->language->id);
$registry->user       = new User($registry);//注册全局与创建后台用户对象
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
 * 后台登录、权限、使用字符编号、输出是压缩等级处理
 */
$registry->exectrl('common/safecheck/login');//登录检测
$registry->exectrl('common/safecheck/permission');//权限检测
$registry->response->addHeader('Content-Type: text/html; charset=utf-8');
$registry->response->setCompression($registry->config->get('config_compression'));

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