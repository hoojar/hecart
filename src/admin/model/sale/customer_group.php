<?php
class ModelSaleCustomerGroup extends Model
{
	public function addGroup($data)
	{
		$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "customer_group SET approval = '{$data['approval']}', company_id_display = '{$data['company_id_display']}', company_id_required = '{$data['company_id_required']}', tax_id_display = '{$data['tax_id_display']}', tax_id_required = '{$data['tax_id_required']}', sort_order = '{$data['sort_order']}'");
		$customer_group_id = $this->mdb()->insert_id();
		foreach ($data['customer_group_description'] as $language_id => $value)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "customer_group_description SET customer_group_id = '{$customer_group_id}', language_id = '{$language_id}', name = '{$value['name']}', description = '{$value['description']}'");
		}
	}

	public function editGroup($customer_group_id, $data)
	{
		$this->mdb()->query('UPDATE ' . DB_PREFIX . "customer_group SET approval = '{$data['approval']}', company_id_display = '{$data['company_id_display']}', company_id_required = '{$data['company_id_required']}', tax_id_display = '{$data['tax_id_display']}', tax_id_required = '{$data['tax_id_required']}', sort_order = '{$data['sort_order']}' WHERE customer_group_id = '{$customer_group_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "customer_group_description WHERE customer_group_id = '{$customer_group_id}'");
		foreach ($data['customer_group_description'] as $language_id => $value)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "customer_group_description SET customer_group_id = '{$customer_group_id}', language_id = '{$language_id}', name = '{$value['name']}', description = '{$value['description']}'");
		}
	}

	public function delGroup($customer_group_id)
	{
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "customer_group WHERE customer_group_id = '{$customer_group_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "customer_group_description WHERE customer_group_id = '{$customer_group_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "product_discount WHERE customer_group_id = '{$customer_group_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "product_special WHERE customer_group_id = '{$customer_group_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "product_reward WHERE customer_group_id = '{$customer_group_id}'");
	}

	public function getGroup($customer_group_id)
	{
		return $this->sdb()->fetch_row('SELECT DISTINCT * FROM ' . DB_PREFIX . "customer_group cg LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (cg.customer_group_id = cgd.customer_group_id) WHERE cg.customer_group_id = '{$customer_group_id}' AND cgd.language_id = '{$this->language_id}'");
	}

	public function getGroups($data = array())
	{
		$sql       = 'SELECT * FROM ' . DB_PREFIX . "customer_group cg LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (cg.customer_group_id = cgd.customer_group_id) WHERE cgd.language_id = '{$this->language_id}'";
		$sort_data = array(
			'cgd.name',
			'cg.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data))
		{
			$sql .= " ORDER BY {$data['sort']}";
		}
		else
		{
			$sql .= " ORDER BY cgd.name";
		}
		$sql .= (isset($data['order']) && ($data['order'] == 'DESC')) ? ' DESC' : ' ASC';
		$sql .= (isset($data['start']) && isset($data['limit'])) ? " LIMIT {$data['start']}, {$data['limit']}" : '';

		return $this->sdb()->fetch_all($sql);
	}

	public function getGroupDescriptions($customer_group_id)
	{
		$customer_group_data = array();
		$customer_group_rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "customer_group_description WHERE customer_group_id = '{$customer_group_id}'");
		foreach ($customer_group_rows as $result)
		{
			$customer_group_data[$result['language_id']] = array(
				'name'        => $result['name'],
				'description' => $result['description']
			);
		}

		return $customer_group_data;
	}

	public function getTotalCustomerGroups()
	{
		$row = $this->sdb()->fetch_row('SELECT COUNT(*) AS total FROM ' . DB_PREFIX . "customer_group");

		return $row['total'];
	}
}
?>