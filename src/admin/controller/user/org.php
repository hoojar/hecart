<?php
/**
 * 机构处理
 */
class ControllerUserOrg extends Controller
{
	private $args = '';
	private $error = array();

	public function __construct($registry)
	{
		parent::__construct($registry);

		$this->args = isset($this->request->get['page']) ? '&page=' . $this->request->get['page'] : '';
		$this->args .= isset($this->request->get['sort']) ? '&sort=' . $this->request->get['sort'] : '';
		$this->args .= isset($this->request->get['order']) ? '&order=' . $this->request->get['order'] : '';
	}

	/**
	 * 列表机构
	 */
	public function index()
	{
		$this->registry->model('user/org');
		$this->registry->language('user/org');
		$this->document->title($this->language->get('heading_title'));

		return $this->getList();
	}

	/**
	 * 新增机构
	 */
	public function insert()
	{
		$this->registry->model('user/org');
		$this->registry->language('user/org');
		$this->document->title($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm())
		{
			$data          = array(
				'name'       => $this->request->get_var('name'),
				'memo'       => $this->request->get_var('memo'),
				'email'      => $this->request->get_var('email'),
				'tel'        => $this->request->get_var('tel'),
				'parent_id'  => $this->request->get_var('parent_id', 'i'),
				'user_total' => $this->request->get_var('user_total', 'i'),
				'notify_url' => $this->request->get_var('notify_url')
			);
			$data['spell'] = '';
			$len           = mb_strlen($data['name'], 'utf-8');
			for ($i = 0; $i < $len; $i++)
			{
				$str = mb_substr($data['name'], $i, 1, 'utf-8');
				$data['spell'] .= wcore_pinyin::first($str);
			}
			$this->model_user_org->insert($data);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->registry->redirect($this->url->link('user/org'));
		}

		return $this->getForm();
	}

	/**
	 * 更新与修改机构
	 */
	public function update()
	{
		$this->registry->model('user/org');
		$this->registry->language('user/org');
		$this->document->title($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm())
		{
			$data          = array(
				'org_id'     => $this->request->get_var('org_id', 'i'),
				'name'       => $this->request->get_var('name'),
				'memo'       => $this->request->get_var('memo'),
				'email'      => $this->request->get_var('email'),
				'tel'        => $this->request->get_var('tel'),
				'parent_id'  => $this->request->get_var('parent_id', 'i'),
				'user_total' => $this->request->get_var('user_total', 'i'),
				'notify_url' => $this->request->get_var('notify_url')
			);
			$data['spell'] = '';
			$len           = mb_strlen($data['name'], 'utf-8');
			for ($i = 0; $i < $len; $i++)
			{
				$str = mb_substr($data['name'], $i, 1, 'utf-8');
				$data['spell'] .= wcore_pinyin::first($str);
			}
			$this->model_user_org->update($data);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->registry->redirect($this->url->link('user/org', $this->args, true));
		}

		return $this->getForm();
	}

	/**
	 * 删除机构
	 */
	public function delete()
	{
		$this->registry->model('user/org');
		$this->registry->language('user/org');
		$this->document->title($this->language->get('heading_title'));

		if (!empty($this->request->post['selected']) && $this->validateDelete())
		{
			foreach ($this->request->post['selected'] as $org_id)
			{
				$this->model_user_org->del($org_id);
			}

			$this->session->data['success'] = $this->language->get('text_delete');
			$this->registry->redirect($this->url->link('user/org', $this->args, true));
		}

		return $this->getList();
	}

	/**
	 * 获取机构列表
	 */
	private function getList()
	{
		/**
		 * 导航栏组合
		 */
		$vrs                  = $this->language->data;
		$vrs['breadcrumbs']   = array();
		$vrs['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', true),
			'separator' => false
		);
		$vrs['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('user/org', $this->args, true),
			'separator' => ' :: '
		);

