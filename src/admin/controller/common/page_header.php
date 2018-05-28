<?php
class ControllerCommonPageHeader extends Controller
{
	public function index()
	{
		$user_group_id = empty($this->session->data['user_group_id']) ? 0 : $this->session->data['user_group_id'];
		$mkey          = "admin-header-g{$user_group_id}";
		$content       = $this->mem_get($mkey);
		if ($user_group_id && !empty($content))
		{
			return $content;
		}

		$vrs['title']       = $this->document->title();
		$vrs['keywords']    = $this->document->keywords();
		$vrs['description'] = $this->document->description();
		$vrs['links']       = $this->document->links();
		$vrs['styles']      = $this->document->styles();
		$vrs['scripts']     = $this->document->scripts();
		$vrs['lang']        = $this->language->get('code');
		$vrs['route']       = isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home';

		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1')))
		{
			$vrs['base'] = 'https://' . DOMAIN_NAME . '/';
		}
		else
		{
			$vrs['base'] = 'http://' . DOMAIN_NAME . '/';
		}

		/**
		 * 系统菜单语言
		 */
		$this->registry->language('common/header');
		$lang_arr = array(
			'direction',
			'heading_title',
			'text_front',
			'text_confirm',
			'text_nochecked',
			'text_country',
			'text_currency',
			'text_customer',
			'text_customer_group',
			'text_customer_export',
			'text_customer_blacklist',
			'text_error_log',
			'text_dashboard',
			'text_information_group',
			'text_information',
			'text_language',
			'text_setting',
			'text_logout',
			'text_backup',
			'text_oper_mem',
			'text_setting',
			'text_system',
			'text_users',
			'text_user',
			'text_user_org',
			'text_user_group',
			'text_user_profile',
		);
		foreach ($lang_arr as $v)
		{
			$vrs[$v] = $this->language->get($v);
		}

		/**
		 * 系统菜单地址
		 */
		$stoken = isset($this->session->data['token']) ? $this->session->data['token'] : '';
		$ctoken = isset($this->request->cookie['token']) ? $this->request->cookie['token'] : '';
		if (!$this->user->isLogged() || $ctoken != $stoken || $ctoken != security_token(SITE_MD5_KEY))
		{
			$vrs['logged'] = '';
			$vrs['home']   = 'common/login';
		}
		else
		{
			$vrs['home']               = 'common/home';
			$vrs['information']        = 'common/information';
			$vrs['information_group']  = 'common/information_group';
			$vrs['country']            = 'setting/country';
			$vrs['currency']           = 'setting/currency';
			$vrs['backup']             = 'tool/backup';
			$vrs['oper_mem']           = 'tool/memcache';
			$vrs['customer']           = 'sale/customer';
			$vrs['customer_group']     = 'sale/customer_group';
			$vrs['customer_export']    = 'sale/customer_export';
			$vrs['customer_blacklist'] = 'sale/customer_blacklist';
			$vrs['error_log']          = 'tool/error_log';
			$vrs['geo_zone']           = 'setting/geo_zone';
			$vrs['language']           = 'setting/language';
			$vrs['logout']             = 'common/logout';
			$vrs['setting']            = 'setting/setting';
			$vrs['user']               = 'user/user';
			$vrs['user_org']           = 'user/org';
			$vrs['user_group']         = 'user/group';
			$vrs['user_profile']       = 'user/profile';
			$vrs['zone']               = 'setting/zone';
			$vrs['logged']             = sprintf($this->language->get('text_logged'), $this->user->getUserName());
		}

		/**
		 * 系统菜单数组
		 */
		$vrs['menus'] = array(
			'information' => array(
				'information',
				'information_group'
			),
			'customer'    => array(
				'customer',
				'customer_group',
				'customer_blacklist'
			),
			'users'       => array(
				'user',
				'user_org',
				'user_group'
			),
			'system'      => array(
				'setting',
				'language',
				'currency',
				'country',
				'zone',
				'geo_zone',
				'error_log',
				'backup',
				'oper_mem'
			),
		);

		$content = $this->view('template/header.tpl', $vrs);
		$this->mem_set($mkey, $content);

		return $content;
	}
}
?>