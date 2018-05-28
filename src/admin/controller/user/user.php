<?php
/**
 * 后台用户处理
 */
class ControllerUserUser extends Controller
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
	 * 列表用户
	 */
	public function index()
	{
		$this->registry->model('user/user');
		$this->registry->language('user/user');
		$this->document->title($this->language->get('heading_title'));

		return $this->getList();
	}

	/*
	 * 启用与停用用户
	 */
	public function onoff()
	{
		$this->registry->model('user/user');
		$this->registry->language('user/user');

		$this->registry->model('user/org');
		$orgs = $this->model_user_org->getTree();

		$this->registry->model('user/group');
		$groups = $this->model_user_group->gets();

		$user_id   = $this->request->get_var('user_id', 'i');
		$user_info = $this->model_user_user->get($user_id);
		if (empty($user_info) || !isset($orgs[$user_info['org_id']]) || !isset($groups[$user_info['group_id']]))
		{
			exit($this->language->get('error_user'));
		}

		$data = array('status' => $this->request->get_var('status', 'i'));
		$user = $this->model_user_user->edit($user_id, $data);
		exit($user ? 'ok' : $this->language->get('error_user'));
	}

	/**
	 * 增加用户
	 */
	public function insert()
	{
		$this->registry->model('user/user');
		$this->registry->language('user/user');
		$this->document->title($this->language->get('heading_title'));

		$vrs = $this->language->data;
		$this->registry->model('user/org');
		$vrs['orgs'] = $this->model_user_org->getTree();

		$this->registry->model('user/group');
		$vrs['groups'] = $this->model_user_group->gets();

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm())
		{
			if (!isset($vrs['orgs'][$this->request->post['org_id']]) || !isset($vrs['groups'][$this->request->post['group_id']]))
			{
				$this->session->data['warning'] = $this->language->get('text_failed');
			}
			else
			{
				$data = array(
					'username'  => $this->request->get_var('username', 's'),
					'firstname' => $this->request->get_var('firstname', 's'),
					'lastname'  => $this->request->get_var('lastname', 's'),
					'password'  => $this->request->get_var('password', 's'),
					'org_id'    => $this->request->get_var('org_id', 'i'),
					'email'     => $this->request->get_var('email', 's'),
					'tel'       => $this->request->get_var('tel', 's'),
					'lang'      => $this->request->get_var('user_lang', 's'),
					'group_id'  => $this->request->get_var('group_id', 'i'),
					'status'    => $this->request->get_var('status', 'i'),
				);
				$this->model_user_user->add($data);
				$this->session->data['success'] = $this->language->get('text_success');
			}

			$this->registry->redirect($this->url->link('user/user', $this->args, true));
		}

		return $this->getForm($vrs);
	}

	/*
	 * 删除用户
	 */
	public function update()
	{
		$this->registry->model('user/user');
		$this->registry->language('user/user');
		$this->document->title($this->language->get('heading_title'));

		$vrs = $this->language->data;
		$this->registry->model('user/org');
		$vrs['orgs'] = $this->model_user_org->getTree();

		$this->registry->model('user/group');
		$vrs['groups'] = $this->model_user_group->gets();

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm())
		{
			if (!isset($vrs['orgs'][$this->request->post['org_id']]) || !isset($vrs['groups'][$this->request->post['group_id']]))
			{
				$this->session->data['warning'] = $this->language->get('text_failed');
			}
			else
			{
				unset($this->request->post['confirm']);
				$this->model_user_user->edit($this->request->get['user_id'], $this->request->post);
				$this->session->data['success'] = $this->language->get('text_success');
			}

			$this->registry->redirect($this->url->link('user/user', $this->args, true));
		}

		return $this->getForm($vrs);
	}

	/**
	 * 删除用户
	 */
	public function delete()
	{
		$this->registry->model('user/user');
		$this->registry->language('user/user');
		$this->document->title($this->language->get('heading_title'));

		if (!empty($this->request->post['selected']) && $this->validateDelete())
		{
			foreach ($this->request->post['selected'] as $user_id)
			{
				$this->model_user_user->del($user_id);
			}

			$this->session->data['success'] = $this->language->get('text_delete');
			$this->registry->redirect($this->url->link('user/user', $this->args, true));
		}

		return $this->getList();
	}

	/**
	 * 列表用户
	 */
	private function getList()
	{
		$vrs          = $this->language->data;
		$vrs['sort']  = $this->request->get_var('sort', 's', '', 'date_added');
		$vrs['order'] = $this->request->get_var('order', 's', '', 'DESC');
		$page         = $this->request->get_var('page', 'i', '', 1);

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
			'href'      => $this->url->link('user/user', $this->args, true),
			'separator' => ' :: '
		);

		$vrs['insert'] = $this->url->link('user/user/insert', $this->args, true);
		$vrs['delete'] = $this->url->link('user/user/delete', $this->args, true);

		/**
		 * 查询处理
		 */
		$this->registry->model('user/org');
		$vrs['orgs'] = $this->model_user_org->getTree();

		$this->registry->model('user/group');
		$vrs['groups']   = $this->model_user_group->gets();
		$vrs['group_id'] = $this->request->get_var('group_id', 'i');

		$data            = array(
			'sort'            => $vrs['sort'],
			'order'           => $vrs['order'],
			'start'           => ((int)$page - 1) * $this->config->get('config_admin_limit'),
			'limit'           => $this->config->get('config_admin_limit'),
			'filter_org_id'   => $this->request->get_var('org_id', 'i'),
			'filter_username' => trim($this->request->get_var('username')),
			'filter_group_id' => $this->request->get_var('group_id', 'i'),
		);
		$vrs['username'] = $data['filter_username'];
		$vrs['org_id']   = $data['filter_org_id'];
		$vrs['group_id'] = $data['filter_group_id'];

		$vrs['users'] = $this->model_user_user->gets($data);
		foreach ($vrs['users'] as &$user)
		{
			$user['action'] = array();
			if ($this->config->mpermission)
			{
				$user['action'][] = array(
					'text' => $this->language->get('text_edit'),
					'href' => $this->url->link('user/user/update', 'user_id=' . $user['user_id'] . $this->args, true)
				);
			}
			$user['selected'] = !empty($this->request->post['selected']) && in_array($user['user_id'], $this->request->post['selected']);
		}

		$vrs['warning']       = '';
		$vrs['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		if (isset($this->session->data['warning']))
		{
			$vrs['warning'] = $this->session->data['warning'];
			unset($this->session->data['warning']);
		}

		$vrs['success'] = '';
		if (isset($this->session->data['success']))
		{
			$vrs['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}

		/**
		 * 排序处理
		 */
		$url                    = '&order=' . ($vrs['order'] == 'ASC' ? 'DESC' : 'ASC') . "&page={$page}";//连接组合处理
		$vrs['sort_org']        = $this->url->link('user/user', 'sort=org_id' . $url, true);
		$vrs['sort_group']      = $this->url->link('user/user', 'sort=group_id' . $url, true);
		$vrs['sort_status']     = $this->url->link('user/user', 'sort=status' . $url, true);
		$vrs['sort_username']   = $this->url->link('user/user', 'sort=username' . $url, true);
		$vrs['sort_date_added'] = $this->url->link('user/user', 'sort=date_added' . $url, true);
		$vrs['sort_date_last']  = $this->url->link('user/user', 'sort=date_last' . $url, true);

		/**
		 * 分页处理
		 */
		$pagination        = new Pagination();
		$pagination->total = $this->model_user_user->gets($data, true);
		$pagination->page  = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text  = $this->language->get('text_pagination');
		$pagination->url   = $this->url->link('user/user', "&sort={$vrs['sort']}&order={$vrs['order']}&page={page}", true);
		$vrs['pagination'] = $pagination->render();

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/user/user_list.tpl', $vrs);
	}

	/**
	 * 获取表单数据
	 */
	private function getForm(&$vrs)
	{
		$vrs['error_org']       = isset($this->error['org']) ? $this->error['org'] : '';
		$vrs['error_tel']       = isset($this->error['tel']) ? $this->error['tel'] : '';
		$vrs['error_email']     = isset($this->error['email']) ? $this->error['email'] : '';
		$vrs['error_warning']   = isset($this->error['warning']) ? $this->error['warning'] : '';
		$vrs['error_username']  = isset($this->error['username']) ? $this->error['username'] : '';
		$vrs['error_firstname'] = isset($this->error['firstname']) ? $this->error['firstname'] : '';
		$vrs['error_lastname']  = isset($this->error['lastname']) ? $this->error['lastname'] : '';
		$vrs['error_password']  = isset($this->error['password']) ? $this->error['password'] : '';
		$vrs['error_confirm']   = isset($this->error['confirm']) ? $this->error['confirm'] : '';

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
			'href'      => $this->url->link('user/user', $this->args, true),
			'separator' => ' :: '
		);

		$vrs['cancel'] = $this->url->link('user/user', $this->args, true);
		$vrs['action'] = $this->url->link('user/user/insert', $this->args, true);

		$vrs['user_id'] = $this->request->get_var('user_id', 'i');
		if (!empty($vrs['user_id']))
		{
			$user_info = $this->model_user_user->get($vrs['user_id']);//用户只能修改其所属下的机构用户
			if (empty($user_info['org_id']) || !isset($vrs['orgs'][$user_info['org_id']]) || !isset($vrs['groups'][$user_info['group_id']]))
			{
				$this->session->data['warning'] = $this->language->get('text_failed');
				$this->registry->redirect($this->url->link('user/user', $this->args, true));
			}

			$vrs['action']       = $this->url->link('user/user/update', 'user_id=' . $vrs['user_id'] . $this->args, true);
			$this->request->post = array_merge($user_info, $this->request->post);
		}

		$vrs['org_id']    = $this->request->get_var('org_id', 'i', 'p');
		$vrs['group_id']  = $this->request->get_var('group_id', 'i', 'p');
		$vrs['username']  = $this->request->get_var('username', 's', 'p');
		$vrs['firstname'] = $this->request->get_var('firstname', 's', 'p');
		$vrs['lastname']  = $this->request->get_var('lastname', 's', 'p');
		$vrs['tel']       = $this->request->get_var('tel', 's', 'p');
		$vrs['email']     = $this->request->get_var('email', 's', 'p');
		$vrs['status']    = $this->request->get_var('status', 'i', 'p');
		$vrs['lang']      = $this->request->get_var('lang', 's', 'p', $this->config->get('config_admin_language'));

		$this->registry->model('setting/language');
		$vrs['languages'] = $this->model_setting_language->getLanguages();

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/user/user_form.tpl', $vrs);
	}

	/**
	 * 校验数据是否正确
	 */
	private function validateForm()
	{
		if (!$this->user->hasPermission('modify', 'user/user'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((mb_strlen($this->request->post['username']) < 3) || (mb_strlen($this->request->post['username']) > 20))
		{
			$this->error['username'] = $this->language->get('error_username');
		}

		if (!wcore_validate::alphanumeric($this->request->post['username']))
		{
			$this->error['username'] = $this->language->get('error_username_illegal');
		}

		if ((mb_strlen($this->request->post['firstname']) < 2) || (mb_strlen($this->request->post['firstname']) > 20))
		{
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		/**
		 * 新增用户，账号名唯一性判断
		 */
		$user_info = $this->model_user_user->getByUsername($this->request->post['username']);
		if (!empty($user_info) && $this->request->get['user_id'] != $user_info['user_id'])
		{
			$this->error['warning'] = $this->language->get('error_exists');
		}

		/**
		 * 获取机构信息
		 */
		$this->registry->model('user/org');
		$org_info = $this->model_user_org->get($this->request->get_var('org_id', 'i'));
		if (empty($org_info))
		{
			$this->error['org'] = $this->language->get('error_org');
		}

		/**
		 * 机构可开账户数量判断
		 */
		if (!empty($org_info))
		{
			$user_total  = $this->model_user_user->getTotalByOrg($org_info['org_id']);
			$user_org_id = empty($user_info) ? 0 : $user_info['org_id'];
			if ($user_total >= $org_info['user_total'] && $user_org_id != $org_info['org_id'])
			{
				$this->error['org'] = $this->language->get('error_org_user');
			}
		}

		/**
		 * 密码处理
		 */
		if ($this->request->post['password'] || (!isset($this->request->get['user_id'])))
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

		if ($this->request->get_var('lastname') && ((mb_strlen($this->request->post['lastname']) < 2) || (mb_strlen($this->request->post['lastname']) > 20)))
		{
			$this->error['lastname'] = $this->language->get('error_lastname');
		}

		if ($this->request->get_var('email') && !wcore_validate::email($this->request->get_var('email')))
		{
			$this->error['email'] = $this->language->get('error_email');
		}

		if ($this->request->get_var('tel') && !wcore_validate::phone($this->request->get_var('tel')))
		{
			$this->error['tel'] = $this->language->get('error_tel');
		}

		return !$this->error;
	}

	/**
	 * 校验是否可删除用户(不自己删除自己)
	 */
	private function validateDelete()
	{
		if (!$this->user->hasPermission('modify', 'user/user'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['selected'] as $user_id)
		{
			if ($this->user->getId() == $user_id)
			{
				$this->error['warning'] = $this->language->get('error_account');
			}
		}

		return !$this->error;
	}
}
?>