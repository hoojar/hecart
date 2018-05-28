<?php
class ControllerAccountEdit extends Controller
{
	private $error = array();

	public function index()
	{
		if (!$this->customer->isLogged())
		{
			$this->session->data['redirect'] = $this->url->link('account/edit', '', true);
			$this->registry->redirect($this->url->link('account/login', '', true));
		}

		$this->registry->language('account/edit');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('account/customer');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate())
		{
			$this->model_account_customer->edit($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->registry->redirect($this->url->link('account/account', '', true));
		}
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
			'text'      => $this->language->get('text_edit'),
			'href'      => $this->url->link('account/edit', '', true),
			'separator' => $this->language->get('text_separator')
		);

		$vrs['heading_title']     = $this->language->get('heading_title');
		$vrs['text_your_details'] = $this->language->get('text_your_details');
		$vrs['entry_firstname']   = $this->language->get('entry_firstname');
		$vrs['entry_lastname']    = $this->language->get('entry_lastname');
		$vrs['entry_email']       = $this->language->get('entry_email');
		$vrs['entry_telephone']   = $this->language->get('entry_telephone');
		$vrs['entry_fax']         = $this->language->get('entry_fax');
		$vrs['button_continue']   = $this->language->get('button_continue');
		$vrs['button_back']       = $this->language->get('button_back');

		$vrs['error_warning']   = isset($this->error['warning']) ? $this->error['warning'] : '';
		$vrs['error_firstname'] = isset($this->error['firstname']) ? $this->error['firstname'] : '';
		$vrs['error_lastname']  = isset($this->error['lastname']) ? $this->error['lastname'] : '';
		$vrs['error_email']     = isset($this->error['email']) ? $this->error['email'] : '';
		$vrs['error_telephone'] = isset($this->error['telephone']) ? $this->error['telephone'] : '';
		$vrs['action']          = $this->url->link('account/edit', '', true);

		if ($this->request->server['REQUEST_METHOD'] != 'POST')
		{
			$customer_info = $this->model_account_customer->get($this->customer->getId());
		}

		if (isset($this->request->post['firstname']))
		{
			$vrs['firstname'] = $this->request->post['firstname'];
		}
		elseif (isset($customer_info))
		{
			$vrs['firstname'] = $customer_info['firstname'];
		}
		else
		{
			$vrs['firstname'] = '';
		}

		if (isset($this->request->post['lastname']))
		{
			$vrs['lastname'] = $this->request->post['lastname'];
		}
		elseif (isset($customer_info))
		{
			$vrs['lastname'] = $customer_info['lastname'];
		}
		else
		{
			$vrs['lastname'] = '';
		}

		if (isset($this->request->post['email']))
		{
			$vrs['email'] = strtolower(trim($this->request->post['email']));
		}
		elseif (isset($customer_info))
		{
			$vrs['email'] = $customer_info['email'];
		}
		else
		{
			$vrs['email'] = '';
		}

		if (isset($this->request->post['telephone']))
		{
			$vrs['telephone'] = $this->request->post['telephone'];
		}
		elseif (isset($customer_info))
		{
			$vrs['telephone'] = $customer_info['telephone'];
		}
		else
		{
			$vrs['telephone'] = '';
		}

		if (isset($this->request->post['fax']))
		{
			$vrs['fax'] = $this->request->post['fax'];
		}
		elseif (isset($customer_info))
		{
			$vrs['fax'] = $customer_info['fax'];
		}
		else
		{
			$vrs['fax'] = '';
		}

		$vrs['back'] = $this->url->link('account/account', '', true);

		/**
		 * 模板处理
		 */
		$vrs['page_footer']    = $this->registry->exectrl('common/page_footer');
		$vrs['page_header']    = $this->registry->exectrl('common/page_header');

		return $this->view('template/account/edit.tpl', $vrs);
	}

	private function validate()
	{
		if ((mb_strlen($this->request->post['firstname']) < 1) || (mb_strlen($this->request->post['firstname']) > 32))
		{
			$this->error['firstname'] = $this->language->get('error_firstname');
		}
		if ((mb_strlen($this->request->post['lastname']) < 1) || (mb_strlen($this->request->post['lastname']) > 32))
		{
			$this->error['lastname'] = $this->language->get('error_lastname');
		}
		if ((mb_strlen(strtolower(trim($this->request->post['email']))) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', strtolower(trim($this->request->post['email']))))
		{
			$this->error['email'] = $this->language->get('error_email');
		}
		if (($this->customer->getEmail() != strtolower(trim($this->request->post['email']))) && $this->model_account_customer->getTotalByEmail(strtolower(trim($this->request->post['email']))))
		{
			$this->error['warning'] = $this->language->get('error_exists');
		}
		if ((mb_strlen($this->request->post['telephone']) < 3) || (mb_strlen($this->request->post['telephone']) > 32))
		{
			$this->error['telephone'] = $this->language->get('error_telephone');
		}

		return !$this->error;
	}
}
?>