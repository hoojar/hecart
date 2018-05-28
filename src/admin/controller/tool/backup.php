<?php
class ControllerToolBackup extends Controller
{
	private $error = array();

	public function index()
	{
		$this->registry->language('tool/backup');
		$this->document->title($this->language->get('heading_title'));
		$this->registry->model('tool/backup');
		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->user->hasPermission('modify', 'tool/backup'))
		{
			if (is_uploaded_file($this->request->files['import']['tmp_name']))
			{
				$content = file_get_contents($this->request->files['import']['tmp_name']);
			}
			else
			{
				$content = false;
			}
			if ($content)
			{
				$this->model_tool_backup->restore($content);
				$this->session->data['success'] = $this->language->get('text_success');
				$this->registry->redirect($this->url->link('tool/backup'));
			}
			else
			{
				$this->error['warning'] = $this->language->get('error_empty');
			}
		}
		$vrs['heading_title']     = $this->language->get('heading_title');
		$vrs['text_select_all']   = $this->language->get('text_select_all');
		$vrs['text_unselect_all'] = $this->language->get('text_unselect_all');
		$vrs['entry_restore']     = $this->language->get('entry_restore');
		$vrs['entry_backup']      = $this->language->get('entry_backup');
		$vrs['button_backup']     = $this->language->get('button_backup');
		$vrs['button_restore']    = $this->language->get('button_restore');
		if (isset($this->session->data['error']))
		{
			$vrs['error_warning'] = $this->session->data['error'];
			unset($this->session->data['error']);
		}
		else
		{
			$vrs['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		}

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
			'href'      => $this->url->link('tool/backup'),
			'separator' => ' :: '
		);

		$vrs['restore'] = $this->url->link('tool/backup');
		$vrs['backup']  = $this->url->link('tool/backup/backup');

		$this->registry->model('tool/backup');
		$vrs['tables'] = $this->model_tool_backup->getTables();

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/tool/backup.tpl', $vrs);
	}

	public function backup()
	{
		$this->registry->language('tool/backup');
		if (!isset($this->request->post['backup']))
		{
			$this->session->data['error'] = $this->language->get('error_backup');
			$this->registry->redirect($this->url->link('tool/backup'));
		}
		elseif ($this->user->hasPermission('modify', 'tool/backup'))
		{
			$this->response->addheader('Pragma: public');
			$this->response->addheader('Expires: 0');
			$this->response->addheader('Content-Description: File Transfer');
			$this->response->addheader('Content-Type: application/octet-stream');
			$this->response->addheader('Content-Disposition: attachment; filename=' . date('Y-m-d_H-i-s', time()) . '_backup.sql');
			$this->response->addheader('Content-Transfer-Encoding: binary');
			$this->registry->model('tool/backup');

			return ($this->model_tool_backup->backup($this->request->post['backup']));
		}
		else
		{
			$this->session->data['error'] = $this->language->get('error_permission');
			$this->registry->redirect($this->url->link('tool/backup'));
		}
	}
}
?>