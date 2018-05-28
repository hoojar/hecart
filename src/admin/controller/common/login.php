<?php
class ControllerCommonLogin extends Controller
{
	private $error = array();

	public function index()
	{
		$this->registry->language('common/login');
		$this->document->title($this->language->get('heading_title'));

		$vtoken = security_token(SITE_MD5_KEY);
		$stoken = isset($this->session->data['token']) ? $this->session->data['token'] : '';
		$ctoken = isset($this->request->cookie['token']) ? $this->request->cookie['token'] : '';
		if ($this->user->isLogged() && $ctoken == $stoken && $ctoken == $vtoken)
		{
			$this->registry->redirect($this->url->link('common/home'));
		}

		/**
		 * 检验登录是否成功
		 */
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate())
		{
			unset($this->session->data['captcha']);
			$this->session->data['token'] = $vtoken;
			wcore_utils::set_cookie('token', $this->session->data['token']);

			if (isset($this->request->post['redirect']))
			{
				$this->registry->redirect($this->request->post['redirect']);
			}
			else
			{
				$this->registry->redirect($this->url->link('common/home'));
			}
		}

		$vrs['heading_title']  = $this->language->get('heading_title');
		$vrs['text_login']     = $this->language->get('text_login');
		$vrs['text_forgotten'] = $this->language->get('text_forgotten');
		$vrs['entry_username'] = $this->language->get('entry_username');
		$vrs['entry_captcha']  = $this->language->get('entry_captcha');
		$vrs['entry_password'] = $this->language->get('entry_password');
		$vrs['button_login']   = $this->language->get('button_login');

		if ($stoken && ($ctoken != $stoken || $ctoken != $vtoken))
		{
			$this->error['warning'] = $this->language->get('error_token');
		}

		$this->session->data['login_salt'] = $vrs['salt'] = substr(md5(uniqid(rand(), true)), 0, 9);//登录安全码

		$vrs['action']        = $this->url->link('common/login', '', true);
		$vrs['forgotten']     = $this->url->link('common/forgotten', '', true);
		$vrs['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$vrs['error_captcha'] = isset($this->error['captcha']) ? $this->error['captcha'] : '';
		$vrs['password']      = '';
		$vrs['username']      = isset($this->request->post['username']) ? $this->request->post['username'] : '';

		$vrs['success'] = '';
		if (isset($this->session->data['success']))
		{
			$vrs['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}

		if (isset($this->request->get['route']))
		{
			$route = $this->request->get['route'];
			unset($this->request->get['route']);

			if (isset($this->request->cookie['token']))
			{
				unset($this->request->cookie['token']);
			}

			$url = '';
			if ($this->request->get)
			{
				$url .= http_build_query($this->request->get);
			}
			$vrs['redirect'] = $this->url->link($route, $url, true);
		}
		else
		{
			$vrs['redirect'] = '';
		}

		/**
		 * 模板处理
		 */
		$vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/login.tpl', $vrs);
	}

	/**
	 * 获取校验码
	 */
	public function captcha()
	{
		$captcha            = new wcore_verify();
		$captcha->font_size = 20;
		//$captcha->bgcolor               = '#FFFFFF';
		$this->session->data['captcha'] = strtoupper($captcha->generate_words());
		$captcha->draw(90, 33);
	}

	private function validate()
	{
		$username = strtolower(trim($this->request->get_var('username', 's', 'p')));
		$user_res = $this->user->securityCheck($username);
		if (empty($user_res))
		{
			$this->error['warning'] = $this->language->get('error_login');
		}
		else
		{
			$login_count_max    = intval($this->config->get('config_login_count_max')); //最大登录次数
			$login_locked_hours = floatval($this->config->get('config_login_locked_hours')); //锁定多长个小时
			$over_time          = strtotime($user_res['date_last']) + (3600 * $login_locked_hours); //限制时间
			$surplus_hour       = ($over_time - time()) / 3600; //剩余小时

			/**
			 * 判断锁定时间是否过期与错误最大次数
			 */
			$locked_tip = sprintf($this->language->get('error_locked'), $surplus_hour);
			if ($over_time > time() && $user_res['error_count'] > $login_count_max)
			{
				$this->error['warning'] = $locked_tip;
			}
			else
			{
				/**
				 * 当登录次数大于设定的次数时，说明限制的时间已过期，可以重新登录
				 */
				$this->registry->model('user/user');
				if ($user_res['error_count'] > $login_count_max || $surplus_hour <= 0)
				{
					$this->model_user_user->edit($user_res['user_id'], array(
						'error_count' => 0,
						'date_last'   => date('Y-m-d H:i:s')
					));
					$this->user->securityCheck($username, true);
					$user_res['error_count'] = 1;
				}

				/**
				 * 密码输错一次将需要用户输入验证码
				 */
				if ($user_res['error_count'] > 1 || isset($this->session->data['captcha'])) //验证码判断
				{
					if (empty($this->session->data['captcha']) || ($this->session->data['captcha'] != strtoupper($this->request->get_var('captcha'))))
					{
						$this->model_user_user->edit($user_res['user_id'], array(
							'error_count' => 'dbf|error_count + 1',
							'date_last'   => date('Y-m-d H:i:s')
						));
						$this->error['warning'] = $this->language->get('error_captcha') . '<br/>' . sprintf($this->language->get('error_pwd'), $login_count_max - $user_res['error_count']);
					}
				}

				/**
				 * 判断用户登录密码是否正确
				 */
				if (empty($this->error))
				{
					if (!$this->user->login($username, $this->request->get_var('password', 's', 'p')))
					{
						$this->model_user_user->edit($user_res['user_id'], array(
							'error_count' => 'dbf|error_count + 1',
							'date_last'   => date('Y-m-d H:i:s')
						));
						$this->error['warning'] = sprintf($this->language->get('error_pwd'), $login_count_max - $user_res['error_count']);
					}
				}

				/**
				 * 将0次机会替换成账户已锁定，多少小时后自动解锁
				 */
				if (!empty($this->error) && $login_count_max - $user_res['error_count'] <= 0)
				{
					$this->error['warning'] = $locked_tip;
				}
			}
		}

		if (empty($this->error))
		{
			$result = true; //无错误登录成功
			$this->mem_del($username);
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