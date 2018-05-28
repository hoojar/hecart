<?php
/**
 * 安全检测处理
 */
class ControllerCommonSafecheck extends Controller
{
	/**
	 * 检查是否需要登录，如果需要登录，但没有登录，则强制登录
	 */
	public function login()
	{
		$stoken = isset($this->session->data['token']) ? $this->session->data['token'] : '';
		$ctoken = isset($this->request->cookie['token']) ? $this->request->cookie['token'] : '';
		if ($this->user->isLogged() && $stoken === $ctoken && $ctoken === security_token(SITE_MD5_KEY))
		{
			return true;
		}

		/**
		 * 排除不需要登录可操作的地址
		 */
		$ignore        = array(
			'common/login',
			'common/reset',
			'common/logout',
			'common/forgotten',
			'error/not_found',
			'error/permission'
		);
		$config_ignore = array();
		if ($this->config->get('config_token_ignore'))
		{
			$config_ignore = unserialize($this->config->get('config_token_ignore'));
		}

		$route = $this->get_route();
		if (!in_array($route[0], array_merge($ignore, $config_ignore)))
		{
			exit($this->registry->exectrl('common/login'));
		}
	}

	/**
	 * 判断用户是否有查看权限
	 */
	public function permission()
	{
		if (isset($this->request->get['route']))
		{
			/**
			 * 排除以下操作路由不判断权限
			 */
			$ignore = array(
				'common/home',
				'common/login',
				'common/reset',
				'common/logout',
				'common/forgotten',
				'error/not_found',
				'error/permission'
			);

			/**
			 * 判断是否拥有访问权限
			 */
			$route = $this->get_route();
			if (!in_array($route[0], $ignore) && !$this->user->hasPermission('access', $route[0]))
			{
				exit($this->registry->exectrl('error/permission'));
			}

			$this->config->apermission = true; //访问权限
			$this->config->mpermission = $this->user->hasPermission('modify', $route[0]);//检测是否有更改权限
		}
	}
}
?>