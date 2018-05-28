<?php
class ModelSettingCountry extends Model
{
	public function addCountry($data)
	{
		$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "country SET name = '{$data['name']}', iso_code_2 = '{$data['iso_code_2']}', iso_code_3 = '{$data['iso_code_3']}', address_format = '{$data['address_format']}', postcode_required = '{$data['postcode_required']}', status = '{$data['status']}'");
	}

	public function editCountry($country_id, $data)
	{
		$this->mdb()->query('UPDATE ' . DB_PREFIX . "country SET name = '{$data['name']}', iso_code_2 = '{$data['iso_code_2']}', iso_code_3 = '{$data['iso_code_3']}', address_format = '{$data['address_format']}', postcode_required = '{$data['postcode_required']}', status = '{$data['status']}' WHERE country_id = '{$country_id}'");
	}

	public function deleteCountry($country_id)
	{
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "country WHERE country_id = '{$country_id}'");
	}

	public function getCountry($country_id)
	{
		return $this->sdb()->fetch_row('SELECT DISTINCT * FROM ' . DB_PREFIX . "country WHERE country_id = '{$country_id}'");
	}

	public function getCountries($data = array())
	{
		if ($data)
		{
			$sql       = 'SELECT * FROM ' . DB_PREFIX . "country";
			$sort_data = array(
				'name',
				'iso_code_2',
				'iso_code_3'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data))
			{
				$sql .= " ORDER BY {$data['sort']}";
			}
			else
			{
				$sql .= " ORDER BY name";
			}

			$sql .= (isset($data['order']) && ($data['order'] == 'DESC')) ? ' DESC' : ' ASC';
			$sql .= (isset($data['start']) && isset($data['limit'])) ? " LIMIT {$data['start']}, {$data['limit']}" : '';

			return $this->sdb()->fetch_all($sql);
		}
		else
		{
			$country_data = $this->mem_get('country');

			if (!$country_data)
			{
				$country_data = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "country ORDER BY name ASC");
				$this->mem_set('country', $country_data);
			}

			return $country_data;
		}
	}

	public function getTotalCountries()
	{
		$row = $this->sdb()->fetch_row('SELECT COUNT(*) AS total FROM ' . DB_PREFIX . "country");

		return $row['total'];
	}
}
?>