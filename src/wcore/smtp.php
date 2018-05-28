<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/smtp.php
 * 简述: 发邮件核心程序类 - 参考了：phpmailer
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: smtp.php 1150 2017-07-24 02:10:33Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_smtp
{
	public $priority = 3; //发送邮件的优称等级(1 = High, 3 = Normal, 5 = low). Default value is 3.
	public $charset = 'utf-8'; //字符集 gb2312 utf-8
	public $content_type = 'plain'; //邮件是以HTML还是text
	public $encoding = 'base64'; //邮件编码 '7bit', 'binary', 'base64', and 'quoted-printable'

	public $from = '#@[]'; //发送邮件地址
	public $fromname = ''; //发送邮件人姓名
	public $subject = ''; //邮件主题
	public $body = ''; //邮件内容

	public $debug = false; //是否开启调试
	public $timezone = '+0000'; //设置时区
	public $request_notify = false; //是否有请求通报
	public $temp_folder = './'; //操作的临时文件夹

	public $error_msg = ''; //出错信息
	public $host = 'localhost'; //邮件服务器地址IP
	public $port = 25; //邮件服务器端口
	public $helo = ''; //发送测试helo请求
	public $timeout = 10; //设置超时长度

	public $to = array(); //收件人地址
	public $cc = array(); //抄送人地址
	public $bcc = array(); //密送人地址
	public $reply_to = array(); //回复人地址

	private $version = '1.0';

	private $attachment = array(); //发送附件
	private $embed_file = array();

	private $boundary = false;

	private $auth_login = false; //是否以登录形式发送
	private $auth_user = ''; //登录用户名
	private $auth_pass = ''; //登录密码
	private $smime_sign = false; //是否签名
	private $smime_crypt = false; //是否加密

	/**
	 * 构造函数
	 *
	 * @param string  $host    邮件服务器主机
	 * @param integer $port    邮件服务器端口
	 * @param string  $user    发送邮件用户名
	 * @param string  $pass    发送邮件密码
	 * @param string  $charset 发送邮件字符集
	 */
	public function __construct($host = 'localhost', $port = 25, $user = '', $pass = '', $charset = 'UTF-8')
	{
		$this->version = '1.0';
		$this->helo    = 'WoodsMail';
		if ($host && $port)
		{
			$this->set_host($host, $port); //连接邮件服务器地址与端口
		}

		if ($user && $pass)
		{
			$this->user_login($user, $pass); //登录邮件服务器
		}

		if ($charset)
		{
			$this->charset = $charset;
		}
	}

	/**
	 * 析构函数
	 *
	 */
	public function __destruct()
	{
		$this->clear();
	}

	/**
	 * 用户登录
	 *
	 * @param string $user username
	 * @param string $pass password
	 */
	public function user_login($user, $pass)
	{
		$this->auth_login = true;
		$this->auth_user  = $user;
		$this->auth_pass  = $pass;
	}

	/**
	 * 是否以HTML发送
	 *
	 * @param boolean $bool
	 */
	public function is_html($bool = true)
	{
		$this->content_type = ($bool) ? "html" : "plain";
	}

	/**
	 * 设置邮件服务器
	 *
	 * @param string  $host 主机
	 * @param integer $port 端口号
	 */
	public function set_host($host, $port = 25)
	{
		$this->host = trim($host);
		if (!empty($port))
		{
			$this->port = $port;
		}
	}

	/**
	 * 设置邮件发送者信息
	 *
	 * @param string $address email地址
	 * @param string $name    email作者名
	 */
	public function set_from($address, $name = "webmaster")
	{
		$this->from     = trim($address);
		$this->fromname = $name;
	}

	/**
	 * 增加收件人邮箱与收件人姓名
	 *
	 * @param string $address email地址
	 * @param string $name    email作者名
	 */
	public function add_to($address, $name = '')
	{
		$cur               = count($this->to);
		$this->to[$cur][0] = trim($address);
		$this->to[$cur][1] = $name;
	}

	/**
	 * 增加抄送人邮箱与抄送人姓名
	 *
	 * @param string $address email地址
	 * @param string $name    email作者名
	 */
	public function add_cc($address, $name = '')
	{
		$cur               = count($this->cc);
		$this->cc[$cur][0] = trim($address);
		$this->cc[$cur][1] = $name;
	}

	/**
	 * 增加密送人邮箱地址与密送人姓名
	 *
	 * @param string $address email地址
	 * @param string $name    email作者名
	 */
	public function add_bcc($address, $name = '')
	{
		$cur                = count($this->bcc);
		$this->bcc[$cur][0] = trim($address);
		$this->bcc[$cur][1] = $name;
	}

	/**
	 * 增加回复人邮箱地址与回复人姓名
	 *
	 * @param string $address email地址
	 * @param string $name    email作者名
	 */
	public function add_reply_to($address, $name = '')
	{
		$cur                     = count($this->reply_to);
		$this->reply_to[$cur][0] = trim($address);
		$this->reply_to[$cur][1] = $name;
	}

	/**
	 * 发送邮件
	 *
	 * @return boolean 成功为true失败为flase
	 */
	public function send()
	{
		if (count($this->to) + count($this->cc) + count($this->bcc) == 0)
		{
			$this->error_msg = "You must provide at least one recipient address";

			return false;
		}
		$msgdata = $this->create_msg();
		if ($this->_send_mail($msgdata) === false)
		{
			return false;
		}

		return $msgdata; //返回邮件格式内容
	}

	/**
	 * 创建邮件信息
	 *
	 * @return string 邮件内容
	 */
	public function create_msg()
	{
		$msgdata  = '';
		$bconvert = false;
		if ($this->smime_sign || $this->smime_crypt)
		{
			$msgdata = $this->_create_header(true);
			$msgdata .= $this->_create_body();

			$fileid       = md5(uniqid());
			$new_msg_file = $this->temp_folder . "_attachments\\" . $this->auth_user . '_' . $fileid . "_newmsg.eml";
			$signfile     = $this->temp_folder . "_attachments\\" . $this->auth_user . '_' . $fileid . "_signmsg.eml";
			$crypt_file   = $this->temp_folder . "_attachments\\" . $this->auth_user . '_' . $fileid . "_cryptmsg.eml";

			if (file_put_contents($new_msg_file, $msgdata))
			{
				$header_info = $this->_get_header_info(true);
				if ($this->smime_sign)
				{
					$bret = read_my_acitve_cert($cert, $key);
					if ($bret && openssl_pkcs7_sign($new_msg_file, $signfile, $cert, $key, $header_info))
					{
						$new_msg_file = $signfile;
						$bconvert     = true;
					}
				}

				if ($this->smime_crypt)
				{
					$arr_address[] = $this->from;
					for ($i = 0; $i < count($this->to); $i++)
					{
						if (!in_array($this->to[$i][0], $arr_address))
						{
							$arr_address[] = $this->to[$i][0];
						}
					}

					for ($i = 0; $i < count($this->cc); $i++)
					{
						if (!in_array($this->to[$i][0], $arr_address))
						{
							$arr_address[] = $this->cc[$i][0];
						}
					}

					for ($i = 0; $i < count($this->bcc); $i++)
					{
						if (!in_array($this->to[$i][0], $arr_address))
						{
							$arr_address[] = $this->bcc[$i][0];
						}
					}

					$bret = read_all_receiver_cert($arr_address, $arr_cert);
					if ($bret && openssl_pkcs7_encrypt($new_msg_file, $crypt_file, $arr_cert, $header_info))
					{
						$new_msg_file = $crypt_file;
						$bconvert     = true;
					}
				}

				if ($bconvert)
				{
					$msgdata = file_get_contents($new_msg_file);
					$msgdata = str_replace("\r\n", "\n", $msgdata);
					$msgdata = str_replace("\n", "\r\n", $msgdata);
				}
			}

			@unlink($new_msg_file);
			@unlink($signfile);
			@unlink($crypt_file);
		}

		if (!$bconvert)
		{
			$msgdata = $this->_create_header();
			$msgdata .= $this->_create_body();
		}

		return $msgdata;
	}

	/**
	 * 增加附件
	 *
	 * @param string $path 路径
	 * @param string $name 文件名
	 * @param string $type 文件类型
	 * @return boolean
	 */
	public function add_attachment($path, $name = '', $type = "application/octet-stream")
	{
		if (!@is_file($path))
		{
			$this->error_msg = sprintf("Could not find %s", $path);

			return false;
		}

		$filename = basename($path);
		if ($name == '')
		{
			$name = $filename;
		}

		$cur                       = count($this->attachment);
		$this->attachment[$cur][0] = $path;
		$this->attachment[$cur][1] = $filename;
		$this->attachment[$cur][2] = $name;
		$this->attachment[$cur][3] = $type;

		return true;
	}

	/**
	 * 将附件插入邮件中
	 *
	 * @param string $path     路径
	 * @param string $name     文件名
	 * @param string $filename 文件名
	 * @param string $type     文件类型
	 * @return unknown
	 */
	public function add_embed_file($path, $name, $filename, $type = "image/gif")
	{
		if (!@is_file($path))
		{
			$this->error_msg = sprintf("Could not find %s", $path);

			return false;
		}

		$name                      = basename($name);
		$cur                       = count($this->embed_file);
		$this->embed_file[$cur][0] = $path;
		$this->embed_file[$cur][1] = $filename;
		$this->embed_file[$cur][2] = $name;
		$this->embed_file[$cur][3] = $type;
		$this->embed_file[$cur][4] = md5(uniqid(time()));

		return true;
	}

	/**
	 * 以为安全发送邮件
	 *
	 * @param string $sign
	 * @param string $crypt
	 */
	public function use_secure_mime($sign, $crypt)
	{
		$this->smime_sign  = $sign;
		$this->smime_crypt = $crypt;
	}

	/**
	 * 清除邮件信息
	 *
	 */
	public function clear()
	{
		$this->fromname   = '';
		$this->from       = '';
		$this->to         = array();
		$this->cc         = array();
		$this->bcc        = array();
		$this->subject    = '';
		$this->body       = '';
		$this->attachment = array();
		$this->embed_file = array();
	}

	/**
	 * 编码
	 *
	 * @param string $string
	 * @return string
	 */
	private function _mime_encode($string)
	{
		if ($string == '')
		{
			return;
		}
		if (!preg_match("/^([[:print:]]*)$/i", $string))
		{
			$string = "=?" . $this->charset . "?B?" . base64_encode($string) . "?=";
		}

		return $string;
	}

	/**
	 * 设置邮件边界
	 *
	 * @return string
	 */
	private function _get_boundary()
	{
		static $index = 0;
		$boundary = "__b_" . sprintf("%03d", $index++) . "_" . md5(uniqid(time()));

		return $boundary;
	}

	/**
	 * 加密文件
	 *
	 * @param string $path 文件路径
	 * @return string
	 */
	private function _encode_file($path)
	{
		$content = file_get_contents($path);
		if ($content === false)
		{
			$this->error_msg = sprintf("Could not find %s", $path);

			return '';
		}
		$encoded = chunk_split(base64_encode($content));
		unset($content);

		return trim($encoded);
	}

	/**
	 * 调整内置文件路径
	 *
	 * @param string $body
	 * @return string
	 */
	private function _adjust_embed_path($body)
	{
		$iEmbedCount = count($this->embed_file);
		for ($i = 0; $i < $iEmbedCount; $i++)
		{
			$filename = $this->embed_file[$i][1];
			$cid      = $this->embed_file[$i][4];
			$body     = str_replace($filename, "cid:" . $cid, $body);
		}

		return $body;
	}

	/**
	 * 创建地址
	 *
	 * @param string $address
	 * @return string
	 */
	private function _create_address($address)
	{
		$strAddress = '';
		$iAddrCount = count($address);
		for ($i = 0; $i < $iAddrCount; $i++)
		{
			if (!empty($strAddress))
			{
				$strAddress .= ", \r\n\t";
			}
			if (trim($address[$i][1]) != '')
			{
				$strAddress .= sprintf("\"%s\" <%s>", $this->_mime_encode($address[$i][1]), $address[$i][0]);
			}
			else
			{
				$strAddress .= sprintf("%s", $address[$i][0]);
			}
		}

		return $strAddress;
	}

	/**
	 * 获取邮件头信息
	 *
	 * @return string
	 */
	private function _get_header_info()
	{
		$header_info         = array();
		$header_info["Date"] = sprintf("%s %s", date("D, j M Y G:i:s"), $this->timezone);
		$header_info["From"] = sprintf("\"%s\" <%s>", $this->_mime_encode($this->fromname), trim($this->from));
		if (count($this->to) > 0)
		{
			$header_info["To"] = $this->_create_address($this->to);
		}
		if (count($this->cc) > 0)
		{
			$header_info["Cc"] = $this->_create_address($this->cc);
		}
		if (count($this->reply_to) > 0)
		{
			$header_info["Reply-to"] = $this->_create_address($this->reply_to);
		}

		$header_info["Subject"]    = $this->_mime_encode(trim($this->subject));
		$header_info["X-Priority"] = $this->priority;
		$header_info["X-Mailer"]   = $this->version;
		$header_info["Message-ID"] = md5(uniqid(rand())) . strstr($this->from, '@');
		if ($this->request_notify)
		{
			$header_info["Disposition-Notification-To"] = sprintf("\"%s\" <%s>\r", $this->_mime_encode($this->fromname), trim($this->from));
		}

		return $header_info;
	}

	/**
	 * 创建邮件头信息
	 *
	 * @param boolean $blite
	 * @return string
	 */
	private function _create_header($blite = false)
	{
		$header_info = array();
		if (!$blite)
		{
			$header_info[] = sprintf("Date: %s %s\r\n", date("D, j M Y G:i:s"), $this->timezone);
			$header_info[] = sprintf("From: \"%s\" <%s>\r\n", $this->_mime_encode($this->fromname), trim($this->from));
			if (count($this->to) > 0)
				$header_info[] = sprintf("To: %s\r\n", $this->_create_address($this->to));
			if (count($this->cc) > 0)
				$header_info[] = sprintf("Cc: %s\r\n", $this->_create_address($this->cc));
			if (count($this->reply_to) > 0)
				$header_info[] = sprintf("Reply-To: %s\r\n", $this->_create_address($this->reply_to));
			$header_info[] = sprintf("Subject: %s\r\n", $this->_mime_encode(trim($this->subject)));
			$header_info[] = sprintf("X-Priority: %d\r\n", $this->priority);
			$header_info[] = sprintf("X-Mailer: %s\r\n", $this->version);
			$header_info[] = sprintf("Message-ID: <%s>\r\n", md5(uniqid(rand())) . strstr($this->from, '@'));
		}

		$this->boundary = $this->_get_boundary();
		if ($this->content_type == "html")
		{
			if (count($this->attachment) > 0)
			{
				$header_info[] = sprintf("Content-Type: multipart/mixed;\r\n");
				$header_info[] = sprintf("\tboundary=\"--=%s\"\r\n", $this->boundary);
			}
			else if (count($this->embed_file) > 0)
			{
				$header_info[] = sprintf("Content-Type: multipart/related; \r\n");
				$header_info[] = sprintf("\ttype=\"multipart/alternative\";\r\n");
				$header_info[] = sprintf("\tboundary=\"--=%s\"\r\n", $this->boundary);
			}
			else
			{
				$header_info[] = sprintf("Content-Type: multipart/alternative;\r\n");
				$header_info[] = sprintf("\tboundary=\"--=%s\"\r\n", $this->boundary);
			}
		}
		else
		{
			if (count($this->attachment) > 0)
			{
				$header_info[] = sprintf("Content-Type: multipart/mixed;\r\n");
				$header_info[] = sprintf("\tboundary=\"--=%s\"\r\n", $this->boundary);
			}
			else
			{
				$header_info[] = sprintf("Content-Type: text/plain; \r\n");
				$header_info[] = sprintf("\tcharset=\"%s\";\r\n", $this->charset);
				$header_info[] = sprintf("Content-Transfer-Encoding: %s\r\n", $this->encoding);
			}
		}

		if (!$blite)
		{
			$header_info[] = "MIME-Version: 1.0\r\n";
			if ($this->request_notify)
			{
				$header_info[] = sprintf("Disposition-Notification-To: \"%s\" <%s>\r\n", $this->_mime_encode($this->fromname), trim($this->from));
			}
		}

		return join('', $header_info) . "\r\n";
	}

	/**
	 * 创建邮件内容主体
	 *
	 * @return string
	 */
	private function _create_body()
	{
		$body              = $this->body;
		$boundary          = $this->boundary;
		$iAttachCount      = count($this->attachment);
		$iEmbedCount       = count($this->embed_file);
		$mimetag           = '';
		$msgbodytag        = '';
		$msgbody           = '';
		$embed_filebodytag = '';
		$embed_filebody    = '';
		$attachbody        = '';

		if ($this->content_type == 'html')
		{
			$mimetag = "This is a multi-part message in MIME format.\r\n\r\n";
			if ($iAttachCount > 0)
			{
				$attachbody = $this->_create_attach_body($boundary);
			}

			if ($iEmbedCount > 0)
			{
				if (!empty($attachbody))
				{
					$newboundary = $this->_get_boundary();
					$embed_filebodytag .= sprintf("----=%s\r\n", $boundary);
					$embed_filebodytag .= sprintf("Content-Type: multipart/related;\r\n");
					$embed_filebodytag .= sprintf("\ttype=\"multipart/alternative\";\r\n");
					$embed_filebodytag .= sprintf("\tboundary=\"--=%s\"\r\n\r\n", $newboundary);
					$boundary = $newboundary;
				}
				$body           = $this->_adjust_embed_path($body);
				$embed_filebody = $this->_create_embed_file_body($boundary);
			}

			if (!empty($attachbody) || !empty($embed_filebody))
			{
				$newboundary = $this->_get_boundary();
				$msgbodytag .= sprintf("----=%s\r\n", $boundary);
				$msgbodytag .= sprintf("Content-Type: multipart/alternative;\r\n");
				$msgbodytag .= sprintf("\tboundary=\"--=%s\"\r\n\r\n", $newboundary);
				$boundary = $newboundary;
			}
			$msgbody .= $this->_create_html_body($boundary, $body);
		}
		else
		{
			if ($iAttachCount > 0)
			{
				$mimetag    = "This is a multi-part message in MIME format.\r\n\r\n";
				$attachbody = $this->_create_attach_body($boundary);
			}
			else
			{
				$boundary = '';
			}
			$msgbody = $this->_create_plain_body($boundary, $body);
		}

		$body = $mimetag;
		$body .= $embed_filebodytag;
		$body .= $msgbodytag;
		$body .= $msgbody;
		$body .= $embed_filebody;
		$body .= $attachbody;

		return trim($body) . "\r\n";
	}

	/**
	 * 加密码邮件主体
	 *
	 * @param string $body
	 * @return string
	 */
	private function _encode_body($body)
	{
		if ($this->encoding == 'base64')
		{
			$mime = chunk_split(base64_encode($body));
		}
		else
		{
			$mime = wordwrap($body, 76, "\r\n");
		}

		return trim($mime);
	}

	/**
	 * 创建文本内容
	 *
	 * @param string $boundary
	 * @param string $body
	 * @return string
	 */
	private function _create_plain_body($boundary = '', $body)
	{
		$mime       = '';
		$encodebody = $this->_encode_body($body);
		if (!empty($boundary))
		{
			$mime = sprintf("----=%s\r\n", $boundary);
			$mime .= sprintf("Content-Type: text/plain;\r\n");
			$mime .= sprintf("\tcharset=\"%s\";\r\n", $this->charset);
			$mime .= sprintf("Content-Transfer-Encoding: %s\r\n\r\n", $this->encoding);
		}
		$mime .= sprintf("%s\r\n\r\n", $encodebody);

		return $mime;
	}

	/**
	 * 创建HTML内容
	 *
	 * @param string $boundary
	 * @param string $body
	 * @return string
	 */
	private function _create_html_body($boundary, $body)
	{
		$encodebody = $this->_encode_body(strip_tags($body));
		$mime       = sprintf("----=%s\r\n", $boundary);
		$mime .= sprintf("Content-Type: text/plain;\r\n");
		$mime .= sprintf("\tcharset=\"%s\";\r\n", $this->charset);
		$mime .= sprintf("Content-Transfer-Encoding: %s\r\n\r\n", $this->encoding);
		$mime .= sprintf("%s\r\n\r\n", $encodebody);
		$encodebody = $this->_encode_body($body);
		$mime .= sprintf("----=%s\r\n", $boundary);
		$mime .= sprintf("Content-Type: text/html;\r\n");
		$mime .= sprintf("\tcharset=\"%s\";\r\n", $this->charset);
		$mime .= sprintf("Content-Transfer-Encoding: %s\r\n\r\n", $this->encoding);
		$mime .= sprintf("%s\r\n\r\n", $encodebody);
		$mime .= sprintf("----=%s--\r\n\r\n", $boundary);

		return $mime;
	}

	private function _create_attach_body($boundary)
	{
		$mime         = '';
		$iAttachCount = count($this->attachment);
		for ($i = 0; $i < $iAttachCount; $i++)
		{
			$path     = $this->attachment[$i][0];
			$filename = $this->attachment[$i][1];
			$name     = $this->attachment[$i][2];
			$type     = $this->attachment[$i][3];
			if (file_exists($path))
			{
				$mime = sprintf("----=%s\r\n", $boundary);
				$mime .= sprintf("Content-Type: %s; name=\"%s\"\r\n", $type, $this->_mime_encode($name));
				$mime .= "Content-Transfer-Encoding: base64\r\n";
				$mime .= sprintf("Content-Disposition: attachment; filename=\"%s\"\r\n\r\n", $this->_mime_encode($name));
				$mime .= sprintf("%s\r\n\r\n", $this->_encode_file($path));
			}
		}
		$mime .= sprintf("----=%s--\r\n\r\n", $boundary);

		return $mime;
	}

	private function _create_embed_file_body($boundary)
	{
		$mime        = '';
		$iEmbedCount = count($this->embed_file);
		for ($i = 0; $i < $iEmbedCount; $i++)
		{
			$path     = $this->embed_file[$i][0];
			$filename = $this->embed_file[$i][1];
			$name     = $this->embed_file[$i][2];
			$type     = $this->embed_file[$i][3];
			$cid      = $this->embed_file[$i][4];
			if (file_exists($path))
			{
				$mime = sprintf("----=%s\r\n", $boundary);
				$mime .= sprintf("Content-Type: %s; name=\"%s\"\r\n", $type, $this->_mime_encode($name));
				$mime .= "Content-Transfer-Encoding: base64\r\n";
				$mime .= sprintf("Content-ID: <%s>\r\n", $cid);
				$mime .= sprintf("Content-Disposition: inline; filename=\"%s\"\r\n\r\n", $this->_mime_encode($name));
				$mime .= sprintf("%s\r\n\r\n", $this->_encode_file($path));
			}
		}
		$mime .= sprintf("----=%s--\r\n\r\n", $boundary);

		return $mime;
	}

	private function _send_mail($msgdata)
	{
		$smtp        = new SMTP;
		$smtp->debug = $this->debug;
		if (!$smtp->connect($this->host, $this->port, $this->timeout))
		{
			$this->error_msg = "SMTP Error: could not connect to SMTP host server";
			$this->error_msg .= "[" . $this->host . ":" . $this->port . "]";

			return false;
		}
		if ($this->auth_login)
		{
			if (!$smtp->auth_hello($this->helo, $this->auth_user, $this->auth_pass))
			{
				$this->error_msg = "SMTP Error: Invalid username/password";
				if ($smtp->error_msg)
					$this->error_msg .= "<br>" . $smtp->error_msg;

				return false;
			}
		}
		else
		{
			$smtp->hello($this->helo);
		}
		if (!$smtp->mail_from(sprintf("<%s>", $this->from)))
		{
			$this->error_msg = "SMTP Error: Mail from [" . $this->from . "] not accepted.";
			if ($smtp->error_msg)
				$this->error_msg .= "<br>" . $smtp->error_msg;

			return false;
		}
		$iToCount = count($this->to);
		for ($i = 0; $i < $iToCount; $i++)
		{
			if (!$smtp->recipient(sprintf("<%s>", $this->to[$i][0])))
			{
				$this->error_msg = "SMTP Error: recipient [" . $this->to[$i][0] . "] not accepted.";
				if ($smtp->error_msg)
					$this->error_msg .= "<br>" . $smtp->error_msg;

				return false;
			}
		}
		$iCcCount = count($this->cc);
		for ($i = 0; $i < $iCcCount; $i++)
		{
			if (!$smtp->recipient(sprintf("<%s>", $this->cc[$i][0])))
			{
				$this->error_msg = "SMTP Error: recipient [" . $this->cc[$i][0] . "] not accepted.";
				if ($smtp->error_msg)
					$this->error_msg .= "<br>" . $smtp->error_msg;

				return false;
			}
		}
		$iBccCount = count($this->bcc);
		for ($i = 0; $i < $iBccCount; $i++)
		{
			if (!$smtp->recipient(sprintf("<%s>", $this->bcc[$i][0])))
			{
				$this->error_msg = "SMTP Error: recipient [" . $this->bcc[$i][0] . "] not accepted.";
				if ($smtp->error_msg)
					$this->error_msg .= "<br>" . $smtp->error_msg;

				return false;
			}
		}
		if (!$smtp->data($msgdata))
		{
			$this->error_msg = "SMTP Error: data not accepted";
			if ($smtp->error_msg)
			{
				$this->error_msg .= "<br>" . $smtp->error_msg;
			}

			return false;
		}
		$smtp->_quit();
	}
}

