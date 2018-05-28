<?php
class ControllerAccountPassword extends Controller
{
	private $error = array();

	public function index()
	{
		if (!$this->customer->isLogged())
		{
			$this->session->data['redirect'] = $this->url->link('account/password', '', true);
			$this->registry->redirect($this->url->link('account/login', '', true));
		}

		$this->registry->language('account/password');
		$this->document->title($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate())
		{
			$this->registry->model('account/customer');
			$this->model_account_customer->editPassword($this->customer->getEmail(), $this->request->post['password']);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->registry->redirect($this->url->link('account/account', '', true));
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
		$vrs['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', true),
			'separator' => $this->language->get('text_separator')
		);
		$vrs['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/password', '', true),
			'separator' => $this->language->get('text_separator')
		);

		$vrs['heading_title']   = $this->language->get('heading_title');
		$vrs['text_password']   = $this->language->get('text_password');
		$vrs['entry_password']  = $this->language->get('entry_password');
		$vrs['entry_confirm']   = $this->language->get('entry_confirm');
		$vrs['button_continue'] = $this->language->get('button_continue');
		$vrs['button_back']     = $this->language->get('button_back');
		$vrs['error_password']  = isset($this->error['password']) ? $this->error['password'] : '';
		$vrs['error_confirm']   = isset($this->error['confirm']) ? $this->error['confirm'] : '';
		$vrs['action']          = $this->url->link('account/password', '', true);
		$vrs['password']        = isset($this->request->post['password']) ? $this->request->post['password'] : '';
		$vrs['confirm']         = isset($this->request->post['confirm']) ? $this->request->post['confirm'] : '';
		$vrs['back']            = $this->url->link('account/account', '', true);

		/**
		 * 模板处理
		 */
		$vrs['page_footer']    = $this->registry->exectrl('common/page_footer');
		$vrs['page_header']    = $this->registry->exectrl('common/page_header');

		return $this->view('template/account/password.tpl', $vrs);
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