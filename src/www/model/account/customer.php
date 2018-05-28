<?php
class ModelAccountCustomer extends Model
{
	public function add($data)
	{
		$cc_group = $this->config->get('config_customer_group_display');
		if (isset($data['customer_group_id']) && is_array($cc_group) && in_array($data['customer_group_id'], $cc_group))
		{
			$customer_group_id = $data['customer_group_id'];
		}
		else
		{
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$this->registry->model('account/customer_group');
		$data['email']  = strtolower(trim($data['email']));
		$s2p            = $this->salt2pwd($data['password']);
		$customer_group = $this->model_account_customer_group->getGroup($customer_group_id);
		$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "customer SET store_id = '{$this->store_id}',
			firstname = '{$data['firstname']}', lastname = '{$data['lastname']}', email = '{$data['email']}',
			telephone = '{$data['telephone']}', salt = '{$s2p['salt']}', password = '{$s2p['pwd']}',
			newsletter = '" . (isset($data['newsletter']) ? (int)$data['newsletter'] : 0) . "',
			customer_group_id = '{$customer_group_id}', ip = '{$this->request->server['REMOTE_ADDR']}',
			status = '1', approved = '" . (int)!$customer_group['approval'] . "', date_added = NOW()");
		$customer_id = $this->mdb()->insert_id();

		/**
		 * 增加用户收货地址
		 */
		if (isset($data['country_id']) && !empty($data['zone_id']))
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "customer_address SET customer_id = '{$customer_id}',
				firstname = '{$data['firstname']}', lastname = '{$data['lastname']}', telephone = '{$data['telephone']}',
				company = '{$data['company']}', company_id = '{$data['company_id']}', tax_id = '{$data['tax_id']}',
				address_1 = '{$data['address_1']}', address_2 = '{$data['address_2']}', city = '{$data['city']}',
				postcode = '{$data['postcode']}', country_id = '{$data['country_id']}', zone_id = '{$data['zone_id']}'");
			$address_id = $this->mdb()->insert_id();
			$this->mdb()->query('UPDATE ' . DB_PREFIX . "customer SET address_id = '{$address_id}' WHERE customer_id = '{$customer_id}'");
		}

		/**
		 * 发送注册成功邮件给客户
		 */
		$this->registry->language('mail/customer');
		$subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));
		$message = sprintf($this->language->get('text_welcome'), $this->config->get('config_name')) . "\n\n";

		if (!$customer_group['approval'])
		{
			$message .= $this->language->get('text_login') . "\n";
		}
		else
		{
			$message .= $this->language->get('text_approval') . "\n";
		}

		$message .= $this->url->flink('account/login', '', true) . "\n\n";
		$message .= $this->language->get('text_services') . "\n\n";
		$message .= $this->language->get('text_thanks') . "\n";
		$message .= $this->config->get('config_name');

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

		$mail->setTo($data['email']);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender($this->config->get('config_name'));
		$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
		$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
		$mail->send();

		// Send to main admin email if new account email is enabled
		if ($this->config->get('config_account_mail'))
		{
			$message = $this->language->get('text_signup') . "\n\n";
			$message .= $this->language->get('text_website') . ' ' . $this->config->get('config_name') . "\n";
			if (isset($data['firstname']) && $data['firstname'])
			{
				$message .= $this->language->get('text_firstname') . ' ' . $data['firstname'] . "\n";
			}

			if (isset($data['lastname']) && $data['lastname'])
			{
				$message .= $this->language->get('text_lastname') . ' ' . $data['lastname'] . "\n";
			}

			$message .= $this->language->get('text_customer_group') . ' ' . $customer_group['name'] . "\n";
			if (isset($data['company']) && $data['company'])
			{
				$message .= $this->language->get('text_company') . ' ' . $data['company'] . "\n";
			}

			$message .= $this->language->get('text_email') . ' ' . $data['email'] . "\n";
			if (isset($data['telephone']) && $data['telephone'])
			{
				$message .= $this->language->get('text_telephone') . ' ' . $data['telephone'] . "\n";
			}

			$mail->setTo($this->config->get('config_email'));
			$mail->setSubject(html_entity_decode($this->language->get('text_new_customer'), ENT_QUOTES, 'UTF-8'));
			$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();

			// Send to additional alert emails if new account email is enabled
			$alert_emails = $this->config->get('config_alert_emails');
			if (!empty($alert_emails))
			{
				$emails = explode(',', $alert_emails);
				foreach ($emails as $email)
				{
					if ($email && preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $email))
					{
						$mail->setTo($email);
						$mail->send();
					}
				}
			}
		}
	}

	public function edit($data, $customer_id = 0)
	{
		$customer_id = $customer_id ? $customer_id : $this->customer->getId();

		return $this->mdb()->update(DB_PREFIX . 'customer', $data, "customer_id = '{$customer_id}'");
	}

	public function editPassword($email, $password)
	{
		$s2p = $this->salt2pwd($password);
		$this->mdb()->query('UPDATE ' . DB_PREFIX . "customer SET salt = '{$s2p['salt']}', password = '{$s2p['pwd']}' WHERE email = '{$email}'");
	}

	public function editNewsletter($newsletter)
	{
		$this->mdb()->query('UPDATE ' . DB_PREFIX . "customer SET newsletter = '{$newsletter}' WHERE customer_id = '{$this->customer->getId()}'");
	}

	public function get($customer_id)
	{
		return $this->mem_sql('SELECT * FROM ' . DB_PREFIX . "customer WHERE customer_id = '{$customer_id}'", DB_GET_ROW);
	}

	public function getByEmail($email)
	{
		return $this->mem_sql('SELECT * FROM ' . DB_PREFIX . "customer WHERE email = '{$email}'", DB_GET_ROW);
	}

	public function getByToken($token)
	{
		$row = $this->mdb()->fetch_row('SELECT * FROM ' . DB_PREFIX . "customer WHERE token = '{$token}' AND token != ''");
		$this->mdb()->query('UPDATE ' . DB_PREFIX . "customer SET token = ''");

		return $row;
	}

	public function gets($data = array())
	{
		$fullname = $this->fullname('c.');
		$sql      = "SELECT *, {$fullname} AS `name`, cg.name AS customer_group FROM " . DB_PREFIX . "customer c LEFT JOIN " . DB_PREFIX . "customer_group cg ON (c.customer_group_id = cg.customer_group_id) ";
		$implode  = array();

		if (isset($data['search']) && !is_null($data['search']))
		{
			$fullname  = $this->fullname('c.');
			$implode[] = "{$fullname} LIKE '%{$data['search']}%'";
		}

		if (isset($data['filter_email']) && !is_null($data['filter_email']))
		{
			$implode[] = "c.email = '{$data['filter_email']}'";
		}

		if (isset($data['filter_customer_group_id']) && !is_null($data['filter_customer_group_id']))
		{
			$implode[] = "cg.customer_group_id = '{$data['filter_customer_group_id']}'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status']))
		{
			$implode[] = "c.status = '{$data['filter_status']}'";
		}

		if (isset($data['filter_approved']) && !is_null($data['filter_approved']))
		{
			$implode[] = "c.approved = '{$data['filter_approved']}'";
		}

		if (isset($data['filter_ip']) && !is_null($data['filter_ip']))
		{
			$implode[] = "c.customer_id IN (SELECT customer_id FROM " . DB_PREFIX . "customer_ip WHERE ip = '{$data['filter_ip']}')";
		}

		if (isset($data['filter_date_added']) && !is_null($data['filter_date_added']))
		{
			$implode[] = "DATE(c.date_added) = DATE('{$data['filter_date_added']}')";
		}

		if ($implode)
		{
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$sort_data = array(
			'name',
			'c.email',
			'customer_group',
			'c.status',
			'c.ip',
			'c.date_added'
		);

		$sql .= (isset($data['sort']) && in_array($data['sort'], $sort_data)) ? " ORDER BY {$data['sort']}" : ' ORDER BY name';
		$sql .= (isset($data['order']) && ($data['order'] == 'DESC')) ? ' DESC' : ' ASC';
		$sql .= (isset($data['start']) && isset($data['limit'])) ? " LIMIT {$data['start']}, {$data['limit']}" : '';

		return $this->sdb()->fetch_all($sql);
	}

	public function getTotalByEmail($email)
	{
		$row = $this->sdb()->fetch_row('SELECT COUNT(*) AS total FROM ' . DB_PREFIX . "customer WHERE email = '{$email}'");

		return $row['total'];
	}

	public function getTotalByFirstname($firstname)
	{
		$row = $this->sdb()->fetch_row('SELECT COUNT(*) AS total FROM ' . DB_PREFIX . "customer WHERE LOWER(firstname) = '{$firstname}'");

		return $row['total'];
	}

	public function getIps($customer_id)
	{
		return $this->sdb()->fetch_all('SELECT * FROM `' . DB_PREFIX . "customer_ip` WHERE customer_id = '{$customer_id}'");
	}

	public function isBlacklisted($ip)
	{
		$row = $this->sdb()->fetch_row('SELECT ip FROM `' . DB_PREFIX . "customer_ip_blacklist` WHERE ip = '{$ip}'");

		return !empty($row) ? true : false;
	}
}
?>