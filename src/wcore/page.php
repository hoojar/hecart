<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/pages.php
 * 简述: 专门用于分页的类库
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: page.php 1150 2017-07-24 02:10:33Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_page
{
	/**
	 * 初始化每页显示几条记录
	 *
	 * @var integer
	 */
	public $page_size = 20;

	/**
	 * 列表分页栏时以显示多少页为一段
	 * show_sec : show section
	 *
	 * @var integer
	 */
	public $show_sec = 8;

	/**
	 * 是否在分页栏出现第一页与最后一页
	 * show_fl : show first last page
	 *
	 * @var boolean
	 */
	public $show_fl = false;

	/**
	 * 是否在分页栏展示总数
	 *
	 * @var boolean
	 */
	public $show_amount = true;

	/**
	 * 初始化第一页
	 *
	 * @var integer
	 */
	public $now_page = 1;

	/**
	 * 操作数据库的对象
	 *
	 * @var wcore_mysql
	 */
	protected $_db = null;

	/**
	 * 统计有多少条记录
	 *
	 * @var integer
	 */
	public $amount = -1;

	/**
	 * 记录能分几页
	 *
	 * @var integer
	 */
	public $how_cleft = 0;

	/**
	 * 开始记录编号
	 *
	 * @var integer
	 */
	public $snum = 0;

	/**
	 * 终止记录编号
	 *
	 * @var integer
	 */
	public $enum = 0;

	/**
	 * 是否能够分下一页
	 *
	 * @var boolean
	 */
	public $can_next = false;

	/**
	 * 是否能够向上一页
	 *
	 * @var boolean
	 */
	public $can_back = false;

	/**
	 * 分页HTML字符串栏
	 *
	 * @var string
	 */
	public $page_tip = '';

	/**
	 * 设置语言集
	 *
	 * @var array
	 */
	private $lang = array();

	/**
	 * 构造函数
	 *
	 * @param integer $now_page  哪一页
	 * @param integer $page_size 一页点多少条记录
	 * @param object  $db_lnik   数据连接对象
	 * @param array   $lang      语言数组
	 */
	public function __construct($now_page = 1, $page_size = 10, $db_lnik = null, $lang = array())
	{
		if ($page_size <= 0)
		{
			$page_size = 10;
		}

		if ($now_page == '')
		{
			$now_page = 1;
		}

		$this->page_size          = $page_size;
		$this->now_page           = $now_page;
		$this->_db                = ($db_lnik) ? $db_lnik : wcore_object::sdb();
		$this->lang['result']     = '相关结果约';
		$this->lang['records']    = '篇 ';
		$this->lang['first_page'] = '<<';
		$this->lang['prev_page']  = '<';
		$this->lang['next_page']  = '>';
		$this->lang['last_page']  = '>>';
		$this->lang['not_data']   = '抱歉，没有找到相关信息，请看看输入的文字是否有误或去掉可能不必要的字词。';

		if (!empty($lang) && is_array($lang))
		{
			$this->lang = array_merge($this->lang, $lang);
		}
		$this->page_tip = $this->lang['not_data'];
	}

	/**
	 * 析构函数
	 *
	 */
	public function __destruct() { unset($this->page_tip, $this->lang); }

	/**
	 * 获取数据库中有多少条记录
	 *
	 * @param string $sql SQL语句
	 * @return integer 数据记录数
	 */
	private function _amount($sql)
	{
		if (stripos($sql, 'GROUP BY') !== false) //判断是否为group查询
		{
			$this->_db->query($sql);
			$row_nu = $this->_db->num_rows();
		}
		else
		{
			$res    = $this->_db->fetch_row($sql);
			$row_nu = $res['CNUM'];
		}

		$this->_db->free();
		$this->amount = $row_nu;

		return $this->amount;
	}

	/**
	 * 获取能够分几页
	 *
	 * @return integer 页数
	 */
	public function how_cleft()
	{
		$how_nu = floor($this->amount / $this->page_size);
		if (($this->amount % $this->page_size) != 0)
		{
			$how_nu = $how_nu + 1;
		}
		$this->how_cleft = floor($how_nu);

		return floor($how_nu);
	}

	/**
	 * 判断是否以是第一页
	 *
	 * @return boolean 是则返回true反之则为false
	 */
	public function is_start_page() { return ($this->now_page <= 1) ? true : false; }

	/**
	 * 判断是否以是最后一页
	 *
	 * @return boolean 是则返回true反之则为false
	 */
	public function is_end_page() { return ($this->now_page >= $this->how_cleft) ? true : false; }

	/**
	 * 是否能显示最前(上)一页
	 *
	 * @return mixed 能向上分页才返回上一页数,不能返回false
	 */
	public function can_back()
	{
		if ($this->now_page > 1)
		{
			$this->can_back = $this->now_page - 1;

			return $this->now_page - 1;
		}
		$this->can_back = false;

		return false;
	}

	/**
	 * 是否能显示最后页(下)一页
	 *
	 * @return mixed 能向上分页才返回下一页数,不能返回false
	 */
	public function can_next()
	{
		if ($this->now_page < $this->how_cleft)
		{
			$this->can_back = $this->now_page + 1;

			return $this->can_back;
		}
		$this->can_back = false;

		return false;
	}

	/**
	 * 获取记录存入到数组中并返回
	 *
	 * @param string $sql  SQL语句
	 * @param bool   $rsql 是否返回分布SQL语句
	 * @return bool|mixed|string 有数据则返回数据数组,无则返回false
	 */
	public function &get_value($sql, $rsql = false)
	{
		$data = array();
		if (empty($sql))
		{
			return $data;
		}

		$sql            = str_replace(';', '', $sql);
		$this->page_tip = $this->lang['not_data']; //未找到内容时显示的提示说明

		/**
		 * 截取FROM后的条件SQL语句
		 */
		$osql      = $sql;
		$order_pos = stripos($osql, ' ORDER');
		if ($order_pos) //统计有多少记录时发现有排序SQL则除去
		{
			$osql = substr($osql, 0, $order_pos);
		}

		if ($this->amount == -1)
		{
			preg_match('/(\t| |\n|\r)?FROM (.+?)$/is', $osql, $from);
			$this->_amount('SELECT COUNT(*) AS CNUM' . $from[0]);
		}

		/**
		 * 计算能分多少页
		 */
		$this->how_cleft();
		if ($this->now_page <= 0)
		{
			$this->now_page = 1;
		}
		$now_page = $this->now_page - 1;
		$snum     = $now_page * $this->page_size;

		/**
		 * 判断指定分页数是否大于实际分页数
		 */
		if ($this->now_page > $this->how_cleft)
		{
			return $data;
		}
		$page_size = $this->page_size;

		/**
		 * 判断数据库操作类型
		 */
		if (get_class($this->_db) == 'wcore_mssql')
		{
			$sql = preg_replace('/SELECT /i', "SELECT TOP {$this->enum} ", $sql);
		}
		else if (get_class($this->_db) == 'wcore_oci')
		{
			$enum = ($snum + $page_size);
			$sql  = "SELECT * FROM (SELECT PTPAGE.*, ROWNUM AS PTRNUM FROM ($sql) PTPAGE WHERE ROWNUM <= {$enum}) WHERE PTRNUM > {$snum}";
		}
		else
		{
			$sql = "{$sql} LIMIT {$snum}, {$page_size};";
		}

		$this->snum = ($snum <= 0) ? 1 : $snum + 1; //开始记录编号
		$this->enum = $snum + $page_size - 1; //终止记录编号

		/**
		 * 判断最终显示的记录是否真的大于总记录
		 */
		if ($this->enum > $this->amount)
		{
			$this->enum = $this->amount;
		}

		/**
		 * 判断是否仅返回分页SQL，若为假则执行SQL并返回所有数据
		 */
		if ($rsql)
		{
			return $sql;
		}
		$result = $this->_db->fetch_all($sql);

		return $result; //返回所有数据
	}

	/**
	 * 获取分页的HTML
	 *
	 * @param string $url 页面连接地址
	 * @return string HTML分页字符串
	 */
	public function &page_tip($url = '')
	{
		$page_tip = '';
		if ($this->show_amount)
		{
			$page_tip = $this->lang['result'] . number_format($this->amount) . $this->lang['records'];
		}
		$count_page = $this->how_cleft;

		if ($count_page > 1)
		{
			$go_back = $this->can_back();
			if ($go_back)
			{
				if ($this->show_fl) //是否显示最前与最后一页
				{
					$page_tip .= $this->_build_page(1, $this->lang['first_page'], $url);
				}
				$page_tip .= $this->_build_page($go_back, $this->lang['prev_page'], $url);
			}

			/**
			 * 快速跳转到第几页
			 */
			for ($i = ($this->now_page > $this->show_sec) ? ($this->now_page - $this->show_sec) : 1; ($i <= ($this->now_page + $this->show_sec)) && ($i <= $this->how_cleft); ++$i)
			{
				if ($this->now_page == $i)
				{
					$page_tip .= "<span>{$i}</span>";
				}
				else
				{
					$page_tip .= $this->_build_page($i, $i, $url);
				}
			}

			$go_next = $this->can_next();
			if ($go_next)
			{
				$page_tip .= $this->_build_page($go_next, $this->lang['next_page'], $url);
				if ($this->show_fl) //是否显示最前与最后一页
				{
					$page_tip .= $this->_build_page($count_page, $this->lang['last_page'], $url);
				}
			}
		}
		$this->page_tip = $page_tip;

		return $page_tip;
	}

	/**
	 * 创建页提示
	 *
	 * @param int    $page 页数
	 * @param string $tip  提示
	 * @param string $url  访问的URL地址
	 * @return string 建立好的HTML
	 */
	private function _build_page($page, $tip, $url = '')
	{
		if (!empty($url))
		{
			return "<a href=\"{$url}{$page}\" target=\"_self\">{$tip}</a>";
		}

		return "<a href=\"javascript:goPage($page)\" target=\"_self\">{$tip}</a>";
	}

	/**
	 * 分页的大小
	 *
	 * @return integer 一页有多少条记录
	 */
	public function page_size() { return $this->page_size; }

	/**
	 * 能分几页
	 *
	 * @return integer 页数
	 */
	public function now_page() { return $this->now_page; }

	/**
	 * 从哪条记录开始
	 *
	 * @return integer 开始记录编号
	 */
	public function snum() { return $this->snum; }

	/**
	 * 终止记录
	 *
	 * @return integer 终止记录编号
	 */
	public function enum() { return $this->enum; }
}
?>