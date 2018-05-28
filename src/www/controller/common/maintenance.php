<?php
class ControllerCommonMaintenance extends Controller
{
	public function index()
	{
		$route = '';
		if (isset($this->request->get['route']))
		{
			$part = explode('/', $this->request->get['route']);
			if (isset($part[0]))
			{
				$route .= $part[0];
			}
		}

		// Show site if logged in as admin
		$this->registry->library('user');
		$this->user = new User($this->registry);
		if (($route != 'payment') && !$this->user->isLogged())
		{
			return $this->registry->exectrl('common/maintenance/info');
		}

		return '';
	}

	public function info()
	{
		define('WCORE_SPEED', true); //允许缓冲页面
		$this->registry->language('common/maintenance');
		$this->document->title($this->language->get('heading_title'));
		$vrs['heading_title'] = $this->language->get('heading_title');
		$vrs['message']       = $this->language->get('text_message');

		$vrs['breadcrumbs']   = array();
		$vrs['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_maintenance'),
			'href'      => $this->url->link('common/maintenance'),
			'separator' => false
		);

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/maintenance.tpl', $vrs);
	}
}
?>