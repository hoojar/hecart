<?php
class ModelSettingSetting extends Model
{
	public function getSetting($group)
	{
		$data = array();
		$rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "setting WHERE `group` = '{$group}'");
		foreach ($rows as $v)
		{
			if (!$v['serialized'])
			{
				$data[$v['key']] = $v['value'];
			}
			else
			{
				$data[$v['key']] = unserialize($v['value']);
			}
		}

		return $data;
	}

	public function editSetting($group, $data)
	{
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "setting WHERE `group` = '{$group}'");
		foreach ($data as $key => $value)
		{
			if (!is_array($value))
			{
				$value = (strpos($value, '@') !== false) ? $value : ($value);
				$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "setting SET `group` = '{$group}', `key` = '{$key}', `value` = '{$value}'");
			}
			else
			{
				$value = (serialize($value));
				$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "setting SET `group` = '{$group}', `key` = '{$key}', `value` = '{$value}', serialized = '1'");
			}
		}

		$this->_emptyCache(); //因修改所以清空先前缓冲
	}

	public function deleteSetting($group)
	{
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "setting WHERE `group` = '{$group}'");
		$this->_emptyCache();
	}

	/**
	 * 清空先前缓冲
	 */
	private function _emptyCache()
	{
		$_GET['nocache'] = 1;
		$this->mem_sql('SELECT * FROM ' . DB_PREFIX . 'setting' , DB_GET_ALL, false);
		unset($_GET['nocache']);
	}
}
?>