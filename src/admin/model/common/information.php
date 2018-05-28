<?php
class ModelCommonInformation extends Model
{
	public function addInformation($data)
	{
		$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "information SET sort_order = '{$data['sort_order']}', link_url = '{$data['link_url']}',information_group_id = '" . (isset($data['information_group_id']) ? (int)$data['information_group_id'] : 0) . "', status = '{$data['status']}'");
		$information_id = $this->mdb()->insert_id();
		foreach ($data['information_description'] as $language_id => $value)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "information_description SET information_id = '{$information_id}', language_id = '{$language_id}', title = '{$value['title']}', description = '{$value['description']}'");
		}
	}

	public function editInformation($information_id, $data)
	{
		$this->mdb()->query('UPDATE ' . DB_PREFIX . "information SET sort_order = '{$data['sort_order']}',link_url = '{$data['link_url']}', information_group_id = '" . (isset($data['information_group_id']) ? (int)$data['information_group_id'] : 0) . "', status = '{$data['status']}' WHERE information_id = '{$information_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "information_description WHERE information_id = '{$information_id}'");
		foreach ($data['information_description'] as $language_id => $value)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "information_description SET information_id = '{$information_id}', language_id = '{$language_id}', title = '{$value['title']}', description = '{$value['description']}'");
		}
	}

	public function deleteInformation($information_id)
	{
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "information WHERE information_id = '{$information_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "information_description WHERE information_id = '{$information_id}'");
	}

	public function getInformation($information_id)
	{
		return $this->sdb()->fetch_row('SELECT DISTINCT * FROM ' . DB_PREFIX . "information WHERE information_id = '{$information_id}'");
	}

	public function getInformations($data = array())
	{
		if ($data)
		{
			$sql       = 'SELECT * FROM ' . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) WHERE id.language_id = '{$this->language_id}'";
			$sort_data = array(
				'id.title',
				'i.sort_order'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data))
			{
				$sql .= " ORDER BY {$data['sort']}";
			}
			else
			{
				$sql .= " ORDER BY id.title";
			}

			$sql .= (isset($data['order']) && ($data['order'] == 'DESC')) ? ' DESC' : ' ASC';
			$sql .= (isset($data['start']) && isset($data['limit'])) ? " LIMIT {$data['start']}, {$data['limit']}" : '';

			return $this->sdb()->fetch_all($sql);
		}
		else
		{
			$information_data = $this->mem_get('information.' . $this->language_id);
			if (!$information_data)
			{
				$information_data = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) WHERE id.language_id = '{$this->language_id}' ORDER BY id.title");
				$this->mem_set('information.' . $this->language_id, $information_data);
			}

			return $information_data;
		}
	}

	public function getInformationDescriptions($information_id)
	{
		$res  = array();
		$rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "information_description WHERE information_id = '{$information_id}'");
		foreach ($rows as $result)
		{
			$res[$result['language_id']] = array(
				'title'       => $result['title'],
				'description' => $result['description']
			);
		}

		return $res;
	}

	public function getTotalInformations()
	{
		$row = $this->sdb()->fetch_row('SELECT COUNT(*) AS total FROM ' . DB_PREFIX . "information");

		return $row['total'];
	}
}
?>