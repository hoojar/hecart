<?php
class ControllerSaleContact extends Controller
{
	private $error = array();

	public function index()
	{
		$this->registry->language('sale/contact');
		$this->document->title($this->language->get('heading_title'));
		$vrs['heading_title']        = $this->language->get('heading_title');
		$vrs['text_default']         = $this->language->get('text_default');
		$vrs['text_newsletter']      = $this->language->get('text_newsletter');
		$vrs['text_customer_all']    = $this->language->get('text_customer_all');
		$vrs['text_customer']        = $this->language->get('text_customer');
		$vrs['text_customer_group']  = $this->language->get('text_customer_group');
		$vrs['text_affiliate_all']   = $this->language->get('text_affiliate_all');
		$vrs['text_affiliate']       = $this->language->get('text_affiliate');
		$vrs['text_product']         = $this->language->get('text_product');
		$vrs['entry_store']          = $this->language->get('entry_store');
		$vrs['entry_to']             = $this->language->get('entry_to');
		$vrs['entry_customer_group'] = $this->language->get('entry_customer_group');
		$vrs['entry_customer']       = $this->language->get('entry_customer');
		$vrs['entry_affiliate']      = $this->language->get('entry_affiliate');
		$vrs['entry_product']        = $this->language->get('entry_product');
		$vrs['entry_subject']        = $this->language->get('entry_subject');
		$vrs['entry_message']        = $this->language->get('entry_message');
		$vrs['button_send']          = $this->language->get('button_send');
		$vrs['button_cancel']        = $this->language->get('button_cancel');
		$vrs['cancel']               = $this->url->link('sale/contact');
		$vrs['breadcrumbs']          = array();
		$vrs['breadcrumbs'][]        = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
			'separator' => false
		);
		$vrs['breadcrumbs'][]        = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/contact'),
			'separator' => ' :: '
		);

		$this->registry->model('setting/store');
		$vrs['stores'] = $this->model_setting_store->getStores();

		$this->registry->model('sale/customer_group');
		$vrs['customer_groups'] = $this->model_sale_customer_group->getGroups(0);

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/sale/contact.tpl', $vrs);
	}

	public function send()
	{
		$json = array();
		$this->registry->language('sale/contact');

		if ($this->request->server['REQUEST_METHOD'] == 'POST')
		{
			if (!$this->user->hasPermission('modify', 'sale/contact'))
			{
				$json['error']['warning'] = $this->language->get('error_permission');
			}

			if (!$this->request->post['subject'])
			{
				$json['error']['subject'] = $this->language->get('error_subject');
			}

			if (!$this->request->post['message'])
			{
				$json['error']['message'] = $this->language->get('error_message');
			}

			if (!$json)
			{
				$this->registry->model('setting/store');
				$store_info = $this->model_setting_store->getStore($this->request->post['store_id']);
				$store_name = ($store_info) ? $store_info['name'] : $this->config->get('config_name');

				$this->registry->model('sale/customer');
				$this->registry->model('sale/customer_group');
				$this->registry->model('sale/affiliate');
				$this->registry->model('sale/order');

				$email_total = 0;
				$emails      = array();
				$page        = isset($this->request->get['page']) ? $this->request->get['page'] : 1;

				switch ($this->request->post['to'])
				{
					case 'newsletter':
						$customer_data = array(
							'filter_newsletter' => 1,
							'start'             => ((int)$page - 1) * 10,
							'limit'             => 10
						);

						$email_total = $this->model_sale_customer->getTotalCustomers($customer_data);
						$results     = $this->model_sale_customer->gets($customer_data);
						foreach ($results as $result)
						{
							$emails[] = $result['email'];
						}
						break;
					case 'customer_all':
						$customer_data = array(
							'start' => ((int)$page - 1) * 10,
							'limit' => 10
						);

						$email_total = $this->model_sale_customer->getTotalCustomers($customer_data);
						$results     = $this->model_sale_customer->gets($customer_data);
						foreach ($results as $result)
						{
							$emails[] = $result['email'];
						}
						break;
					case 'customer_group':
						$customer_data = array(
							'filter_customer_group_id' => $this->request->post['customer_group_id'],
							'start'                    => ((int)$page - 1) * 10,
							'limit'                    => 10
						);

						$email_total = $this->model_sale_customer->getTotalCustomers($customer_data);
						$results     = $this->model_sale_customer->gets($customer_data);
						foreach ($results as $result)
						{
							$emails[$result['customer_id']] = $result['email'];
						}
						break;
					case 'customer':
						if (!empty($this->request->post['customer']))
						{
							foreach ($this->request->post['customer'] as $customer_id)
							{
								$customer_info = $this->model_sale_customer->get($customer_id);

								if ($customer_info)
								{
									$emails[] = $customer_info['email'];
								}
							}
						}
						break;
					case 'affiliate_all':
						$affiliate_data = array(
							'start' => ((int)$page - 1) * 10,
							'limit' => 10
						);

						$email_total = $this->model_sale_affiliate->getTotalAffiliates($affiliate_data);
						$results     = $this->model_sale_affiliate->getAffiliates($affiliate_data);
						foreach ($results as $result)
						{
							$emails[] = $result['email'];
						}
						break;
					case 'affiliate':
						if (!empty($this->request->post['affiliate']))
						{
							foreach ($this->request->post['affiliate'] as $affiliate_id)
							{
								$affiliate_info = $this->model_sale_affiliate->getAffiliate($affiliate_id);
								if ($affiliate_info)
								{
									$emails[] = $affiliate_info['email'];
								}
							}
						}
						break;
					case 'product':
						if (isset($this->request->post['product']))
						{
							$email_total = $this->model_sale_order->getTotalEmailsByProductsOrdered($this->request->post['product']);
							$results     = $this->model_sale_order->getEmailsByProductsOrdered($this->request->post['product'], ((int)$page - 1) * 10, 10);
							foreach ($results as $result)
							{
								$emails[] = $result['email'];
							}
						}
						break;
				}

				if ($emails)
				{
					$start = ((int)$page - 1) * 10;
					$end   = $start + 10;

					if ($end < $email_total)
					{
						$json['success'] = sprintf($this->language->get('text_sent'), $start, $email_total);
					}
					else
					{
						$json['success'] = $this->language->get('text_success');
					}

					if ($end < $email_total)
					{
						$json['next'] = str_replace('&amp;', '&', $this->url->link('sale/contact/send', 'page=' . ($page + 1), true));
					}
					else
					{
						$json['next'] = '';
					}

					$message = '<html dir="ltr" lang="en">' . "\n";
					$message .= '  <head>' . "\n";
					$message .= '    <title>' . $this->request->post['subject'] . '</title>' . "\n";
					$message .= '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
					$message .= '  </head>' . "\n";
					$message .= '  <body>' . html_entity_decode($this->request->post['message'], ENT_QUOTES, 'UTF-8') . '</body>' . "\n";
					$message .= '</html>' . "\n";

					/**
					 * 发送邮件处理
					 */
					$mail            = new Mail();
					$mail->protocol  = $this->config->get('config_mail_protocol');
					$mail->parameter = $this->config->get('config_mail_parameter');
					$mail->hostname  = $this->config->get('config_smtp_host');
					$mail->username  = $this->config->get('config_smtp_username');
					$mail->password  = $this->config->get('config_smtp_password');
					$mail->port      = $this->config->get('config_smtp_port');
					$mail->timeout   = $this->config->get('config_smtp_timeout');

					$mail->setFrom($this->config->get('config_email'));
					$mail->setSender($store_name);
					$mail->setSubject(html_entity_decode($this->request->post['subject'], ENT_QUOTES, 'UTF-8'));
					$mail->setHtml($message);
					foreach ($emails as $email)
					{
						$mail->setTo($email);
						$mail->send();
					}
				}
			}
		}

		return json_encode($json);
	}
}
?>