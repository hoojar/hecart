<?php
class Mail
{
	protected $to = '';

	protected $from = '';

	protected $sender = '';

	protected $subject = '';

	protected $text = '';

	protected $html = '';

	protected $attachments = array();

	public $protocol = 'mail';

	public $hostname = '';

	public $username = '';

	public $password = '';

	public $port = 25;

	public $timeout = 5;

	public $newline = "\n";

	public $crlf = "\r\n";

	public $verp = false;

	public $parameter = '';

	public $charset = 'UTF-8'; //字符集 GB2312 UTF-8

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
		$this->port     = $port;
		$this->hostname = $host;
		$this->username = $user;
		$this->password = $pass;
		$this->charset  = $charset;
	}

	public function setTo($to)
	{
		$this->to = $to;
	}

	public function setFrom($from)
	{
		$this->from = $from;
	}

	public function setSender($sender)
	{
		$this->sender = $sender;
	}

	public function setSubject($subject)
	{
		$this->subject = $subject;
	}

	public function setText($text)
	{
		$this->html = '';
		$this->text = $text;
	}

	public function setHtml($html)
	{
		$this->html = $html;
	}

	public function addAttachment($filename)
	{
		$this->attachments[] = $filename;
	}

	public function send()
	{
		if (!$this->to)
		{
			trigger_error('Error: email to required!');

			return false;
		}

		if (!$this->from)
		{
			trigger_error('Error: email from required!');

			return false;
		}

		if (!$this->sender)
		{
			trigger_error('Error: email sender required!');

			return false;
		}

		if (!$this->subject)
		{
			trigger_error('Error: email subject required!');

			return false;
		}

		if ((!$this->text) && (!$this->html))
		{
			trigger_error('Error: email message required!');

			return false;
		}

		if (is_array($this->to))
		{
			$to = implode(',', $this->to);
		}
		else
		{
			$to = $this->to;
		}

		$boundary = '----=_NextPart_' . md5(time());
		$header   = '';
		$header .= 'MIME-Version: 1.0' . $this->newline;

		if ($this->protocol != 'mail')
		{
			$header .= 'To: ' . $to . $this->newline;
			$header .= 'Subject: ' . $this->subject . $this->newline;
		}

		$header .= 'Date: ' . date('D, d M Y H:i:s O') . $this->newline;
		$header .= "From: =?{$this->charset}?B?" . base64_encode($this->sender) . '?=' . '<' . $this->from . '>' . $this->newline;
		$header .= "Reply-To: =?{$this->charset}?B?" . base64_encode($this->sender) . '?=' . '<' . $this->from . '>' . $this->newline;
		$header .= 'Return-Path: ' . $this->from . $this->newline;
		$header .= 'X-Mailer: PHP/' . phpversion() . $this->newline;
		$header .= 'Content-Type: multipart/related; boundary="' . $boundary . '"' . $this->newline . $this->newline;

		if (!$this->html)
		{
			$message = '--' . $boundary . $this->newline;
			$message .= "Content-Type: text/plain; charset=\"{$this->charset}\"{$this->newline}";
			$message .= 'Content-Transfer-Encoding: 8bit' . $this->newline . $this->newline;
			$message .= $this->text . $this->newline;
		}
		else
		{
			$message = '--' . $boundary . $this->newline;
			$message .= 'Content-Type: multipart/alternative; boundary="' . $boundary . '_alt"' . $this->newline . $this->newline;
			$message .= '--' . $boundary . '_alt' . $this->newline;
			$message .= "Content-Type: text/plain; charset=\"{$this->charset}\"{$this->newline}";
			$message .= 'Content-Transfer-Encoding: 8bit' . $this->newline . $this->newline;

			if ($this->text)
			{
				$message .= $this->text . $this->newline;
			}
			else
			{
				$message .= 'This is a HTML email and your email client software does not support HTML email!' . $this->newline;
			}

			$message .= '--' . $boundary . '_alt' . $this->newline;
			$message .= "Content-Type: text/html; charset=\"{$this->charset}\"{$this->newline}";
			$message .= 'Content-Transfer-Encoding: 8bit' . $this->newline . $this->newline;
			$message .= $this->html . $this->newline;
			$message .= '--' . $boundary . '_alt--' . $this->newline;
		}

		foreach ($this->attachments as $attachment)
		{
			if (file_exists($attachment))
			{
				$handle  = fopen($attachment, 'r');
				$content = fread($handle, filesize($attachment));
				fclose($handle);

				$message .= '--' . $boundary . $this->newline;
				$message .= 'Content-Type: application/octet-stream; name="' . basename($attachment) . '"' . $this->newline;
				$message .= 'Content-Transfer-Encoding: base64' . $this->newline;
				$message .= 'Content-Disposition: attachment; filename="' . basename($attachment) . '"' . $this->newline;
				$message .= 'Content-ID: <' . basename(urlencode($attachment)) . '>' . $this->newline;
				$message .= 'X-Attachment-Id: ' . basename(urlencode($attachment)) . $this->newline . $this->newline;
				$message .= chunk_split(base64_encode($content));
			}
		}

		$message .= '--' . $boundary . '--' . $this->newline;

		if ($this->protocol == 'mail')
		{
			ini_set('sendmail_from', $this->from);
			if ($this->parameter)
			{
				@mail($to, "=?{$this->charset}?B?" . base64_encode($this->subject) . '?=', $message, $header, $this->parameter);
			}
			else
			{
				@mail($to, "=?{$this->charset}?B?" . base64_encode($this->subject) . '?=', $message, $header);
			}

			return true;
		}

		/**
		 * SMTP
		 */
		if (empty($this->hostname) || empty($this->port))
		{
			return false;
		}

		$handle = fsockopen($this->hostname, $this->port, $errno, $errstr, $this->timeout);
		if (!$handle)
		{
			trigger_error('Error: ' . $errstr . ' (' . $errno . ')');

			return false;
		}

		if (substr(PHP_OS, 0, 3) != 'WIN')
		{
			socket_set_timeout($handle, $this->timeout, 0);
		}

		while ($line = fgets($handle, 515))
		{
			if (substr($line, 3, 1) == ' ')
			{
				break;
			}
		}

		if (substr($this->hostname, 0, 3) == 'tls')
		{
			fputs($handle, 'STARTTLS' . $this->crlf);
			$reply = '';
			while ($line = fgets($handle, 515))
			{
				$reply .= $line;
				if (substr($line, 3, 1) == ' ')
				{
					break;
				}
			}

			if (substr($reply, 0, 3) != 220)
			{
				trigger_error('Error: STARTTLS not accepted from server! Error Msg:' . $reply);

				return false;
			}
		}

		if (!empty($this->username) && !empty($this->password))
		{
			if (strpos($this->username, '@') !== false)
			{
				$this->from = $this->username;
			}

			fputs($handle, 'EHLO ' . getenv('SERVER_NAME') . $this->crlf);
			$reply = '';
			while ($line = fgets($handle, 515))
			{
				$reply .= $line;
				if (substr($line, 3, 1) == ' ')
				{
					break;
				}
			}

			if (substr($reply, 0, 3) != 250)
			{
				trigger_error('Error: EHLO not accepted from server! Error Msg:' . $reply);

				return false;
			}

			fputs($handle, 'AUTH LOGIN' . $this->crlf);

			$reply = '';
			while ($line = fgets($handle, 515))
			{
				$reply .= $line;
				if (substr($line, 3, 1) == ' ')
				{
					break;
				}
			}

			if (substr($reply, 0, 3) != 334)
			{
				trigger_error('Error: AUTH LOGIN not accepted from server! Error Msg:' . $reply);

				return false;
			}

			fputs($handle, base64_encode($this->username) . $this->crlf);

			$reply = '';
			while ($line = fgets($handle, 515))
			{
				$reply .= $line;
				if (substr($line, 3, 1) == ' ')
				{
					break;
				}
			}

			if (substr($reply, 0, 3) != 334)
			{
				trigger_error('Error: Username not accepted from server! Error Msg:' . $reply);

				return false;
			}

			fputs($handle, base64_encode($this->password) . $this->crlf);

			$reply = '';
			while ($line = fgets($handle, 515))
			{
				$reply .= $line;
				if (substr($line, 3, 1) == ' ')
				{
					break;
				}
			}

			if (substr($reply, 0, 3) != 235)
			{
				trigger_error('Error: Password not accepted from server! Error Msg:' . $reply);

				return false;
			}
		}
		else
		{
			fputs($handle, 'HELO ' . getenv('SERVER_NAME') . $this->crlf);

			$reply = '';
			while ($line = fgets($handle, 515))
			{
				$reply .= $line;
				if (substr($line, 3, 1) == ' ')
				{
					break;
				}
			}

			if (substr($reply, 0, 3) != 250)
			{
				trigger_error('Error: HELO not accepted from server! Error Msg:' . $reply);

				return false;
			}
		}

		if ($this->verp)
		{
			fputs($handle, 'MAIL FROM: <' . $this->from . '>XVERP' . $this->crlf);
		}
		else
		{
			fputs($handle, 'MAIL FROM: <' . $this->from . '>' . $this->crlf);
		}

		$reply = '';
		while ($line = fgets($handle, 515))
		{
			$reply .= $line;
			if (substr($line, 3, 1) == ' ')
			{
				break;
			}
		}

		if (substr($reply, 0, 3) != 250)
		{
			trigger_error('Error: MAIL FROM not accepted from server! Error Msg:' . $reply);

			return false;
		}

		if (!is_array($this->to))
		{
			fputs($handle, 'RCPT TO: <' . $this->to . '>' . $this->crlf);

			$reply = '';
			while ($line = fgets($handle, 515))
			{
				$reply .= $line;
				if (substr($line, 3, 1) == ' ')
				{
					break;
				}
			}

			if ((substr($reply, 0, 3) != 250) && (substr($reply, 0, 3) != 251))
			{
				trigger_error('Error: RCPT TO not accepted from server! Error Msg:' . $reply);

				return false;
			}
		}
		else
		{
			foreach ($this->to as $recipient)
			{
				fputs($handle, 'RCPT TO: <' . $recipient . '>' . $this->crlf);

				$reply = '';
				while ($line = fgets($handle, 515))
				{
					$reply .= $line;
					if (substr($line, 3, 1) == ' ')
					{
						break;
					}
				}

				if ((substr($reply, 0, 3) != 250) && (substr($reply, 0, 3) != 251))
				{
					trigger_error('Error: RCPT TO not accepted from server! Error Msg:' . $reply);

					return false;
				}
			}
		}

		fputs($handle, 'DATA' . $this->crlf);
		$reply = '';
		while ($line = fgets($handle, 515))
		{
			$reply .= $line;
			if (substr($line, 3, 1) == ' ')
			{
				break;
			}
		}

		if (substr($reply, 0, 3) != 354)
		{
			trigger_error('Error: DATA not accepted from server! Error Msg:' . $reply);

			return false;
		}

		// According to rfc 821 we should not send more than 1000 including the CRLF
		$message = str_replace("\r\n", "\n", $header . $message);
		$message = str_replace("\r", "\n", $message);
		$lines   = explode("\n", $message);
		foreach ($lines as $line)
		{
			$results = str_split($line, 998);
			foreach ($results as $result)
			{
				if (substr(PHP_OS, 0, 3) != 'WIN')
				{
					fputs($handle, $result . $this->crlf);
				}
				else
				{
					fputs($handle, str_replace("\n", "\r\n", $result) . $this->crlf);
				}
			}
		}

		fputs($handle, '.' . $this->crlf);
		$reply = '';
		while ($line = fgets($handle, 515))
		{
			$reply .= $line;
			if (substr($line, 3, 1) == ' ')
			{
				break;
			}
		}

		if (substr($reply, 0, 3) != 250)
		{
			trigger_error('Error: DATA not accepted from server! Error Msg:' . $reply);

			return false;
		}

		fputs($handle, 'QUIT' . $this->crlf);
		$reply = '';
		while ($line = fgets($handle, 515))
		{
			$reply .= $line;
			if (substr($line, 3, 1) == ' ')
			{
				break;
			}
		}

		if (substr($reply, 0, 3) != 221)
		{
			trigger_error('Error: QUIT not accepted from server! Error Msg:' . $reply);

			return false;
		}

		fclose($handle);

		return true;
	}
}
?>