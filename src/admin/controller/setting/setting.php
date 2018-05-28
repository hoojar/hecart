<?php
class ControllerSettingSetting extends Controller
{
	private $error = array();

	public function index()
	{
		$this->registry->language('setting/setting');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate())
		{
			$this->model_setting_setting->editSetting('config', $this->request->post);
			if ($this->config->get('config_currency_auto'))
			{
				$this->registry->model('setting/currency');
				$this->model_setting_currency->updateCurrencies();
			}

			$this->session->data['success'] = $this->language->get('text_success');
			$this->registry->redirect($this->url->link('setting/setting'));
		}

		$lang_arr = array(
			'heading_title',
			'text_select',
			'text_none',
			'text_yes',
			'text_no',
			'text_items',
			'text_product',
			'text_voucher',
			'text_tax',
			'text_account',
			'text_browse',
			'text_clear',
			'text_payment',
			'text_mail',
			'text_smtp',
			'entry_name',
			'entry_owner',
			'entry_address',
			'entry_email',
			'entry_telephone',
			'entry_fax',
			'entry_title',
			'entry_meta_description',
			'entry_template',
			'entry_country',
			'entry_zone',
			'entry_language',
			'entry_admin_language',
			'entry_currency',
			'entry_currency_auto',
			'entry_length_class',
			'entry_weight_class',
			'entry_catalog_limit',
			'entry_admin_limit',
			'entry_account',
			'entry_logo',
			'entry_icon',
			'entry_mail_protocol',
			'entry_mail_parameter',
			'entry_smtp_host',
			'entry_smtp_username',
			'entry_smtp_password',
			'entry_smtp_port',
			'entry_smtp_timeout',
			'entry_use_ssl',
			'entry_file_mime_allowed',
			'entry_file_extension_allowed',
			'entry_maintenance',
			'entry_encryption',
			'entry_login_count_max',
			'entry_login_locked_hours',
			'entry_compression',
			'entry_error_display',
			'entry_error_log',
			'entry_error_filename',
			'entry_google_analytics',
			'button_save',
			'button_cancel',
			'tab_general',
			'tab_store',
			'tab_local',
			'tab_option',
			'tab_image',
			'tab_mail',
			'tab_server'
		);
		foreach ($lang_arr as $v)
		{
			$vrs[$v] = $this->language->get($v);
		}
		unset($lang_arr);

		$err_arr = array(
			'warning',
			'name',
			'owner',
			'address',
			'email',
			'telephone',
			'title',
			'error_filename',
			'catalog_limit',
			'admin_limit',
		);
		foreach ($err_arr as $v)
		{
			$vrs["error_{$v}"] = isset($this->error[$v]) ? $this->error[$v] : '';
		}

		/**
		 * 导航栏组合
		 */
		$vrs['breadcrumbs']   = array();
		$vrs['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
			'separator' => false
		);

		$vrs['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('setting/setting'),
			'separator' => ' :: '
		);

