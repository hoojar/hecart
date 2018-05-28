<?php
class ControllerAccountLogout extends Controller
{
	public function index()
	{
		if ($this->customer->isLogged())
		{
			$this->customer->logout();
			$this->session->data = array(); //清空SESSION数据
		}

		$this->customer->clearCookie(); //清空COOKIE数据
		$this->registry->language('account/logout');
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
		$vrs['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_logout'),
			'href'      => $this->url->link('account/logout', '', true),
			'separator' => $this->language->get('text_separator')
		);

		$vrs['heading_title']   = $this->language->get('heading_title');
		$vrs['text_message']    = $this->language->get('text_message');
		$vrs['button_continue'] = $this->language->get('button_continue');
		$vrs['continue']        = $this->url->link('common/home');

		/**
		 * 模板处理
		 */
		$vrs['page_footer']    = $this->registry->exectrl('common/page_footer');
		$vrs['page_header']    = $this->registry->exectrl('common/page_header');

		return $this->view('template/success.tpl', $vrs);
	}
}
?>