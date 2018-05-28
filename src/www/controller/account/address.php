<?php
class ControllerAccountAddress extends Controller
{
	private $error = array();

	public function index()
	{
		if (!$this->customer->isLogged())
		{
			$this->session->data['redirect'] = $this->url->link('account/address', '', true);
			$this->registry->redirect($this->url->link('account/login', '', true));
		}

		$this->registry->language('account/address');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('account/address');

		return $this->getList();
	}

	public function insert()
	{
		if (!$this->customer->isLogged())
		{
			$this->session->data['redirect'] = $this->url->link('account/address', '', true);
			$this->registry->redirect($this->url->link('account/login', '', true));
		}

		$this->registry->language('account/address');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('account/address');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm())
		{
			$this->model_account_address->addAddress($this->request->post);
			$this->session->data['success'] = $this->language->get('text_insert');
			$this->registry->redirect($this->url->link('account/address', '', true));
		}

		return $this->getForm();
	}

	public function update()
	{
		if (!$this->customer->isLogged())
		{
			$this->session->data['redirect'] = $this->url->link('account/address', '', true);
			$this->registry->redirect($this->url->link('account/login', '', true));
		}

		$this->registry->language('account/address');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('account/address');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm())
		{
			$this->model_account_address->editAddress($this->request->get['address_id'], $this->request->post);

			// Default Shipping Address
			if (isset($this->session->data['shipping_address_id']) && ($this->request->get['address_id'] == $this->session->data['shipping_address_id']))
			{
				$this->session->data['shipping_country_id'] = $this->request->post['country_id'];
				$this->session->data['shipping_zone_id']    = $this->request->post['zone_id'];
				$this->session->data['shipping_postcode']   = $this->request->post['postcode'];
				unset($this->session->data['shipping_method'], $this->session->data['shipping_methods']);
			}

			// Default Payment Address
			if (isset($this->session->data['payment_address_id']) && ($this->request->get['address_id'] == $this->session->data['payment_address_id']))
			{
				$this->session->data['payment_country_id'] = $this->request->post['country_id'];
				$this->session->data['payment_zone_id']    = $this->request->post['zone_id'];
				unset($this->session->data['payment_method'], $this->session->data['payment_methods']);
			}

			$this->session->data['success'] = $this->language->get('text_update');
			$this->registry->redirect($this->url->link('account/address', '', true));
		}

		return $this->getForm();
	}

	public function delete()
	{
		if (!$this->customer->isLogged())
		{
			$this->session->data['redirect'] = $this->url->link('account/address', '', true);
			$this->registry->redirect($this->url->link('account/login', '', true));
		}

		$this->registry->language('account/address');
		$this->registry->model('account/address');
		$this->document->title($this->language->get('heading_title'));

		if (isset($this->request->get['address_id']) && $this->validateDelete())
		{
			$this->model_account_address->deleteAddress($this->request->get['address_id']);

			// Default Shipping Address
			if (isset($this->session->data['shipping_address_id']) && ($this->request->get['address_id'] == $this->session->data['shipping_address_id']))
			{
				unset($this->session->data['shipping_address_id'], $this->session->data['shipping_country_id']);
				unset($this->session->data['shipping_zone_id'], $this->session->data['shipping_postcode']);
				unset($this->session->data['shipping_method'], $this->session->data['shipping_methods']);
			}

			// Default Payment Address
			if (isset($this->session->data['payment_address_id']) && ($this->request->get['address_id'] == $this->session->data['payment_address_id']))
			{
				unset($this->session->data['payment_method'], $this->session->data['payment_methods']);
				unset($this->session->data['payment_address_id'], $this->session->data['payment_country_id'], $this->session->data['payment_zone_id']);
			}

			$this->session->data['success'] = $this->language->get('text_delete');
			$this->registry->redirect($this->url->link('account/address', '', true));
		}

		return $this->getList();
	}

	private function getList()
	{
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
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/address', '', true),
			'separator' => $this->language->get('text_separator')
		);

		$vrs['heading_title']      = $this->language->get('heading_title');
		$vrs['text_address_book']  = $this->language->get('text_address_book');
		$vrs['button_new_address'] = $this->language->get('button_new_address');
		$vrs['button_edit']        = $this->language->get('button_edit');
		$vrs['button_delete']      = $this->language->get('button_delete');
		$vrs['button_back']        = $this->language->get('button_back');
		$vrs['error_warning']      = isset($this->error['warning']) ? $this->error['warning'] : '';

		$vrs['success'] = '';
		if (isset($this->session->data['success']))
		{
			$vrs['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}

		$vrs['addresses'] = array();
		$results          = $this->model_account_address->getAddresses();
		foreach ($results as $result)
		{
			$vrs['addresses'][] = array(
				'address_id' => $result['address_id'],
				'address'    => $result['full_address'],
				'update'     => $this->url->link('account/address/update', 'address_id=' . $result['address_id'], true),
				'delete'     => $this->url->link('account/address/delete', 'address_id=' . $result['address_id'], true)
			);
		}

		$vrs['insert'] = $this->url->link('account/address/insert', '', true);
		$vrs['back']   = $this->url->link('account/account', '', true);

		/**
		 * 模板处理
		 */
		$vrs['page_footer']    = $this->registry->exectrl('common/page_footer');
		$vrs['page_header']    = $this->registry->exectrl('common/page_header');

		return $this->view('template/account/address_list.tpl', $vrs);
	}

	private function getForm()
	{
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
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/address', '', true),
			'separator' => $this->language->get('text_separator')
		);
		if (!isset($this->request->get['address_id']))
		{
			$vrs['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_edit_address'),
				'href'      => $this->url->link('account/address/insert', '', true),
				'separator' => $this->language->get('text_separator')
			);
		}
		else
		{
			$vrs['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_edit_address'),
				'href'      => $this->url->link('account/address/update', 'address_id=' . $this->request->get['address_id'], true),
				'separator' => $this->language->get('text_separator')
			);
		}

		$vrs['heading_title']     = $this->language->get('heading_title');
		$vrs['text_edit_address'] = $this->language->get('text_edit_address');
		$vrs['text_yes']          = $this->language->get('text_yes');
		$vrs['text_no']           = $this->language->get('text_no');
		$vrs['text_select']       = $this->language->get('text_select');
		$vrs['text_none']         = $this->language->get('text_none');
		$vrs['entry_firstname']   = $this->language->get('entry_firstname');
		$vrs['entry_lastname']    = $this->language->get('entry_lastname');
		$vrs['entry_telephone']   = $this->language->get('entry_telephone');
		$vrs['entry_company']     = $this->language->get('entry_company');
		$vrs['entry_company_id']  = $this->language->get('entry_company_id');
		$vrs['entry_tax_id']      = $this->language->get('entry_tax_id');
		$vrs['entry_address_1']   = $this->language->get('entry_address_1');
		$vrs['entry_address_2']   = $this->language->get('entry_address_2');
		$vrs['entry_postcode']    = $this->language->get('entry_postcode');
		$vrs['entry_city']        = $this->language->get('entry_city');
		$vrs['entry_country']     = $this->language->get('entry_country');
		$vrs['entry_zone']        = $this->language->get('entry_zone');
		$vrs['entry_default']     = $this->language->get('entry_default');
		$vrs['button_continue']   = $this->language->get('button_continue');
		$vrs['button_back']       = $this->language->get('button_back');

		$vrs['error_firstname']  = isset($this->error['firstname']) ? $this->error['firstname'] : '';
		$vrs['error_lastname']   = isset($this->error['lastname']) ? $this->error['lastname'] : '';
		$vrs['error_telephone']  = isset($this->error['telephone']) ? $this->error['telephone'] : '';
		$vrs['error_company_id'] = isset($this->error['company_id']) ? $this->error['company_id'] : '';
		$vrs['error_tax_id']     = isset($this->error['tax_id']) ? $this->error['tax_id'] : '';
		$vrs['error_address_1']  = isset($this->error['address_1']) ? $this->error['address_1'] : '';
		$vrs['error_city']       = isset($this->error['city']) ? $this->error['city'] : '';
		$vrs['error_postcode']   = isset($this->error['postcode']) ? $this->error['postcode'] : '';
		$vrs['error_country']    = isset($this->error['country']) ? $this->error['country'] : '';
		$vrs['error_zone']       = isset($this->error['zone']) ? $this->error['zone'] : '';

		if (!isset($this->request->get['address_id']))
		{
			$vrs['action'] = $this->url->link('account/address/insert', '', true);
		}
		else
		{
			$vrs['action'] = $this->url->link('account/address/update', 'address_id=' . $this->request->get['address_id'], true);
		}

		if (isset($this->request->get['address_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST'))
		{
			$address_info = $this->model_account_address->getAddress($this->request->get['address_id']);
		}

		if (isset($this->request->post['firstname']))
		{
			$vrs['firstname'] = $this->request->post['firstname'];
		}
		elseif (!empty($address_info))
		{
			$vrs['firstname'] = $address_info['firstname'];
		}
		else
		{
			$vrs['firstname'] = '';
		}
		if (isset($this->request->post['lastname']))
		{
			$vrs['lastname'] = $this->request->post['lastname'];
		}
		elseif (!empty($address_info))
		{
			$vrs['lastname'] = $address_info['lastname'];
		}
		else
		{
			$vrs['lastname'] = '';
		}

		if (isset($this->request->post['telephone']))
		{
			$vrs['telephone'] = $this->request->post['telephone'];
		}
		elseif (!empty($address_info))
		{
			$vrs['telephone'] = $address_info['telephone'];
		}
		else
		{
			$vrs['telephone'] = '';
		}

		if (isset($this->request->post['company']))
		{
			$vrs['company'] = $this->request->post['company'];
		}
		elseif (!empty($address_info))
		{
			$vrs['company'] = $address_info['company'];
		}
		else
		{
			$vrs['company'] = '';
		}

		if (isset($this->request->post['company_id']))
		{
			$vrs['company_id'] = $this->request->post['company_id'];
		}
		elseif (!empty($address_info))
		{
			$vrs['company_id'] = $address_info['company_id'];
		}
		else
		{
			$vrs['company_id'] = '';
		}

		if (isset($this->request->post['tax_id']))
		{
			$vrs['tax_id'] = $this->request->post['tax_id'];
		}
		elseif (!empty($address_info))
		{
			$vrs['tax_id'] = $address_info['tax_id'];
		}
		else
		{
			$vrs['tax_id'] = '';
		}

		$this->registry->model('account/customer_group');
		$customer_group_info = $this->model_account_customer_group->getGroup($this->customer->getGroupId());
		if ($customer_group_info)
		{
			$vrs['company_id_display'] = $customer_group_info['company_id_display'];
		}
		else
		{
			$vrs['company_id_display'] = '';
		}

		if ($customer_group_info)
		{
			$vrs['tax_id_display'] = $customer_group_info['tax_id_display'];
		}
		else
		{
			$vrs['tax_id_display'] = '';
		}

		if (isset($this->request->post['address_1']))
		{
			$vrs['address_1'] = $this->request->post['address_1'];
		}
		elseif (!empty($address_info))
		{
			$vrs['address_1'] = $address_info['address_1'];
		}
		else
		{
			$vrs['address_1'] = '';
		}

		if (isset($this->request->post['address_2']))
		{
			$vrs['address_2'] = $this->request->post['address_2'];
		}
		elseif (!empty($address_info))
		{
			$vrs['address_2'] = $address_info['address_2'];
		}
		else
		{
			$vrs['address_2'] = '';
		}

		if (isset($this->request->post['postcode']))
		{
			$vrs['postcode'] = $this->request->post['postcode'];
		}
		elseif (!empty($address_info))
		{
			$vrs['postcode'] = $address_info['postcode'];
		}
		else
		{
			$vrs['postcode'] = '';
		}

		if (isset($this->request->post['city']))
		{
			$vrs['city'] = $this->request->post['city'];
		}
		elseif (!empty($address_info))
		{
			$vrs['city'] = $address_info['city'];
		}
		else
		{
			$vrs['city'] = '';
		}

		if (isset($this->request->post['country_id']))
		{
			$vrs['country_id'] = $this->request->post['country_id'];
		}
		elseif (!empty($address_info))
		{
			$vrs['country_id'] = $address_info['country_id'];
		}
		else
		{
			$vrs['country_id'] = $this->config->get('config_country_id');
		}

		if (isset($this->request->post['zone_id']))
		{
			$vrs['zone_id'] = $this->request->post['zone_id'];
		}
		elseif (!empty($address_info))
		{
			$vrs['zone_id'] = $address_info['zone_id'];
		}
		else
		{
			$vrs['zone_id'] = '';
		}

		$this->registry->model('common/country');
		$vrs['countries'] = $this->model_common_country->getCountries();
		if (isset($this->request->post['default']))
		{
			$vrs['default'] = $this->request->post['default'];
		}
		else
		{
			$vrs['default'] = isset($this->request->get['address_id']) ? $this->request->get['address_id'] : false;
		}
		$vrs['back'] = $this->url->link('account/address', '', true);

		/**
		 * 模板处理
		 */
		$vrs['page_footer']    = $this->registry->exectrl('common/page_footer');
		$vrs['page_header']    = $this->registry->exectrl('common/page_header');

		return $this->view('template/account/address_form.tpl', $vrs);
	}

	private function validateForm()
	{
		if ((mb_strlen($this->request->post['firstname']) < 1) || (mb_strlen($this->request->post['firstname']) > 32))
		{
			$this->error['firstname'] = $this->language->get('error_firstname');
		}
		if ((mb_strlen($this->request->post['lastname']) < 1) || (mb_strlen($this->request->post['lastname']) > 32))
		{
			$this->error['lastname'] = $this->language->get('error_lastname');
		}
		if ((mb_strlen($this->request->post['telephone']) < 1) || (mb_strlen($this->request->post['telephone']) > 32))
		{
			$this->error['telephone'] = $this->language->get('error_telephone');
		}
		if ((mb_strlen($this->request->post['address_1']) < 3) || (mb_strlen($this->request->post['address_1']) > 128))
		{
			$this->error['address_1'] = $this->language->get('error_address_1');
		}

		$_POST['city'] = isset($_POST['city']) ? $_POST['city'] : '';
		if ((mb_strlen($this->request->post['city']) < 2) || (mb_strlen($this->request->post['city']) > 128))
		{
			$this->error['city'] = $this->language->get('error_city');
		}

		$this->registry->model('common/country');
		$country_info = $this->model_common_country->getCountry($this->request->post['country_id']);
		if ($country_info)
		{
			if ($country_info['postcode_required'] && (mb_strlen($this->request->post['postcode']) < 2) || (mb_strlen($this->request->post['postcode']) > 10))
			{
				$this->error['postcode'] = $this->language->get('error_postcode');
			}

			// VAT Validation
			if ($this->config->get('config_vat') && !empty($this->request->post['tax_id']) && (modules_vat::validation($country_info['iso_code_2'], $this->request->post['tax_id']) == 'invalid'))
			{
				$this->error['tax_id'] = $this->language->get('error_vat');
			}
		}
		if ($this->request->post['country_id'] == '')
		{
			$this->error['country'] = $this->language->get('error_country');
		}
		if ($this->request->post['zone_id'] == '')
		{
			$this->error['zone'] = $this->language->get('error_zone');
		}

		return !$this->error;
	}

	private function validateDelete()
	{
		if ($this->model_account_address->getTotalAddresses() == 1)
		{
			$this->error['warning'] = $this->language->get('error_delete');
		}

		if ($this->customer->getAddressId() == $this->request->get['address_id'])
		{
			$this->error['warning'] = $this->language->get('error_default');
		}

		return !$this->error;
	}
}
?>