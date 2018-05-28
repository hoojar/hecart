<?php
/**
 * 后台用户登录处理类库
 */
class User extends modules_mem
{
	/**
	 * @var int 用户编号
	 */
	private $user_id = 0;

	/**
	 * @var int 用户组编号
	 */
	private $group_id = 0;

	/**
	 * @var int 所属机构编号
	 */
	private $org_id = 0;

	/**
	 * @var string 用户名
	 */
	private $username = '';

	/**
	 * @var array 拥有的权限列表
	 */
	private $permission = array();

	/**
	 * @var array 可查询的机构列表
	 */
	private $orgpos = '';

	/**
	 * @var wcore_session SESSION对象
	 */
	protected $session;

	public function __construct($registry)
	{
		parent::__construct();
		$this->session = $registry->get('session');

		if (isset($this->session->data['user_id']))
		{
			$this->permission = $this->_getPermission($this->session->data['group_id']);
			if (empty($this->permission))
			{
				$this->logout();
			}
			else
			{
				$this->org_id   = $this->session->data['org_id'];
				$this->user_id  = $this->session->data['user_id'];
				$this->username = $this->session->data['username'];
				$this->group_id = $this->session->data['group_id'];
			}
		}
	}

	/**
	 * 登录处理
	 *
	 * @param string $username 用户账号
	 * @param string $password 用户密码
	 * @param bool   $override 覆盖登录无需密码
	 * @return bool true登录成功,false登录失败
	 */
	public function login($username, $password, $override = false)
	{
		$sql = 'SELECT * FROM ' . DB_PREFIX . "user WHERE username = '{$username}' AND status = 1";
		if (!$override)//非覆盖（正常）登录需要判断用户密码是否正确
		{
			$salt = empty($this->session->data['login_salt']) ? mt_rand(1, 999) : $this->session->data['login_salt'];
			$sql  = "{$sql} AND MD5(CONCAT(password, '{$salt}')) = '{$password}'";
			unset($this->session->data['login_salt']);
		}

		$user_info = $this->sdb()->fetch_row($sql);
		if (!empty($user_info))
		{
			$this->user_id                   = $user_info['user_id'];
			$this->username                  = $user_info['username'];
			$this->session->data['org_id']   = $user_info['org_id'];
			$this->session->data['user_id']  = $user_info['user_id'];
			$this->session->data['username'] = $user_info['username'];
			$this->session->data['group_id'] = $user_info['group_id'];

			wcore_utils::set_cookie('language', $user_info['lang'], 365);
			wcore_utils::set_cookie('groupid', $user_info['group_id'], 365);
			$this->permission = $this->_getPermission($user_info['group_id']);

			/**
			 * 绑定微信公众号的用户
			 */
			$wx_openid = $this->session->get('wx_openid');
			if (!empty($wx_openid))
			{
				$wx_openid = ", open_id = '{$wx_openid}'";
				$this->session->del('wx_openid');
			}
			$this->mdb()->query('UPDATE ' . DB_PREFIX . "user SET ip = '{$_SERVER['REMOTE_ADDR']}', error_count = 0, date_last = NOW(){$wx_openid} WHERE user_id = '{$this->session->data['user_id']}'");

			return true;
		}

		return false;
	}

