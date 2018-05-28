<?php
class ModelUserOrg extends Model
{
	private $_opt = '';//操作的表名

	public function __construct($registry)
	{
		parent::__construct($registry);
		$this->_opt = DB_PREFIX . 'org';
	}

	/**
	 * 插入机构数据
	 *
	 * @param array $data 机构数据组
	 * @return bool|int 成功返回更新编号,失败为0
	 */
	public function insert($data = array())
	{
		$data['partner_id']  = strtolower(wcore_utils::rand_string(15));
		$data['partner_key'] = md5(wcore_utils::rand_string(20));
		$data['time_added']  = date('Y-m-d H:i:s');
		$result              = $this->mdb()->insert($this->_opt, $data);
		$_GET['nocache']     = 1;
		$this->getTree();

		return $result;
	}

	/**
	 * 更新机构数据
	 *
	 * @param array $data 机构数据组
	 * @return bool|int 成功返回更新编号,失败为0
	 */
	public function update($data = array())
	{
		$data['time_modify'] = date('Y-m-d H:i:s');
		$result              = $this->mdb()->update($this->_opt, $data, "org_id = '{$data['org_id']}'");
		$_GET['nocache']     = 1;
		$this->getTree();

		return $result;
	}

	/**
	 * 删除机构同时删除所属机构下的后台用户
	 *
	 * @param integer $org_id 机构编号
	 */
	public function del($org_id)
	{
		$mdb = $this->mdb();
		if ($mdb->del($this->_opt, "org_id = '{$org_id}'"))
		{
			$mdb->del(DB_PREFIX . 'user', "org_id = '{$org_id}'");
		}

		$_GET['nocache'] = 1;
		$this->getTree();
	}

	/**
	 * 获取数据
	 *
	 * @param int $org_id 机构编号
	 * @return mixed 机构数组
	 */
	public function get($org_id)
	{
		return $this->sdb()->fetch_row("SELECT * FROM {$this->_opt} WHERE org_id = '{$org_id}'");
	}

	/**
	 * 获取机构列表
	 *
	 * @param array $data       查询与分页
	 * @param bool  $just_total 仅获取数量
	 * @return mixed
	 */
	public function gets($data = array(), $just_total = false)
	{
		$sql = "SELECT * FROM {$this->_opt} WHERE ";
		$sql .= $this->user->getOrgPos() == '*' ? '1' : "org_id IN ({$this->user->getOrgPos()})";

		if (!empty($data['filter_name']))
		{
			$sql .= " AND name LIKE '%{$data['filter_name']}%'";
		}

		if (!empty($data['filter_org_id']))
		{
			$sql .= " AND org_id = '{$data['filter_org_id']}'";
		}

		if ($just_total)//判断是否仅获取条数
		{
			return $this->sdb()->fetch_one(str_replace(' * ', ' COUNT(*) ', $sql));
		}

		$sql .= ' ORDER BY time_added DESC';//根据分页情况获取数据
		$sql .= (isset($data['start']) && isset($data['limit'])) ? " LIMIT {$data['start']}, {$data['limit']}" : '';

		return $this->sdb()->fetch_all($sql);
	}

	/**
	 * 根据机构名称获取机构数据组
	 *
	 * @param string $name 机构名称
	 * @return mixed 机构数组
	 */
	public function getByName($name)
	{
		return $this->sdb()->fetch_row("SELECT * FROM {$this->_opt} WHERE name = '{$name}'");
	}

	/**
	 * 递归获取机构下的所有子机构
	 *
	 * @param int $parent_id 父亲编号
	 * @return array|mixed 机构列表
	 */
	public function &getTree($parent_id = 0)
	{
		$opos = $this->user->getOrgPos();
		$mkey = 'HASH-ORGS-' . md5("{$parent_id}{$opos}");
		$tree = $this->mem_get($mkey);
		if (empty($tree))
		{
			/**
			 * 判断用户所能查看的机构,不能查看的机构不显示
			 */
			$tree = $this->_getTree($parent_id);
			$tree = wcore_utils::hash_array($tree, 'org_id');
			if ($opos != '*') //*代表拥有所有机构权限
			{
				foreach ($tree as $k => $v)
				{
					if (strpos(",{$opos},", ",{$k},") === false)
					{
						unset($tree[$k]);
					}
				}
			}
			$this->mem_set($mkey, $tree);
		}

		return $tree;
	}

	/**
	 * 根据机构向上递归获取完全路径
	 *
	 * @param integer $org_id 机构编号
	 * @return string 机构递归后的完整路径
	 */
	public function getPath($org_id)
	{
		$mkey = "HASH-ORGS-PATH-{$org_id}";
		$path = $this->mem_get($mkey);
		if (empty($path))
		{
			$path = $this->_getPath($org_id);
			$this->mem_set($mkey, $path);
		}

		return $path;
	}

	/**
	 * 递归获取机构下的所有子机构(按树结构)
	 *
	 * @param int $parent_id 父亲编号
	 * @return array|mixed 机构列表
	 */
	private function _getTree($parent_id = 0)
	{
		$orgs = array();
		$sql  = '(SELECT COUNT(*) FROM ' . DB_PREFIX . 'user U WHERE U.org_id = O.org_id) AS user_count';
		$sql  = "SELECT O.*, {$sql} FROM {$this->_opt} O WHERE O.parent_id = '{$parent_id}' ORDER BY org_id";
		$rows = $this->sdb()->fetch_all($sql);
		if (!empty($rows))
		{
			foreach ($rows as $row)
			{
				$row['name'] = $this->getPath($row['org_id']);
				$orgs[]      = $row;
				$orgs        = array_merge($orgs, $this->_getTree($row['org_id']));
			}
		}

		return $orgs;
	}

	/**
	 * 根据机构向上递归获取完全路径
	 *
	 * @param integer $org_id 机构编号
	 * @return string 机构递归后的完整路径
	 */
	private function _getPath($org_id)
	{
		$row = $this->sdb()->fetch_row("SELECT `name`, parent_id FROM {$this->_opt} WHERE org_id = '{$org_id}'");
		if (empty($row['parent_id']))
		{
			return $row['name'];
		}

		return $this->_getPath($row['parent_id']) . $this->language->get('text_separator') . $row['name'];
	}
}
?>