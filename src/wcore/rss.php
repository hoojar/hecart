<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/rss.php
 * 简述: rss分析库
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: rss.php 1150 2017-07-24 02:10:33Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_rss
{
	/**
	 * rss文件的地址
	 *
	 * @var string
	 */
	public $url;

	/**
	 * rss文件的内容
	 *
	 * @var string
	 */
	public $data;

	/**
	 * rss文件的版本号
	 *
	 * @var string
	 */
	public $version;

	/**
	 * rss文件中的频道信息
	 *
	 * @var string
	 */
	public $channel;

	/**
	 * rss数据内容
	 *
	 * @var array
	 */
	public $items = array();

	/**
	 * 输入哪种编码
	 *
	 * @var string utf-8 gb2312 gbk
	 */
	public $charset = 'utf-8';

	/**
	 * xml解析器句柄
	 *
	 * @var object
	 */
	private $xml_parser;

	/**
	 * XML当前解析深度
	 *
	 * @var integer
	 */
	private $depth;

	/**
	 * 当前正在解析的XML元素
	 *
	 * @var string
	 */
	private $tag;

	/**
	 * 当前正在解析的上一个元素
	 *
	 * @var string
	 */
	private $prev_tag;

	/**
	 * 用来标记制定的深度
	 *
	 * @var integer
	 */
	private $marker;

	/**
	 * 实践名称:CHANNEL and ITEM
	 *
	 * @var object
	 */
	private $event;

	/**
	 * item元素索引
	 *
	 * @var integer
	 */
	private $item_index = -1;

	/**
	 * 初始化类
	 *
	 * @param string $rss_url rss url
	 */
	public function __construct($rss_url)
	{
		if ($this->data = file_get_contents($rss_url))
		{
			//编码转换
			if (preg_match("/encoding=\"(gb2312|GBK)\"/", $this->data))
			{
				$this->data = preg_replace("/encoding=\"(gb2312|GBK)\"/is", "encoding=\"utf-8\"", $this->data);
				$this->data = iconv('GB2312', 'UTF-8//IGNORE', $this->data);
			}

			//初始化xml解析器
			$this->xml_parser = xml_parser_create('UTF-8');
			xml_set_object($this->xml_parser, $this);
			xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, 0);
			xml_set_element_handler($this->xml_parser, 'startElement', 'endElement');
			xml_set_character_data_handler($this->xml_parser, 'analyse_data');

			//开始解析数据
			if (!@xml_parse($this->xml_parser, $this->data))
			{
				trigger_error('XML error: ' . xml_error_string(xml_get_error_code($this->xml_parser)) . ' at line ' . xml_get_current_line_number($this->xml_parser), E_USER_NOTICE);
			}
		}
		else
		{
			trigger_error("Cannot init rss xml, {$rss_url} can not visit.", E_USER_NOTICE);
		}
	}

	/**
	 * 析构函数
	 *
	 */
	public function __destruct()
	{
		unset($this->data);
		unset($this->items);
	}

	/**
	 * 开始解析XML元素
	 *
	 * @param object $parser
	 * @param string $name
	 * @param string $attribs
	 * @return boolean
	 */
	private function startElement($parser, $name, $attribs)
	{
		$this->depth++;
		$this->tag = $name;
		switch ($name)
		{
			case 'rss':
				$this->event   = $name;
				$this->version = $attribs['version'];
				break;
			case 'channel':
				$this->event  = $name;
				$this->marker = $this->depth + 1;
				break;
			case 'item':
				$this->item_index++;
				$this->event  = $name;
				$this->marker = $this->depth + 1;
				break;
			default:
				return null;
		}
	}

	/**
	 * 结束某个元素解析时
	 *
	 * @param object $parser
	 * @param string $name
	 */
	private function endElement($parser, $name)
	{
		$this->depth--;

		return;
	}

	/**
	 * 处理数据
	 *
	 * @param object $parser
	 * @param string $data
	 */
	private function analyse_data($parser, $data)
	{
		if (strtolower($this->charset) != 'utf-8')
		{
			$data = iconv('utf-8', 'gb2312', trim($data));
		}

		//当数据为chanel下的数据时执行
		if ($this->event == 'channel' && $this->marker == $this->depth)
		{
			$this->channel[$this->tag] = $data;
		}

		//当数据为item下的数据时执行
		if ($this->event == 'item' && $this->marker == $this->depth)
		{
			if ($this->prev_tag == $this->tag)
			{
				$this->items[$this->item_index][$this->tag] .= $data;
			}
			else
			{
				$this->items[$this->item_index][$this->tag] = $data;
			}
		}
		$this->prev_tag = $this->tag;
	}
}
?>