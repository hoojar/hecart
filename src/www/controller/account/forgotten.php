<?php
class ControllerAccountForgotten extends Controller
{
	private $error = array();

	public function index()
	{
		if ($this->customer->isLogged())
		{
			$this->registry->redirect($this->url->link('account/account', '', true));
		}

		$this->registry->language('account/forgotten');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('account/customer');

		/**
		 * 发送随机新密码到用户注册邮箱
		 */
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate())
		{
			$this->registry->language('mail/forgotten');
			$password = substr(sha1(uniqid(mt_rand(), true)), 0, 10);
			$this->model_account_customer->editPassword(strtolower(trim($this->request->post['email'])), md5($password));

			$subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));
			$message = sprintf($this->language->get('text_greeting'), $this->config->get('config_name')) . "\n\n";
			$message .= $this->language->get('text_password') . "\n\n";
			$message .= $password;

			/**
			 * 发送邮件处理
			 */
			$mail            = new Mail();
			$mail->protocol  = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->hostname  = $this->config->get('config_smtp_host');
			$mail->username  = $this->config->get('config_smtp_username');
			$mail->password  = $this->config->get('config_smtp_password');
			$mail->port      = $this->config->get('config_smtp_port');
			$mail->timeout   = $this->config->get('config_smtp_timeout');

			$mail->setTo(strtolower(trim($this->request->post['email'])));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($this->config->get('config_name'));
			$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
			$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();

			$this->session->data['success'] = $this->language->get('text_success');
			$this->registry->redirect($this->url->link('account/login', '', true));
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
			'text'      => $this->language->get('text_forgotten'),
			'href'      => $this->url->link('account/forgotten', '', true),
			'separator' => $this->language->get('text_separator')
		);

		$vrs['heading_title']   = $this->language->get('heading_title');
		$vrs['text_your_email'] = $this->language->get('text_your_email');
		$vrs['text_email']      = $this->language->get('text_email');
		$vrs['entry_email']     = $this->language->get('entry_email');
		$vrs['button_continue'] = $this->language->get('button_continue');
		$vrs['button_back']     = $this->language->get('button_back');
		$vrs['error_warning']   = isset($this->error['warning']) ? $this->error['warning'] : '';
		$vrs['action']          = $this->url->link('account/forgotten', '', true);
		$vrs['back']            = $this->url->link('account/login', '', true);

		/**
		 * 模板处理
		 */
		$vrs['page_footer']    = $this->registry->exectrl('common/page_footer');
		$vrs['page_header']    = $this->registry->exectrl('common/page_header');

		return $this->view('template/account/forgotten.tpl', $vrs);
	}

	private function validate()
	{
		if (!isset($this->request->post['email']))
		{
			$this->error['warning'] = $this->language->get('error_email');
		}
		elseif (!$this->model_account_customer->getTotalByEmail(strtolower(trim($this->request->post['email']))))
		{
			$this->error['warning'] = $this->language->get('error_email');
		}

		return !$this->error;
	}
}
?>