		$vrs['insert']        = $this->url->link('user/org/insert', $this->args, true);
		$vrs['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';

		$vrs['success'] = '';
		if (isset($this->session->data['success']))
		{
			$vrs['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}

		/**
		 * 查询处理
		 */
		$page            = $this->request->get_var('page', 'i', '', 1);
		$data            = array(
			'start'         => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit'         => $this->config->get('config_admin_limit'),
			'filter_org_id' => $this->request->get_var('org_id', 'i')
		);
		$vrs['org_id']   = $data['filter_org_id'];
		$vrs['orgs']     = $this->model_user_org->getTree();
		$vrs['org_list'] = $this->model_user_org->gets($data);

		/**
		 * 分页处理
		 */
		$pagination        = new Pagination();
		$pagination->page  = $page;
		$pagination->total = $this->model_user_org->gets($data, true);
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text  = $this->language->get('text_pagination');
		$pagination->url   = $this->url->link('user/org', "page={page}", true);
		$vrs['pagination'] = $pagination->render();

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/user/org_list.tpl', $vrs);
	}

	/**
	 * 修改机构获取表单
	 */
	private function getForm()
	{
		$vrs                     = $this->language->data;
		$vrs['error_warning']    = isset($this->error['warning']) ? $this->error['warning'] : '';
		$vrs['error_name']       = isset($this->error['name']) ? $this->error['name'] : '';
		$vrs['error_memo']       = isset($this->error['memo']) ? $this->error['memo'] : '';
		$vrs['error_email']      = isset($this->error['email']) ? $this->error['email'] : '';
		$vrs['error_tel']        = isset($this->error['tel']) ? $this->error['tel'] : '';
		$vrs['error_parent_id']  = isset($this->error['parent_id']) ? $this->error['parent_id'] : '';
		$vrs['error_user_total'] = isset($this->error['user_total']) ? $this->error['user_total'] : '';
		$vrs['error_notify_url'] = isset($this->error['notify_url']) ? $this->error['notify_url'] : '';

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
			'href'      => $this->url->link('user/org', $this->args, true),
			'separator' => ' :: '
		);

		$vrs['cancel'] = $this->url->link('user/org', $this->args, true);
		$vrs['action'] = $this->url->link('user/org/insert', $this->args, true);

		$vrs['orgs']   = $this->model_user_org->getTree();
		$vrs['org_id'] = $this->request->get_var('org_id', 'i');
		if (!empty($vrs['org_id']))
		{
			$vrs['action']       = $this->url->link('user/org/update', 'org_id=' . $vrs['org_id']);
			$org_info            = $this->model_user_org->get($vrs['org_id']);
			$this->request->post = array_merge($org_info, $this->request->post);
		}

		$vrs['name']         = $this->request->get_var('name', 's', 'p');
		$vrs['memo']         = $this->request->get_var('memo', 's', 'p');
		$vrs['email']        = $this->request->get_var('email', 's', 'p');
		$vrs['tel']          = $this->request->get_var('tel', 's', 'p');
		$vrs['parent_id']    = $this->request->get_var('parent_id', 'i', 'p');
		$vrs['user_total']   = $this->request->get_var('user_total', 'i', 'p', 5);
		$vrs['notify_url']   = $this->request->get_var('notify_url', 's', 'p');
		$vrs['rebate_value'] = $this->request->get_var('rebate_value', 'f', 'p');

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/user/org_form.tpl', $vrs);
	}

	/**
	 * 新增或修改机构表单校验
	 */
	private function validateForm()
	{
		if (!$this->user->hasPermission('modify', 'user/org'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!trim($this->request->post['name']) || (mb_strlen($this->request->post['name']) < 1) || (mb_strlen($this->request->post['name']) > 100))
		{
			$this->error['name'] = $this->language->get('error_name');
		}

		if (!isset($this->request->post['user_total']) || $this->request->post['user_total'] < 1)
		{
			$this->error['user_total'] = $this->language->get('error_user_total');
		}

		$org_info = $this->model_user_org->getByName($this->request->post['name']);
		if ($org_info && $org_info['org_id'] != $this->request->get_var('org_id'))
		{
			$this->error['warning'] = $this->language->get('error_exists');
		}

		if ($this->request->get_var('email') && !wcore_validate::email($this->request->get_var('email')))
		{
			$this->error['email'] = $this->language->get('error_email');
		}

		if ($this->request->get_var('tel') && !wcore_validate::mobile($this->request->get_var('tel')))
		{
			$this->error['tel'] = $this->language->get('error_tel');
		}

		if ($this->request->get_var('notify_url') && !wcore_validate::url($this->request->get_var('notify_url')))
		{
			$this->error['notify_url'] = $this->language->get('error_notify_url');
		}

		/**
		 * 判断是否为拥有所有机构权限,如果不是则需要判断指定的父机构下创建子机构是否有权限
		 */
		if ($this->user->getOrgPos() != '*')
		{
			$orgs      = $this->model_user_org->getTree();
			$parent_id = $this->request->get_var('parent_id', 'i');
			if (!isset($orgs[$parent_id]))
			{
				$this->error['parent_id'] = $this->language->get('error_parent_id');
			}
		}

		return !$this->error;
	}

	/**
	 * 删除机构表单校验
	 */
	private function validateDelete()
	{
		if (!$this->user->hasPermission('modify', 'user/org'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['selected'] as $org_id)
		{
			if ($this->user->getOrgId() == $org_id)
			{
				$this->error['warning'] = $this->language->get('error_account');
			}
		}

		return !$this->error;
	}
}
?>