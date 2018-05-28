<?php
class ModelCommonInformationGroup extends Model
{
	public function addInformationGroup($data)
	{
		$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "information_group SET sort_order = '{$data['sort_order']}'");
		$information_group_id = $this->mdb()->insert_id();
		foreach ($data['information_group_description'] as $language_id => $value)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "information_group_description SET information_group_id = '{$information_group_id}', language_id = '{$language_id}', name = '{$value['name']}'");
		}
	}

	public function editInformationGroup($information_group_id, $data)
	{
		$this->mdb()->query('UPDATE ' . DB_PREFIX . "information_group SET sort_order = '{$data['sort_order']}' WHERE information_group_id = '{$information_group_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "information_group_description WHERE information_group_id = '{$information_group_id}'");
		foreach ($data['information_group_description'] as $language_id => $value)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "information_group_description SET information_group_id = '{$information_group_id}', language_id = '{$language_id}', name = '{$value['name']}'");
		}
	}

	public function deleteInformationGroup($information_group_id)
	{
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "information_group WHERE information_group_id = '{$information_group_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "information_group_description WHERE information_group_id = '{$information_group_id}'");
	}

	public function getInformationGroup($information_group_id)
	{
		return $this->sdb()->fetch_row('SELECT * FROM ' . DB_PREFIX . "information_group WHERE information_group_id = '{$information_group_id}'");
	}

	public function getInformationGroups($data = array())
	{
		$sql       = 'SELECT * FROM ' . DB_PREFIX . "information_group ag LEFT JOIN " . DB_PREFIX . "information_group_description agd ON (ag.information_group_id = agd.information_group_id) WHERE agd.language_id = '{$this->language_id}'";
		$sort_data = array(
			'agd.name',
			'ag.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data))
		{
			$sql .= " ORDER BY {$data['sort']}";
		}
		else
		{
			$sql .= " ORDER BY agd.name";
		}

		$sql .= (isset($data['order']) && ($data['order'] == 'DESC')) ? ' DESC' : ' ASC';
		$sql .= (isset($data['start']) && isset($data['limit'])) ? " LIMIT {$data['start']}, {$data['limit']}" : '';

		return $this->sdb()->fetch_all($sql);
	}

	public function getInformationGroupDescriptions($information_group_id)
	{
		$res  = array();
		$rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "information_group_description WHERE information_group_id = '{$information_group_id}'");
		foreach ($rows as $result)
		{
			$res[$result['language_id']] = array('name' => $result['name']);
		}

		return $res;
	}

	public function getTotalInformationGroups()
	{
		$row = $this->sdb()->fetch_row('SELECT COUNT(*) AS total FROM ' . DB_PREFIX . "information_group");

		return $row['total'];
	}
}
?>