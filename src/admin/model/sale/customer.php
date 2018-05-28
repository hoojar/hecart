<?php
class ModelSaleCustomer extends Model
{
	public function add($data)
	{
		$s2p = $this->salt2pwd($data['password']);
		$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "customer SET
		firstname = '{$data['firstname']}', lastname = '{$data['lastname']}', email = '{$data['email']}',
		telephone = '{$data['telephone']}', fax = '{$data['fax']}', newsletter = '{$data['newsletter']}',
		customer_group_id = '{$data['customer_group_id']}', salt = '{$s2p['salt']}', password = '{$s2p['pwd']}',
		status = '{$data['status']}', date_added = NOW()");

		if (isset($data['address']))
		{
			$customer_id = $this->mdb()->insert_id();
			foreach ($data['address'] as $address)
			{
				$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "customer_address SET
				customer_id = '{$customer_id}', firstname = '{$address['firstname']}', telephone = '{$address['telephone']}',
				company = '{$address['company']}', company_id = '{$address['company_id']}', tax_id = '{$address['tax_id']}',
				address_1 = '{$address['address_1']}', address_2 = '{$address['address_2']}', city = '{$address['city']}',
				postcode = '{$address['postcode']}', country_id = '{$address['country_id']}', zone_id = '{$address['zone_id']}'");
				if (isset($address['default']))
				{
					$address_id = $this->mdb()->insert_id();
					$this->mdb()->query('UPDATE ' . DB_PREFIX . "customer SET address_id = '{$address_id}' WHERE customer_id = '{$customer_id}'");
				}
			}
		}
	}

	public function edit($customer_id, $data)
	{
		$this->mdb()->query('UPDATE ' . DB_PREFIX . "customer SET firstname = '{$data['firstname']}', lastname = '{$data['lastname']}',
		email = '{$data['email']}', telephone = '{$data['telephone']}', fax = '{$data['fax']}', newsletter = '{$data['newsletter']}',
		customer_group_id = '{$data['customer_group_id']}', status = '{$data['status']}' WHERE customer_id = '{$customer_id}'");
		if ($data['password'])
		{
			$s2p = $this->salt2pwd($data['password']);
			$this->mdb()->query('UPDATE ' . DB_PREFIX . "customer SET salt = '{$s2p['salt']}', password = '{$s2p['pwd']}' WHERE customer_id = '{$customer_id}'");
		}

		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "customer_address WHERE customer_id = '{$customer_id}'");
		if (isset($data['address']))
		{
			foreach ($data['address'] as $address)
			{
				$sql = 'INSERT INTO ' . DB_PREFIX . "customer_address SET address_id = '{$address['address_id']}', customer_id = '{$customer_id}',
				firstname = '{$address['firstname']}', telephone = '{$address['telephone']}', company = '{$address['company']}',
				company_id = '{$address['company_id']}', tax_id = '{$address['tax_id']}', address_1 = '{$address['address_1']}',
				address_2 = '{$address['address_2']}', city = '{$address['city']}', postcode = '{$address['postcode']}',
				country_id = '{$address['country_id']}', zone_id = '{$address['zone_id']}'";
				$this->mdb()->query($sql);
				if (isset($address['default']))
				{
					$address_id = $this->mdb()->insert_id();
					$this->mdb()->query('UPDATE ' . DB_PREFIX . "customer SET address_id = '{$address_id}' WHERE customer_id = '{$customer_id}'");
				}
			}
		}
	}

	public function editToken($customer_id, $token)
	{
		$this->mdb()->query('UPDATE ' . DB_PREFIX . "customer SET error_count = 0, token = '{$token}' WHERE customer_id = '{$customer_id}'");
	}

