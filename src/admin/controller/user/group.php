<?php
/**
 * 权限组相关处理
 */
class ControllerUserGroup extends Controller
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
	 * 列表权限组
	 */
	public function index()
	{
		$this->registry->model('user/group');
		$this->registry->language('user/group');
		$this->document->title($this->language->get('heading_title'));

		return $this->getList();
	}

	/**
	 * 新增权限组
	 */
	public function insert()
	{
		$this->registry->model('user/group');
		$this->registry->language('user/group');
		$this->document->title($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm())
		{
			$this->model_user_group->add($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->registry->redirect($this->url->link('user/group', $this->args, true));
		}

		return $this->getForm();
	}

	/**
	 * 修改权限组
	 */
	public function update()
	{
		$this->registry->model('user/group');
		$this->registry->language('user/group');
		$this->document->title($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm())
		{
			$this->model_user_group->edit($this->request->get['group_id'], $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->registry->redirect($this->url->link('user/group', $this->args, true));
		}

		return $this->getForm();
	}

	/**
	 * 删除权限组
	 */
	public function delete()
	{
		$this->registry->model('user/group');
		$this->registry->language('user/group');
		$this->document->title($this->language->get('heading_title'));

		if (!empty($this->request->post['selected']) && $this->validateDelete())
		{
			foreach ($this->request->post['selected'] as $group_id)
			{
				$this->model_user_group->del($group_id);
			}

			$this->session->data['success'] = $this->language->get('text_delete');
			$this->registry->redirect($this->url->link('user/group', $this->args, true));
		}

		return $this->getList();
	}

	/**
	 * 获取权限组列表
	 */
	private function getList()
	{
		$vrs          = $this->language->data;
		$vrs['sort']  = $this->request->get_var('sort', 's');
		$vrs['order'] = $this->request->get_var('order', 's', '', 'DESC');
		$page         = $this->request->get_var('page', 'i', '', 1);

		$vrs['insert'] = $this->url->link('user/group/insert', $this->args, true);
		$vrs['delete'] = $this->url->link('user/group/delete', $this->args, true);

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
			'href'      => $this->url->link('user/group', $this->args, true),
			'separator' => ' :: '
		);

		/**
		 * 查询处理
		 */
		$data          = array(
			'sort'  => $vrs['sort'],
			'order' => $vrs['order'],
			'start' => ((int)$page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);
		$vrs['groups'] = $this->model_user_group->gets($data);
		foreach ($vrs['groups'] as $key => $group)
		{
			$action = array();
			if ($this->config->mpermission)
			{
				$action[] = array(
					'text' => $this->language->get('text_edit'),
					'href' => $this->url->link('user/group/update', 'group_id=' . $group['group_id'] . $this->args, true)
				);
			}
			$vrs['groups'][$key]['action'] = $action;
		}

		$vrs['success']       = '';
		$vrs['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		if (isset($this->session->data['success']))
		{
			$vrs['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}

		/**
		 * 排序处理
		 */
		$url              = '&order=' . ($vrs['order'] == 'ASC' ? 'DESC' : 'ASC') . "&page={$page}";//连接组合处理
		$vrs['sort_name'] = $this->url->link('user/group', 'sort=name' . $url, true);

		/**
		 * 分页处理
		 */
		$pagination        = new Pagination();
		$pagination->total = $this->model_user_group->gets($data, true);
		$pagination->page  = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text  = $this->language->get('text_pagination');
		$pagination->url   = $this->url->link('user/group', "&sort={$vrs['sort']}&order={$vrs['order']}&page={page}", true);
		$vrs['pagination'] = $pagination->render();

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/user/group_list.tpl', $vrs);
	}

	/**
	 * 新增或修改权限组表单
	 */
	private function getForm()
	{
		$vrs                  = $this->language->data;
		$vrs['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$vrs['error_name']    = isset($this->error['name']) ? $this->error['name'] : '';

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
			'href'      => $this->url->link('user/group', $this->args, true),
			'separator' => ' :: '
		);

		$vrs['cancel'] = $this->url->link('user/group', $this->args, true);
		$vrs['action'] = $this->url->link('user/group/insert', $this->args, true);

		$this->registry->model('user/org');
		$vrs['orgs']     = $this->model_user_org->getTree();
		$vrs['group_id'] = $this->request->get_var('group_id', 'i');
		if (!empty($vrs['group_id']))
		{
			$group_info          = $this->model_user_group->get($vrs['group_id']);
			$this->request->post = array_merge($group_info, $this->request->post);
			$vrs['action']       = $this->url->link('user/group/update', "group_id={$vrs['group_id']}{$this->args}", true);
		}

		$ignore = array(
			'common/home',
			'common/startup',
			'common/login',
			'common/logout',
			'common/forgotten',
			'common/reset',
			'error/not_found',
			'error/permission',
			'common/page_footer',
			'common/page_header'
		);

		$vrs['permissions_data'] = $this->language->data;
		$vrs['permissions']      = array();
		$files                   = glob(DIR_SITE . '/controller/*/*.php');
		foreach ($files as $file)
		{
			$data       = explode('/', dirname($file));
			$permission = end($data) . '/' . basename($file, '.php');
			if (!in_array($permission, $ignore))
			{
				$vrs['permissions'][] = $permission;
			}
		}

		$vrs['name']        = $this->request->get_var('name', 's', 'p');
		$vrs['orgpos']      = $this->request->get_var('orgpos', 's', 'p');
		$vrs['description'] = $this->request->get_var('description', 's', 'p');

		$permission    = $this->request->get_var('permission', 'a', 'p');
		$vrs['access'] = isset($permission['access']) ? $permission['access'] : array();
		$vrs['modify'] = isset($permission['modify']) ? $permission['modify'] : array();

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/user/group_form.tpl', $vrs);
	}

	/**
	 * 新增与修改权限组表单校验
	 */
	private function validateForm()
	{
		if (!$this->user->hasPermission('modify', 'user/group'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((mb_strlen(trim($this->request->post['name'])) < 2) || (mb_strlen($this->request->post['name']) > 64))
		{
			$this->error['name'] = $this->language->get('error_name');
		}

		$gorup_info = $this->model_user_group->getByName($this->request->post['name']);
		if ($gorup_info && $gorup_info['group_id'] != $this->request->get_var('group_id'))
		{
			$this->error['warning'] = $this->language->get('error_exists');
		}

		return !$this->error;
	}

	/**
	 * 删除权限组表单校验
	 */
	private function validateDelete()
	{
		$this->registry->model('user/user');
		if (!$this->user->hasPermission('modify', 'user/group'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['selected'] as $group_id)
		{
			$user_total = $this->model_user_user->getTotalByGroupId($group_id);
			if ($user_total)
			{
				$this->error['warning'] = sprintf($this->language->get('error_user'), $user_total);
			}
		}

		return !$this->error;
	}
}
?>