<?php
/**
 * 微信接口库
 */
class modules_wechat extends modules_mem
{
	/**
	 * @var string 公共平台APPID
	 */
	private $_appid = WX_APPID;

	/**
	 * @var string 公共平台appsecret
	 */
	private $_appsecret = WX_APPSECRET;

	/**
	 * @var string 公共平台token
	 */
	private $_apptoken = WX_APPTOKEN;

	/**
	 * 判断是否在微信中访问
	 */
	public function isWeixin()
	{
		if (!isset($_SERVER['HTTP_USER_AGENT']) || stripos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false)
		{
			return false;
		}

		return true;
	}

	/**
	 * 创建临时字符串
	 *
	 * @param int $length 创建长度
	 * @return string 字符串
	 */
	public function createNonce($length = 32)
	{
		$str   = '';
		$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
		for ($i = 0; $i < $length; $i++)
		{
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}

		return $str;
	}

	/**
	 * 校验签名
	 *
	 * @return bool true成功false失败
	 */
	public function checkSignature()
	{
		$nonce     = isset($_REQUEST['nonce']) ? $_REQUEST['nonce'] : '';
		$signature = isset($_REQUEST['signature']) ? $_REQUEST['signature'] : '';
		$timestamp = isset($_REQUEST['timestamp']) ? $_REQUEST['timestamp'] : '';
		$tmp_arr   = array(
			$this->_apptoken,
			$timestamp,
			$nonce
		);
		sort($tmp_arr, SORT_STRING);

		return (sha1(implode('', $tmp_arr)) == $signature);
	}

	/**
	 * 获取access token
	 *
	 * @return string token串
	 */
	public function getAccessToken()
	{
		$access_token = $this->mem_get("ACCESS-TOKEN-{$this->_appid}");
		if (!empty($access_token))
		{
			return $access_token;
		}

		$url          = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->_appid}&secret={$this->_appsecret}";
		$res          = json_decode(wcore_utils::curl($url), true);
		$access_token = isset($res['access_token']) ? trim($res['access_token']) : '';
		$this->mem_set("ACCESS-TOKEN-{$this->_appid}", $access_token, 77);

		return $access_token;
	}

	/**
	 * 获取jsapi Ticket
	 *
	 * @return string Ticket串
	 */
	public function getJsapiTicket()
	{
		$jsapi_ticket = $this->mem_get("JSAPI-TICKET-{$this->_appid}");
		if (!empty($jsapi_ticket))
		{
			return $jsapi_ticket;
		}

		$access_token = $this->getAccessToken();
		$url          = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$access_token}&type=jsapi";
		$res          = json_decode(wcore_utils::curl($url), true);
		$jsapi_ticket = isset($res['ticket']) ? trim($res['ticket']) : '';
		$this->mem_set("JSAPI-TICKET-{$this->_appid}", $jsapi_ticket, 77);

		return $jsapi_ticket;
	}

	/**
	 * 获取JSAPI加密后的所有参数
	 *
	 * @return array 参数数组
	 */
	public function getJsapiParameters()
	{
		$string = '';
		$ticket = $this->getJsapiTicket();
		if (empty($ticket))
		{
			$_GET['nocache'] = 1;
			$ticket          = $this->getJsapiTicket();
		}

		$parameters = array(
			'jsapi_ticket' => $ticket,
			'timestamp'    => time(),
			'noncestr'     => $this->createNonce(),
			'url'          => substr(HTTP_STORE, 0, -1) . $_SERVER['REQUEST_URI'],
		);
		ksort($parameters);
		foreach ($parameters as $k => $v)
		{
			$string .= "{$k}={$v}&";
		}
		$parameters['appid']     = $this->_appid;
		$parameters['signature'] = sha1(substr($string, 0, -1));

		return $parameters;
	}

	/**
	 * 获取微信用户openid
	 *
	 * @return string 用户openid
	 */
	public function getOpenid()
	{
		if (!empty($_SESSION['wx_openid']))
		{
			return $_SESSION['wx_openid'];
		}

		if (!$this->isWeixin())
		{
			return '';//不在微信中打开，就无法获取open id
		}

		/**
		 * 获取微信登录授权CODE
		 */
		if (empty($_GET['code']))
		{
			$url = urlencode(HTTP_STORE . $_REQUEST['route']);
			$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . WX_APPID . "&redirect_uri={$url}&response_type=code&scope=snsapi_base&state=sptcj#wechat_redirect";
			header("Location: {$url}");
			exit();
		}

		/**
		 * 根据得到的微信授权code登录，获取微信open id
		 */
		$openid = '';
		$url    = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . WX_APPID . '&secret=' . WX_APPSECRET . "&code={$_GET['code']}&grant_type=authorization_code";
		$res    = json_decode(wcore_utils::curl($url), true);
		if (isset($res['openid']))
		{
			$openid                = $res['openid'];
			$_SESSION['wx_openid'] = $res['openid'];
		}

		return $openid;
	}

	/**
	 * 发送消息给微信用户
	 *
	 * @param string $toid 发送给谁
	 * @param string $msg  发送的内容
	 * @return mixed
	 */
	public function sendMsg($toid, $msg)
	{
		$url     = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . $this->getAccessToken();
		$content = '{"touser":"' . $toid . '","msgtype":"text","text":{"content":"' . $msg . '"}}';
		$result  = wcore_utils::curl($url, $content, true);

		if (defined('DEBUG_LOG') && DEBUG_LOG)//记录日志
		{
			file_put_contents(DIR_ROOT . '/logs/wx-msg.log', date('Y-m-d H:i:s') . "\n{$url}\n{$content}\n{$result}\n\n", FILE_APPEND);
		}

		/**
		 * 判断是否为 invalid credential, access_token is invalid or not latest hint
		 * 如果是因access_token过期而产生的，则重新生成access_token再次调用此接口
		 */
		$res = @json_decode($result, true);
		if (isset($res['errcode']) && $res['errcode'] == 40001)
		{
			$_GET['nocache'] = 1;//重新刷新access_token
			$res             = $this->sendMsg($toid, $msg);
		}

		return $res;
	}

	/**
	 * 发送模板消息给微信用户
	 *
	 * @param string $toid 发送给谁
	 * @param string $tpl  使用哪个模板
	 * @param array  $data 要发送的具体数据
	 * @return mixed
	 */
	public function sendTplMsg($toid, $tpl, $data)
	{
		$url                 = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $this->getAccessToken();
		$data['touser']      = $toid;
		$data['template_id'] = $tpl;
		$content             = json_encode($data, JSON_UNESCAPED_UNICODE);
		$result              = wcore_utils::curl($url, $content, true);

		if (defined('DEBUG_LOG') && DEBUG_LOG)//记录日志
		{
			file_put_contents(DIR_ROOT . '/logs/wx-tpl.log', date('Y-m-d H:i:s') . "\n{$url}\n{$content}\n{$result}\n\n", FILE_APPEND);
		}

		/**
		 * 判断是否为 invalid credential, access_token is invalid or not latest hint
		 * 如果是因access_token过期而产生的，则重新生成access_token再次调用此接口
		 */
		$res = @json_decode($result, true);
		if (isset($res['errcode']) && $res['errcode'] == 40001)
		{
			$_GET['nocache'] = 1;//重新刷新access_token
			$res             = $this->sendTplMsg($toid, $tpl, $data);
		}

		return $res;
	}
}
?>