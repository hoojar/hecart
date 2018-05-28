<?php
class ModelCommonInformation extends Model
{
	public function getInformation($information_id)
	{
		$sql = 'SELECT DISTINCT * FROM ' . DB_PREFIX . "information i
		LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id)
		WHERE i.information_id = '{$information_id}' AND id.language_id = '{$this->language_id}' AND i.status = '1'";

		return $this->mem_sql($sql, DB_GET_ROW);
	}

	public function getInformations()
	{
		$sql = 'SELECT * FROM ' . DB_PREFIX . "information i
		LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id)
		WHERE id.language_id = '{$this->language_id}' AND i.status = '1'
		ORDER BY i.sort_order, id.title ASC";

		return $this->mem_sql($sql, DB_GET_ALL);
	}

	public function getInformationLayoutId($information_id)
	{
		$sql       = "SELECT layout_id FROM " . DB_PREFIX . "information_to_layout WHERE information_id = '{$information_id}' AND store_id = '{$this->store_id}'";
		$layout_id = $this->mem_sql($sql, DB_GET_ONE);

		return (!empty($layout_id)) ? $layout_id : $this->config->get('config_layout_information');
	}

	public function getInformation2Groups()
	{
		$mkey   = __FUNCTION__ . $this->language_id;
		$result = $this->mem_get($mkey);
		if (!empty($result))
		{
			return $result;
		}

		$sql    = 'SELECT ig.information_group_id,igd.name,i.information_id,i.link_url,id.title
			FROM ' . DB_PREFIX . 'information_group ig
			LEFT JOIN ' . DB_PREFIX . 'information_group_description igd ON (ig.information_group_id = igd.information_group_id)
			LEFT JOIN ' . DB_PREFIX . 'information i ON (i.information_group_id = ig.information_group_id)
			LEFT JOIN ' . DB_PREFIX . "information_description id ON (id.information_id = i.information_id)
			WHERE igd.language_id = {$this->language_id} and id.language_id = {$this->language_id} and i.status = 1
			ORDER BY ig.sort_order, i.sort_order";
		$res    = $this->sdb()->fetch_all($sql);
		$result = array();
		foreach ($res as $v)
		{
			if (!empty($v['link_url']))
			{
				$href = $v['link_url'];
			}
			else
			{
				$href = "/information/{$v['information_id']}.html";
			}

			$result["cate{$v['information_group_id']}"]['name']                               = $v['name'];
			$result["cate{$v['information_group_id']}"]['res'][$v['information_id']]['href']  = $href;
			$result["cate{$v['information_group_id']}"]['res'][$v['information_id']]['title'] = $v['title'];
			$result["cate{$v['information_group_id']}"]['res'][$v['information_id']]['id']    = $v['information_id'];
		}

		$this->mem_set($mkey, $result);

		return $result;
	}
}
?>