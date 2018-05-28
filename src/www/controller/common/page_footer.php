<?php
class ControllerCommonPageFooter extends Controller
{
	public function index()
	{
		$this->registry->language('common/footer');
		$vrs['text_lang'] = $this->language->directory;

		$this->registry->model('common/information');
		$vrs['informations']     = $this->model_common_information->getInformation2Groups();
		$vrs['powered']          = sprintf($this->language->get('text_powered'), $this->config->get('config_name'), date('Y', time()));
		$vrs['google_analytics'] = html_entity_decode($this->config->get('config_google_analytics'), ENT_QUOTES, 'UTF-8');

		return $this->view('template/footer.tpl', $vrs);
	}
}
?>