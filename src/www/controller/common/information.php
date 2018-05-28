<?php
class ControllerCommonInformation extends Controller
{
	public function index()
	{
		define('WCORE_SPEED', true); //允许缓冲页面
		$this->registry->language('common/information');
		$this->registry->model('common/information');

		/**
		 * 导航栏组合
		 */
		$vrs['breadcrumbs']   = array();
		$vrs['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
			'separator' => false
		);

		$information_id = isset($this->request->get['information_id']) ? $this->request->get['information_id'] : 0;
		$information_info = $this->model_common_information->getInformation($information_id);
		if ($information_info)
		{
			$this->document->title($information_info['title']);

			$vrs['breadcrumbs'][]   = array(
				'text'      => $information_info['title'],
				'href'      => "/information/{$information_id}.html",
				'separator' => $this->language->get('text_separator')
			);
			$vrs['heading_title']   = $information_info['title'];
			$vrs['button_continue'] = $this->language->get('button_continue');
			$vrs['description']     = html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8');
			$vrs['continue']        = $this->url->link('common/home');

			/**
			 * 模板处理
			 */
			$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');
			$vrs['page_header'] = $this->registry->exectrl('common/page_header');

			return $this->view('template/information.tpl', $vrs);
		}
		else
		{
			$vrs['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_error'),
				'href'      => "/information/{$information_id}.html",
				'separator' => $this->language->get('text_separator')
			);
			$this->document->title($this->language->get('text_error'));
			$vrs['heading_title']   = $this->language->get('text_error');
			$vrs['text_error']      = $this->language->get('text_error');
			$vrs['button_continue'] = $this->language->get('button_continue');
			$vrs['continue']        = $this->url->link('common/home');

			/**
			 * 模板处理
			 */
			$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');
			$vrs['page_header'] = $this->registry->exectrl('common/page_header');

			return $this->view('template/error/not_found.tpl', $vrs);
		}
	}

	public function info()
	{
		$this->registry->model('common/information');
		$information_id = isset($this->request->get['information_id']) ? $this->request->get['information_id'] : 0;
		$information_info = $this->model_common_information->getInformation($information_id);
		if ($information_info)
		{
			$output = '<html dir="ltr" lang="en">' . "\n";
			$output .= '<head>' . "\n";
			$output .= '  <title>' . $information_info['title'] . '</title>' . "\n";
			$output .= '  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
			$output .= '  <meta name="robots" content="noindex">' . "\n";
			$output .= '</head>' . "\n";
			$output .= '<body>' . "\n";
			$output .= '  <h1>' . $information_info['title'] . '</h1>' . "\n";
			$output .= html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8') . "\n";
			$output .= '  </body>' . "\n";
			$output .= '</html>' . "\n";

			return ($output);
		}
	}
}
?>