class SMTP
{
	public $port = 25;

	public $debug;

	public $error_msg;

	public $smtp_obj;

	public $CRLF = "\r\n";

	public function __construct()
	{
		$this->smtp_obj  = 0;
		$this->error_msg = '';
		$this->debug     = 0;
	}

	public function __destruct()
	{
	}

	/**
	 * 连接邮件服务器
	 *
	 * @param string  $host 主机
	 * @param integer $port 端口
	 * @param integer $tval 超时秒
	 * @return boolean
	 */
	public function connect($host, $port = 25, $tval = 30)
	{
		$this->error_msg = '';
		if ($this->_connected())
		{
			$this->error_msg = "Already connected to a server";

			return false;
		}

		$this->smtp_obj = @fsockopen($host, $port, $errno, $errstr, $tval);
		if (empty($this->smtp_obj))
		{
			$this->error_msg = "Failed to connect to server ($errno $errstr)";

			return false;
		}
		$this->_get_lines();

		return true;
	}

	private function _close()
	{
		$this->error_msg = '';
		if (!empty($this->smtp_obj))
		{
			fclose($this->smtp_obj);
			$this->smtp_obj = 0;
		}
	}

	public function hello($host = '')
	{
		$this->error_msg = '';
		if (!$this->_connected())
		{
			$this->error_msg = "Called hello() without being connected";

			return false;
		}

		if (empty($host))
		{
			$host = "SMTP Client";
		}

		$this->_send_line("HELO " . $host);
		$rply = $this->_get_lines();
		$code = substr($rply, 0, 3);
		if ($code != 250)
		{
			$this->error_msg = $rply;

			return false;
		}

		return true;
	}

