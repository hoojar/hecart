<?php
class ControllerCommonInformationGroup extends Controller
{
	private $error = array();

	public function index()
	{
		$this->registry->language('common/information_group');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('common/information_group');

		return $this->getList();
	}

	public function insert()
	{
		$this->registry->language('common/information_group');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('common/information_group');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm())
		{
			$this->model_common_information_group->addInformationGroup($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
			$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';
			$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';
			$this->registry->redirect($this->url->link('common/information_group', $url, true));
		}

		return $this->getForm();
	}

	public function update()
	{
		$this->registry->language('common/information_group');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('common/information_group');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm())
		{
			$this->model_common_information_group->editInformationGroup($this->request->get['information_group_id'], $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
			$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';
			$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';
			$this->registry->redirect($this->url->link('common/information_group', $url, true));
		}

		return $this->getForm();
	}

	public function delete()
	{
		$this->registry->language('common/information_group');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('common/information_group');

		if (isset($this->request->post['selected']) && $this->validateDelete())
		{
			foreach ($this->request->post['selected'] as $information_group_id)
			{
				$this->model_common_information_group->deleteInformationGroup($information_group_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
			$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';
			$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';
			$this->registry->redirect($this->url->link('common/information_group', $url, true));
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
			'href'      => $this->url->link('common/information_group', $url, true),
			'separator' => ' :: '
		);

		$vrs['insert']             = $this->url->link('common/information_group/insert', $url, true);
		$vrs['delete']             = $this->url->link('common/information_group/delete', $url, true);
		$vrs['information_groups'] = array();
		$data                      = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ((int)$page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);

		$information_group_total = $this->model_common_information_group->getTotalInformationGroups();
		$results                 = $this->model_common_information_group->getInformationGroups($data);
		foreach ($results as $result)
		{
			$action = array();
			if ($this->config->mpermission)
			{
				$action[] = array(
					'text' => $this->language->get('text_edit'),
					'href' => $this->url->link('common/information_group/update', 'information_group_id=' . $result['information_group_id'] . $url, true)
				);
			}

			$vrs['information_groups'][] = array(
				'information_group_id' => $result['information_group_id'],
				'name'                 => $result['name'],
				'sort_order'           => $result['sort_order'],
				'selected'             => isset($this->request->post['selected']) && in_array($result['information_group_id'], $this->request->post['selected']),
				'action'               => $action
			);
		}

		$vrs['heading_title']     = $this->language->get('heading_title');
		$vrs['text_no_results']   = $this->language->get('text_no_results');
		$vrs['column_name']       = $this->language->get('column_name');
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

		$vrs['sort_name']       = $this->url->link('common/information_group', 'sort=agd.name' . $url, true);
		$vrs['sort_sort_order'] = $this->url->link('common/information_group', 'sort=ag.sort_order' . $url, true);

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
		$pagination->total = $information_group_total;
		$pagination->page  = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text  = $this->language->get('text_pagination');
		$pagination->url   = $this->url->link('common/information_group', "{$url}&page={page}", true);

		$vrs['pagination'] = $pagination->render();
		$vrs['sort']       = $sort;
		$vrs['order']      = $order;

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/common/information_group_list.tpl', $vrs);
	}

	private function getForm()
	{
		$vrs['heading_title']    = $this->language->get('heading_title');
		$vrs['entry_name']       = $this->language->get('entry_name');
		$vrs['entry_sort_order'] = $this->language->get('entry_sort_order');
		$vrs['button_save']      = $this->language->get('button_save');
		$vrs['button_cancel']    = $this->language->get('button_cancel');
		$vrs['error_warning']    = isset($this->error['warning']) ? $this->error['warning'] : '';
		$vrs['error_name']       = isset($this->error['name']) ? $this->error['name'] : array();

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
			'href'      => $this->url->link('common/information_group', $url, true),
			'separator' => ' :: '
		);

		if (!isset($this->request->get['information_group_id']))
		{
			$vrs['action'] = $this->url->link('common/information_group/insert', $url, true);
		}
		else
		{
			$vrs['action'] = $this->url->link('common/information_group/update', 'information_group_id=' . $this->request->get['information_group_id'] . $url, true);
		}
		$vrs['cancel'] = $this->url->link('common/information_group', $url, true);

		if (isset($this->request->get['information_group_id']))
		{
			$information_group_info = $this->model_common_information_group->getInformationGroup($this->request->get['information_group_id']);
		}

		$this->registry->model('setting/language');
		$vrs['languages'] = $this->model_setting_language->getLanguages();

		if (isset($this->request->post['information_group_description']))
		{
			$vrs['information_group_description'] = $this->request->post['information_group_description'];
		}
		elseif (isset($this->request->get['information_group_id']))
		{
			$vrs['information_group_description'] = $this->model_common_information_group->getInformationGroupDescriptions($this->request->get['information_group_id']);
		}
		else
		{
			$vrs['information_group_description'] = array();
		}

		if (isset($this->request->post['sort_order']))
		{
			$vrs['sort_order'] = $this->request->post['sort_order'];
		}
		elseif (!empty($information_group_info))
		{
			$vrs['sort_order'] = $information_group_info['sort_order'];
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

		return $this->view('template/common/information_group_form.tpl', $vrs);
	}

	private function validateForm()
	{
		if (!$this->user->hasPermission('modify', 'common/information_group'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['information_group_description'] as $language_id => $value)
		{
			if ((mb_strlen($value['name']) < 2) || (mb_strlen($value['name']) > 64))
			{
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
		}

		return !$this->error;
	}

	private function validateDelete()
	{
		if (!$this->user->hasPermission('modify', 'common/information_group'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->registry->model('common/information');
		foreach ($this->request->post['selected'] as $information_group_id)
		{
			$information_total = $this->model_common_information->getTotalInformationsByInformationGroupId($information_group_id);

			if ($information_total)
			{
				$this->error['warning'] = sprintf($this->language->get('error_information'), $information_total);
			}
		}

		return !$this->error;
	}
}
?>