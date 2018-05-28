<?php
class ModelSettingCurrency extends Model
{
	public function addCurrency($data)
	{
		$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "currency SET title = '{$data['title']}', code = '{$data['code']}', symbol_left = '{$data['symbol_left']}', symbol_right = '{$data['symbol_right']}', decimal_place = '{$data['decimal_place']}', value = '{$data['value']}', status = '{$data['status']}', date_modified = NOW()");

		if ($this->config->get('config_currency_auto'))
		{
			$this->updateCurrencies(true);
		}
	}

	public function editCurrency($currency_id, $data)
	{
		$this->mdb()->query('UPDATE ' . DB_PREFIX . "currency SET title = '{$data['title']}', code = '{$data['code']}', symbol_left = '{$data['symbol_left']}', symbol_right = '{$data['symbol_right']}', decimal_place = '{$data['decimal_place']}', value = '{$data['value']}', status = '{$data['status']}', date_modified = NOW() WHERE currency_id = '{$currency_id}'");
	}

	public function deleteCurrency($currency_id)
	{
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "currency WHERE currency_id = '{$currency_id}'");
	}

	public function getCurrency($currency_id)
	{
		return $this->sdb()->fetch_row('SELECT DISTINCT * FROM ' . DB_PREFIX . "currency WHERE currency_id = '{$currency_id}'");
	}

	public function getCurrencyByCode($currency)
	{
		return $this->sdb()->fetch_row('SELECT DISTINCT * FROM ' . DB_PREFIX . "currency WHERE code = '{$currency}'");
	}

	public function getCurrencies($data = array())
	{
		if ($data)
		{
			$sql       = 'SELECT * FROM ' . DB_PREFIX . "currency";
			$sort_data = array(
				'title',
				'code',
				'value',
				'date_modified'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data))
			{
				$sql .= " ORDER BY {$data['sort']}";
			}
			else
			{
				$sql .= " ORDER BY title";
			}

			$sql .= (isset($data['order']) && ($data['order'] == 'DESC')) ? ' DESC' : ' ASC';
			$sql .= (isset($data['start']) && isset($data['limit'])) ? " LIMIT {$data['start']}, {$data['limit']}" : '';

			return $this->sdb()->fetch_all($sql);
		}
		else
		{
			$currency_data = $this->mem_get('currency');
			if (!$currency_data)
			{
				$currency_data = array();
				$currency_rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "currency ORDER BY title ASC");
				foreach ($currency_rows as $result)
				{
					$currency_data[$result['code']] = array(
						'currency_id'   => $result['currency_id'],
						'title'         => $result['title'],
						'code'          => $result['code'],
						'symbol_left'   => $result['symbol_left'],
						'symbol_right'  => $result['symbol_right'],
						'decimal_place' => $result['decimal_place'],
						'value'         => $result['value'],
						'status'        => $result['status'],
						'date_modified' => $result['date_modified']
					);
				}

				$this->mem_set('currency', $currency_data);
			}

			return $currency_data;
		}
	}

	public function updateCurrencies($force = false)
	{
		if (!extension_loaded('curl'))
		{
			return false;
		}

		if ($force)
		{
			$sql = 'SELECT * FROM ' . DB_PREFIX . "currency WHERE code != '{$this->config->get('config_currency')}'";
		}
		else
		{
			$sql = 'SELECT * FROM ' . DB_PREFIX . "currency WHERE code != '{$this->config->get('config_currency')}' AND date_modified < '" . date('Y-m-d H:i:s', strtotime('-1 day')) . "'";
		}

		$data = array();
		$rows = $this->sdb()->fetch_all($sql);
		foreach ($rows as $result)
		{
			$data[] = $this->config->get('config_currency') . $result['code'] . '=X';
		}

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, 'http://download.finance.yahoo.com/d/quotes.csv?s=' . implode(',', $data) . '&f=sl1&e=.csv');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);//10秒超时
		$content = curl_exec($curl);
		curl_close($curl);

		if (empty($content))
		{
			return false;
		}

		$lines = explode("\n", trim($content));
		foreach ($lines as $line)
		{
			$currency = trim(mb_substr($line, 4, 3));
			$value    = floatval(mb_substr($line, 11, 6));

			if ((float)$value)
			{
				$this->mdb()->query('UPDATE ' . DB_PREFIX . "currency SET value = '{$value}', date_modified = '" . (date('Y-m-d H:i:s')) . "' WHERE code = '{$currency}'");
			}
		}

		$this->mdb()->query('UPDATE ' . DB_PREFIX . "currency SET value = '1.00000', date_modified = '" . (date('Y-m-d H:i:s')) . "' WHERE code = '" . $this->config->get('config_currency') . "'");

		return true;
	}

	public function getTotalCurrencies()
	{
		$row = $this->sdb()->fetch_row('SELECT COUNT(*) AS total FROM ' . DB_PREFIX . "currency");

		return $row['total'];
	}
}
?>