	public function auth_hello($host = '', $user = '', $pass = '')
	{
		$this->error_msg = null;
		if (!$this->_connected())
		{
			$this->error_msg = "Called hello() without being connected";

			return false;
		}

		if (empty($host))
		{
			$host = "SMTP Client";
		}

		$this->_send_line("EHLO " . $host);
		$rply = $this->_get_lines();
		$code = substr($rply, 0, 3);
		if ($code != 250)
		{
			$this->error_msg = $rply;

			return false;
		}

		$this->_send_line("AUTH LOGIN");
		$rply = $this->_get_lines();
		$code = substr($rply, 0, 3);
		if ($code != 334)
		{
			$this->error_msg = $rply;

			return false;
		}

		$this->_send_line(base64_encode($user));
		$rply = $this->_get_lines();
		$code = substr($rply, 0, 3);
		if ($code != 334)
		{
			$this->error_msg = $rply;

			return false;
		}

		$this->_send_line(base64_encode($pass));
		$rply = $this->_get_lines();
		$code = substr($rply, 0, 3);
		if ($code != 235)
		{
			$this->error_msg = $rply;

			return false;
		}

		return true;
	}

	/**
	 * 邮件来源
	 *
	 * @param string $from
	 * @return boolean
	 */
	public function mail_from($from)
	{
		$this->error_msg = null;
		if (!$this->_connected())
		{
			$this->error_msg = "Called Mail() without being connected";

			return false;
		}

		$this->_send_line("MAIL FROM:" . $from);
		$rply = $this->_get_lines();
		$code = substr($rply, 0, 3);
		if ($code != 250)
		{
			$this->error_msg = $rply;

			return false;
		}

		return true;
	}

