<?php
class ControllerSaleCustomerBlacklist extends Controller
{
	private $error = array();

	public function index()
	{
		$this->registry->language('sale/customer_blacklist');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('sale/customer_blacklist');

		return $this->getList();
	}

	public function insert()
	{
		$this->registry->language('sale/customer_blacklist');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('sale/customer_blacklist');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm())
		{
			$this->model_sale_customer_blacklist->addBlacklist($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
			$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';
			$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';
			$this->registry->redirect($this->url->link('sale/customer_blacklist', $url, true));
		}

		return $this->getForm();
	}

	public function update()
	{
		$this->registry->language('sale/customer_blacklist');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('sale/customer_blacklist');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm())
		{
			$this->model_sale_customer_blacklist->editBlacklist($this->request->get['customer_ip_blacklist_id'], $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
			$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';
			$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';
			$this->registry->redirect($this->url->link('sale/customer_blacklist', $url, true));
		}

		return $this->getForm();
	}

	public function delete()
	{
		$this->registry->language('sale/customer_blacklist');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('sale/customer_blacklist');
		if (isset($this->request->post['selected']) && $this->validateDelete())
		{
			foreach ($this->request->post['selected'] as $customer_ip_blacklist_id)
			{
				$this->model_sale_customer_blacklist->delBlacklist($customer_ip_blacklist_id);
			}
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
			$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';
			$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';
			$this->registry->redirect($this->url->link('sale/customer_blacklist', $url, true));
		}

		return $this->getList();
	}

	private function getList()
	{
		$sort  = isset($this->request->get['sort']) ? $this->request->get['sort'] : 'ip';
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
			'href'      => $this->url->link('sale/customer_blacklist', $url, true),
			'separator' => ' :: '
		);

		$vrs['insert']              = $this->url->link('sale/customer_blacklist/insert', $url, true);
		$vrs['delete']              = $this->url->link('sale/customer_blacklist/delete', $url, true);
		$vrs['customer_blacklists'] = array();
		$data                       = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ((int)$page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);

		$customer_blacklist_total = $this->model_sale_customer_blacklist->getTotalCustomerBlacklists($data);
		$results                  = $this->model_sale_customer_blacklist->getBlacklists($data);
		foreach ($results as $result)
		{
			$action = array();
			if ($this->config->mpermission)
			{
				$action[] = array(
					'text' => $this->language->get('text_edit'),
					'href' => $this->url->link('sale/customer_blacklist/update', 'customer_ip_blacklist_id=' . $result['customer_ip_blacklist_id'] . $url, true)
				);
			}

			$vrs['customer_blacklists'][] = array(
				'customer_ip_blacklist_id' => $result['customer_ip_blacklist_id'],
				'ip'                       => $result['ip'],
				'total'                    => $result['total'],
				'customer'                 => $this->url->link('sale/customer', 'filter_ip=' . $result['ip'], true),
				'selected'                 => isset($this->request->post['selected']) && in_array($result['customer_ip_blacklist_id'], $this->request->post['selected']),
				'action'                   => $action
			);
		}

		$vrs['heading_title']   = $this->language->get('heading_title');
		$vrs['text_no_results'] = $this->language->get('text_no_results');
		$vrs['column_ip']       = $this->language->get('column_ip');
		$vrs['column_customer'] = $this->language->get('column_customer');
		$vrs['column_action']   = $this->language->get('column_action');
		$vrs['button_insert']   = $this->language->get('button_insert');
		$vrs['button_delete']   = $this->language->get('button_delete');
		$vrs['error_warning']   = isset($this->error['warning']) ? $this->error['warning'] : '';

		$vrs['success'] = '';
		if (isset($this->session->data['success']))
		{
			$vrs['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}

		$url = ($order == 'ASC') ? '&order=DESC' : '&order=ASC';
		$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';

		$vrs['sort_ip'] = $this->url->link('sale/customer_blacklist', 'sort=ip' . $url, true);

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
		$pagination->total = $customer_blacklist_total;
		$pagination->page  = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text  = $this->language->get('text_pagination');
		$pagination->url   = $this->url->link('sale/customer_blacklist', "{$url}&page={page}", true);
		$vrs['pagination'] = $pagination->render();
		$vrs['sort']       = $sort;
		$vrs['order']      = $order;

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/sale/customer_blacklist_list.tpl', $vrs);
	}

	private function getForm()
	{
		$vrs['heading_title'] = $this->language->get('heading_title');
		$vrs['entry_ip']      = $this->language->get('entry_ip');
		$vrs['button_save']   = $this->language->get('button_save');
		$vrs['button_cancel'] = $this->language->get('button_cancel');
		$vrs['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$vrs['error_ip']      = isset($this->error['ip']) ? $this->error['ip'] : '';

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
			'href'      => $this->url->link('sale/customer_blacklist', $url, true),
			'separator' => ' :: '
		);

		if (!isset($this->request->get['customer_ip_blacklist_id']))
		{
			$vrs['action'] = $this->url->link('sale/customer_blacklist/insert', $url, true);
		}
		else
		{
			$vrs['action'] = $this->url->link('sale/customer_blacklist/update', 'customer_ip_blacklist_id=' . $this->request->get['customer_ip_blacklist_id'] . $url, true);
		}
		$vrs['cancel'] = $this->url->link('sale/customer_blacklist', $url, true);
		if (isset($this->request->get['customer_ip_blacklist_id']))
		{
			$customer_blacklist_info = $this->model_sale_customer_blacklist->getBlacklist($this->request->get['customer_ip_blacklist_id']);
		}

		if (isset($this->request->post['ip']))
		{
			$vrs['ip'] = $this->request->post['ip'];
		}
		elseif (!empty($customer_blacklist_info))
		{
			$vrs['ip'] = $customer_blacklist_info['ip'];
		}
		else
		{
			$vrs['ip'] = '';
		}

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/sale/customer_blacklist_form.tpl', $vrs);
	}

	private function validateForm()
	{
		if (!$this->user->hasPermission('modify', 'sale/customer_blacklist'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}
		if ((mb_strlen($this->request->post['ip']) < 1) || (mb_strlen($this->request->post['ip']) > 40))
		{
			$this->error['ip'] = $this->language->get('error_ip');
		}

		return !$this->error;
	}

	private function validateDelete()
	{
		if (!$this->user->hasPermission('modify', 'sale/customer_blacklist'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
?>