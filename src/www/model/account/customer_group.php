<?php
class ModelAccountCustomerGroup extends Model
{
	public function getGroup($customer_group_id)
	{
		$sql = 'SELECT DISTINCT * FROM ' . DB_PREFIX . "customer_group cg
		LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (cg.customer_group_id = cgd.customer_group_id)
		WHERE cg.customer_group_id = '" . (int)$customer_group_id . "' AND cgd.language_id = '{$this->language_id}'";

		return $this->mem_sql($sql, DB_GET_ROW);
	}

	public function getGroups()
	{
		$sql = 'SELECT * FROM ' . DB_PREFIX . "customer_group cg
		LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (cg.customer_group_id = cgd.customer_group_id)
		WHERE cgd.language_id = '{$this->language_id}' ORDER BY cg.sort_order ASC, cgd.name ASC";

		return $this->mem_sql($sql, DB_GET_ALL);
	}
}
?>