	/**
	 * 获取用户组权限
	 *
	 * @param integer $group_id 用户组编号
	 * @return array 权限数据姐
	 */
	private function _getPermission($group_id)
	{
		$okey             = "USER-GROUP{$group_id}-ORG";
		$mkey             = "USER-GROUP{$group_id}-PERMISSION";
		$this->orgpos     = $this->mem_get($okey);
		$group_permission = $this->mem_get($mkey);
		if (empty($group_permission))
		{
			/**
			 * 机构数据权限处理
			 */
			$group_row    = $this->sdb()->fetch_row('SELECT permission, orgpos FROM ' . DB_PREFIX . "user_group WHERE group_id = '{$group_id}'");
			$this->orgpos = $group_row['orgpos'];
			$this->mem_set($okey, $this->orgpos, 0);

			/**
			 * 功能权限处理
			 */
			$permissions = json_decode($group_row['permission'], true);
			if (is_array($permissions))
			{
				foreach ($permissions as $key => $value)
				{
					$group_permission[$key] = array_flip($value);
				}
			}
			$this->mem_set($mkey, $group_permission, 0);
		}

		/**
		 * 判断是否获取所有机构数据，如果不是则可获取用户所属机构与子机构下的数据
		 */
		if ($this->orgpos != '*')
		{
			$org_id = $this->session->data['org_id'];
			$orgpos = $this->mem_get("POS-ORG-ID-{$org_id}");
			if (empty($orgpos))
			{
				$orgpos = $this->getSubOrgId($org_id) . $org_id . (!empty($this->orgpos) ? ",{$this->orgpos}" : '');
				$this->mem_set("POS-ORG-ID-{$org_id}", $orgpos);
			}
			$this->orgpos = $orgpos;
		}

		return $group_permission;
	}

	/**
	 * 获取机构下面所有的子机构编号
	 *
	 * @param integer $parent_id 父亲编号
	 * @return array
	 */
	public function getSubOrgId($parent_id)
	{
		$opos = '';
		$sql  = 'SELECT org_id, parent_id FROM ' . DB_PREFIX . "org WHERE parent_id = '{$parent_id}'";
		$rows = $this->sdb()->fetch_all($sql);
		if (!empty($rows))
		{
			foreach ($rows as $row)
			{
				$opos .= "{$row['org_id']},{$this->getSubOrgId($row['org_id'])}";
			}
		}

		return $opos;
	}

	/**
	 * 安全退出
	 */
	public function logout()
	{
		$this->user_id  = 0;
		$this->group_id = 0;
		$this->username = '';
		$this->session->destroy();
	}

	/**
	 * 安全检测登录出错次数
	 *
	 * @param  string $username 用户账号
	 * @param bool    $reload   重新从数据库加载用户数据
	 * @return mixed 用户数据组
	 */
	public function securityCheck($username, $reload = false)
	{
		$res = $this->mem_get($username);
		if (empty($res) || $reload)
		{
			$res = $this->sdb()->fetch_row('SELECT * FROM ' . DB_PREFIX . "user WHERE LOWER(username) = '{$username}' AND status = 1");
		}

		if (!empty($res))
		{
			$res['error_count'] = intval($res['error_count']) + 1;
		}
		$this->mem_set($username, $res);

		return $res;
	}

	/**
	 * 检察某个功能是否有读或写权限
	 *
	 * @param string $key   访问或修改(access|modify)
	 * @param string $value 功能权限值
	 * @return bool true为有权限false无权限
	 */
	public function hasPermission($key, $value)
	{
		return isset($this->permission[$key][$value]);
	}

	/**
	 * 是否已登录
	 *
	 * @return bool 结果,true已登录false未登录
	 */
	public function isLogged()
	{
		return $this->user_id > 0;
	}

	/**
	 * 获取用户编号
	 *
	 * @return int 用户编号
	 */
	public function getId()
	{
		return $this->user_id;
	}

	/**
	 * 获取机构编号
	 *
	 * @return int 机构编号
	 */
	public function getOrgId()
	{
		return $this->org_id;
	}

	/**
	 * 获取用户组编号
	 *
	 * @return int 用户组编号
	 */
	public function getGroupId()
	{
		return $this->group_id;
	}

	/**
	 * 获取用户账号
	 *
	 * @return string 用户账号
	 */
	public function getUserName()
	{
		return $this->username;
	}

	/**
	 * 获取账号可操作的机构列表
	 *
	 * @return string 机构列表1,2,3,4,5
	 */
	public function getOrgPos()
	{
		return $this->orgpos;
	}
}
?>