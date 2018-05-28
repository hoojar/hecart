<?php
class ControllerCommonHome extends Controller
{
	/**
	 * 控制面板
	 */
	public function index()
	{
		define('WCORE_SPEED', true); //允许缓冲页面
		$this->registry->language('common/home');
		$this->document->title($this->language->get('heading_title'));
		$lang_arr = array(
			'heading_title',
			'text_overview',
			'text_statistics',
			'text_latest_10_orders',
			'text_total_sale',
			'text_total_sale_year',
			'text_total_order',
			'text_total_customer',
			'text_total_customer_approval',
			'text_total_review_approval',
			'text_total_affiliate',
			'text_total_affiliate_approval',
			'text_day',
			'text_week',
			'text_month',
			'text_year',
			'text_no_results',
			'column_order',
			'column_customer',
			'column_status',
			'column_date_added',
			'column_total',
			'column_firstname',
			'column_lastname',
			'column_action',
			'entry_range'
		);
		foreach ($lang_arr as $v)
		{
			$vrs[$v] = $this->language->get($v);
		}

		// Check install directory exists
		if (is_dir(dirname(DIR_SITE) . '/install'))
		{
			$vrs['error_install'] = $this->language->get('error_install');
		}
		else
		{
			$vrs['error_install'] = '';
		}

		// Check image directory is writable
		if (!is_writable(DIR_IMAGE))
		{
			$vrs['error_image'] = sprintf($this->language->get('error_image'), DIR_IMAGE);
		}
		else
		{
			$vrs['error_image'] = '';
		}

		// Check image cache directory is writable
		if (!is_writable(DIR_IMAGE . 'cache'))
		{
			$vrs['error_image_cache'] = sprintf($this->language->get('error_image_cache'), DIR_IMAGE . 'cache/');
			$vrs['error_cache']       = sprintf($this->language->get('error_image_cache'), DIR_ROOT . '/system/cache/');
		}
		else
		{
			$vrs['error_image_cache'] = '';
			$vrs['error_cache']       = '';
		}

		// Check logs directory is writable
		if (!is_writable(DIR_ROOT . '/logs'))
		{
			$vrs['error_logs'] = sprintf($this->language->get('error_logs'), DIR_ROOT . '/logs/');
		}
		else
		{
			$vrs['error_logs'] = '';
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

		$this->registry->model('sale/customer');
		$vrs['total_customer']          = $this->model_sale_customer->getTotalCustomers();
		$vrs['total_customer_approval'] = $this->model_sale_customer->getTotalCustomersAwaitingApproval();

		if ($this->config->get('config_currency_auto'))
		{
			$this->registry->model('setting/currency');
			$this->model_setting_currency->updateCurrencies();
		}

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/home.tpl', $vrs);
	}
}
?>