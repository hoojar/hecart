<?php
/**
 * 网站页面类库
 */
class Document
{
	private $title;
	private $description;
	private $keywords;
	private $links = array();
	private $styles = array();
	private $scripts = array();

	public function title($title = '')
	{
		if (!empty($title))
		{
			$this->title = $title;
		}

		return $this->title;
	}

	public function keywords($keywords = '')
	{
		if (!empty($keywords))
		{
			$this->keywords = $keywords;
		}

		return $this->keywords;
	}

	public function description($description = '')
	{
		if (!empty($description))
		{
			$this->description = $description;
		}

		return $this->description;
	}

	public function links($href = '', $rel = '')
	{
		if (!empty($href))
		{
			$this->links[md5($href)] = array(
				'href' => $href,
				'rel'  => $rel
			);
		}

		return $this->links;
	}

	public function styles($href = '', $rel = 'stylesheet', $media = 'screen')
	{
		if (!empty($href))
		{
			$this->styles[md5($href)] = array(
				'href'  => $href,
				'rel'   => $rel,
				'media' => $media
			);
		}

		return $this->styles;
	}

	public function scripts($script = '')
	{
		if (!empty($script))
		{
			$this->scripts[md5($script)] = $script;
		}

		return $this->scripts;
	}
}
?>