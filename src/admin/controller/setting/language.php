<?php
class ControllerSettingLanguage extends Controller
{
	private $error = array();

	public function index()
	{
		$this->registry->language('setting/language');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('setting/language');

		return $this->getList();
	}

	public function insert()
	{
		$this->registry->language('setting/language');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('setting/language');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm())
		{
			$this->model_setting_language->addLanguage($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
			$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';
			$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';
			$this->registry->redirect($this->url->link('setting/language', $url, true));
		}

		return $this->getForm();
	}

	public function update()
	{
		$this->registry->language('setting/language');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('setting/language');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm())
		{
			$this->model_setting_language->editLanguage($this->request->get['language_id'], $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
			$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';
			$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';
			$this->registry->redirect($this->url->link('setting/language', $url, true));
		}

		return $this->getForm();
	}

	public function delete()
	{
		$this->registry->language('setting/language');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('setting/language');

		if (isset($this->request->post['selected']) && $this->validateDelete())
		{
			foreach ($this->request->post['selected'] as $language_id)
			{
				$this->model_setting_language->deleteLanguage($language_id);
			}
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			$url .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
			$url .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';
			$url .= isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';
			$this->registry->redirect($this->url->link('setting/language', $url, true));
		}

		return $this->getList();
	}

	private function getList()
	{
		$sort  = isset($this->request->get['sort']) ? $this->request->get['sort'] : 'name';
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
			'href'      => $this->url->link('setting/language', $url, true),
			'separator' => ' :: '
		);

		$vrs['insert']    = $this->url->link('setting/language/insert', $url, true);
		$vrs['delete']    = $this->url->link('setting/language/delete', $url, true);
		$vrs['languages'] = array();
		$data             = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ((int)$page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);

		$language_total = $this->model_setting_language->getTotalLanguages();
		$results        = $this->model_setting_language->getLanguages($data);
		foreach ($results as $result)
		{
			$action = array();
			if ($this->config->mpermission)
			{
				$action[] = array(
					'text' => $this->language->get('text_edit'),
					'href' => $this->url->link('setting/language/update', 'language_id=' . $result['language_id'] . $url, true)
				);
			}

			$vrs['languages'][] = array(
				'language_id' => $result['language_id'],
				'name'        => $result['name'] . (($result['code'] == $this->config->get('config_language')) ? $this->language->get('text_default') : null),
				'code'        => $result['code'],
				'sort_order'  => $result['sort_order'],
				'selected'    => isset($this->request->post['selected']) && in_array($result['language_id'], $this->request->post['selected']),
				'action'      => $action
			);
		}
		$vrs['heading_title']     = $this->language->get('heading_title');
		$vrs['text_no_results']   = $this->language->get('text_no_results');
		$vrs['column_name']       = $this->language->get('column_name');
		$vrs['column_code']       = $this->language->get('column_code');
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

		$vrs['sort_name']       = $this->url->link('setting/language', 'sort=name' . $url, true);
		$vrs['sort_code']       = $this->url->link('setting/language', 'sort=code' . $url, true);
		$vrs['sort_sort_order'] = $this->url->link('setting/language', 'sort=sort_order' . $url, true);

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
		$pagination->total = $language_total;
		$pagination->page  = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text  = $this->language->get('text_pagination');
		$pagination->url   = $this->url->link('setting/language', "{$url}&page={page}", true);

