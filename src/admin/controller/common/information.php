<?php
class ControllerCommonInformation extends Controller
{
	private $error = array();

	public function index()
	{
		$this->registry->language('common/information');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('common/information');

		return $this->getList();
	}

	public function insert()
	{
		$this->registry->language('common/information');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('common/information');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm())
		{
			$this->model_common_information->addInformation($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
			$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';
			$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';
			$this->registry->redirect($this->url->link('common/information', $url, true));
		}

		return $this->getForm();
	}

	public function update()
	{
		$this->registry->language('common/information');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('common/information');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm())
		{
			$this->model_common_information->editInformation($this->request->get['information_id'], $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
			$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';
			$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';
			$this->registry->redirect($this->url->link('common/information', $url, true));
		}

		return $this->getForm();
	}

	public function delete()
	{
		$this->registry->language('common/information');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('common/information');
		if (isset($this->request->post['selected']) && $this->validateDelete())
		{
			foreach ($this->request->post['selected'] as $information_id)
			{
				$this->model_common_information->deleteInformation($information_id);
			}
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
			$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';
			$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';
			$this->registry->redirect($this->url->link('common/information', $url, true));
		}

		return $this->getList();
	}

	private function getList()
	{
		$sort  = isset($this->request->get['sort']) ? $this->request->get['sort'] : 'id.title';
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
			'href'      => $this->url->link('common/information', $url, true),
			'separator' => ' :: '
		);

		$vrs['insert']       = $this->url->link('common/information/insert', $url, true);
		$vrs['delete']       = $this->url->link('common/information/delete', $url, true);
		$vrs['informations'] = array();
		$data                = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ((int)$page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);

		$information_total = $this->model_common_information->getTotalInformations();
		$results           = $this->model_common_information->getInformations($data);
		foreach ($results as $result)
		{
			$action = array();
			if ($this->config->mpermission)
			{
				$action[] = array(
					'text' => $this->language->get('text_edit'),
					'href' => $this->url->link('common/information/update', 'information_id=' . $result['information_id'] . $url, true)
				);
			}

			$vrs['informations'][] = array(
				'information_id' => $result['information_id'],
				'title'          => $result['title'],
				'sort_order'     => $result['sort_order'],
				'selected'       => isset($this->request->post['selected']) && in_array($result['information_id'], $this->request->post['selected']),
				'action'         => $action
			);
		}
		$vrs['heading_title']     = $this->language->get('heading_title');
		$vrs['text_no_results']   = $this->language->get('text_no_results');
		$vrs['column_title']      = $this->language->get('column_title');
		$vrs['column_sort_order'] = $this->language->get('column_sort_order');
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

