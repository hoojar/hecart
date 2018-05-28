<?php
class ControllerAccountRegister extends Controller
{
	private $error = array();

	public function index()
	{
		define('WCORE_SPEED', true); //允许缓冲页面
		if ($this->customer->isLogged())
		{
			$this->registry->redirect($this->url->link('account/account', '', true));
		}

		$this->registry->language('account/register');
		$this->registry->model('account/customer');
		$this->document->title($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate())
		{
			$this->model_account_customer->add($this->request->post);
			$this->customer->login($this->request->post['email'], $this->request->post['password'], true);
			unset($this->session->data['guest']);
			$this->registry->redirect($this->url->link('account/success'));
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
			'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', true),
			'separator' => $this->language->get('text_separator')
		);
		$vrs['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_register'),
			'href'      => $this->url->link('account/register', '', true),
			'separator' => $this->language->get('text_separator')
		);

		$vrs['text_account_already'] = sprintf($this->language->get('text_account_already'), $this->url->link('account/login', '', true));
		$lang_arr                    = array(
			'heading_title',
			'text_your_details',
			'text_your_address',
			'text_your_password',
			'text_newsletter',
			'text_yes',
			'text_no',
			'text_select',
			'text_none',
			'entry_firstname',
			'entry_lastname',
			'entry_email',
			'entry_telephone',
			'entry_fax',
			'entry_company',
			'entry_customer_group',
			'entry_company_id',
			'entry_tax_id',
			'entry_address_1',
			'entry_address_2',
			'entry_postcode',
			'entry_city',
			'entry_country',
			'entry_zone',
			'entry_newsletter',
			'entry_password',
			'entry_confirm',
			'entry_captcha',
			'button_back',
			'button_continue'
		);
		foreach ($lang_arr as $v)
		{
			$vrs[$v] = $this->language->get($v);
		}

		$err_arr = array(
			'warning',
			'firstname',
			'lastname',
			'email',
			'telephone',
			'password',
			'confirm',
			'captcha',
			'company_id',
			'tax_id',
			'address_1',
			'city',
			'postcode',
			'country',
			'zone'
		);
		foreach ($err_arr as $e)
		{
			$vrs['error_' . $e] = isset($this->error[$e]) ? $this->error[$e] : '';
		}

		$vrs['action']    = $this->url->link('account/register', '', true);
		$vrs['firstname'] = $this->request->get_var('firstname');
		$vrs['lastname']  = $this->request->get_var('lastname');
		$vrs['email']     = strtolower(trim($this->request->get_var('email')));
		$vrs['telephone'] = trim($this->request->get_var('telephone'));
		$vrs['fax']       = $this->request->get_var('fax');
		$vrs['company']   = $this->request->get_var('company');

		$vrs['customer_groups'] = array();
		$this->registry->model('account/customer_group');
		if (is_array($this->config->get('config_customer_group_display')))
		{
			$customer_groups = $this->model_account_customer_group->getGroups();
			foreach ($customer_groups as $customer_group)
			{
				if (in_array($customer_group['customer_group_id'], $this->config->get('config_customer_group_display')))
				{
					$vrs['customer_groups'][] = $customer_group;
				}
			}
		}

		$vrs['customer_group_id'] = isset($this->request->post['customer_group_id']) ? $this->request->post['customer_group_id'] : $this->config->get('config_customer_group_id');
		$vrs['company_id']        = $this->request->get_var('company_id');
		$vrs['tax_id']            = $this->request->get_var('tax_id');
		$vrs['address_1']         = $this->request->get_var('address_1');
		$vrs['address_2']         = $this->request->get_var('address_2');
		$vrs['postcode']          = $this->request->get_var('postcode');
		$vrs['city']              = $this->request->get_var('city');

		if (!$vrs['postcode'] && isset($this->session->data['shipping_postcode']))
		{
			$vrs['postcode'] = $this->session->data['shipping_postcode'];
		}

		if (isset($this->request->post['country_id']))
		{
			$vrs['country_id'] = $this->request->post['country_id'];
		}
		elseif (isset($this->session->data['shipping_country_id']))
		{
			$vrs['country_id'] = $this->session->data['shipping_country_id'];
		}
		else
		{
			$vrs['country_id'] = $this->config->get('config_country_id');
		}

		$vrs['zone_id'] = $this->request->get_var('zone_id', 'i');
		if (!$vrs['zone_id'] && isset($this->session->data['shipping_zone_id']))
		{
			$vrs['zone_id'] = $this->session->data['shipping_zone_id'];
		}

		$this->registry->model('common/country');
		$vrs['countries']  = $this->model_common_country->getCountries();
		$vrs['password']   = $vrs['confirm'] = $vrs['text_agree'] = '';
		$vrs['newsletter'] = $this->request->get_var('newsletter');
		$vrs['agree']      = $this->request->get_var('agree', 'b', '', false);

		if ($this->config->get('config_account_id'))
		{
			$this->registry->model('common/information');
			$information_info = $this->model_common_information->getInformation($this->config->get('config_account_id'));
			if ($information_info)
			{
				$vrs['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/info', 'information_id=' . $this->config->get('config_account_id'), true), $information_info['title'], $information_info['title']);
			}
		}

		/**
		 * 模板处理
		 */
		$vrs['page_footer']    = $this->registry->exectrl('common/page_footer');
		$vrs['page_header']    = $this->registry->exectrl('common/page_header');

		return $this->view('template/account/register.tpl', $vrs);
	}

	private function validate()
	{
		if ((mb_strlen($this->request->post['firstname']) < 1) || (mb_strlen($this->request->post['firstname']) > 32))
		{
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		if ((mb_strlen(strtolower(trim($this->request->post['email']))) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', strtolower(trim($this->request->post['email']))))
		{
			$this->error['email'] = $this->language->get('error_email');
		}

		if ($this->model_account_customer->getTotalByEmail(strtolower(trim($this->request->post['email']))))
		{
			$this->error['warning'] = $this->language->get('error_exists');
		}

		if (mb_strlen($this->request->post['password']) < 6)
		{
			$this->error['password'] = $this->language->get('error_password');
		}

		if ($this->request->post['confirm'] != $this->request->post['password'])
		{
			$this->error['confirm'] = $this->language->get('error_confirm');
		}

		$captcha = empty($this->session->data['captcha']) ? mt_rand(1, 999) : $this->session->data['captcha'];
		if ($captcha != strtoupper($this->request->post['captcha']))
		{
			$this->error['captcha'] = $this->language->get('error_captcha');
		}

		if ($this->config->get('config_account_id'))
		{
			$this->registry->model('catalog/information');
			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));
			if ($information_info && !isset($this->request->post['agree']))
			{
				$this->error['warning'] = sprintf($this->language->get('error_agree'), $information_info['title']);
			}
		}

		return !$this->error;
	}
}
?>