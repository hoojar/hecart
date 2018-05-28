<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/grid.php
 * 简述: 生成表格列表库
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: grid.php 1150 2017-07-24 02:10:33Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_grid
{
	/**
	 * 表格标题说明
	 *
	 * @var string
	 */
	public $caption = "";

	/**
	 * 表格展示列表标题为一维数组
	 *
	 * @var array
	 */
	public $title = array();

	/**
	 * 数据内容为二维数组
	 *
	 * @var array
	 */
	public $data = array();

	/**
	 * 字符超过多长就裁切
	 *
	 * @var integer
	 */
	public $cutting = 0;

	/**
	 * 是否展示完全提示内容
	 *
	 * @var boolean
	 */
	public $tool_tip = true;

	/**
	 * 分页栏内容
	 *
	 * @var unknown_type
	 */
	public $ptip = "";

	/**
	 * 生成表格后的HTML内容
	 *
	 * @var string
	 */
	public $html = "";

	/**
	 * 初始化类库
	 *
	 * @param string $caption 表格标题
	 * @param array  $title   表格展示列表标题为一维数组
	 */
	public function __construct($caption = "", $title = array())
	{
		$this->caption = $caption;
		if (!empty($title))
		{
			self::set_title($title);
		}
	}

	/**
	 * 设置展示列表标题
	 *
	 * @param array $t 表格展示列表标题为一维数组
	 * @return boolean
	 */
	public function set_title($title)
	{
		if (is_array($title))
		{
			$this->title = $title;

			return true;
		}

		return false;
	}

	/**
	 * 设置展示列表标题
	 *
	 * @param array $data 要展示的二维数组数据
	 * @return boolean
	 */
	public function set_data($data)
	{
		if (is_array($data))
		{
			$this->data = $data;

			return true;
		}

		return false;
	}

	public function parse($data = array())
	{
		if (!empty($data))
		{
			$this->data = $data;
		}
		if (empty($this->title))
		{
			return false;
		}
		if (empty($this->data))
		{
			return false;
		}
		$this->html = '<table class="grid_table" cellspacing="1" border="0" align="center">';
		$flen       = count($this->title);
		$this->html .= "<tr class=\"grid_caption\"><td colspan=\"{$flen}\">{$this->caption}</td></tr><tr class=\"grid_menu\">";
		for ($i = 0; $i < $flen; ++$i) //Header
		{
			$this->html .= "<td>{$this->title[$i]}</td>";
		}
		$this->html .= "</tr>\n";
		$clen = count($this->data);
		for ($i = 0; $i < $clen; ++$i) //Data
		{
			$css = ($i % 2 === 0) ? "grid_bgc1" : "grid_bgc2";
			$this->html .= "<tr class=\"{$css}\"><td>" . implode("</td><td>", $this->data[$i]) . "</td></tr>\r\n";
		}
		if ($this->ptip)
		{
			$this->html .= "<tr class=\"btn_bar\"><td colspan=\"{$flen}\">{$this->ptip}</td></tr>";
		}
		$this->html .= "</table>";

		return $this->html;
	}
}
?>