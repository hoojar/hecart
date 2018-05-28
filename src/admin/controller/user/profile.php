<?php
/**
 * 用户自行修改密码
 */
class ControllerUserProfile extends Controller
{
	private $error = array();

	public function index()
	{
		$this->registry->model('user/user');
		$this->registry->language('user/user');
		$this->document->title($this->language->get('text_profile'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm())
		{
			$this->model_user_user->editPassword($this->user->getId(), $this->request->post['password']);
			$this->session->data['success'] = $this->language->get('text_pwd_success');
			$this->registry->redirect($this->url->link('common/home'));
		}

		$vrs                   = $this->language->data;
		$vrs['error_warning']  = isset($this->error['warning']) ? $this->error['warning'] : '';
		$vrs['error_password'] = isset($this->error['password']) ? $this->error['password'] : '';
		$vrs['error_confirm']  = isset($this->error['confirm']) ? $this->error['confirm'] : '';
		$vrs['error_oldpwd']   = isset($this->error['oldpwd']) ? $this->error['oldpwd'] : '';

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
			'text'      => $this->language->get('text_profile'),
			'href'      => $this->url->link('user/profile'),
			'separator' => ' :: '
		);

		$this->registry->model('user/org');
		$this->registry->model('user/group');
		$orgs      = $this->model_user_org->getTree();
		$groups    = $this->model_user_group->gets();
		$user_info = $this->model_user_user->get($this->user->getId());

		$vrs['cancel']     = $this->url->link('common/home');
		$vrs['action']     = $this->url->link('user/profile');
		$vrs['username']   = $user_info['username'];
		$vrs['org_name']   = $orgs[$user_info['org_id']]['name'];
		$vrs['group_name'] = $groups[$user_info['group_id']]['name'];

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/user/profile.tpl', $vrs);
	}

	/**
	 * 表单数据校验
	 */
	private function validateForm()
	{
		if (!$this->user->hasPermission('modify', 'user/profile'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->registry->model('user/user');

		if (empty($this->request->post['oldpwd']))
		{
			$this->error['oldpwd'] = $this->language->get('error_oldpwd_empty');
		}
		elseif ($this->model_user_user->checkPassword($this->user->getId(), $this->request->post['oldpwd']))
		{
			if (empty($this->request->post['password']))
			{
				$this->error['password'] = $this->language->get('error_newpwd_empty');
			}
			elseif ($this->request->post['password'] != $this->request->post['oldpwd'])
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
			else
			{
				$this->error['password'] = $this->language->get('error_same_pwd');
			}
		}
		else
		{
			$this->error['oldpwd'] = $this->language->get('error_oldpwd');
		}

		return !$this->error;
	}
}
?>