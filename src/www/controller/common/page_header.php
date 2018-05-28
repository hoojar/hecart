<?php
class ControllerCommonPageHeader extends Controller
{
	public function index()
	{
		$vrs['base']        = HTTP_STORE;
		$vrs['title']       = $this->document->title();
		$vrs['keywords']    = $this->document->keywords();
		$vrs['description'] = $this->document->description();
		$vrs['links']       = $this->document->links();
		$vrs['styles']      = $this->document->styles();
		$vrs['scripts']     = $this->document->scripts();
		$vrs['lang']        = $this->language->get('code');
		$vrs['direction']   = $this->language->get('direction');

		$this->registry->language('common/header');
		$vrs['icon']            = ($this->config->get('config_icon') && file_exists(DIR_SITE . '/' . IMAGES_PATH . $this->config->get('config_icon'))) ? $this->registry->execdn($this->config->get('config_icon'), IMAGES_PATH) : '';
		$vrs['name']            = $this->config->get('config_name');
		$vrs['logo']            = ($this->config->get('config_logo') && file_exists(DIR_SITE . '/' . IMAGES_PATH . $this->config->get('config_logo'))) ? $this->registry->execdn($this->config->get('config_logo'), IMAGES_PATH) : '';
		$vrs['text_home']       = $this->language->get('text_home');
		$vrs['text_infomation'] = $this->language->get('text_infomation');
		$vrs['text_welcome']    = sprintf($this->language->get('text_welcome'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true));
		$vrs['text_logged']     = sprintf($this->language->get('text_logged'), $this->url->link('account/account', '', true), $this->language->get('text_account'), $this->url->link('account/logout', '', true));
		$vrs['text_account']    = $this->language->get('text_account');
		$vrs['home']            = $this->url->link('common/home');
		$vrs['logged']          = $this->customer->isLogged();
		$vrs['account']         = $this->url->link('account/account', '', true);
		$vrs['search']          = isset($this->request->get['search']) ? $this->request->get['search'] : '';

		$vrs['language'] = $this->registry->exectrl('module/language');
		$vrs['currency'] = $this->registry->exectrl('module/currency');

		$callback = array(
			&$this,
			'checkcdn'
		);
		$pattern  = '/ (src|href)=(\'|")([\d\w_\/\.\-]+\.(jpg|png|gif|css|js)\??[\d\w_\.\-\;\&\=]*)(\'|")/i';
		$content  = preg_replace_callback($pattern, $callback, $this->view('template/header.tpl', $vrs));

		return $content;
	}

	/**
	 * 检测是否需要CDN处理
	 *
	 * @param array $m
	 * @return string 组合好的CDN地址
	 */
	private function checkcdn($m)
	{
		return " {$m[1]}={$m[2]}" . $this->registry->execdn($m[3], '', ".{$m[4]}") . $m[5];
	}
}
?>