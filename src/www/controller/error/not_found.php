<?php
class ControllerErrorNotFound extends Controller
{
	public function index()
	{
		$this->registry->language('error/not_found');
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

		if (isset($this->request->get['route']))
		{
			$data = $this->request->get;
			unset($data['_route_']);
			$route = $data['route'];
			unset($data['route']);

			$url = '';
			if ($data)
			{
				$url = '&' . urldecode(http_build_query($data, '', '&'));
			}

			if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1')))
			{
				$connection = true;
			}
			else
			{
				$connection = false;
			}

			$vrs['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link($route, $url, $connection),
				'separator' => $this->language->get('text_separator')
			);
		}

		$vrs['continue']        = $this->url->link('common/home');
		$vrs['heading_title']   = $this->language->get('heading_title');
		$vrs['button_continue'] = $this->language->get('button_continue');
		$vrs['text_error']      = isset($this->session->data['text_error']) ? $this->session->data['text_error'] : $this->language->get('text_error');
		$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . '/1.1 404 Not Found');

		/**
		 * 模板处理
		 */
		$vrs['page_footer']    = $this->registry->exectrl('common/page_footer');
		$vrs['page_header']    = $this->registry->exectrl('common/page_header');

		return $this->view('template/error/not_found.tpl', $vrs);
	}
}
?>