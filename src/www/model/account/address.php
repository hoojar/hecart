<?php
class ModelAccountAddress extends Model
{
	public function addAddress($data)
	{
		$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "customer_address SET customer_id = '{$this->customer->getId()}',
			firstname = '{$data['firstname']}',lastname = '{$data['lastname']}', telephone = '{$data['telephone']}',
			company = '{$data['company']}', company_id = '" . (isset($data['company_id']) ? $data['company_id'] : '') . "',
			tax_id = '" . (isset($data['tax_id']) ? $data['tax_id'] : '') . "', address_1 = '{$data['address_1']}',
			address_2 = '{$data['address_2']}', postcode = '{$data['postcode']}', city = '{$data['city']}',
			zone_id = '{$data['zone_id']}', country_id = '{$data['country_id']}'");
		$address_id = $this->mdb()->insert_id();

		if (!empty($data['default']))
		{
			$this->mdb()->query('UPDATE ' . DB_PREFIX . "customer SET address_id = '{$address_id}' WHERE customer_id = '{$this->customer->getId()}'");
		}

		return $address_id;
	}

	public function editAddress($address_id, $data)
	{
		$this->mdb()->query('UPDATE ' . DB_PREFIX . "customer_address SET firstname = '{$data['firstname']}',
			lastname = '{$data['lastname']}', telephone = '{$data['telephone']}', company = '{$data['company']}',
			company_id = '" . (isset($data['company_id']) ? $data['company_id'] : '') . "', tax_id = '" . (isset($data['tax_id']) ? $data['tax_id'] : '') . "',
			address_1 = '{$data['address_1']}', address_2 = '{$data['address_2']}', postcode = '{$data['postcode']}', city = '{$data['city']}',
			zone_id = '{$data['zone_id']}', country_id = '{$data['country_id']}'
			WHERE address_id  = '{$address_id}' AND customer_id = '{$this->customer->getId()}'");

		if (!empty($data['default']))
		{
			$this->mdb()->query('UPDATE ' . DB_PREFIX . "customer SET address_id = '{$address_id}' WHERE customer_id = '{$this->customer->getId()}'");
		}
	}

	public function deleteAddress($address_id)
	{
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "customer_address WHERE address_id = '{$address_id}' AND customer_id = '{$this->customer->getId()}'");
	}

	public function getAddress($address_id)
	{
		$mkey         = __FUNCTION__ . $address_id . $this->customer->getId();
		$address_data = $this->mem_get($mkey);
		if (!empty($address_data))
		{
			return $address_data;
		}

		$address_row = $this->sdb()->fetch_row('SELECT DISTINCT * FROM ' . DB_PREFIX . "customer_address WHERE address_id = '{$address_id}' AND customer_id = '{$this->customer->getId()}'");
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

			$this->mem_set($mkey, $address_data);

			return $address_data;
		}
		else
		{
			return false;
		}
	}

	public function getAddresses()
	{
		$mkey         = __FUNCTION__ . $this->customer->getId();
		$address_data = $this->mem_get($mkey);
		if (!empty($address_data))
		{
			return $address_data;
		}

		$address_data = array();
		$address_rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "customer_address WHERE customer_id = '{$this->customer->getId()}'");
		foreach ($address_rows as $result)
		{
			$country_row = $this->sdb()->fetch_row('SELECT * FROM `' . DB_PREFIX . "country` WHERE country_id = '{$result['country_id']}'");
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

			$zone_row = $this->sdb()->fetch_row('SELECT * FROM `' . DB_PREFIX . "zone` WHERE zone_id = '{$result['zone_id']}'");
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

			if (empty($address_format))
			{
				$address_format = "{firstname} {lastname} {telephone} \n{company} \n{address_1} \n{address_2} \n{city} {postcode} \n{zone} \n{country}";
			}

			$find    = array(
				'{firstname}',
				'{lastname}',
				'{telephone}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			);
			$replace = array(
				'firstname' => $result['firstname'],
				'lastname'  => $result['lastname'],
				'telephone' => $result['telephone'],
				'company'   => $result['company'],
				'address_1' => $result['address_1'],
				'address_2' => $result['address_2'],
				'city'      => $result['city'],
				'postcode'  => $result['postcode'],
				'zone'      => $zone,
				'zone_code' => $zone_code,
				'country'   => $country
			);

			$address_data[$result['address_id']] = array(
				'address_id'     => $result['address_id'],
				'firstname'      => $result['firstname'],
				'lastname'       => $result['lastname'],
				'telephone'      => $result['telephone'],
				'company'        => $result['company'],
				'company_id'     => $result['company_id'],
				'tax_id'         => $result['tax_id'],
				'address_1'      => $result['address_1'],
				'address_2'      => $result['address_2'],
				'postcode'       => $result['postcode'],
				'city'           => $result['city'],
				'zone_id'        => $result['zone_id'],
				'zone'           => $zone,
				'zone_code'      => $zone_code,
				'country_id'     => $result['country_id'],
				'country'        => $country,
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'full_address'   => nl2br(str_replace($find, $replace, $address_format)),
				'address_format' => $address_format
			);
		}

		$this->mem_set($mkey, $address_data);

		return $address_data;
	}

	public function getTotalAddresses()
	{
		return $this->mem_sql('SELECT COUNT(*) AS total FROM ' . DB_PREFIX . "customer_address WHERE customer_id = '{$this->customer->getId()}'", DB_GET_ONE);
	}
}
?>