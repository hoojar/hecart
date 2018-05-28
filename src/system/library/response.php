<?php
/**
 * 网站响应类库
 */
class Response
{
	private $level = 0;

	private $headers = array();

	public function addHeader($header)
	{
		$this->headers[] = $header;
	}

	public function setCompression($level)
	{
		$this->level = $level;
	}

	public function compress($data, $level = 0)
	{
		if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false))
		{
			$encoding = 'gzip';
		}

		if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false))
		{
			$encoding = 'x-gzip';
		}

		if (!isset($encoding))
		{
			return $data;
		}

		if (!extension_loaded('zlib') || ini_get('zlib.output_compression'))
		{
			return $data;
		}

		if (headers_sent())
		{
			return $data;
		}

		if (connection_status())
		{
			return $data;
		}

		$this->addHeader('Content-Encoding: ' . $encoding);

		return gzencode($data, (int)$level);
	}

	public function output($html)
	{
		if ($this->level && $html)
		{
			$html = $this->compress($html, $this->level);
		}

		if (!headers_sent())
		{
			foreach ($this->headers as $header)
			{
				header($header, true);
			}
		}

		exit($html);
	}
}
?>