	public function del($customer_id)
	{
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "customer WHERE customer_id = '{$customer_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "customer_reward WHERE customer_id = '{$customer_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "customer_transaction WHERE customer_id = '{$customer_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "customer_ip WHERE customer_id = '{$customer_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "customer_address WHERE customer_id = '{$customer_id}'");
	}

	public function get($customer_id)
	{
		$fullname = $this->fullname('c.');

		return $this->sdb()->fetch_row("SELECT DISTINCT c.*, {$fullname} as fullname FROM " . DB_PREFIX . "customer c WHERE c.customer_id = '{$customer_id}'");
	}

	public function getByEmail($email)
	{
		$fullname = $this->fullname('c.');

		return $this->sdb()->fetch_row("SELECT DISTINCT c.*, {$fullname} as fullname FROM " . DB_PREFIX . "customer c WHERE c.email = '{$email}'");
	}

	public function gets($data = array())
	{
		$implode  = array();
		$fullname = $this->fullname('c.');
		$sql      = "SELECT *, {$fullname} AS `name`, cgd.name AS customer_group FROM " . DB_PREFIX . "customer c
		LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id)
		WHERE cgd.language_id = '{$this->language_id}'";

		if (!empty($data['search']))
		{
			$implode[] = "{$fullname} LIKE '%{$data['search']}%'";
		}

		if (!empty($data['filter_email']))
		{
			$implode[] = "c.email LIKE '%{$data['filter_email']}%'";
		}

		if (isset($data['filter_newsletter']) && !is_null($data['filter_newsletter']))
		{
			$implode[] = "c.newsletter = '{$data['filter_newsletter']}'";
		}

		if (!empty($data['filter_customer_group_id']))
		{
			$implode[] = "c.customer_group_id = '{$data['filter_customer_group_id']}'";
		}

		if (!empty($data['filter_ip']))
		{
			$implode[] = "c.customer_id IN (SELECT customer_id FROM " . DB_PREFIX . "customer_ip WHERE ip = '{$data['filter_ip']}')";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status']))
		{
			$implode[] = "c.status = '{$data['filter_status']}'";
		}

		if (isset($data['filter_approved']) && !is_null($data['filter_approved']))
		{
			$implode[] = "c.approved = '{$data['filter_approved']}'";
		}

		if (!empty($data['filter_date_added']))
		{
			$implode[] = "DATE(c.date_added) = DATE('{$data['filter_date_added']}')";
		}

		if ($implode)
		{
			$sql .= " AND " . implode(" AND ", $implode);
		}

		$sort_data = array(
			'c.firstname',
			'c.email',
			'customer_group',
			'c.status',
			'c.approved',
			'c.ip',
			'c.date_added'
		);

		$sql .= (isset($data['sort']) && in_array($data['sort'], $sort_data)) ? " ORDER BY {$data['sort']}" : ' ORDER BY c.firstname';
		$sql .= (isset($data['order']) && ($data['order'] == 'DESC')) ? ' DESC' : ' ASC';
		$sql .= (isset($data['start']) && isset($data['limit'])) ? " LIMIT {$data['start']}, {$data['limit']}" : '';

		return $this->sdb()->fetch_all($sql);
	}

	public function approve($customer_id)
	{
		$customer_info = $this->get($customer_id);
		if ($customer_info)
		{
			$this->mdb()->query('UPDATE ' . DB_PREFIX . "customer SET approved = '1' WHERE customer_id = '{$customer_id}'");
			$this->registry->language('mail/customer');
			$this->registry->model('setting/store');
			$store_info = $this->model_setting_store->getStore($customer_info['store_id']);
			if ($store_info)
			{
				$store_name = $store_info['name'];
				$store_url  = $store_info['url'] . 'account/login';
			}
			else
			{
				$store_name = $this->config->get('config_name');
				$store_url  = $this->url->flink('account/login');
			}

			$message = sprintf($this->language->get('text_approve_welcome'), $store_name) . "\n\n";
			$message .= $this->language->get('text_approve_login') . "\n";
			$message .= $store_url . "\n\n";
			$message .= $this->language->get('text_approve_services') . "\n\n";
			$message .= $this->language->get('text_approve_thanks') . "\n";
			$message .= $store_name;

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
			$mail->setTo($customer_info['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($store_name);
			$mail->setSubject(html_entity_decode(sprintf($this->language->get('text_approve_subject'), $store_name), ENT_QUOTES, 'UTF-8'));
			$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();
		}
	}

	public function getAddress($address_id)
	{
		$mkey         = __CLASS__ . __FUNCTION__ . $address_id;
		$address_data = $this->mem_get($mkey);
		if (!empty($address_data))
		{
			return $address_data;
		}

		$address_row = $this->sdb()->fetch_row('SELECT * FROM ' . DB_PREFIX . "customer_address WHERE address_id = '{$address_id}'");
		if (!empty($address_row))
		{
			$country_row = $this->sdb()->fetch_row('SELECT * FROM `' . DB_PREFIX . "country` WHERE country_id = '{$address_row['country_id']}'");
			if (!empty($country_row))
			{
				$country        = $country_row['name'];
				$iso_code_2     = $country_row['iso_code_2'];
				$iso_code_3     = $country_row['iso_code_3'];
				$address_format = $country_row['address_format'];
			}
			else
			{
				$country        = '';
				$iso_code_2     = '';
				$iso_code_3     = '';
				$address_format = '';
			}

			$zone_row = $this->sdb()->fetch_row('SELECT * FROM `' . DB_PREFIX . "zone` WHERE zone_id = '{$address_row['zone_id']}'");
			if (!empty($zone_row))
			{
				$zone      = $zone_row['name'];
				$zone_code = $zone_row['code'];
			}
			else
			{
				$zone      = '';
				$zone_code = '';
			}

			$address_data = array(
				'address_id'     => $address_row['address_id'],
				'customer_id'    => $address_row['customer_id'],
				'firstname'      => $address_row['firstname'],
				'lastname'       => $address_row['lastname'],
				'telephone'      => $address_row['telephone'],
				'company'        => $address_row['company'],
				'company_id'     => $address_row['company_id'],
				'tax_id'         => $address_row['tax_id'],
				'address_1'      => $address_row['address_1'],
				'address_2'      => $address_row['address_2'],
				'postcode'       => $address_row['postcode'],
				'city'           => $address_row['city'],
				'zone_id'        => $address_row['zone_id'],
				'zone'           => $zone,
				'zone_code'      => $zone_code,
				'country_id'     => $address_row['country_id'],
				'country'        => $country,
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format
			);
		}
		$this->mem_set($mkey, $address_data);

		return $address_data;
	}

	public function getAddresses($customer_id)
	{
		$mkey         = __CLASS__ . __FUNCTION__ . $customer_id;
		$address_data = $this->mem_get($mkey);
		if (!empty($address_data))
		{
			return $address_data;
		}

		$address_data = array();
		$address_rows = $this->sdb()->fetch_all("SELECT address_id FROM " . DB_PREFIX . "customer_address WHERE customer_id = '{$customer_id}'");
		foreach ($address_rows as $result)
		{
			$address_info = $this->getAddress($result['address_id']);
			if ($address_info)
			{
				$address_data[$result['address_id']] = $address_info;
			}
		}

		$this->mem_set($mkey, $address_data);

		return $address_data;
	}

	public function getTotalCustomers($data = array())
	{
		$implode = array();
		$sql     = 'SELECT COUNT(*) AS total FROM ' . DB_PREFIX . "customer";

		if (!empty($data['search']))
		{
			$fullname  = $this->fullname();
			$implode[] = "{$fullname} LIKE '%{$data['search']}%'";
		}

		if (!empty($data['filter_email']))
		{
			$implode[] = "email LIKE '%{$data['filter_email']}%'";
		}

		if (isset($data['filter_newsletter']) && !is_null($data['filter_newsletter']))
		{
			$implode[] = "newsletter = '{$data['filter_newsletter']}'";
		}

		if (!empty($data['filter_customer_group_id']))
		{
			$implode[] = "customer_group_id = '{$data['filter_customer_group_id']}'";
		}

		if (!empty($data['filter_ip']))
		{
			$implode[] = "customer_id IN (SELECT customer_id FROM " . DB_PREFIX . "customer_ip WHERE ip = '{$data['filter_ip']}')";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status']))
		{
			$implode[] = "status = '{$data['filter_status']}'";
		}

		if (isset($data['filter_approved']) && !is_null($data['filter_approved']))
		{
			$implode[] = "approved = '{$data['filter_approved']}'";
		}

		if (!empty($data['filter_date_added']))
		{
			$implode[] = "DATE(date_added) = DATE('{$data['filter_date_added']}')";
		}

		if ($implode)
		{
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		return $this->mem_sql($sql, DB_GET_ONE);
	}

	public function getTotalCustomersAwaitingApproval()
	{
		$sql = 'SELECT COUNT(*) AS total FROM ' . DB_PREFIX . "customer WHERE status = '0' OR approved = '0'";

		return $this->mem_sql($sql, DB_GET_ONE);
	}

	public function getTotalAddressesByCustomerId($customer_id)
	{
		$sql = 'SELECT COUNT(*) AS total FROM ' . DB_PREFIX . "customer_address WHERE customer_id = '{$customer_id}'";

		return $this->mem_sql($sql, DB_GET_ONE);
	}

	public function getTotalAddressesByCountryId($country_id)
	{
		$sql = 'SELECT COUNT(*) AS total FROM ' . DB_PREFIX . "customer_address WHERE country_id = '{$country_id}'";

		return $this->mem_sql($sql, DB_GET_ONE);
	}

	public function getTotalAddressesByZoneId($zone_id)
	{
		$sql = 'SELECT COUNT(*) AS total FROM ' . DB_PREFIX . "customer_address WHERE zone_id = '{$zone_id}'";

		return $this->mem_sql($sql, DB_GET_ONE);
	}

	public function getTotalCustomersByCustomerGroupId($customer_group_id)
	{
		$sql = 'SELECT COUNT(*) AS total FROM ' . DB_PREFIX . "customer WHERE customer_group_id = '{$customer_group_id}'";

		return $this->mem_sql($sql, DB_GET_ONE);
	}

	public function addTransaction($customer_id, $description = '', $amount = '', $order_id = 0)
	{
		$customer_info = $this->get($customer_id);
		if ($customer_info)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "customer_transaction SET customer_id = '{$customer_id}', order_id = '{$order_id}', description = '{$description}', amount = '{$amount}', date_added = NOW()");
			$this->registry->language('mail/customer');

			if ($customer_info['store_id'])
			{
				$this->registry->model('setting/store');
				$store_info = $this->model_setting_store->getStore($customer_info['store_id']);
				$store_name = ($store_info) ? $store_info['name'] : $this->config->get('config_name');
			}
			else
			{
				$store_name = $this->config->get('config_name');
			}

			$message = sprintf($this->language->get('text_transaction_accept'), $this->currency->format($amount, $this->config->get('config_currency'))) . "\n\n";
			$message .= sprintf($this->language->get('text_transaction_total'), $this->currency->format($this->getTransactionTotal($customer_id)));

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
			$mail->setTo($customer_info['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($store_name);
			$mail->setSubject(html_entity_decode(sprintf($this->language->get('text_transaction_subject'), $this->config->get('config_name')), ENT_QUOTES, 'UTF-8'));
			$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();
		}
	}

	public function deleteTransaction($order_id)
	{
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "customer_transaction WHERE order_id = '{$order_id}'");
	}

	public function getTransactions($customer_id, $start = 0, $limit = 10)
	{
		if ($start < 0)
		{
			$start = 0;
		}

		if ($limit < 1)
		{
			$limit = 10;
		}

		return $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "customer_transaction WHERE customer_id = '{$customer_id}' ORDER BY date_added DESC LIMIT {$start}, {$limit}");
	}

	public function getTotalTransactions($customer_id)
	{
		$sql = 'SELECT COUNT(*) AS total FROM ' . DB_PREFIX . "customer_transaction WHERE customer_id = '{$customer_id}'";

		return $this->mem_sql($sql, DB_GET_ONE);
	}

	public function getTransactionTotal($customer_id)
	{
		$sql = 'SELECT SUM(amount) AS total FROM ' . DB_PREFIX . "customer_transaction WHERE customer_id = '{$customer_id}'";

		return $this->mem_sql($sql, DB_GET_ONE);
	}

	public function getTotalTransactionsByOrderId($order_id)
	{
		$sql = 'SELECT COUNT(*) AS total FROM ' . DB_PREFIX . "customer_transaction WHERE order_id = '{$order_id}'";

		return $this->mem_sql($sql, DB_GET_ONE);
	}

	public function addReward($customer_id, $description = '', $points = '', $order_id = '', $type = '')
	{
		$customer_info = $this->get($customer_id);
		if ($customer_info)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "customer_reward SET customer_id = '{$customer_id}',
			order_id = '{$order_id}', points = '{$points}', for_type = '{$type}', description = '{$description}', date_added = NOW()");
			$this->registry->language('mail/customer');

			if ($order_id)
			{
				$this->registry->model('sale/order');
				$order_info = $this->model_sale_order->getOrder($order_id);
				$store_name = ($order_info) ? $order_info['store_name'] : $this->config->get('config_name');
			}
			else
			{
				$store_name = $this->config->get('config_name');
			}

			$message = sprintf($this->language->get('text_reward_received'), $points) . "\n\n";
			$message .= sprintf($this->language->get('text_reward_total'), $this->getRewardTotal($customer_id));

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

			$mail->setTo($customer_info['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($store_name);
			$mail->setSubject(html_entity_decode(sprintf($this->language->get('text_reward_subject'), $store_name), ENT_QUOTES, 'UTF-8'));
			$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();
		}
	}

	public function deleteReward($order_id, $description = '', $type)
	{
		$mdb = $this->mdb();
		$mdb->query('INSERT INTO ' . DB_PREFIX . "customer_reward (customer_id, order_id, points, description, date_added)
		SELECT customer_id, order_id, -points, '{$description}', NOW() FROM " . DB_PREFIX . "customer_reward WHERE order_id = '{$order_id}'");

		$mdb->query('UPDATE ' . DB_PREFIX . "customer_reward SET for_type = '' WHERE order_id = '{$order_id}' AND for_type = '{$type}'");
	}

	public function getRewards($customer_id, $start = 0, $limit = 10)
	{
		$sql = 'SELECT * FROM ' . DB_PREFIX . "customer_reward WHERE customer_id = '{$customer_id}' ORDER BY date_added DESC LIMIT {$start}, {$limit}";

		return $this->mem_sql($sql, DB_GET_ALL);
	}

	public function getTotalRewards($customer_id)
	{
		$sql = 'SELECT COUNT(*) AS total FROM ' . DB_PREFIX . "customer_reward WHERE customer_id = '{$customer_id}'";

		return $this->mem_sql($sql, DB_GET_ONE);
	}

	public function getRewardTotal($customer_id)
	{
		$sql = "SELECT SUM(points) AS total FROM " . DB_PREFIX . "customer_reward WHERE customer_id = '{$customer_id}'";

		return $this->mem_sql($sql, DB_GET_ONE);
	}

	public function getTotalCustomerRewardsByOrderId($order_id, $type)
	{
		$sql = 'SELECT COUNT(*) AS total FROM ' . DB_PREFIX . "customer_reward WHERE order_id = '{$order_id}' AND for_type = '{$type}'";

		return $this->mem_sql($sql, DB_GET_ONE);
	}

	public function getIpsByCustomerId($customer_id)
	{
		$sql = 'SELECT * FROM ' . DB_PREFIX . "customer_ip WHERE customer_id = '{$customer_id}'";

		return $this->mem_sql($sql, DB_GET_ALL);
	}

	public function getTotalCustomersByIp($ip)
	{
		$sql = 'SELECT COUNT(*) AS total FROM ' . DB_PREFIX . "customer_ip WHERE ip = '{$ip}'";

		return $this->mem_sql($sql, DB_GET_ONE);
	}

	public function addBlacklist($ip)
	{
		$this->mdb()->query('INSERT INTO `' . DB_PREFIX . "customer_ip_blacklist` SET `ip` = '{$ip}'");
	}

	public function deleteBlacklist($ip)
	{
		$this->mdb()->query('DELETE FROM `' . DB_PREFIX . "customer_ip_blacklist` WHERE `ip` = '{$ip}'");
	}

	public function getTotalBlacklistsByIp($ip)
	{
		$sql = 'SELECT COUNT(*) AS total FROM `' . DB_PREFIX . "customer_ip_blacklist` WHERE `ip` = '{$ip}'";

		return $this->mem_sql($sql, DB_GET_ONE);
	}
}
?>