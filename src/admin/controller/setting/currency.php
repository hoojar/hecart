<?php
class ControllerSettingCurrency extends Controller
{
	private $error = array();

	public function index()
	{
		$this->registry->language('setting/currency');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('setting/currency');

		return $this->getList();
	}

	public function insert()
	{
		$this->registry->language('setting/currency');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('setting/currency');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm())
		{
			$this->model_setting_currency->addCurrency($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
			$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';
			$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';
			$this->registry->redirect($this->url->link('setting/currency', $url, true));
		}

		return $this->getForm();
	}

	public function update()
	{
		$this->registry->language('setting/currency');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('setting/currency');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm())
		{
			$this->model_setting_currency->editCurrency($this->request->get['currency_id'], $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
			$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';
			$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';
			$this->registry->redirect($this->url->link('setting/currency', $url, true));
		}

		return $this->getForm();
	}

	public function delete()
	{
		$this->registry->language('setting/currency');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('setting/currency');
		if (isset($this->request->post['selected']) && $this->validateDelete())
		{
			foreach ($this->request->post['selected'] as $currency_id)
			{
				$this->model_setting_currency->deleteCurrency($currency_id);
			}
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
			$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';
			$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';
			$this->registry->redirect($this->url->link('setting/currency', $url, true));
		}

		return $this->getList();
	}

	private function getList()
	{
		$sort  = isset($this->request->get['sort']) ? $this->request->get['sort'] : 'title';
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
			'href'      => $this->url->link('setting/currency', $url, true),
			'separator' => ' :: '
		);

		$vrs['insert']     = $this->url->link('setting/currency/insert', $url, true);
		$vrs['delete']     = $this->url->link('setting/currency/delete', $url, true);
		$vrs['currencies'] = array();
		$data              = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ((int)$page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);

		$currency_total = $this->model_setting_currency->getTotalCurrencies();
		$results        = $this->model_setting_currency->getCurrencies($data);
		foreach ($results as $result)
		{
			$action = array();
			if ($this->config->mpermission)
			{
				$action[] = array(
					'text' => $this->language->get('text_edit'),
					'href' => $this->url->link('setting/currency/update', 'currency_id=' . $result['currency_id'] . $url, true)
				);
			}
			$vrs['currencies'][] = array(
				'currency_id'   => $result['currency_id'],
				'title'         => $result['title'] . (($result['code'] == $this->config->get('config_currency')) ? $this->language->get('text_default') : null),
				'code'          => $result['code'],
				'value'         => $result['value'],
				'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
				'selected'      => isset($this->request->post['selected']) && in_array($result['currency_id'], $this->request->post['selected']),
				'action'        => $action
			);
		}
		$vrs['heading_title']        = $this->language->get('heading_title');
		$vrs['text_no_results']      = $this->language->get('text_no_results');
		$vrs['column_title']         = $this->language->get('column_title');
		$vrs['column_code']          = $this->language->get('column_code');
		$vrs['column_value']         = $this->language->get('column_value');
		$vrs['column_date_modified'] = $this->language->get('column_date_modified');
		$vrs['column_action']        = $this->language->get('column_action');
		$vrs['button_insert']        = $this->language->get('button_insert');
		$vrs['button_delete']        = $this->language->get('button_delete');
		$vrs['error_warning']        = isset($this->error['warning']) ? $this->error['warning'] : '';

