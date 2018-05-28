<?php
class ControllerModuleLanguage extends Controller
{
	public function index()
	{
		if (isset($this->request->post['language_code']))
		{
			$lang_code = $this->request->post['language_code'];
			wcore_utils::set_cookie('language', $lang_code, 365);

			$redirect = isset($this->request->post['redirect']) ? $this->request->post['redirect'] : '/';
			$this->registry->redirect($redirect);
		}

		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1')))
		{
			$connection = true;
		}
		else
		{
			$connection = false;
		}

		$this->registry->language('module/language');
		$vrs['languages']     = $this->language->list;
		$vrs['language_code'] = $this->request->cookie['language'];
		$vrs['text_language'] = $this->language->get('text_language');
		$vrs['current_res']   = $this->language->list[$vrs['language_code']];
		$vrs['action']        = $this->url->link('module/language', '', $connection);

		if (!isset($this->request->get['route']))
		{
			$vrs['redirect'] = $this->url->link('common/home');
		}
		else
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
			$vrs['redirect'] = $this->url->link($route, $url, $connection);
		}

		return $this->view('template/module/language.tpl', $vrs);
	}
}
?>