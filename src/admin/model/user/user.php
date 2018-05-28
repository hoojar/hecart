<?php
/**
 * 系统后台用户处理
 */
class ModelUserUser extends Model
{
	private $_opt = '';//操作的表名

	public function __construct($registry)
	{
		parent::__construct($registry);
		$this->_opt = DB_PREFIX . 'user';
	}

	/**
	 * 增加用户
	 *
	 * @param array $data 用户数据组
	 * @return int 新增编号
	 */
	public function add($data)
	{
		$s2p                = $this->salt2pwd($data['password']);
		$data['salt']       = $s2p['salt'];
		$data['password']   = $s2p['pwd'];
		$data['date_added'] = date('Y-m-d H:i:s');

		return $this->mdb()->insert($this->_opt, $data);
	}

	/**
	 * 编辑用户信息
	 *
	 * @param integer $user_id 用户编号
	 * @param array   $data    用户数据组
	 * @return int 修改影响号
	 */
	public function edit($user_id, $data)
	{
		$data['date_last'] = date('Y-m-d H:i:s');
		if (!empty($data['password']))
		{
			$this->editPassword($user_id, $data['password']);
		}

		unset($data['password']);
		if (isset($data['username']))
		{
			$this->mem_del(trim($data['username']));
		}

		if (isset($data['org_id']))
		{
			$this->mem_del("POS-ORG-ID-{$data['org_id']}");
		}

		return $this->mdb()->update($this->_opt, $data, "user_id = '{$user_id}'");
	}

	/**
	 * 删除用户
	 *
	 * @param integer $user_id 用户编号
	 * @return int 删除成功返回编号失败返回0
	 */
	public function del($user_id)
	{
		$mdb = $this->mdb();
		$mdb->query("DELETE FROM {$this->_opt} WHERE user_id = '{$user_id}'");

		return $mdb->affected_rows() ? $mdb->affected_rows() : 0;
	}

	/**
	 * 检查用户密码成功后返回用户信息
	 *
	 * @param integer $user_id  用户编号
	 * @param string  $password 用户密码
	 * @return mixed 用户信息
	 */
	public function checkPassword($user_id, $password)
	{
		$s2p = $this->salt2pwd($password);

		return $this->sdb()->fetch_all("SELECT * FROM {$this->_opt} WHERE salt = '{$s2p['salt']}' AND password = '{$s2p['pwd']}'AND user_id = '{$user_id}'");
	}

	/**
	 * 修改用户密码
	 *
	 * @param integer $user_id  用户编号
	 * @param string  $password 用户密码
	 */
	public function editPassword($user_id, $password)
	{
		$s2p = $this->salt2pwd($password);
		$this->mdb()->query("UPDATE {$this->_opt} SET salt = '{$s2p['salt']}', password = '{$s2p['pwd']}', code = '' WHERE user_id = '{$user_id}'");
	}

	/**
	 * 设置找回密码的验证码
	 *
	 * @param string $email 邮箱地址
	 * @param string $code  验证码
	 */
	public function editCode($email, $code)
	{
		$this->mdb()->query("UPDATE {$this->_opt} SET code = '{$code}' WHERE email = '{$email}'");
	}

	/**
	 * 根据验证码获取用户信息
	 *
	 * @param string $code 验证码
	 * @return mixed 用户信息
	 */
	public function getByCode($code)
	{
		return $this->sdb()->fetch_row("SELECT * FROM {$this->_opt} WHERE code = '{$code}' AND code != ''");
	}

	/**
	 * 根据用户名获取用户信息
	 *
	 * @param string $username 用户名
	 * @return mixed 用户信息
	 */
	public function getByUsername($username)
	{
		return $this->sdb()->fetch_row("SELECT * FROM {$this->_opt} WHERE username = '{$username}'");
	}

	/**
	 * 根据邮箱获取用户信息
	 *
	 * @param string $email 邮箱地址
	 * @return mixed 用户数据
	 */
	public function getByEmail($email)
	{
		return $this->sdb()->fetch_row("SELECT * FROM {$this->_opt} WHERE email = '{$email}'");
	}

	/**
	 * 根据用户编号获取单条用户信息
	 *
	 * @param integer $user_id 用户编号
	 * @return array 用户数据
	 */
	public function get($user_id)
	{
		return $this->sdb()->fetch_row("SELECT * FROM {$this->_opt} WHERE user_id = '{$user_id}'");
	}

	/**
	 * 获取多条用户数据
	 *
	 * @param array $data       过滤条件
	 * @param bool  $just_total 仅获取总条
	 * @return mixed|string 用户数据
	 */
	public function gets($data = array(), $just_total = false)
	{
		$sql = "SELECT * FROM {$this->_opt} WHERE ";
		$sql .= $this->user->getOrgPos() == '*' ? '1' : "org_id IN ({$this->user->getOrgPos()})";

		if (!empty($data['filter_username']))//过滤用户名
		{
			$sql .= " AND username LIKE '%{$data['filter_username']}%'";
		}

		if (!empty($data['filter_org_id']))//过滤用户机构编号
		{
			$sql .= " AND org_id = '{$data['filter_org_id']}'";
		}

		if (!empty($data['filter_group_id']))//过滤权限组编号
		{
			$sql .= " AND group_id = '{$data['filter_group_id']}'";
		}

		if ($just_total)//判断是否仅获取条数
		{
			return $this->sdb()->fetch_one(str_replace(' * ', ' COUNT(*) ', $sql));
		}

		$sort = array(
			'username',
			'group_id',
			'org_id',
			'status',
			'date_added',
			'date_last'
		);
		$sql .= (isset($data['sort']) && in_array($data['sort'], $sort)) ? " ORDER BY {$data['sort']}" : ' ORDER BY username';
		$sql .= (isset($data['order']) && ($data['order'] == 'DESC')) ? ' DESC' : ' ASC';
		$sql .= (isset($data['start']) && isset($data['limit'])) ? " LIMIT {$data['start']}, {$data['limit']}" : '';

		return $this->sdb()->fetch_all($sql);
	}

	/**
	 * 根据机构编号获取此机构下开了多少个用户数
	 *
	 * @param integer $org_id 机构编号
	 * @return integer 用户数
	 */
	public function getTotalByOrg($org_id)
	{
		return $this->sdb()->fetch_one("SELECT COUNT(*) FROM {$this->_opt} WHERE org_id = '{$org_id}'");
	}

	/**
	 * 获取在某权限组下有多少个用户
	 *
	 * @param integer $group_id 权限组编号
	 * @return integer 用户数
	 */
	public function getTotalByGroupId($group_id)
	{
		return $this->sdb()->fetch_one("SELECT COUNT(*) FROM {$this->_opt} WHERE group_id = '{$group_id}'");
	}
}
?>