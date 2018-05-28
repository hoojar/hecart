<?php
class ControllerCommonReset extends Controller
{
	private $error = array();

	public function index()
	{
		if ($this->user->isLogged())
		{
			$this->registry->redirect($this->url->link('common/home', '', true));
		}

		$code = isset($this->request->get['code']) ? $this->request->get['code'] : '';

		$this->registry->model('user/user');
		$user_info = $this->model_user_user->getUserByCode($code);
		if ($user_info)
		{
			$this->registry->language('common/reset');

			if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate())
			{
				$this->model_user_user->editPassword($user_info['user_id'], $this->request->post['password']);
				$this->session->data['success'] = $this->language->get('text_success');
				$this->registry->redirect($this->url->link('common/login', '', true));
			}

			/**
			 * 导航栏组合
			 */
			$vrs['breadcrumbs']   = array();
			$vrs['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home'),
				'separator' => false
			);

			$vrs['breadcrumbs'][]  = array(
				'text'      => $this->language->get('text_reset'),
				'href'      => $this->url->link('common/reset', '', true),
				'separator' => $this->language->get('text_separator')
			);
			$vrs['heading_title']  = $this->language->get('heading_title');
			$vrs['text_password']  = $this->language->get('text_password');
			$vrs['entry_password'] = $this->language->get('entry_password');
			$vrs['entry_confirm']  = $this->language->get('entry_confirm');
			$vrs['button_save']    = $this->language->get('button_save');
			$vrs['button_cancel']  = $this->language->get('button_cancel');

			if (isset($this->error['password']))
			{
				$vrs['error_password'] = $this->error['password'];
			}
			else
			{
				$vrs['error_password'] = '';
			}

			if (isset($this->error['confirm']))
			{
				$vrs['error_confirm'] = $this->error['confirm'];
			}
			else
			{
				$vrs['error_confirm'] = '';
			}

			$vrs['action'] = $this->url->link('common/reset', 'code=' . $code, true);

			$vrs['cancel'] = $this->url->link('common/login', '', true);

			if (isset($this->request->post['password']))
			{
				$vrs['password'] = $this->request->post['password'];
			}
			else
			{
				$vrs['password'] = '';
			}

			if (isset($this->request->post['confirm']))
			{
				$vrs['confirm'] = $this->request->post['confirm'];
			}
			else
			{
				$vrs['confirm'] = '';
			}

			/**
			 * 模板处理
			 */
			$vrs['page_header'] = $this->registry->exectrl('common/page_header');
			$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

			return $this->view('template/reset.tpl', $vrs);
		}
		else
		{
			return $this->registry->exectrl('common/login');
		}
	}

	private function validate()
	{
		if (mb_strlen($this->request->post['password']) < 6)
		{
			$this->error['password'] = $this->language->get('error_password');
		}

		if ($this->request->post['confirm'] != $this->request->post['password'])
		{
			$this->error['confirm'] = $this->language->get('error_confirm');
		}

		return !$this->error;
	}
}
?>