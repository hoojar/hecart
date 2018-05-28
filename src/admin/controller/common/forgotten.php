<?php
class ControllerCommonForgotten extends Controller
{
	private $error = array();

	public function index()
	{
		if ($this->user->isLogged())
		{
			$this->registry->redirect($this->url->link('common/home', '', true));
		}

		$this->registry->language('common/forgotten');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('user/user');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate())
		{
			$this->registry->language('mail/forgotten');
			$code = substr(sha1(uniqid(mt_rand(), true)), 0, 10);
			$this->model_user_user->editCode(strtolower(trim($this->request->post['email'])), md5($code));

			$subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));
			$message = sprintf($this->language->get('text_greeting'), $this->config->get('config_name')) . "\n\n";
			$message .= sprintf($this->language->get('text_change'), $this->config->get('config_name')) . "\n\n";
			$message .= $this->url->flink('common/reset', 'code=' . $code, true) . "\n\n";
			$message .= sprintf($this->language->get('text_ip'), $this->request->server['REMOTE_ADDR']) . "\n\n";

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
		$vrs['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_forgotten'),
			'href'      => $this->url->link('common/forgotten', '', true),
			'separator' => $this->language->get('text_separator')
		);

		$vrs['heading_title']   = $this->language->get('heading_title');
		$vrs['text_your_email'] = $this->language->get('text_your_email');
		$vrs['text_email']      = $this->language->get('text_email');
		$vrs['entry_email']     = $this->language->get('entry_email');
		$vrs['button_reset']    = $this->language->get('button_reset');
		$vrs['button_cancel']   = $this->language->get('button_cancel');
		$vrs['error_warning']   = isset($this->error['warning']) ? $this->error['warning'] : '';

		$vrs['action'] = $this->url->link('common/forgotten', '', true);
		$vrs['cancel'] = $this->url->link('common/login', '', true);
		$vrs['email']  = isset($this->request->post['email']) ? strtolower(trim($this->request->post['email'])) : '';

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/forgotten.tpl', $vrs);
	}

	private function validate()
	{
		if (!isset($this->request->post['email']))
		{
			$this->error['warning'] = $this->language->get('error_email');
		}
		elseif (!$this->model_user_user->getTotalUsersByEmail(strtolower(trim($this->request->post['email']))))
		{
			$this->error['warning'] = $this->language->get('error_email');
		}

		return !$this->error;
	}
}
?>