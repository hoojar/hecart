<?php
class ControllerSaleCustomer extends Controller
{
	private $error = array();

	public function index()
	{
		$this->registry->language('sale/customer');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('sale/customer');

		return $this->getList();
	}

	public function insert()
	{
		$this->registry->language('sale/customer');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('sale/customer');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm())
		{
			$this->model_sale_customer->add($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			if (isset($this->request->get['search']))
			{
				$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
			}
			if (isset($this->request->get['filter_email']))
			{
				$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
			}
			$url .= isset($this->request->get['filter_customer_group_id']) ? '&filter_customer_group_id=' . $this->request->get['filter_customer_group_id'] : '';
			$url .= isset($this->request->get['filter_status']) ? '&filter_status=' . $this->request->get['filter_status'] : '';
			$url .= isset($this->request->get['filter_approved']) ? '&filter_approved=' . $this->request->get['filter_approved'] : '';
			$url .= isset($this->request->get['filter_ip']) ? '&filter_ip=' . $this->request->get['filter_ip'] : '';
			$url .= isset($this->request->get['filter_date_added']) ? '&filter_date_added=' . $this->request->get['filter_date_added'] : '';
			$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
			$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';
			$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';
			$this->registry->redirect($this->url->link('sale/customer', $url, true));
		}

		return $this->getForm();
	}

	public function update()
	{
		$this->registry->language('sale/customer');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('sale/customer');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm())
		{
			$this->model_sale_customer->edit($this->request->get['customer_id'], $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			if (isset($this->request->get['search']))
			{
				$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
			}
			if (isset($this->request->get['filter_email']))
			{
				$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
			}
			$url .= isset($this->request->get['filter_customer_group_id']) ? '&filter_customer_group_id=' . $this->request->get['filter_customer_group_id'] : '';
			$url .= isset($this->request->get['filter_status']) ? '&filter_status=' . $this->request->get['filter_status'] : '';
			$url .= isset($this->request->get['filter_approved']) ? '&filter_approved=' . $this->request->get['filter_approved'] : '';
			$url .= isset($this->request->get['filter_ip']) ? '&filter_ip=' . $this->request->get['filter_ip'] : '';
			$url .= isset($this->request->get['filter_date_added']) ? '&filter_date_added=' . $this->request->get['filter_date_added'] : '';
			$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
			$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';
			$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';
			$this->registry->redirect($this->url->link('sale/customer', $url, true));
		}

		return $this->getForm();
	}

	public function delete()
	{
		$this->registry->language('sale/customer');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('sale/customer');
		if (isset($this->request->post['selected']) && $this->validateDelete())
		{
			foreach ($this->request->post['selected'] as $customer_id)
			{
				$this->model_sale_customer->del($customer_id);
			}
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			if (isset($this->request->get['search']))
			{
				$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
			}
			if (isset($this->request->get['filter_email']))
			{
				$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
			}
			$url .= isset($this->request->get['filter_customer_group_id']) ? '&filter_customer_group_id=' . $this->request->get['filter_customer_group_id'] : '';
			$url .= isset($this->request->get['filter_status']) ? '&filter_status=' . $this->request->get['filter_status'] : '';
			$url .= isset($this->request->get['filter_approved']) ? '&filter_approved=' . $this->request->get['filter_approved'] : '';
			$url .= isset($this->request->get['filter_ip']) ? '&filter_ip=' . $this->request->get['filter_ip'] : '';
			$url .= isset($this->request->get['filter_date_added']) ? '&filter_date_added=' . $this->request->get['filter_date_added'] : '';
			$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
			$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';
			$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';
			$this->registry->redirect($this->url->link('sale/customer', $url, true));
		}

		return $this->getList();
	}

