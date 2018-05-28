<?php
class ControllerSettingCountry extends Controller
{
	private $error = array();

	public function index()
	{
		$this->registry->language('setting/country');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('setting/country');

		return $this->getList();
	}

	/**
	 * 获取国家下的区域信息
	 *
	 */
	public function country()
	{
		$country_id = (int)$this->request->get['country_id'];
		$mkey       = __CLASS__ . __FUNCTION__ . $country_id;
		$res        = $this->mem_get($mkey);

		if (empty($res))
		{
			$json = array();
			$this->registry->model('setting/country');
			$country_info = $this->model_setting_country->getCountry($country_id);
			if ($country_info)
			{
				$this->registry->model('setting/zone');
				$json = array(
					'country_id'        => $country_info['country_id'],
					'name'              => $country_info['name'],
					'iso_code_2'        => $country_info['iso_code_2'],
					'iso_code_3'        => $country_info['iso_code_3'],
					'address_format'    => $country_info['address_format'],
					'postcode_required' => $country_info['postcode_required'],
					'zone'              => $this->model_setting_zone->getZonesByCountryId($country_id),
					'status'            => $country_info['status']
				);
			}
			$res = json_encode($json);
			$this->mem_set($mkey, $res);
		}

		return ($res);
	}

	public function insert()
	{
		$this->registry->language('setting/country');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('setting/country');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm())
		{
			$this->model_setting_country->addCountry($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
			$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';
			$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';
			$this->registry->redirect($this->url->link('setting/country', $url, true));
		}

		return $this->getForm();
	}

	public function update()
	{
		$this->registry->language('setting/country');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('setting/country');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm())
		{
			$this->model_setting_country->editCountry($this->request->get['country_id'], $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
			$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';
			$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';
			$this->registry->redirect($this->url->link('setting/country', $url, true));
		}

		return $this->getForm();
	}

