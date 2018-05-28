<?php
class ControllerAccountLogin extends Controller
{
	private $error = array();

	public function index()
	{
		$this->registry->model('account/customer');

		// Login override for admin users
		$token = $this->request->get_var('token');
		if (!empty($token))
		{
			$this->customer->logout();
			$customer_info = $this->model_account_customer->getByToken($token);
			if ($customer_info && $this->customer->login($customer_info['email'], '', true))
			{
				// Default Addresses
				$this->registry->model('account/address');
				$address_info = $this->model_account_address->getAddress($this->customer->getAddressId());
				if ($address_info)
				{
					if ($this->config->get('config_tax_customer') == 'shipping')
					{
						$this->session->data['shipping_country_id'] = $address_info['country_id'];
						$this->session->data['shipping_zone_id']    = $address_info['zone_id'];
						$this->session->data['shipping_postcode']   = $address_info['postcode'];
					}

					if ($this->config->get('config_tax_customer') == 'payment')
					{
						$this->session->data['payment_country_id'] = $address_info['country_id'];
						$this->session->data['payment_zone_id']    = $address_info['zone_id'];
					}
				}
				else
				{
					unset($this->session->data['shipping_country_id']);
					unset($this->session->data['shipping_zone_id']);
					unset($this->session->data['shipping_postcode']);
					unset($this->session->data['payment_country_id']);
					unset($this->session->data['payment_zone_id']);
				}

				$this->registry->redirect($this->url->link('account/account', '', true));
			}
		}

		if ($this->customer->isLogged())
		{
			$this->registry->redirect($this->url->link('account/account', '', true));
		}

		$this->registry->language('account/login');
		$this->document->title($this->language->get('heading_title'));

		/**
		 * 检验登录是否成功
		 */
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate())
		{
			unset($this->session->data['guest']);

			// Default Shipping Address
			$this->registry->model('account/address');
			$address_info = $this->model_account_address->getAddress($this->customer->getAddressId());
			if ($address_info)
			{
				if ($this->config->get('config_tax_customer') == 'shipping')
				{
					$this->session->data['shipping_country_id'] = $address_info['country_id'];
					$this->session->data['shipping_zone_id']    = $address_info['zone_id'];
					$this->session->data['shipping_postcode']   = $address_info['postcode'];
				}

				if ($this->config->get('config_tax_customer') == 'payment')
				{
					$this->session->data['payment_country_id'] = $address_info['country_id'];
					$this->session->data['payment_zone_id']    = $address_info['zone_id'];
				}
			}
			else
			{
				unset($this->session->data['shipping_country_id']);
				unset($this->session->data['shipping_zone_id']);
				unset($this->session->data['shipping_postcode']);
				unset($this->session->data['payment_country_id']);
				unset($this->session->data['payment_zone_id']);
			}

			// Added strpos check to pass McAfee PCI compliance test (http://forum.hecart.com/viewtopic.php?f=10&t=12043&p=151494#p151295)
			if (isset($this->request->post['redirect']))
			{
				$this->registry->redirect(str_replace('&amp;', '&', $this->request->post['redirect']));
			}
			else
			{
				$this->registry->redirect($this->url->link('account/account', '', true));
			}
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
			'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', true),
			'separator' => $this->language->get('text_separator')
		);
		$vrs['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_login'),
			'href'      => $this->url->link('account/login', '', true),
			'separator' => $this->language->get('text_separator')
		);

		$vrs['heading_title']                = $this->language->get('heading_title');
		$vrs['text_new_customer']            = $this->language->get('text_new_customer');
		$vrs['text_register']                = $this->language->get('text_register');
		$vrs['text_register_account']        = $this->language->get('text_register_account');
		$vrs['text_returning_customer']      = $this->language->get('text_returning_customer');
		$vrs['text_i_am_returning_customer'] = $this->language->get('text_i_am_returning_customer');
		$vrs['text_forgotten']               = $this->language->get('text_forgotten');
		$vrs['entry_email']                  = $this->language->get('entry_email');
		$vrs['entry_captcha']                = $this->language->get('entry_captcha');
		$vrs['entry_password']               = $this->language->get('entry_password');
		$vrs['button_continue']              = $this->language->get('button_continue');
		$vrs['button_login']                 = $this->language->get('button_login');
		$vrs['action']                       = $this->url->link('account/login', '', true);
		$vrs['register']                     = $this->url->link('account/register', '', true);
		$vrs['forgotten']                    = $this->url->link('account/forgotten', '', true);
		$vrs['error_warning']                = isset($this->error['warning']) ? $this->error['warning'] : '';
		$vrs['password']                     = '';
		$vrs['email']                        = isset($this->request->post['email']) ? trim($this->request->post['email']) : '';
		$this->session->data['login_salt']   = $vrs['salt'] = substr(md5(uniqid(rand(), true)), 0, 9);

		if (isset($this->request->post['redirect']))
		{
			$vrs['redirect'] = $this->request->post['redirect'];
		}
		elseif (isset($this->session->data['redirect']))
		{
			$vrs['redirect'] = $this->session->data['redirect'];
			unset($this->session->data['redirect']);
		}
		else
		{
			$vrs['redirect'] = '';
		}

		$vrs['success'] = '';
		if (isset($this->session->data['success']))
		{
			$vrs['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}

		/**
		 * 模板处理
		 */
		$vrs['page_footer']    = $this->registry->exectrl('common/page_footer');
		$vrs['page_header']    = $this->registry->exectrl('common/page_header');

		return $this->view('template/account/login.tpl', $vrs);
	}

	private function validate()
	{
		$email = strtolower(trim($this->request->post['email']));
		$ures  = $this->customer->securityCheck($email);
		if (empty($ures))
		{
			$this->error['warning'] = $this->language->get('error_login');
		}
		else
		{
			$login_count_max    = intval($this->config->get('config_login_count_max')); //最大登录次数
			$login_locked_hours = floatval($this->config->get('config_login_locked_hours')); //锁定多长个小时
			$over_time          = strtotime($ures['date_last']) + (3600 * $login_locked_hours); //限制时间
			$surplus_hour       = ($over_time - time()) / 3600; //剩余小时

			/**
			 * 判断锁定时间是否过期与错误最大次数
			 */
			$locked_tip = sprintf($this->language->get('error_locked'), $surplus_hour);
			if ($over_time > time() && $ures['error_count'] > $login_count_max)
			{
				$this->error['warning'] = $locked_tip;
			}
			else
			{
				/**
				 * 当登录次数大于设定的次数时，说明限制的时间已过期，可以重新登录
				 */
				if ($ures['error_count'] > $login_count_max || $surplus_hour <= 0)
				{
					$this->model_account_customer->edit(array(
						'error_count' => 0,
						'date_last'   => 'dbf|NOW()'
					), $ures['customer_id']);
					$this->customer->securityCheck($email, true);
					$ures['error_count'] = 1;
				}

				/**
				 * 密码输错一次将需要用户输入验证码
				 */
				if ($ures['error_count'] > 1 || isset($this->session->data['captcha'])) //验证码判断
				{
					if (empty($this->session->data['captcha']) || ($this->session->data['captcha'] != strtoupper($this->request->post['captcha'])))
					{
						$this->model_account_customer->edit(array(
							'error_count' => 'dbf|error_count + 1',
							'date_last'   => 'dbf|NOW()'
						), $ures['customer_id']);
						$this->error['warning'] = $this->language->get('error_captcha') . sprintf($this->language->get('error_pwd'), $login_count_max - $ures['error_count']);
					}
				}

				/**
				 * 判断用户登录密码是否正确
				 */
				if (empty($this->error))
				{
					if (!$this->customer->login($email, $this->request->post['password']))
					{
						$this->model_account_customer->edit(array(
							'error_count' => 'dbf|error_count + 1',
							'date_last'   => 'dbf|NOW()'
						), $ures['customer_id']);
						$this->error['warning'] = sprintf($this->language->get('error_pwd'), $login_count_max - $ures['error_count']);
					}
					else
					{
						if (!$ures['approved']) //是否已审核通过验证
						{
							$this->error['warning'] = $this->language->get('error_approved');
						}
					}
				}

				/**
				 * 将0次机会替换成账户已锁定，多少小时后自动解锁
				 */
				if (!empty($this->error) && $login_count_max - $ures['error_count'] <= 0)
				{
					$this->error['warning'] = $locked_tip;
				}
			}
		}

		if (empty($this->error))
		{
			$result = true; //无错误登录成功
			$this->mem_del($email);
			unset($this->session->data['captcha']);
		}
		else
		{
			$result                         = false; //登录失败
			$this->session->data['captcha'] = true; //登录出错再次登录则需要校验码
		}

		return $result;
	}
}
?>