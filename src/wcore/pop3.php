<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/pop3.php
 * 简述: 收邮件核心程序类 - 参考了：phpmailer
 * 作者: woods·zhang     ->     hoojar@163.com
 * 版本: $Id: pop3.php 1150 2017-07-24 02:10:33Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_pop3
{
	/**
	 * Default POP3 port
	 *
	 * @var int
	 */
	public $pop3_port = 110;

	/**
	 * Default Timeout
	 *
	 * @var int
	 */
	public $pop3_timeout = 30;

	/**
	 * POP3 Carriage Return + Line Feed
	 *
	 * @var string
	 */
	public $crlf = "\r\n";

	/**
	 * Displaying Debug warnings?(0 = now, 1+ = yes)
	 *
	 * @var int
	 */
	public $do_debug = 2;

	/**
	 * POP3 Mail Server
	 *
	 * @var string
	 */
	public $host;

	/**
	 * POP3 Port
	 *
	 * @var int
	 */
	public $port;

	/**
	 * POP3 Timeout Value
	 *
	 * @var int
	 */
	public $tval;

	/**
	 * POP3 Username
	 *
	 * @var string
	 */
	public $username;

	/**
	 * POP3 Password
	 *
	 * @var string
	 */
	public $password;

	/**
	 * PROPERTIES, PRIVATE AND PROTECTED
	 *
	 * @var unknown_type
	 */
	private $pop_conn;

	private $connected;

	private $error; //Error log array

	/**
	 * Constructor, sets the initial values
	 */
	public function __construct()
	{
		$this->pop_conn  = 0;
		$this->connected = false;
		$this->error     = null;
	}

	/**
	 * Combination of public events - connect, login, disconnect
	 *
	 * @param      $host
	 * @param bool $port
	 * @param bool $tval
	 * @param      $username
	 * @param      $password
	 * @param int  $debug_level
	 * @return bool
	 */
	public function authorise($host, $port = false, $tval = false, $username, $password, $debug_level = 0)
	{
		$this->host = $host;

		//If no port value is passed, retrieve it
		$this->port = ($port == false) ? $this->pop3_port : $port;

		//If no port value is passed, retrieve it
		$this->tval     = ($tval == false) ? $this->pop3_timeout : $tval;
		$this->do_debug = $debug_level;
		$this->username = $username;
		$this->password = $password;

		//Refresh the error log
		$this->error = null;

		//connect
		$result = $this->connect($this->host, $this->port, $this->tval);
		if ($result)
		{
			$login_result = $this->login($this->username, $this->password);
			if ($login_result)
			{
				$this->disconnect();

				return true;
			}
		}

		//We need to disconnect regardless if the login succeeded
		$this->disconnect();

		return false;
	}

	/**
	 * connect to the POP3 server
	 *
	 * @access public
	 * @param string  $host
	 * @param integer $port
	 * @param integer $tval
	 * @return boolean
	 */
	public function connect($host, $port = false, $tval = 30)
	{
		//Are we already connected?
		if ($this->connected)
		{
			return true;
		}
		/*
						 On Windows this will raise a PHP Warning error if the hostname doesn't exist.
						 Rather than supress it with @fsockopen, let's capture it cleanly instead
						 */
		set_error_handler(array(
							   &$this,
							   'catchWarning'
						  ));
		//connect to the POP3 server
		$this->pop_conn = fsockopen($host, //POP3 Host
			$port, //Port #
			$errno, //Error Number
			$errstr, //Error Message
			$tval); //Timeout(seconds)
		//Restore the error handler
		restore_error_handler();
		//Does the Error Log now contain anything?
		if ($this->error && $this->do_debug >= 1)
		{
			$this->display_errors();
		}
		//Did we connect?
		if ($this->pop_conn == false)
		{
			//It would appear not...
			$this->error = array(
				'error'  => "Failed to connect to server $host on port $port",
				'errno'  => $errno,
				'errstr' => $errstr
			);
			if ($this->do_debug >= 1)
			{
				$this->display_errors();
			}

			return false;
		}
		//Increase the stream time-out
		//Check for PHP 4.3.0 or later
		if (version_compare(phpversion(), '5.0.0', 'ge'))
		{
			stream_set_timeout($this->pop_conn, $tval, 0);
		}
		else
		{
			//Does not work on Windows
			if (substr(PHP_OS, 0, 3) !== 'WIN')
			{
				socket_set_timeout($this->pop_conn, $tval, 0);
			}
		}
		//Get the POP3 server response
		$pop3_response = $this->get_response();
		//Check for the +OK
		if ($this->check_response($pop3_response))
		{
			//The connection is established and the POP3 server is talking
			$this->connected = true;

			return true;
		}
	}

	/**
	 * login to the POP3 server(does not support APOP yet)
	 *
	 * @access public
	 * @param string $username
	 * @param string $password
	 * @return boolean
	 */
	public function login($username = '', $password = '')
	{
		if ($this->connected == false)
		{
			$this->error = 'Not connected to POP3 server';
			if ($this->do_debug >= 1)
			{
				$this->display_errors();
			}
		}
		if (empty($username))
		{
			$username = $this->username;
		}
		if (empty($password))
		{
			$password = $this->password;
		}
		$pop_username = "USER {$username}{$this->crlf}";
		$pop_password = "PASS {$password}{$this->crlf}";
		//Send the Username
		$this->send_cmd($pop_username);
		$pop3_response = $this->get_response();
		if ($this->check_response($pop3_response))
		{
			//Send the Password
			$this->send_cmd($pop_password);
			$pop3_response = $this->get_response();

			return ($this->check_response($pop3_response)) ? true : false;
		}

		return false;
	}

	/**
	 * disconnect from the POP3 server
	 *
	 * @access public
	 */
	public function disconnect()
	{
		$this->send_cmd('QUIT');
		fclose($this->pop_conn);
	}

	/**
	 * Get the socket response back.
	 * $size is the maximum number of bytes to retrieve
	 *
	 * @access private
	 * @param integer $size
	 * @return string
	 */
	private function get_response($size = 128)
	{
		$pop3_response = fgets($this->pop_conn, $size);

		return $pop3_response;
	}

	/**
	 * Send a string down the open socket connection to the POP3 server
	 *
	 * @access private
	 * @param string $string
	 * @return integer
	 */
	private function send_cmd($string)
	{
		$bytes_sent = fwrite($this->pop_conn, $string, strlen($string));

		return $bytes_sent;
	}

	/**
	 * Checks the POP3 server response for +OK or -ERR
	 *
	 * @access private
	 * @param string $string
	 * @return boolean
	 */
	private function check_response($string)
	{
		if (substr($string, 0, 3) !== '+OK')
		{
			$this->error = array(
				'error'  => "Server reported an error: $string",
				'errno'  => 0,
				'errstr' => ''
			);
			if ($this->do_debug >= 1)
			{
				$this->display_errors();
			}

			return false;
		}

		return true;
	}

	/**
	 * If debug is enabled, display the error message array
	 *
	 * @access private
	 */
	private function display_errors()
	{
		echo '<pre>';
		foreach ($this->error as $single_error)
		{
			print_r($single_error);
		}
		echo '</pre>';
	}

	/**
	 * Takes over from PHP for the socket warning handler
	 *
	 * @access private
	 * @param integer $errno
	 * @param string  $errstr
	 * @param string  $errfile
	 * @param integer $errline
	 */
	private function catchWarning($errno, $errstr, $errfile, $errline)
	{
		$this->error[] = array(
			'error'  => "connecting to the POP3 server raised a PHP warning: ",
			'errno'  => $errno,
			'errstr' => $errstr
		);
	}
}
?>