	public function delete()
	{
		$this->registry->language('setting/country');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('setting/country');

		if (isset($this->request->post['selected']) && $this->validateDelete())
		{
			foreach ($this->request->post['selected'] as $country_id)
			{
				$this->model_setting_country->deleteCountry($country_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
			$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';
			$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';
			$this->registry->redirect($this->url->link('setting/country', $url, true));
		}

		return $this->getList();
	}

	private function getList()
	{
		$sort = isset($this->request->get['sort']) ? $this->request->get['sort'] : 'name';

		$order = isset($this->request->get['order']) ? $this->request->get['order'] : 'ASC';
		$page  = isset($this->request->get['page']) ? $this->request->get['page'] : 1;

		/**
		 * 连接组合处理
		 */
		$url = '';
		$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
		$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';
		$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';

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
			'href'      => $this->url->link('setting/country', $url, true),
			'separator' => ' :: '
		);

		$vrs['insert']    = $this->url->link('setting/country/insert', $url, true);
		$vrs['delete']    = $this->url->link('setting/country/delete', $url, true);
		$vrs['countries'] = array();
		$data             = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ((int)$page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);

		$country_total = $this->model_setting_country->getTotalCountries();
		$results       = $this->model_setting_country->getCountries($data);
		foreach ($results as $result)
		{
			$action = array();
			if ($this->config->mpermission)
			{
				$action[] = array(
					'text' => $this->language->get('text_edit'),
					'href' => $this->url->link('setting/country/update', 'country_id=' . $result['country_id'] . $url, true)
				);
			}

			$vrs['countries'][] = array(
				'country_id' => $result['country_id'],
				'name'       => $result['name'] . (($result['country_id'] == $this->config->get('config_country_id')) ? $this->language->get('text_default') : null),
				'iso_code_2' => $result['iso_code_2'],
				'iso_code_3' => $result['iso_code_3'],
				'selected'   => isset($this->request->post['selected']) && in_array($result['country_id'], $this->request->post['selected']),
				'action'     => $action
			);
		}

		$vrs['heading_title']     = $this->language->get('heading_title');
		$vrs['text_no_results']   = $this->language->get('text_no_results');
		$vrs['column_name']       = $this->language->get('column_name');
		$vrs['column_iso_code_2'] = $this->language->get('column_iso_code_2');
		$vrs['column_iso_code_3'] = $this->language->get('column_iso_code_3');
		$vrs['column_action']     = $this->language->get('column_action');
		$vrs['button_insert']     = $this->language->get('button_insert');
		$vrs['button_delete']     = $this->language->get('button_delete');
		$vrs['error_warning']     = isset($this->error['warning']) ? $this->error['warning'] : '';

		$vrs['success'] = '';
		if (isset($this->session->data['success']))
		{
			$vrs['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}

		$url = ($order == 'ASC') ? '&order=DESC' : '&order=ASC';
		$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';

		$vrs['sort_name']       = $this->url->link('setting/country', 'sort=name' . $url, true);
		$vrs['sort_iso_code_2'] = $this->url->link('setting/country', 'sort=iso_code_2' . $url, true);
		$vrs['sort_iso_code_3'] = $this->url->link('setting/country', 'sort=iso_code_3' . $url, true);

		/**
		 * 连接组合处理
		 */
		$url = '';
		$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
		$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';

		/**
		 * 分页处理
		 */
		$pagination        = new Pagination();
		$pagination->total = $country_total;
		$pagination->page  = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text  = $this->language->get('text_pagination');
		$pagination->url   = $this->url->link('setting/country', "{$url}&page={page}", true);

		$vrs['pagination'] = $pagination->render();
		$vrs['sort']       = $sort;
		$vrs['order']      = $order;

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/setting/country_list.tpl', $vrs);
	}

	private function getForm()
	{
		$vrs['heading_title']           = $this->language->get('heading_title');
		$vrs['text_enabled']            = $this->language->get('text_enabled');
		$vrs['text_disabled']           = $this->language->get('text_disabled');
		$vrs['text_yes']                = $this->language->get('text_yes');
		$vrs['text_no']                 = $this->language->get('text_no');
		$vrs['entry_name']              = $this->language->get('entry_name');
		$vrs['entry_iso_code_2']        = $this->language->get('entry_iso_code_2');
		$vrs['entry_iso_code_3']        = $this->language->get('entry_iso_code_3');
		$vrs['entry_address_format']    = $this->language->get('entry_address_format');
		$vrs['entry_postcode_required'] = $this->language->get('entry_postcode_required');
		$vrs['entry_status']            = $this->language->get('entry_status');
		$vrs['button_save']             = $this->language->get('button_save');
		$vrs['button_cancel']           = $this->language->get('button_cancel');
		$vrs['error_warning']           = isset($this->error['warning']) ? $this->error['warning'] : '';
		$vrs['error_name']              = isset($this->error['name']) ? $this->error['name'] : '';

		$url = '';
		$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
		$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';
		$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';

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
			'href'      => $this->url->link('setting/country', $url, true),
			'separator' => ' :: '
		);

		if (!isset($this->request->get['country_id']))
		{
			$vrs['action'] = $this->url->link('setting/country/insert', $url, true);
		}
		else
		{
			$vrs['action'] = $this->url->link('setting/country/update', 'country_id=' . $this->request->get['country_id'] . $url, true);
		}
		$vrs['cancel'] = $this->url->link('setting/country', $url, true);

		if (isset($this->request->get['country_id']))
		{
			$country_info = $this->model_setting_country->getCountry($this->request->get['country_id']);
		}

		if (isset($this->request->post['name']))
		{
			$vrs['name'] = $this->request->post['name'];
		}
		elseif (!empty($country_info))
		{
			$vrs['name'] = $country_info['name'];
		}
		else
		{
			$vrs['name'] = '';
		}

		if (isset($this->request->post['iso_code_2']))
		{
			$vrs['iso_code_2'] = $this->request->post['iso_code_2'];
		}
		elseif (!empty($country_info))
		{
			$vrs['iso_code_2'] = $country_info['iso_code_2'];
		}
		else
		{
			$vrs['iso_code_2'] = '';
		}

		if (isset($this->request->post['iso_code_3']))
		{
			$vrs['iso_code_3'] = $this->request->post['iso_code_3'];
		}
		elseif (!empty($country_info))
		{
			$vrs['iso_code_3'] = $country_info['iso_code_3'];
		}
		else
		{
			$vrs['iso_code_3'] = '';
		}

		if (isset($this->request->post['address_format']))
		{
			$vrs['address_format'] = $this->request->post['address_format'];
		}
		elseif (!empty($country_info))
		{
			$vrs['address_format'] = $country_info['address_format'];
		}
		else
		{
			$vrs['address_format'] = '';
		}

		if (isset($this->request->post['postcode_required']))
		{
			$vrs['postcode_required'] = $this->request->post['postcode_required'];
		}
		elseif (!empty($country_info))
		{
			$vrs['postcode_required'] = $country_info['postcode_required'];
		}
		else
		{
			$vrs['postcode_required'] = 0;
		}

		if (isset($this->request->post['status']))
		{
			$vrs['status'] = $this->request->post['status'];
		}
		elseif (!empty($country_info))
		{
			$vrs['status'] = $country_info['status'];
		}
		else
		{
			$vrs['status'] = '1';
		}

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/setting/country_form.tpl', $vrs);
	}

	private function validateForm()
	{
		if (!$this->user->hasPermission('modify', 'setting/country'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((mb_strlen($this->request->post['name']) < 2) || (mb_strlen($this->request->post['name']) > 128))
		{
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}

	private function validateDelete()
	{
		if (!$this->user->hasPermission('modify', 'setting/country'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->registry->model('setting/store');

		$this->registry->model('sale/customer');

		$this->registry->model('sale/affiliate');

		$this->registry->model('setting/zone');

		$this->registry->model('setting/geo_zone');
		foreach ($this->request->post['selected'] as $country_id)
		{
			if ($this->config->get('config_country_id') == $country_id)
			{
				$this->error['warning'] = $this->language->get('error_default');
			}

			$store_total = $this->model_setting_store->getTotalStoresByCountryId($country_id);

			if ($store_total)
			{
				$this->error['warning'] = sprintf($this->language->get('error_store'), $store_total);
			}

			$address_total = $this->model_sale_customer->getTotalAddressesByCountryId($country_id);

			if ($address_total)
			{
				$this->error['warning'] = sprintf($this->language->get('error_address'), $address_total);
			}

			$affiliate_total = $this->model_sale_affiliate->getTotalAffiliatesByCountryId($country_id);

			if ($affiliate_total)
			{
				$this->error['warning'] = sprintf($this->language->get('error_affiliate'), $affiliate_total);
			}

			$zone_total = $this->model_setting_zone->getTotalZonesByCountryId($country_id);

			if ($zone_total)
			{
				$this->error['warning'] = sprintf($this->language->get('error_zone'), $zone_total);
			}

			$zone_to_geo_zone_total = $this->model_setting_geo_zone->getTotalZoneToGeoZoneByCountryId($country_id);

			if ($zone_to_geo_zone_total)
			{
				$this->error['warning'] = sprintf($this->language->get('error_zone_to_geo_zone'), $zone_to_geo_zone_total);
			}
		}

		return !$this->error;
	}
}
?>