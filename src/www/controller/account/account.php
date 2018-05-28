<?php
class ControllerAccountAccount extends Controller
{
	public function index()
	{
		if (!$this->customer->isLogged())
		{
			$this->customer->clearCookie(); //清空COOKIE数据
			$this->session->data['redirect'] = $this->url->link('account/account', '', true);
			$this->registry->redirect($this->url->link('account/login', '', true));
		}

		$this->registry->language('account/account');
		$this->document->title($this->language->get('heading_title'));

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

		$vrs['success'] = '';
		if (isset($this->session->data['success']))
		{
			$vrs['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}

		$vrs['heading_title']      = $this->language->get('heading_title');
		$vrs['text_service']       = $this->language->get('text_service');
		$vrs['text_my_account']    = $this->language->get('text_my_account');
		$vrs['text_my_orders']     = $this->language->get('text_my_orders');
		$vrs['text_edit']          = $this->language->get('text_edit');
		$vrs['text_password']      = $this->language->get('text_password');
		$vrs['text_address']       = $this->language->get('text_address');
		$vrs['button_logout']      = $this->language->get('button_logout');
		$vrs['edit']               = $this->url->link('account/edit', '', true);
		$vrs['password']           = $this->url->link('account/password', '', true);
		$vrs['address']            = $this->url->link('account/address', '', true);

		/**
		 * 模板处理
		 */
		$vrs['page_footer']    = $this->registry->exectrl('common/page_footer');
		$vrs['page_header']    = $this->registry->exectrl('common/page_header');

		return $this->view('template/account/account.tpl', $vrs);
	}
}
?>