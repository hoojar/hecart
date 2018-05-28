<?php
class ModelSaleCustomerBlacklist extends Model
{
	public function addBlacklist($data)
	{
		$this->mdb()->query('INSERT INTO `' . DB_PREFIX . "customer_ip_blacklist` SET `ip` = '{$data['ip']}'");
	}

	public function editBlacklist($customer_ip_blacklist_id, $data)
	{
		$this->mdb()->query('UPDATE `' . DB_PREFIX . "customer_ip_blacklist` SET `ip` = '{$data['ip']}' WHERE customer_ip_blacklist_id = '{$customer_ip_blacklist_id}'");
	}

	public function delBlacklist($customer_ip_blacklist_id)
	{
		$this->mdb()->query('DELETE FROM `' . DB_PREFIX . "customer_ip_blacklist` WHERE customer_ip_blacklist_id = '{$customer_ip_blacklist_id}'");
	}

	public function getBlacklist($customer_ip_blacklist_id)
	{
		return $this->sdb()->fetch_row('SELECT * FROM `' . DB_PREFIX . "customer_ip_blacklist` WHERE customer_ip_blacklist_id = '{$customer_ip_blacklist_id}'");
	}

	public function getBlacklists($data = array())
	{
		$sql = "SELECT *, (SELECT COUNT(DISTINCT customer_id) FROM `" . DB_PREFIX . "customer_ip` ci WHERE ci.ip = cib.ip) AS total FROM `" . DB_PREFIX . "customer_ip_blacklist` cib ";
		$sql .= ' ORDER BY `ip` ' . (isset($data['order']) && ($data['order'] == 'DESC') ? 'DESC' : 'ASC');

		$sql .= (isset($data['start']) && isset($data['limit'])) ? " LIMIT {$data['start']}, {$data['limit']}" : '';

		return $this->sdb()->fetch_all($sql);
	}

	public function getTotalCustomerBlacklists($data = array())
	{
		$row = $this->sdb()->fetch_row('SELECT COUNT(*) AS total FROM `' . DB_PREFIX . "customer_ip_blacklist`");

		return $row['total'];
	}
}
?>