<?php
class ControllerModuleCurrency extends Controller
{
	public function index()
	{
		if (isset($this->request->post['currency_code']))
		{
			$curr_code = $this->request->post['currency_code'];
			$this->currency->set($curr_code);
			wcore_utils::set_cookie('currency', $curr_code, 365);

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);

			if (isset($this->request->post['redirect']))
			{
				$this->registry->redirect($this->request->post['redirect']);
			}
			else
			{
				$this->registry->redirect($this->url->link('common/home'));
			}
		}

		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1')))
		{
			$connection = true;
		}
		else
		{
			$connection = false;
		}

		$this->registry->language('module/currency');
		$vrs['text_currency'] = $this->language->get('text_currency');
		$vrs['currencies']    = $this->currency->currencies;
		$vrs['currency_code'] = $this->currency->getCode();
		$vrs['action']        = $this->url->link('module/currency', '', $connection);
		$vrs['current_res']   = $this->currency->currencies[$vrs['currency_code']];

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

		return $this->view('template/module/currency.tpl', $vrs);
	}
}
?>