<?php
class ControllerToolErrorLog extends Controller
{
	private $error = array();

	public function index()
	{
		$this->registry->language('tool/error_log');
		$this->document->title($this->language->get('heading_title'));
		$vrs['heading_title'] = $this->language->get('heading_title');
		$vrs['button_clear']  = $this->language->get('button_clear');

		$vrs['success'] = '';
		if (isset($this->session->data['success']))
		{
			$vrs['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
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
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('tool/error_log'),
			'separator' => ' :: '
		);

		$vrs['log']   = '';
		$vrs['clear'] = $this->url->link('tool/error_log/clear');
		$file         = DIR_ROOT . '/logs/' . $this->config->get('config_error_filename');
		if (file_exists($file))
		{
			$vrs['log'] = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
		}

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/tool/error_log.tpl', $vrs);
	}

	public function clear()
	{
		$this->registry->language('tool/error_log');
		if ($this->validate())
		{
			$file   = DIR_ROOT . '/logs/' . $this->config->get('config_error_filename');
			$handle = fopen($file, 'w+');
			fclose($handle);
			$this->session->data['success'] = $this->language->get('text_success');
		}
		else
		{
			$this->session->data['success'] = $this->error['warning'];
		}

		$this->registry->redirect($this->url->link('tool/error_log'));
	}

	private function validate()
	{
		if (!$this->user->hasPermission('modify', 'tool/error_log'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
?>