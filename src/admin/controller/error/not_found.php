<?php
class ControllerErrorNotFound extends Controller
{
	public function index()
	{
		$this->registry->language('error/not_found');
		$this->document->title($this->language->get('heading_title'));
		$vrs['heading_title']  = $this->language->get('heading_title');
		$vrs['text_not_found'] = $this->language->get('text_not_found');
		$vrs['breadcrumbs']    = array();
		$vrs['breadcrumbs'][]  = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
			'separator' => false
		);
		$vrs['breadcrumbs'][]  = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('error/not_found'),
			'separator' => ' :: '
		);

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/error/not_found.tpl', $vrs);
	}
}
?>