		$vrs['sort_title']      = $this->url->link('common/information', 'sort=id.title' . $url, true);
		$vrs['sort_sort_order'] = $this->url->link('common/information', 'sort=i.sort_order' . $url, true);

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
		$pagination->total = $information_total;
		$pagination->page  = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text  = $this->language->get('text_pagination');
		$pagination->url   = $this->url->link('common/information', "{$url}&page={page}", true);
		$vrs['pagination'] = $pagination->render();
		$vrs['sort']       = $sort;
		$vrs['order']      = $order;

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/common/information_list.tpl', $vrs);
	}

	private function getForm()
	{
		$vrs['heading_title']           = $this->language->get('heading_title');
		$vrs['text_default']            = $this->language->get('text_default');
		$vrs['text_enabled']            = $this->language->get('text_enabled');
		$vrs['text_disabled']           = $this->language->get('text_disabled');
		$vrs['entry_title']             = $this->language->get('entry_title');
		$vrs['entry_description']       = $this->language->get('entry_description');
		$vrs['entry_store']             = $this->language->get('entry_store');
		$vrs['entry_link_url']          = $this->language->get('entry_link_url');
		$vrs['entry_information_group'] = $this->language->get('entry_information_group');
		$vrs['entry_sort_order']        = $this->language->get('entry_sort_order');
		$vrs['entry_status']            = $this->language->get('entry_status');
		$vrs['entry_layout']            = $this->language->get('entry_layout');
		$vrs['button_save']             = $this->language->get('button_save');
		$vrs['button_cancel']           = $this->language->get('button_cancel');
		$vrs['tab_general']             = $this->language->get('tab_general');
		$vrs['tab_data']                = $this->language->get('tab_data');
		$vrs['tab_design']              = $this->language->get('tab_design');
		$vrs['error_warning']           = isset($this->error['warning']) ? $this->error['warning'] : '';
		$vrs['error_title']             = isset($this->error['title']) ? $this->error['title'] : array();
		$vrs['error_description']       = isset($this->error['description']) ? $this->error['description'] : array();

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
			'href'      => $this->url->link('common/information', $url, true),
			'separator' => ' :: '
		);

		if (!isset($this->request->get['information_id']))
		{
			$vrs['action'] = $this->url->link('common/information/insert', $url, true);
		}
		else
		{
			$vrs['action'] = $this->url->link('common/information/update', 'information_id=' . $this->request->get['information_id'] . $url, true);
		}
		$vrs['cancel'] = $this->url->link('common/information', $url, true);
		if (isset($this->request->get['information_id']))
		{
			$information_info = $this->model_common_information->getInformation($this->request->get['information_id']);
		}

		$this->registry->model('setting/language');
		$vrs['languages'] = $this->model_setting_language->getLanguages();
		if (isset($this->request->post['information_description']))
		{
			$vrs['information_description'] = $this->request->post['information_description'];
		}
		elseif (isset($this->request->get['information_id']))
		{
			$vrs['information_description'] = $this->model_common_information->getInformationDescriptions($this->request->get['information_id']);
		}
		else
		{
			$vrs['information_description'] = array();
		}

		if (isset($this->request->post['link_url']))
		{
			$vrs['link_url'] = $this->request->post['link_url'];
		}
		elseif (!empty($information_info))
		{
			$vrs['link_url'] = $information_info['link_url'];
		}
		else
		{
			$vrs['link_url'] = '';
		}

		if (isset($this->request->post['information_group_id']))
		{
			$vrs['information_group_id'] = $this->request->post['information_group_id'];
		}
		elseif (!empty($information_info))
		{
			$vrs['information_group_id'] = $information_info['information_group_id'];
		}
		else
		{
			$vrs['information_group_id'] = 0;
		}

		$this->registry->model('common/information_group');
		$vrs['information_groups'] = $this->model_common_information_group->getInformationGroups();
		if (isset($this->request->post['status']))
		{
			$vrs['status'] = $this->request->post['status'];
		}
		elseif (!empty($information_info))
		{
			$vrs['status'] = $information_info['status'];
		}
		else
		{
			$vrs['status'] = 1;
		}

		if (isset($this->request->post['sort_order']))
		{
			$vrs['sort_order'] = $this->request->post['sort_order'];
		}
		elseif (!empty($information_info))
		{
			$vrs['sort_order'] = $information_info['sort_order'];
		}
		else
		{
			$vrs['sort_order'] = '';
		}

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/common/information_form.tpl', $vrs);
	}

	private function validateForm()
	{
		if (!$this->user->hasPermission('modify', 'common/information'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['information_description'] as $language_id => $value)
		{
			if ((mb_strlen($value['title']) < 3) || (mb_strlen($value['title']) > 64))
			{
				$this->error['title'][$language_id] = $this->language->get('error_title');
			}
			if (mb_strlen($value['description']) < 3)
			{
				$this->error['description'][$language_id] = $this->language->get('error_description');
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
		if (!$this->user->hasPermission('modify', 'common/information'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->registry->model('setting/store');
		foreach ($this->request->post['selected'] as $information_id)
		{
			if ($this->config->get('config_account_id') == $information_id)
			{
				$this->error['warning'] = $this->language->get('error_account');
			}

			if ($this->config->get('config_checkout_id') == $information_id)
			{
				$this->error['warning'] = $this->language->get('error_checkout');
			}

			if ($this->config->get('config_affiliate_id') == $information_id)
			{
				$this->error['warning'] = $this->language->get('error_affiliate');
			}

			$store_total = $this->model_setting_store->getTotalStoresByInformationId($information_id);
			if ($store_total)
			{
				$this->error['warning'] = sprintf($this->language->get('error_store'), $store_total);
			}
		}

		return !$this->error;
	}
}
?>