	public function approve()
	{
		$this->registry->language('sale/customer');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('sale/customer');
		if (!$this->user->hasPermission('modify', 'sale/customer'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}
		elseif (isset($this->request->post['selected']))
		{
			$approved = 0;
			foreach ($this->request->post['selected'] as $customer_id)
			{
				$customer_info = $this->model_sale_customer->get($customer_id);
				if ($customer_info && !$customer_info['approved'])
				{
					$this->model_sale_customer->approve($customer_id);
					$approved++;
				}
			}
			$this->session->data['success'] = sprintf($this->language->get('text_approved'), $approved);

			$url = '';
			if (isset($this->request->get['search']))
			{
				$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_email']))
			{
				$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
			}

			$url .= isset($this->request->get['filter_customer_group_id']) ? '&filter_customer_group_id=' . $this->request->get['filter_customer_group_id'] : '';
			$url .= isset($this->request->get['filter_status']) ? '&filter_status=' . $this->request->get['filter_status'] : '';
			$url .= isset($this->request->get['filter_approved']) ? '&filter_approved=' . $this->request->get['filter_approved'] : '';
			$url .= isset($this->request->get['filter_ip']) ? '&filter_ip=' . $this->request->get['filter_ip'] : '';
			$url .= isset($this->request->get['filter_date_added']) ? '&filter_date_added=' . $this->request->get['filter_date_added'] : '';
			$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
			$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';
			$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';
			$this->registry->redirect($this->url->link('sale/customer', $url, true));
		}

		return $this->getList();
	}

	private function getList()
	{
		$search                   = isset($this->request->get['search']) ? $this->request->get['search'] : null;
		$filter_email             = isset($this->request->get['filter_email']) ? $this->request->get['filter_email'] : null;
		$filter_customer_group_id = isset($this->request->get['filter_customer_group_id']) ? $this->request->get['filter_customer_group_id'] : null;
		$filter_status            = isset($this->request->get['filter_status']) ? $this->request->get['filter_status'] : null;
		$filter_approved          = isset($this->request->get['filter_approved']) ? $this->request->get['filter_approved'] : null;
		$filter_ip                = isset($this->request->get['filter_ip']) ? $this->request->get['filter_ip'] : null;
		$filter_date_added        = isset($this->request->get['filter_date_added']) ? $this->request->get['filter_date_added'] : null;
		$sort                     = isset($this->request->get['sort']) ? $this->request->get['sort'] : 'name';
		$order                    = isset($this->request->get['order']) ? $this->request->get['order'] : 'ASC';
		$page                     = isset($this->request->get['page']) ? $this->request->get['page'] : 1;

		$url = '';
		if (isset($this->request->get['search']))
		{
			$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_email']))
		{
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}

		$url .= isset($this->request->get['filter_customer_group_id']) ? '&filter_customer_group_id=' . $this->request->get['filter_customer_group_id'] : '';
		$url .= isset($this->request->get['filter_status']) ? '&filter_status=' . $this->request->get['filter_status'] : '';
		$url .= isset($this->request->get['filter_approved']) ? '&filter_approved=' . $this->request->get['filter_approved'] : '';
		$url .= isset($this->request->get['filter_ip']) ? '&filter_ip=' . $this->request->get['filter_ip'] : '';
		$url .= isset($this->request->get['filter_date_added']) ? '&filter_date_added=' . $this->request->get['filter_date_added'] : '';
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
			'href'      => $this->url->link('sale/customer', $url, true),
			'separator' => ' :: '
		);

		$vrs['approve'] = $this->url->link('sale/customer/approve', $url, true);
		$vrs['insert']  = $this->url->link('sale/customer/insert', $url, true);
		$vrs['delete']  = $this->url->link('sale/customer/delete', $url, true);

		$vrs['customers'] = array();
		$data             = array(
			'search'                   => $search,
			'filter_email'             => $filter_email,
			'filter_customer_group_id' => $filter_customer_group_id,
			'filter_status'            => $filter_status,
			'filter_approved'          => $filter_approved,
			'filter_date_added'        => $filter_date_added,
			'filter_ip'                => $filter_ip,
			'sort'                     => $sort,
			'order'                    => $order,
			'start'                    => ((int)$page - 1) * $this->config->get('config_admin_limit'),
			'limit'                    => $this->config->get('config_admin_limit')
		);

		$customer_total = $this->model_sale_customer->getTotalCustomers($data);
		$results        = $this->model_sale_customer->gets($data);
		foreach ($results as $result)
		{
			$action = array();
			if ($this->config->mpermission)
			{
				$action[] = array(
					'text' => $this->language->get('text_edit'),
					'href' => $this->url->link('sale/customer/update', 'customer_id=' . $result['customer_id'] . $url, true)
				);
			}

			$vrs['customers'][] = array(
				'customer_id'    => $result['customer_id'],
				'name'           => $result['name'],
				'email'          => $result['email'],
				'customer_group' => $result['customer_group'],
				'status'         => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'approved'       => ($result['approved'] ? $this->language->get('text_yes') : $this->language->get('text_no')),
				'ip'             => $result['ip'],
				'date_added'     => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'selected'       => isset($this->request->post['selected']) && in_array($result['customer_id'], $this->request->post['selected']),
				'action'         => $action
			);
		}

		$vrs['heading_title']         = $this->language->get('heading_title');
		$vrs['text_enabled']          = $this->language->get('text_enabled');
		$vrs['text_disabled']         = $this->language->get('text_disabled');
		$vrs['text_yes']              = $this->language->get('text_yes');
		$vrs['text_no']               = $this->language->get('text_no');
		$vrs['text_select']           = $this->language->get('text_select');
		$vrs['text_default']          = $this->language->get('text_default');
		$vrs['text_no_results']       = $this->language->get('text_no_results');
		$vrs['column_name']           = $this->language->get('column_name');
		$vrs['column_email']          = $this->language->get('column_email');
		$vrs['column_customer_group'] = $this->language->get('column_customer_group');
		$vrs['column_status']         = $this->language->get('column_status');
		$vrs['column_approved']       = $this->language->get('column_approved');
		$vrs['column_ip']             = $this->language->get('column_ip');
		$vrs['column_date_added']     = $this->language->get('column_date_added');
		$vrs['column_login']          = $this->language->get('column_login');
		$vrs['column_action']         = $this->language->get('column_action');
		$vrs['button_approve']        = $this->language->get('button_approve');
		$vrs['button_insert']         = $this->language->get('button_insert');
		$vrs['button_delete']         = $this->language->get('button_delete');
		$vrs['button_filter']         = $this->language->get('button_filter');
		$vrs['error_warning']         = isset($this->error['warning']) ? $this->error['warning'] : '';

		$vrs['success'] = '';
		if (isset($this->session->data['success']))
		{
			$vrs['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}

		$url = '';
		if (isset($this->request->get['search']))
		{
			$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_email']))
		{
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}

		$url .= isset($this->request->get['filter_customer_group_id']) ? '&filter_customer_group_id=' . $this->request->get['filter_customer_group_id'] : '';
		$url .= isset($this->request->get['filter_status']) ? '&filter_status=' . $this->request->get['filter_status'] : '';
		$url .= isset($this->request->get['filter_approved']) ? '&filter_approved=' . $this->request->get['filter_approved'] : '';
		$url .= isset($this->request->get['filter_ip']) ? '&filter_ip=' . $this->request->get['filter_ip'] : '';
		$url .= isset($this->request->get['filter_date_added']) ? '&filter_date_added=' . $this->request->get['filter_date_added'] : '';
		$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';
		$url .= ($order == 'ASC') ? '&order=DESC' : '&order=ASC';

		$vrs['sort_name']           = $this->url->link('sale/customer', 'sort=name' . $url, true);
		$vrs['sort_email']          = $this->url->link('sale/customer', 'sort=c.email' . $url, true);
		$vrs['sort_customer_group'] = $this->url->link('sale/customer', 'sort=customer_group' . $url, true);
		$vrs['sort_status']         = $this->url->link('sale/customer', 'sort=c.status' . $url, true);
		$vrs['sort_approved']       = $this->url->link('sale/customer', 'sort=c.approved' . $url, true);
		$vrs['sort_ip']             = $this->url->link('sale/customer', 'sort=c.ip' . $url, true);
		$vrs['sort_date_added']     = $this->url->link('sale/customer', 'sort=c.date_added' . $url, true);

		$url = '';
		if (isset($this->request->get['search']))
		{
			$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_email']))
		{
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}

		$url .= isset($this->request->get['filter_customer_group_id']) ? '&filter_customer_group_id=' . $this->request->get['filter_customer_group_id'] : '';
		$url .= isset($this->request->get['filter_status']) ? '&filter_status=' . $this->request->get['filter_status'] : '';
		$url .= isset($this->request->get['filter_approved']) ? '&filter_approved=' . $this->request->get['filter_approved'] : '';
		$url .= isset($this->request->get['filter_ip']) ? '&filter_ip=' . $this->request->get['filter_ip'] : '';
		$url .= isset($this->request->get['filter_date_added']) ? '&filter_date_added=' . $this->request->get['filter_date_added'] : '';
		$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
		$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';

		/**
		 * 分页处理
		 */
		$pagination        = new Pagination();
		$pagination->total = $customer_total;
		$pagination->page  = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text  = $this->language->get('text_pagination');
		$pagination->url   = $this->url->link('sale/customer', "{$url}&page={page}", true);

		$vrs['pagination']               = $pagination->render();
		$vrs['search']                   = $search;
		$vrs['filter_email']             = $filter_email;
		$vrs['filter_customer_group_id'] = $filter_customer_group_id;
		$vrs['filter_status']            = $filter_status;
		$vrs['filter_approved']          = $filter_approved;
		$vrs['filter_ip']                = $filter_ip;
		$vrs['filter_date_added']        = $filter_date_added;

		$this->registry->model('sale/customer_group');
		$vrs['customer_groups'] = $this->model_sale_customer_group->getGroups();
		$vrs['sort']   = $sort;
		$vrs['order']  = $order;

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/sale/customer_list.tpl', $vrs);
	}

	private function getForm()
	{
		$lang_arr = array(
			'heading_title',
			'text_enabled',
			'text_disabled',
			'text_select',
			'text_none',
			'text_wait',
			'text_no_results',
			'text_add_blacklist',
			'text_remove_blacklist',
			'column_ip',
			'column_total',
			'column_date_added',
			'column_action',
			'entry_firstname',
			'entry_lastname',
			'entry_email',
			'entry_telephone',
			'entry_fax',
			'entry_password',
			'entry_confirm',
			'entry_newsletter',
			'entry_customer_group',
			'entry_status',
			'entry_company',
			'entry_company_id',
			'entry_tax_id',
			'entry_address_1',
			'entry_address_2',
			'entry_city',
			'entry_postcode',
			'entry_zone',
			'entry_country',
			'entry_default',
			'entry_amount',
			'entry_points',
			'entry_description',
			'button_save',
			'button_cancel',
			'button_add_address',
			'button_add_transaction',
			'button_add_reward',
			'button_remove',
			'tab_general',
			'tab_address',
			'tab_transaction',
			'tab_reward',
			'tab_ip'
		);
		foreach ($lang_arr as $v)
		{
			$vrs[$v] = $this->language->get($v);
		}

		$vrs['customer_id']             = isset($this->request->get['customer_id']) ? $this->request->get['customer_id'] : 0;
		$vrs['error_warning']           = isset($this->error['warning']) ? $this->error['warning'] : '';
		$vrs['error_firstname']         = isset($this->error['firstname']) ? $this->error['firstname'] : '';
		$vrs['error_lastname']          = isset($this->error['lastname']) ? $this->error['lastname'] : '';
		$vrs['error_email']             = isset($this->error['email']) ? $this->error['email'] : '';
		$vrs['error_telephone']         = isset($this->error['telephone']) ? $this->error['telephone'] : '';
		$vrs['error_password']          = isset($this->error['password']) ? $this->error['password'] : '';
		$vrs['error_confirm']           = isset($this->error['confirm']) ? $this->error['confirm'] : '';
		$vrs['error_address_firstname'] = isset($this->error['address_firstname']) ? $this->error['address_firstname'] : '';
		$vrs['error_address_lastname']  = isset($this->error['address_telephone']) ? $this->error['address_telephone'] : '';
		$vrs['error_address_tax_id']    = isset($this->error['address_tax_id']) ? $this->error['address_tax_id'] : '';
		$vrs['error_address_address_1'] = isset($this->error['address_address_1']) ? $this->error['address_address_1'] : '';
		$vrs['error_address_city']      = isset($this->error['address_city']) ? $this->error['address_city'] : '';
		$vrs['error_address_postcode']  = isset($this->error['address_postcode']) ? $this->error['address_postcode'] : '';
		$vrs['error_address_country']   = isset($this->error['address_country']) ? $this->error['address_country'] : '';
		$vrs['error_address_zone']      = isset($this->error['address_zone']) ? $this->error['address_zone'] : '';

		$url = '';
		if (isset($this->request->get['search']))
		{
			$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_email']))
		{
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}

		$url .= isset($this->request->get['filter_customer_group_id']) ? '&filter_customer_group_id=' . $this->request->get['filter_customer_group_id'] : '';
		$url .= isset($this->request->get['filter_status']) ? '&filter_status=' . $this->request->get['filter_status'] : '';
		$url .= isset($this->request->get['filter_approved']) ? '&filter_approved=' . $this->request->get['filter_approved'] : '';
		$url .= isset($this->request->get['filter_date_added']) ? '&filter_date_added=' . $this->request->get['filter_date_added'] : '';
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
			'href'      => $this->url->link('sale/customer', $url, true),
			'separator' => ' :: '
		);

		if (!isset($this->request->get['customer_id']))
		{
			$vrs['action'] = $this->url->link('sale/customer/insert', $url, true);
		}
		else
		{
			$vrs['action'] = $this->url->link('sale/customer/update', 'customer_id=' . $this->request->get['customer_id'] . $url, true);
		}

		$vrs['cancel'] = $this->url->link('sale/customer', $url, true);

		if (isset($this->request->get['customer_id']))
		{
			$customer_info = $this->model_sale_customer->get($this->request->get['customer_id']);
		}

		if (isset($this->request->post['firstname']))
		{
			$vrs['firstname'] = $this->request->post['firstname'];
		}
		elseif (!empty($customer_info))
		{
			$vrs['firstname'] = $customer_info['firstname'];
		}
		else
		{
			$vrs['firstname'] = '';
		}

		if (isset($this->request->post['lastname']))
		{
			$vrs['lastname'] = $this->request->post['lastname'];
		}
		elseif (!empty($customer_info))
		{
			$vrs['lastname'] = $customer_info['lastname'];
		}
		else
		{
			$vrs['lastname'] = '';
		}

		if (isset($this->request->post['email']))
		{
			$vrs['email'] = strtolower(trim($this->request->post['email']));
		}
		elseif (!empty($customer_info))
		{
			$vrs['email'] = $customer_info['email'];
		}
		else
		{
			$vrs['email'] = '';
		}

		if (isset($this->request->post['telephone']))
		{
			$vrs['telephone'] = $this->request->post['telephone'];
		}
		elseif (!empty($customer_info))
		{
			$vrs['telephone'] = $customer_info['telephone'];
		}
		else
		{
			$vrs['telephone'] = '';
		}

		if (isset($this->request->post['fax']))
		{
			$vrs['fax'] = $this->request->post['fax'];
		}
		elseif (!empty($customer_info))
		{
			$vrs['fax'] = $customer_info['fax'];
		}
		else
		{
			$vrs['fax'] = '';
		}

		if (isset($this->request->post['newsletter']))
		{
			$vrs['newsletter'] = $this->request->post['newsletter'];
		}
		elseif (!empty($customer_info))
		{
			$vrs['newsletter'] = $customer_info['newsletter'];
		}
		else
		{
			$vrs['newsletter'] = '';
		}

		$this->registry->model('sale/customer_group');
		$vrs['customer_groups'] = $this->model_sale_customer_group->getGroups();
		if (isset($this->request->post['customer_group_id']))
		{
			$vrs['customer_group_id'] = $this->request->post['customer_group_id'];
		}
		elseif (!empty($customer_info))
		{
			$vrs['customer_group_id'] = $customer_info['customer_group_id'];
		}
		else
		{
			$vrs['customer_group_id'] = $this->config->get('config_customer_group_id');
		}

		if (isset($this->request->post['status']))
		{
			$vrs['status'] = $this->request->post['status'];
		}
		elseif (!empty($customer_info))
		{
			$vrs['status'] = $customer_info['status'];
		}
		else
		{
			$vrs['status'] = 1;
		}

		$vrs['password'] = isset($this->request->post['password']) ? $this->request->post['password'] : '';
		$vrs['confirm']  = isset($this->request->post['confirm']) ? $this->request->post['confirm'] : '';

		$this->registry->model('setting/country');
		$vrs['countries'] = $this->model_setting_country->getCountries();
		if (isset($this->request->post['address']))
		{
			$vrs['addresses'] = $this->request->post['address'];
		}
		elseif (isset($this->request->get['customer_id']))
		{
			$vrs['addresses'] = $this->model_sale_customer->getAddresses($this->request->get['customer_id']);
		}
		else
		{
			$vrs['addresses'] = array();
		}

		if (isset($this->request->post['address_id']))
		{
			$vrs['address_id'] = $this->request->post['address_id'];
		}
		elseif (!empty($customer_info))
		{
			$vrs['address_id'] = $customer_info['address_id'];
		}
		else
		{
			$vrs['address_id'] = '';
		}

		$vrs['ips'] = array();
		if (!empty($customer_info))
		{
			$results = $this->model_sale_customer->getIpsByCustomerId($this->request->get['customer_id']);
			foreach ($results as $result)
			{
				$blacklist_total = $this->model_sale_customer->getTotalBlacklistsByIp($result['ip']);
				$vrs['ips'][]    = array(
					'ip'         => $result['ip'],
					'total'      => $this->model_sale_customer->getTotalCustomersByIp($result['ip']),
					'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
					'filter_ip'  => $this->url->link('sale/customer', 'filter_ip=' . $result['ip'], true),
					'blacklist'  => $blacklist_total
				);
			}
		}

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/sale/customer_form.tpl', $vrs);
	}

	private function validateForm()
	{
		if (!$this->user->hasPermission('modify', 'sale/customer'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}
		if ((mb_strlen($this->request->post['firstname']) < 1) || (mb_strlen($this->request->post['firstname']) > 32))
		{
			$this->error['firstname'] = $this->language->get('error_firstname');
		}
		if ((mb_strlen(strtolower(trim($this->request->post['email']))) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', strtolower(trim($this->request->post['email']))))
		{
			$this->error['email'] = $this->language->get('error_email');
		}

		$customer_info = $this->model_sale_customer->getByEmail(strtolower(trim($this->request->post['email'])));
		if (!isset($this->request->get['customer_id']))
		{
			if ($customer_info)
			{
				$this->error['warning'] = $this->language->get('error_exists');
			}
		}
		else
		{
			if ($customer_info && ($this->request->get['customer_id'] != $customer_info['customer_id']))
			{
				$this->error['warning'] = $this->language->get('error_exists');
			}
		}

		if ((mb_strlen($this->request->post['telephone']) < 3) || (mb_strlen($this->request->post['telephone']) > 32))
		{
			$this->error['telephone'] = $this->language->get('error_telephone');
		}

		if ($this->request->post['password'] || (!isset($this->request->get['customer_id'])))
		{
			if (mb_strlen($this->request->post['password']) < 6)
			{
				$this->error['password'] = $this->language->get('error_password');
			}
			if ($this->request->post['password'] != $this->request->post['confirm'])
			{
				$this->error['confirm'] = $this->language->get('error_confirm');
			}
		}

		if (isset($this->request->post['address']))
		{
			foreach ($this->request->post['address'] as $key => $value)
			{
				if ((mb_strlen($value['firstname']) < 1) || (mb_strlen($value['firstname']) > 32))
				{
					$this->error['address_firstname'][$key] = $this->language->get('error_firstname');
				}
				if ((mb_strlen($value['telephone']) < 3) || (mb_strlen($value['telephone']) > 32))
				{
					$this->error['address_telephone'][$key] = $this->language->get('error_telephone');
				}
				if ((mb_strlen($value['address_1']) < 3) || (mb_strlen($value['address_1']) > 128))
				{
					$this->error['address_address_1'][$key] = $this->language->get('error_address_1');
				}
				if ((mb_strlen($value['city']) < 2) || (mb_strlen($value['city']) > 128))
				{
					$this->error['address_city'][$key] = $this->language->get('error_city');
				}

				$this->registry->model('setting/country');
				$country_info = $this->model_setting_country->getCountry($value['country_id']);
				if ($country_info)
				{
					if ($country_info['postcode_required'] && (mb_strlen($value['postcode']) < 2) || (mb_strlen($value['postcode']) > 10))
					{
						$this->error['address_postcode'][$key] = $this->language->get('error_postcode');
					}
					// VAT Validation
					if ($this->config->get('config_vat') && $value['tax_id'] && (modules_vat::validation($country_info['iso_code_2'], $value['tax_id']) != 'invalid'))
					{
						$this->error['address_tax_id'][$key] = $this->language->get('error_vat');
					}
				}

				if ($value['country_id'] == '')
				{
					$this->error['address_country'][$key] = $this->language->get('error_country');
				}
				if ($value['zone_id'] == '')
				{
					$this->error['address_zone'][$key] = $this->language->get('error_zone');
				}
			}
		}

		if ($this->error && !isset($this->error['warning']))
		{
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	private function validateDelete()
	{
		if (!$this->user->hasPermission('modify', 'sale/customer'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function login()
	{
		$customer_id = isset($this->request->get['customer_id']) ? $this->request->get['customer_id'] : 0;
		$this->registry->model('sale/customer');
		$customer_info = $this->model_sale_customer->get($customer_id);
		if ($customer_info)
		{
			$token = md5(mt_rand());
			$this->model_sale_customer->editToken($customer_id, $token);
			$this->registry->redirect(HTTP_STORE . 'account/login?token=' . $token);
		}

		/**
		 * 用户未找到
		 */
		$this->registry->language('error/not_found');
		$this->document->title($this->language->get('heading_title'));
		$vrs['heading_title']  = $this->language->get('heading_title');
		$vrs['text_not_found'] = $this->language->get('text_not_found');

		/**
		 * 导航栏处理
		 */
		$vrs['breadcrumbs']   = array();
		$vrs['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
			'separator' => false
		);
		$vrs['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('error/not_found'),
			'separator' => ' :: '
		);

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/error/not_found.tpl', $vrs);
	}

	public function transaction()
	{
		$this->registry->language('sale/customer');
		$this->registry->model('sale/customer');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->user->hasPermission('modify', 'sale/customer'))
		{
			$this->model_sale_customer->addTransaction($this->request->get['customer_id'], $this->request->post['description'], $this->request->post['amount']);
			$vrs['success'] = $this->language->get('text_success');
		}
		else
		{
			$vrs['success'] = '';
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && !$this->user->hasPermission('modify', 'sale/customer'))
		{
			$vrs['error_warning'] = $this->language->get('error_permission');
		}
		else
		{
			$vrs['error_warning'] = '';
		}

		$vrs['text_no_results']    = $this->language->get('text_no_results');
		$vrs['text_balance']       = $this->language->get('text_balance');
		$vrs['column_date_added']  = $this->language->get('column_date_added');
		$vrs['column_description'] = $this->language->get('column_description');
		$vrs['column_amount']      = $this->language->get('column_amount');
		$vrs['transactions']       = array();

		$page    = isset($this->request->get['page']) ? $this->request->get['page'] : 1;
		$results = $this->model_sale_customer->getTransactions($this->request->get['customer_id'], ((int)$page - 1) * 10, 10);
		foreach ($results as $result)
		{
			$vrs['transactions'][] = array(
				'amount'      => $this->currency->format($result['amount'], $this->config->get('config_currency')),
				'description' => $result['description'],
				'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			);
		}

		$vrs['balance']    = $this->currency->format($this->model_sale_customer->getTransactionTotal($this->request->get['customer_id']), $this->config->get('config_currency'));
		$transaction_total = $this->model_sale_customer->getTotalTransactions($this->request->get['customer_id']);

		/**
		 * 分页处理
		 */
		$pagination        = new Pagination();
		$pagination->total = $transaction_total;
		$pagination->page  = $page;
		$pagination->limit = 10;
		$pagination->text  = $this->language->get('text_pagination');
		$pagination->url   = $this->url->link('sale/customer/transaction', 'customer_id=' . $this->request->get['customer_id'] . "&page={page}", true);
		$vrs['pagination'] = $pagination->render();

		return $this->view('template/sale/customer_transaction.tpl', $vrs);
	}

	public function reward()
	{
		$this->registry->language('sale/customer');
		$this->registry->model('sale/customer');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->user->hasPermission('modify', 'sale/customer'))
		{
			$this->model_sale_customer->addReward($this->request->get['customer_id'], $this->request->post['description'], $this->request->post['points'], '', 'reward');
			$vrs['success'] = $this->language->get('text_success');
		}
		else
		{
			$vrs['success'] = '';
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && !$this->user->hasPermission('modify', 'sale/customer'))
		{
			$vrs['error_warning'] = $this->language->get('error_permission');
		}
		else
		{
			$vrs['error_warning'] = '';
		}

		$vrs['text_no_results']    = $this->language->get('text_no_results');
		$vrs['text_balance']       = $this->language->get('text_balance');
		$vrs['column_date_added']  = $this->language->get('column_date_added');
		$vrs['column_description'] = $this->language->get('column_description');
		$vrs['column_points']      = $this->language->get('column_points');
		$vrs['rewards']            = array();

		$page    = isset($this->request->get['page']) ? $this->request->get['page'] : 1;
		$results = $this->model_sale_customer->getRewards($this->request->get['customer_id'], ((int)$page - 1) * 10, 10);
		foreach ($results as $result)
		{
			$vrs['rewards'][] = array(
				'points'      => $result['points'],
				'description' => $result['description'],
				'date_added'  => date($this->language->get('date_format_long'), strtotime($result['date_added']))
			);
		}

		$vrs['balance'] = $this->model_sale_customer->getRewardTotal($this->request->get['customer_id']);
		$reward_total   = $this->model_sale_customer->getTotalRewards($this->request->get['customer_id']);

		/**
		 * 分页处理
		 */
		$pagination        = new Pagination();
		$pagination->total = $reward_total;
		$pagination->page  = $page;
		$pagination->limit = 10;
		$pagination->text  = $this->language->get('text_pagination');
		$pagination->url   = $this->url->link('sale/customer/reward', 'customer_id=' . $this->request->get['customer_id'] . "&page={page}", true);
		$vrs['pagination'] = $pagination->render();

		return $this->view('template/sale/customer_reward.tpl', $vrs);
	}

	public function addBlacklist()
	{
		$json = array();
		$this->registry->language('sale/customer');
		if (isset($this->request->post['ip']))
		{
			if (!$this->user->hasPermission('modify', 'sale/customer'))
			{
				$json['error'] = $this->language->get('error_permission');
			}
			else
			{
				$this->registry->model('sale/customer');
				$this->model_sale_customer->addBlacklist($this->request->post['ip']);
				$json['success'] = $this->language->get('text_success');
			}
		}

		return json_encode($json);
	}

	public function removeBlacklist()
	{
		$json = array();
		$this->registry->language('sale/customer');
		if (isset($this->request->post['ip']))
		{
			if (!$this->user->hasPermission('modify', 'sale/customer'))
			{
				$json['error'] = $this->language->get('error_permission');
			}
			else
			{
				$this->registry->model('sale/customer');
				$this->model_sale_customer->deleteBlacklist($this->request->post['ip']);
				$json['success'] = $this->language->get('text_success');
			}
		}

		return json_encode($json);
	}

	public function autocomplete()
	{
		$json = array();
		if (isset($this->request->get['search']))
		{
			$this->registry->model('sale/customer');
			$data    = array(
				'search' => $this->request->get['search'],
				'start'  => 0,
				'limit'  => 20
			);
			$results = $this->model_sale_customer->gets($data);
			foreach ($results as $result)
			{
				$json[] = array(
					'customer_id'       => $result['customer_id'],
					'customer_group_id' => $result['customer_group_id'],
					'name'              => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'customer_group'    => $result['customer_group'],
					'firstname'         => $result['firstname'],
					'lastname'          => $result['lastname'],
					'email'             => $result['email'],
					'telephone'         => $result['telephone'],
					'fax'               => $result['fax'],
					'address'           => $this->model_sale_customer->getAddresses($result['customer_id'])
				);
			}
		}

		$sort_order = array();
		foreach ($json as $key => $value)
		{
			$sort_order[$key] = $value['name'];
		}
		array_multisort($sort_order, SORT_ASC, $json);

		return json_encode($json);
	}

	public function address()
	{
		$json = array();
		if (!empty($this->request->get['address_id']))
		{
			$this->registry->model('sale/customer');
			$json = $this->model_sale_customer->getAddress($this->request->get['address_id']);
		}

		return json_encode($json);
	}
}
?>