		$vrs['success'] = '';
		if (isset($this->session->data['success']))
		{
			$vrs['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}

		$vrs['action'] = $this->url->link('setting/setting');
		$vrs['cancel'] = $this->url->link('setting/store');
		$data_arr      = array(
			'config_name',
			'config_owner',
			'config_address',
			'config_email',
			'config_telephone',
			'config_fax',
			'config_title',
			'config_meta_description',
			'config_layout_id',
			'config_template',
			'config_language',
			'config_admin_language',
			'config_catalog_limit',
			'config_admin_limit',
			'config_currency',
			'config_currency_auto',
			'config_logo',
			'config_icon',
			'config_mail_protocol',
			'config_mail_parameter',
			'config_smtp_host',
			'config_smtp_username',
			'config_smtp_password',
			'config_use_ssl',
			'config_file_mime_allowed',
			'config_file_extension_allowed',
			'config_maintenance',
			'config_encryption',
			'config_compression',
			'config_error_display',
			'config_error_log',
			'config_error_filename',
			'config_google_analytics'
		);
		foreach ($data_arr as $v)
		{
			$vrs[$v] = isset($this->request->post[$v]) ? $this->request->post[$v] : $this->config->get($v);
		}

		$vrs['templates'] = array();
		$directories      = glob(DIR_ROOT . '/www/view/*', GLOB_ONLYDIR);
		foreach ($directories as $directory)
		{
			$vrs['templates'][] = basename($directory);
		}

		$this->registry->model('setting/language');
		$vrs['languages'] = $this->model_setting_language->getLanguages();

		$this->registry->model('setting/currency');
		$vrs['currencies'] = $this->model_setting_currency->getCurrencies();

		if (isset($this->request->post['config_login_count_max']))
		{
			$vrs['config_login_count_max'] = $this->request->post['config_login_count_max'];
		}
		elseif ($this->config->get('config_login_count_max'))
		{
			$vrs['config_login_count_max'] = $this->config->get('config_login_count_max');
		}
		else
		{
			$vrs['config_login_count_max'] = 3;
		}

		if (isset($this->request->post['config_login_locked_hours']))
		{
			$vrs['config_login_locked_hours'] = $this->request->post['config_login_locked_hours'];
		}
		elseif ($this->config->get('config_login_locked_hours'))
		{
			$vrs['config_login_locked_hours'] = $this->config->get('config_login_locked_hours');
		}
		else
		{
			$vrs['config_login_locked_hours'] = 8;
		}

		if (isset($this->request->post['config_commission']))
		{
			$vrs['config_commission'] = $this->request->post['config_commission'];
		}
		elseif ($this->config->has('config_commission'))
		{
			$vrs['config_commission'] = $this->config->get('config_commission');
		}
		else
		{
			$vrs['config_commission'] = '5.00';
		}

		$this->registry->model('tool/image');
		if ($this->config->get('config_logo') && file_exists(DIR_IMAGE . $this->config->get('config_logo')) && is_file(DIR_IMAGE . $this->config->get('config_logo')))
		{
			$vrs['logo'] = $this->model_tool_image->resize($this->config->get('config_logo'), 100, 100);
		}
		else
		{
			$vrs['logo'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);
		}

		if ($this->config->get('config_icon') && file_exists(DIR_IMAGE . $this->config->get('config_icon')) && is_file(DIR_IMAGE . $this->config->get('config_icon')))
		{
			$vrs['icon'] = $this->model_tool_image->resize($this->config->get('config_icon'), 100, 100);
		}
		else
		{
			$vrs['icon'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);
		}

		$vrs['no_image'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);

		if (isset($this->request->post['config_smtp_port']))
		{
			$vrs['config_smtp_port'] = $this->request->post['config_smtp_port'];
		}
		elseif ($this->config->get('config_smtp_port'))
		{
			$vrs['config_smtp_port'] = $this->config->get('config_smtp_port');
		}
		else
		{
			$vrs['config_smtp_port'] = 25;
		}

		if (isset($this->request->post['config_smtp_timeout']))
		{
			$vrs['config_smtp_timeout'] = $this->request->post['config_smtp_timeout'];
		}
		elseif ($this->config->get('config_smtp_timeout'))
		{
			$vrs['config_smtp_timeout'] = $this->config->get('config_smtp_timeout');
		}
		else
		{
			$vrs['config_smtp_timeout'] = 5;
		}

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/setting/setting.tpl', $vrs);
	}

	private function validate()
	{
		if (!$this->user->hasPermission('modify', 'setting/setting'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['config_name'])
		{
			$this->error['name'] = $this->language->get('error_name');
		}

		if ((mb_strlen($this->request->post['config_owner']) < 3) || (mb_strlen($this->request->post['config_owner']) > 64))
		{
			$this->error['owner'] = $this->language->get('error_owner');
		}

		if ((mb_strlen($this->request->post['config_address']) < 3) || (mb_strlen($this->request->post['config_address']) > 256))
		{
			$this->error['address'] = $this->language->get('error_address');
		}

		if ((mb_strlen($this->request->post['config_email']) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['config_email']))
		{
			$this->error['email'] = $this->language->get('error_email');
		}

		if ((mb_strlen($this->request->post['config_telephone']) < 3) || (mb_strlen($this->request->post['config_telephone']) > 32))
		{
			$this->error['telephone'] = $this->language->get('error_telephone');
		}

		if (!$this->request->post['config_title'])
		{
			$this->error['title'] = $this->language->get('error_title');
		}

		if (!$this->request->post['config_error_filename'])
		{
			$this->error['error_filename'] = $this->language->get('error_error_filename');
		}

		if (!$this->request->post['config_admin_limit'])
		{
			$this->error['admin_limit'] = $this->language->get('error_limit');
		}

		if (!$this->request->post['config_catalog_limit'])
		{
			$this->error['catalog_limit'] = $this->language->get('error_limit');
		}

		if ($this->error && !isset($this->error['warning']))
		{
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	public function template()
	{
		if (file_exists(DIR_IMAGE . 'themes/' . basename($this->request->get['template']) . '.png'))
		{
			$image = $this->registry->execdn('themes/' . basename($this->request->get['template']) . '.png', IMAGES_PATH);
		}
		else
		{
			$image = $this->registry->execdn('no_image.jpg', IMAGES_PATH);
		}

		return ('<img src="' . $image . '" />');
	}
}
?>