		$vrs['pagination'] = $pagination->render();
		$vrs['sort']       = $sort;
		$vrs['order']      = $order;

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/setting/language_list.tpl', $vrs);
	}

	private function getForm()
	{
		$vrs['heading_title']    = $this->language->get('heading_title');
		$vrs['text_enabled']     = $this->language->get('text_enabled');
		$vrs['text_disabled']    = $this->language->get('text_disabled');
		$vrs['entry_name']       = $this->language->get('entry_name');
		$vrs['entry_code']       = $this->language->get('entry_code');
		$vrs['entry_locale']     = $this->language->get('entry_locale');
		$vrs['entry_image']      = $this->language->get('entry_image');
		$vrs['entry_directory']  = $this->language->get('entry_directory');
		$vrs['entry_filename']   = $this->language->get('entry_filename');
		$vrs['entry_sort_order'] = $this->language->get('entry_sort_order');
		$vrs['entry_status']     = $this->language->get('entry_status');
		$vrs['button_save']      = $this->language->get('button_save');
		$vrs['button_cancel']    = $this->language->get('button_cancel');
		$vrs['error_warning']    = isset($this->error['warning']) ? $this->error['warning'] : '';
		$vrs['error_name']       = isset($this->error['name']) ? $this->error['name'] : '';
		$vrs['error_code']       = isset($this->error['code']) ? $this->error['code'] : '';
		$vrs['error_locale']     = isset($this->error['locale']) ? $this->error['locale'] : '';
		$vrs['error_image']      = isset($this->error['image']) ? $this->error['image'] : '';
		$vrs['error_directory']  = isset($this->error['directory']) ? $this->error['directory'] : '';
		$vrs['error_filename']   = isset($this->error['filename']) ? $this->error['filename'] : '';

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
			'href'      => $this->url->link('setting/language', $url, true),
			'separator' => ' :: '
		);

		if (!isset($this->request->get['language_id']))
		{
			$vrs['action'] = $this->url->link('setting/language/insert', $url, true);
		}
		else
		{
			$vrs['action'] = $this->url->link('setting/language/update', 'language_id=' . $this->request->get['language_id'] . $url, true);
		}
		$vrs['cancel'] = $this->url->link('setting/language', $url, true);
		if (isset($this->request->get['language_id']))
		{
			$language_info = $this->model_setting_language->getLanguage($this->request->get['language_id']);
		}

		if (isset($this->request->post['name']))
		{
			$vrs['name'] = $this->request->post['name'];
		}
		elseif (!empty($language_info))
		{
			$vrs['name'] = $language_info['name'];
		}
		else
		{
			$vrs['name'] = '';
		}

		if (isset($this->request->post['code']))
		{
			$vrs['code'] = $this->request->post['code'];
		}
		elseif (!empty($language_info))
		{
			$vrs['code'] = $language_info['code'];
		}
		else
		{
			$vrs['code'] = '';
		}

		if (isset($this->request->post['locale']))
		{
			$vrs['locale'] = $this->request->post['locale'];
		}
		elseif (!empty($language_info))
		{
			$vrs['locale'] = $language_info['locale'];
		}
		else
		{
			$vrs['locale'] = '';
		}

		if (isset($this->request->post['image']))
		{
			$vrs['image'] = $this->request->post['image'];
		}
		elseif (!empty($language_info))
		{
			$vrs['image'] = $language_info['image'];
		}
		else
		{
			$vrs['image'] = '';
		}

		if (isset($this->request->post['directory']))
		{
			$vrs['directory'] = $this->request->post['directory'];
		}
		elseif (!empty($language_info))
		{
			$vrs['directory'] = $language_info['directory'];
		}
		else
		{
			$vrs['directory'] = '';
		}

		if (isset($this->request->post['filename']))
		{
			$vrs['filename'] = $this->request->post['filename'];
		}
		elseif (!empty($language_info))
		{
			$vrs['filename'] = $language_info['filename'];
		}
		else
		{
			$vrs['filename'] = '';
		}

		if (isset($this->request->post['sort_order']))
		{
			$vrs['sort_order'] = $this->request->post['sort_order'];
		}
		elseif (!empty($language_info))
		{
			$vrs['sort_order'] = $language_info['sort_order'];
		}
		else
		{
			$vrs['sort_order'] = '';
		}

		if (isset($this->request->post['status']))
		{
			$vrs['status'] = $this->request->post['status'];
		}
		elseif (!empty($language_info))
		{
			$vrs['status'] = $language_info['status'];
		}
		else
		{
			$vrs['status'] = 1;
		}

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/setting/language_form.tpl', $vrs);
	}

	private function validateForm()
	{
		if (!$this->user->hasPermission('modify', 'setting/language'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}
		if ((mb_strlen($this->request->post['name']) < 2) || (mb_strlen($this->request->post['name']) > 32))
		{
			$this->error['name'] = $this->language->get('error_name');
		}
		if (mb_strlen($this->request->post['code']) < 2)
		{
			$this->error['code'] = $this->language->get('error_code');
		}
		if (!$this->request->post['locale'])
		{
			$this->error['locale'] = $this->language->get('error_locale');
		}
		if (!$this->request->post['directory'])
		{
			$this->error['directory'] = $this->language->get('error_directory');
		}
		if (!$this->request->post['filename'])
		{
			$this->error['filename'] = $this->language->get('error_filename');
		}
		if ((mb_strlen($this->request->post['image']) < 3) || (mb_strlen($this->request->post['image']) > 32))
		{
			$this->error['image'] = $this->language->get('error_image');
		}

		return !$this->error;
	}

	private function validateDelete()
	{
		if (!$this->user->hasPermission('modify', 'setting/language'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->registry->model('setting/store');

		$this->registry->model('sale/order');
		foreach ($this->request->post['selected'] as $language_id)
		{
			$language_info = $this->model_setting_language->getLanguage($language_id);
			if ($language_info)
			{
				if ($this->config->get('config_language') == $language_info['code'])
				{
					$this->error['warning'] = $this->language->get('error_default');
				}
				if ($this->config->get('config_admin_language') == $language_info['code'])
				{
					$this->error['warning'] = $this->language->get('error_admin');
				}
				$store_total = $this->model_setting_store->getTotalStoresByLanguage($language_info['code']);
				if ($store_total)
				{
					$this->error['warning'] = sprintf($this->language->get('error_store'), $store_total);
				}
			}
			$order_total = $this->model_sale_order->getTotalOrdersByLanguageId($language_id);
			if ($order_total)
			{
				$this->error['warning'] = sprintf($this->language->get('error_order'), $order_total);
			}
		}

		return !$this->error;
	}
}
?>