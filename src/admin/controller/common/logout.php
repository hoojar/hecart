<?php
/**
 * 后台用户退出处理
 */
class ControllerCommonLogout extends Controller
{
	public function index()
	{
		$this->user->logout();
		wcore_utils::set_cookie('token', null);
		$this->registry->redirect($this->url->link('common/login', '', true));
	}

	/**
	 * 转换权限数据到JSON
	 */
	public function gjson()
	{
		$opt = DB_PREFIX . 'user_group';
		$res = $this->sdb()->fetch_all("SELECT group_id, permission FROM {$opt}");
		foreach ($res as $v)
		{
			$permission = unserialize($v['permission']);
			$data       = array('permission' => json_encode($permission));
			$this->mdb()->update($opt, $data, "group_id = '{$v['group_id']}'");
		}
		exit('Change unserialize to json success.');
	}
}
?>