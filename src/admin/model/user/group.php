<?php
/**
 * 权限与权限组管理
 */
class ModelUserGroup extends Model
{
	private $_opt = '';//操作的表名

	public function __construct($registry)
	{
		parent::__construct($registry);
		$this->_opt = DB_PREFIX . 'user_group';
	}

	/**
	 * 增加权限群组
	 *
	 * @param array $data 权限数组
	 * @return int 增加成功返回自动编号失败返回0
	 */
	public function add($data)
	{
		$mdb         = $this->mdb();
		$orgpos      = isset($data['orgpos']) ? $data['orgpos'] : '';
		$description = isset($data['description']) ? $data['description'] : '';
		$permission  = isset($data['permission']) ? json_encode($data['permission']) : '';
		$mdb->query("INSERT INTO {$this->_opt} SET name = '{$data['name']}', orgpos = '{$orgpos}', permission = '{$permission}', description = '{$description}'");

		return $mdb->affected_rows() ? $mdb->insert_id() : 0;
	}

	/**
	 * 编辑权限群组
	 *
	 * @param integer $group_id 权限组编号
	 * @param array   $data     权限数组
	 * @return int 修改成功返回编号失败返回0
	 */
	public function edit($group_id, $data)
	{
		$mdb         = $this->mdb();
		$orgpos      = isset($data['orgpos']) ? $data['orgpos'] : '';
		$description = isset($data['description']) ? $data['description'] : '';
		$permission  = isset($data['permission']) ? json_encode($data['permission']) : '';
		$mdb->query("UPDATE {$this->_opt} SET name = '{$data['name']}', orgpos = '{$orgpos}', permission = '{$permission}', description = '{$description}' WHERE group_id = '{$group_id}'");
		$this->mem_del("USER-GROUP{$group_id}-PERMISSION");

		return $mdb->affected_rows() ? $mdb->affected_rows() : 0;
	}

	/**
	 * 删除权限群组
	 *
	 * @param integer $group_id 权限组编号
	 * @return int 删除成功返回编号失败返回0
	 */
	public function del($group_id)
	{
		$mdb = $this->mdb();
		$mdb->query("DELETE FROM {$this->_opt} WHERE group_id = '{$group_id}'");

		return $mdb->affected_rows() ? $mdb->affected_rows() : 0;
	}

	/**
	 * 根据权限组编号获取权限数据
	 *
	 * @param integer $group_id 权限组编号
	 * @return array 权限数据
	 */
	public function get($group_id)
	{
		$row = $this->sdb()->fetch_row("SELECT * FROM {$this->_opt} WHERE group_id = '{$group_id}'");
		if (!empty($row))
		{
			$row['permission'] = json_decode($row['permission'], true);
		}

		return $row;
	}

	/**
	 * 获取多个权限组
	 *
	 * @param array $data       条件数组
	 * @param bool  $just_total 仅获取总条
	 * @return array 权限数据组
	 */
	public function gets($data = array(), $just_total = false)
	{
		$sql = "SELECT * FROM {$this->_opt}" . ($this->user->getOrgPos() == '*' ? '' : " WHERE orgpos != '*'");
		if ($just_total)//判断是否仅获取条数
		{
			return $this->mem_sql(str_replace(' * ', ' COUNT(*) ', $sql), DB_GET_ONE);
		}

		$sort = array(
			'group_id',
			'name'
		);
		$sql .= (isset($data['sort']) && in_array($data['sort'], $sort)) ? " ORDER BY {$data['sort']}" : ' ORDER BY group_id';
		$sql .= (isset($data['order']) && ($data['order'] == 'DESC')) ? ' DESC' : ' ASC';
		$sql .= (isset($data['start']) && isset($data['limit'])) ? " LIMIT {$data['start']}, {$data['limit']}" : '';

		return $this->hash_sql($sql, 'group_id');
	}

	/**
	 * 根据权限组名获取权限数据组
	 *
	 * @param string $name 权限组名
	 * @return mixed 权限数据组
	 */
	public function getByName($name)
	{
		return $this->sdb()->fetch_row("SELECT * FROM {$this->_opt} WHERE name = '{$name}'");
	}

	/**
	 * 增加权限
	 *
	 * @param integer $user_id 用户编号
	 * @param string  $type    权限类型 (access|modify)
	 * @param string  $route   路由标识
	 * @return boolean 是否增加成功
	 */
	public function addPermission($user_id, $type, $route)
	{
		$ures = $this->sdb()->fetch_row('SELECT group_id FROM ' . DB_PREFIX . "user WHERE user_id = '{$user_id}'");
		if (!empty($ures))
		{
			$gres = $this->sdb()->fetch_row("SELECT * FROM {$this->_opt} WHERE group_id = '{$ures['group_id']}'");
			if (!empty($gres))
			{
				$data          = json_decode($gres['permission'], true);
				$data[$type][] = $route;
				$mdb           = $this->mdb();
				$mdb->query("UPDATE {$this->_opt} SET permission = '" . json_encode($data) . "' WHERE group_id = '{$ures['group_id']}'");
				$this->mem_del("USER-GROUP{$ures['group_id']}-PERMISSION");

				return $mdb->affected_rows() ? true : false;
			}
		}

		return false;
	}
}
?>