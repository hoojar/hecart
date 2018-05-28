<?php
class ControllerCommonHome extends Controller
{
	public function index()
	{
		$this->document->title($this->config->get('config_title'));
		$this->document->description($this->config->get('config_meta_description'));
		$vrs['content'] = '<p style="font-size:20px;text-align:center">Hello World</p>';

		/**
		 * 模板处理
		 */
		$vrs['page_footer']    = $this->registry->exectrl('common/page_footer');
		$vrs['page_header']    = $this->registry->exectrl('common/page_header');

		return $this->view('template/home.tpl', $vrs);
	}

	/**
	 * 获取校验码
	 */
	public function captcha()
	{
		$captcha                        = new wcore_verify();
		$captcha->font_size             = 20;
		$captcha->bgcolor               = '#FFFFFF';
		$this->session->data['captcha'] = strtoupper($captcha->generate_words());
		$captcha->draw(90, 33);
	}
}
?>