		$vrs['success'] = '';
		if (isset($this->session->data['success']))
		{
			$vrs['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}
		$url = ($order == 'ASC') ? '&order=DESC' : '&order=ASC';
		$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';

		$vrs['sort_title']         = $this->url->link('setting/currency', 'sort=title' . $url, true);
		$vrs['sort_code']          = $this->url->link('setting/currency', 'sort=code' . $url, true);
		$vrs['sort_value']         = $this->url->link('setting/currency', 'sort=value' . $url, true);
		$vrs['sort_date_modified'] = $this->url->link('setting/currency', 'sort=date_modified' . $url, true);

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
		$pagination->total = $currency_total;
		$pagination->page  = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text  = $this->language->get('text_pagination');
		$pagination->url   = $this->url->link('setting/currency', "{$url}&page={page}", true);
		$vrs['pagination'] = $pagination->render();
		$vrs['sort']       = $sort;
		$vrs['order']      = $order;

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/setting/currency_list.tpl', $vrs);
	}

	private function getForm()
	{
		$vrs['heading_title']       = $this->language->get('heading_title');
		$vrs['text_enabled']        = $this->language->get('text_enabled');
		$vrs['text_disabled']       = $this->language->get('text_disabled');
		$vrs['entry_title']         = $this->language->get('entry_title');
		$vrs['entry_code']          = $this->language->get('entry_code');
		$vrs['entry_value']         = $this->language->get('entry_value');
		$vrs['entry_symbol_left']   = $this->language->get('entry_symbol_left');
		$vrs['entry_symbol_right']  = $this->language->get('entry_symbol_right');
		$vrs['entry_decimal_place'] = $this->language->get('entry_decimal_place');
		$vrs['entry_status']        = $this->language->get('entry_status');
		$vrs['button_save']         = $this->language->get('button_save');
		$vrs['button_cancel']       = $this->language->get('button_cancel');
		$vrs['tab_general']         = $this->language->get('tab_general');
		$vrs['error_warning']       = isset($this->error['warning']) ? $this->error['warning'] : '';
		$vrs['error_title']         = isset($this->error['title']) ? $this->error['title'] : '';
		$vrs['error_code']          = isset($this->error['code']) ? $this->error['code'] : '';

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
			'href'      => $this->url->link('setting/currency', $url, true),
			'separator' => ' :: '
		);

		if (!isset($this->request->get['currency_id']))
		{
			$vrs['action'] = $this->url->link('setting/currency/insert', $url, true);
		}
		else
		{
			$vrs['action'] = $this->url->link('setting/currency/update', 'currency_id=' . $this->request->get['currency_id'] . $url, true);
		}
		$vrs['cancel'] = $this->url->link('setting/currency', $url, true);
		if (isset($this->request->get['currency_id']))
		{
			$currency_info = $this->model_setting_currency->getCurrency($this->request->get['currency_id']);
		}

		if (isset($this->request->post['title']))
		{
			$vrs['title'] = $this->request->post['title'];
		}
		elseif (!empty($currency_info))
		{
			$vrs['title'] = $currency_info['title'];
		}
		else
		{
			$vrs['title'] = '';
		}

		if (isset($this->request->post['code']))
		{
			$vrs['code'] = $this->request->post['code'];
		}
		elseif (!empty($currency_info))
		{
			$vrs['code'] = $currency_info['code'];
		}
		else
		{
			$vrs['code'] = '';
		}

		if (isset($this->request->post['symbol_left']))
		{
			$vrs['symbol_left'] = $this->request->post['symbol_left'];
		}
		elseif (!empty($currency_info))
		{
			$vrs['symbol_left'] = $currency_info['symbol_left'];
		}
		else
		{
			$vrs['symbol_left'] = '';
		}

		if (isset($this->request->post['symbol_right']))
		{
			$vrs['symbol_right'] = $this->request->post['symbol_right'];
		}
		elseif (!empty($currency_info))
		{
			$vrs['symbol_right'] = $currency_info['symbol_right'];
		}
		else
		{
			$vrs['symbol_right'] = '';
		}

		if (isset($this->request->post['decimal_place']))
		{
			$vrs['decimal_place'] = $this->request->post['decimal_place'];
		}
		elseif (!empty($currency_info))
		{
			$vrs['decimal_place'] = $currency_info['decimal_place'];
		}
		else
		{
			$vrs['decimal_place'] = '';
		}

		if (isset($this->request->post['value']))
		{
			$vrs['value'] = $this->request->post['value'];
		}
		elseif (!empty($currency_info))
		{
			$vrs['value'] = $currency_info['value'];
		}
		else
		{
			$vrs['value'] = '';
		}

		if (isset($this->request->post['status']))
		{
			$vrs['status'] = $this->request->post['status'];
		}
		elseif (!empty($currency_info))
		{
			$vrs['status'] = $currency_info['status'];
		}
		else
		{
			$vrs['status'] = '';
		}

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/setting/currency_form.tpl', $vrs);
	}

	private function validateForm()
	{
		if (!$this->user->hasPermission('modify', 'setting/currency'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}
		if ((mb_strlen($this->request->post['title']) < 3) || (mb_strlen($this->request->post['title']) > 32))
		{
			$this->error['title'] = $this->language->get('error_title');
		}
		if (mb_strlen($this->request->post['code']) != 3)
		{
			$this->error['code'] = $this->language->get('error_code');
		}

		return !$this->error;
	}

	private function validateDelete()
	{
		if (!$this->user->hasPermission('modify', 'setting/currency'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->registry->model('setting/store');

		$this->registry->model('sale/order');
		foreach ($this->request->post['selected'] as $currency_id)
		{
			$currency_info = $this->model_setting_currency->getCurrency($currency_id);
			if ($currency_info)
			{
				if ($this->config->get('config_currency') == $currency_info['code'])
				{
					$this->error['warning'] = $this->language->get('error_default');
				}
				$store_total = $this->model_setting_store->getTotalStoresByCurrency($currency_info['code']);
				if ($store_total)
				{
					$this->error['warning'] = sprintf($this->language->get('error_store'), $store_total);
				}
			}
			$order_total = $this->model_sale_order->getTotalOrdersByCurrencyId($currency_id);
			if ($order_total)
			{
				$this->error['warning'] = sprintf($this->language->get('error_order'), $order_total);
			}
		}

		return !$this->error;
	}
}
?>