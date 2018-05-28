<?php
/**
 * 网站客户处理类库
 */
class Customer extends modules_mem
{
	private $customer_id = 0;

	private $firstname = '';

	private $lastname = '';

	private $email = '';

	private $telephone = '';

	private $fax = '';

	private $newsletter = '';

	private $group_id;

	private $address_id = 0;

	public function __construct($registry)
	{
		parent::__construct();
		$this->config  = $registry->get('config');
		$this->request = $registry->get('request');
		$this->session = $registry->get('session');

		if (isset($this->session->data['customer_id']))
		{
			$ures = $this->mem_sql('SELECT * FROM ' . DB_PREFIX . "customer WHERE customer_id = '{$this->session->data['customer_id']}' AND status = 1");
			if (!empty($ures))
			{
				$this->customer_id = $ures['customer_id'];
				$this->firstname   = $ures['firstname'];
				$this->lastname    = $ures['lastname'];
				$this->email       = $ures['email'];
				$this->telephone   = $ures['telephone'];
				$this->fax         = $ures['fax'];
				$this->newsletter  = $ures['newsletter'];
				$this->group_id    = $ures['customer_group_id'];
				$this->address_id  = $ures['address_id'];
			}
			else
			{
				$this->logout();
			}
		}
	}

	/**
	 * 客户登录
	 *
	 * @param string $email    客户邮箱
	 * @param string $password 客户密码
	 * @param bool   $override 是否无需密码登录
	 * @return bool
	 */
	public function login($email, $password, $override = false)
	{
		$email = strtolower(trim($email));
		if ($override)
		{
			$sql = 'SELECT * FROM ' . DB_PREFIX . "customer WHERE email = '{$email}' AND status = '1'";
		}
		else
		{
			$salt = empty($this->session->data['login_salt']) ? mt_rand(1, 999) : $this->session->data['login_salt'];
			$sql  = 'SELECT * FROM ' . DB_PREFIX . "customer WHERE email = '{$email}' AND MD5(CONCAT(password, '{$salt}')) = '{$password}' AND status = '1' AND approved = '1'";
			unset($this->session->data['login_salt']);
		}

		$customer_info = $this->sdb()->fetch_row($sql);
		if (empty($customer_info))
		{
			return false; //登录失败
		}

		/**
		 * 登录成功
		 */
		$this->session->data['customer_id'] = $customer_info['customer_id'];
		if ($customer_info['cart'] && is_string($customer_info['cart']))
		{
			if (!isset($this->session->data['cart']))
			{
				$this->session->data['cart'] = array();
			}

			$cart = unserialize($customer_info['cart']);
			foreach ($cart as $key => $value)
			{
				if (!array_key_exists($key, $this->session->data['cart']))
				{
					$this->session->data['cart'][$key] = $value;
				}
				else
				{
					$this->session->data['cart'][$key] += $value;
				}
			}
		}

		$this->customer_id = $customer_info['customer_id'];
		$this->firstname   = $customer_info['firstname'];
		$this->lastname    = $customer_info['lastname'];
		$this->email       = $customer_info['email'];
		$this->telephone   = $customer_info['telephone'];
		$this->fax         = $customer_info['fax'];
		$this->newsletter  = $customer_info['newsletter'];
		$this->group_id    = $customer_info['customer_group_id'];
		$this->address_id  = $customer_info['address_id'];

		/**
		 * 生成客户验证信息到Cookie中
		 */
		$dres   = wcore_utils::parse_url2(DOMAIN_NAME);
		$domain = isset($dres['domain']) ? $dres['domain'] : DOMAIN_NAME;
		$ticket = wcore_passport::make_ticket($this->customer_id, $this->email);
		wcore_utils::set_cookie('my_ticket', $ticket, 0, '/', $domain);
		wcore_utils::set_cookie('cust_email', $this->email, 0, '/', $domain);
		wcore_utils::set_cookie('cust_id', $this->customer_id, 0, '/', $domain);
		wcore_utils::set_cookie('cust_name', $this->firstname, 0, '/', $domain);

		/**
		 * 记录客户的IP地址
		 */
		$ip = wcore_utils::get_ip();
		$this->mdb()->query('UPDATE ' . DB_PREFIX . "customer SET ip = '{$ip}', error_count = 0, date_last = NOW() WHERE customer_id = '{$this->customer_id}'");
		$uip_res = $this->mem_sql('SELECT * FROM ' . DB_PREFIX . "customer_ip WHERE customer_id = '{$this->session->data['customer_id']}' AND ip = '{$ip}'");
		if (!empty($uip_res))
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "customer_ip SET customer_id = '{$this->session->data['customer_id']}', ip = '{$ip}', date_added = NOW()");
		}

		return true;
	}

	/**
	 * 退出登录
	 */
	public function logout()
	{
		/**
		 * 保存客户购物车中的所购买的产品与收藏产品
		 */
		$ip       = wcore_utils::get_ip();
		$this->mdb()->query('UPDATE ' . DB_PREFIX . "customer SET ip = '{$ip}' WHERE customer_id = '{$this->customer_id}'");
		unset($this->session->data['customer_id']); //清空用户所存储的数据

		/**
		 * 清空登录信息
		 */
		$this->customer_id = '';
		$this->firstname   = '';
		$this->lastname    = '';
		$this->group_id    = '';
		$this->fax         = '';
		$this->email       = '';
		$this->telephone   = '';
		$this->newsletter  = '';
		$this->address_id  = '';
	}

	public function clearCookie()
	{
		$dres   = wcore_utils::parse_url2(DOMAIN_NAME);
		$domain = isset($dres['domain']) ? $dres['domain'] : DOMAIN_NAME;
		wcore_utils::set_cookie('cust_id', null, -1, '/', $domain);
		wcore_utils::set_cookie('cust_name', null, -1, '/', $domain);
		wcore_utils::set_cookie('cust_email', null, -1, '/', $domain);
		wcore_utils::set_cookie('my_ticket', null, -1, '/', $domain);
	}

	public function securityCheck($email, $reload = false)
	{
		$res = $this->mem_get($email);
		if (empty($res) || $reload)
		{
			$res = $this->sdb()->fetch_row('SELECT * FROM ' . DB_PREFIX . "customer WHERE email = '{$email}' AND status = '1' AND approved = '1'");
		}

		if (!empty($res))
		{
			$res['error_count'] = intval($res['error_count']) + 1;
		}
		$this->mem_set($email, $res);

		return $res;
	}

	public function isLogged()
	{
		return $this->customer_id;
	}

	public function getId()
	{
		return $this->customer_id;
	}

	public function getFirstName()
	{
		return $this->firstname;
	}

	public function getLastName()
	{
		return $this->lastname;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getTelephone()
	{
		return $this->telephone;
	}

	public function getFax()
	{
		return $this->fax;
	}

	public function getNewsletter()
	{
		return $this->newsletter;
	}

	public function getGroupId()
	{
		return $this->group_id;
	}

	public function getAddressId()
	{
		return $this->address_id;
	}

	public function getBalance()
	{
		$row = $this->sdb()->fetch_row('SELECT SUM(amount) AS total FROM ' . DB_PREFIX . "customer_transaction WHERE customer_id = '{$this->customer_id}'");

		return $row['total'];
	}

	public function getRewardPoints()
	{
		$row = $this->sdb()->fetch_row("SELECT SUM(points) AS total FROM " . DB_PREFIX . "customer_reward WHERE customer_id = '{$this->customer_id}'");

		return $row['total'];
	}
}
?>