	public function recipient($to)
	{
		$this->error_msg = null;
		if (!$this->_connected())
		{
			$this->error_msg = "Called recipient() without being connected";

			return false;
		}

		$this->_send_line("RCPT TO:" . $to);
		$rply = $this->_get_lines();
		$code = substr($rply, 0, 3);
		if ($code != 250 && $code != 251)
		{
			$this->error_msg = $rply;

			return false;
		}

		return true;
	}

	public function data($msg_data)
	{
		$this->error_msg = '';
		if (!$this->_connected())
		{
			$this->error_msg = "Called data() without being connected";

			return false;
		}

		$this->_send_line("DATA");
		$rply = $this->_get_lines();
		$code = substr($rply, 0, 3);
		if ($code != 354)
		{
			$this->error_msg = $rply;

			return false;
		}

		$this->_send_line($msg_data);
		$this->_send_line(".");
		$rply = $this->_get_lines();
		$code = substr($rply, 0, 3);
		if ($code != 250)
		{
			$this->error_msg = $rply;

			return false;
		}

		return true;
	}

	public function _quit($close_on_error = true)
	{
		$this->error_msg = null;
		if (!$this->_connected())
		{
			$this->error_msg = "Called _quit() without being connected";

			return false;
		}

		$this->_send_line("QUIT");
		$byemsg = $this->_get_lines();
		$rval   = true;
		$e      = null;
		$code   = substr($byemsg, 0, 3);
		if ($code != 221)
		{
			$e               = $byemsg;
			$this->error_msg = $byemsg;
			$rval            = false;
		}

		if (empty($e) || $close_on_error)
		{
			$this->_close();
		}

		return $rval;
	}

