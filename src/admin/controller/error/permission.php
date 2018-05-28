<?php
class ControllerErrorPermission extends Controller
{
	public function index()
	{
		$this->registry->language('error/permission');
		$this->document->title($this->language->get('heading_title'));
		$vrs['heading_title']   = $this->language->get('heading_title');
		$vrs['text_permission'] = $this->language->get('text_permission');
		$vrs['breadcrumbs']     = array();
		$vrs['breadcrumbs'][]   = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
			'separator' => false
		);
		$vrs['breadcrumbs'][]   = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('error/permission'),
			'separator' => ' :: '
		);

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/error/permission.tpl', $vrs);
	}
}
?>