	private function _connected()
	{
		if (!empty($this->smtp_obj))
		{
			$sock_status = socket_get_status($this->smtp_obj);
			if ($sock_status["eof"])
			{
				$this->_close();

				return false;
			}

			return true;
		}

		return false;
	}

	private function _get_lines()
	{
		$data = '';
		while ($str = fgets($this->smtp_obj, 512))
		{
			$data .= $str;
			if (substr($str, 3, 1) == " ")
			{
				break;
			}
		}

		if ($this->debug)
		{
			$tmp = preg_replace("(\r|\n)", '', $data);
			echo("<font style=\"font-size:12px; font-family: Courier New; background-color: white; color: black;\"><- <b>" . htmlspecialchars($tmp) . "</b></font><br>\r\n");
			flush();
		}

		return $data;
	}

	private function _send_line($data)
	{
		fputs($this->smtp_obj, $data . $this->CRLF);
		if ($this->debug)
		{
			$data = htmlspecialchars($data);
			echo("<font style=\"font-size:12px; font-family: Courier New; background-color: white; color: black;\">-> " . nl2br($data) . "</font><br>\r\n");
			flush();
		}
	}
}

/*
include("./woods-smtp.php");//发送邮件库
$smtp = new wcore_stmp("127.0.0.1", 25, "postmaster", "woods", "gb2312");
$smtp->is_html();
$smtp->add_to("website@harmony-lighting.com", "woods");
$smtp->set_from("hoojar@hoojar.com", "woods.zhang");
$smtp->subject = "i love you";
$smtp->body = file_get_contents("./content.txt");//邮件内容
echo ($smtp->send()) ? "succeed" : "fail";
*/
?>