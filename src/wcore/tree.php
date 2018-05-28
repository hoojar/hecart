<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/tree.php
 * 简述: 无限极分类类库
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: tree.php 1150 2017-07-24 02:10:33Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_tree
{
	private $_db; //操作数据库的句标
	private $opt; //操作的数据表
	private $id_name; //唯一编号名
	private $alias_name; //名称别名
	private $fid_name; //父亲字段名
	private $rank_name; //等级字段名
	private $son_num_name; //儿子字段名
	private $lang = array(); //设置语言集
	private $rv = ""; //递归时返回的值
	private $show_rv = array(); //显示等级层递归时返回的值
	/**
	 * 初始化所需的操作条件
	 *
	 * @param string $opt          操作的数据表
	 * @param string $id_name      唯一编号名
	 * @param string $alias_name   名称别名
	 * @param string $fid_name     父亲字段名
	 * @param string $rank_name    等级字段名
	 * @param string $son_num_name 儿子字段名
	 * @param array  $lang         语言数组
	 */
	public function __construct($opt, $id_name, $alias_name, $fid_name, $rank_name, $son_num_name, $lang)
	{
		$this->lang = $lang;
		if ($opt == "")
		{
			self::halt($lang["sans_db_table"]);
		}
		if ($id_name == "")
		{
			self::halt($lang["sans_id_name"]);
		}
		if ($alias_name == "")
		{
			self::halt($lang["sans_alias_name"]);
		}
		if ($fid_name == "")
		{
			self::halt($lang["sans_fid_name"]);
		}
		if ($rank_name == "")
		{
			self::halt($lang["sans_rank_name"]);
		}
		if ($son_num_name == "")
		{
			self::halt($lang["sans_son_num_name"]);
		}
		$this->_db          = wcore_object::db();
		$this->opt          = $opt;
		$this->id_name      = $id_name;
		$this->alias_name   = $alias_name;
		$this->fid_name     = $fid_name;
		$this->rank_name    = $rank_name;
		$this->son_num_name = $son_num_name;
	}

	/**
	 * 析构函数
	 *
	 */
	function __destruct()
	{
		unset($this->show_rv);
		unset($this->rv);
	}

	/**
	 * 类与到严重错误时停执行
	 *
	 * @param string $msg 出错信息
	 */
	private function halt($msg)
	{
		print("<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><title>{$this->lang["title"]}</title></head><body><p align=\"center\">{$this->lang["halt_tip"]} {$msg}</p></body></html>");
		flush();
		exit(0);
	}

	/**
	 * 类与到错误显示出来
	 *
	 * @param string $msg
	 */
	private function error($msg)
	{
		print("<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><title>{$this->lang["title"]}</title></head><body><p align=\"center\">{$this->lang["halt_tip"]} {$msg}</p></body></html>");
		flush();
	}

	/**
	 * 增加一个分类
	 *
	 * @param string  $name        分类的名称
	 * @param integer $fid         分类的父亲是谁
	 * @param boolean $check_exist 查检是否先前的$name存在
	 * @param string  $data        要增加的其他数据字段内容
	 * @return boolean 成功返回增加成功的自动编号,反之则为false
	 */
	public function add($name, $fid, $check_exist = false, $data = "")
	{
		//判断要增加的名称有没有
		if ($name == "")
		{
			self::halt($this->lang["sans_add_name"]);
		}
		//父亲编号不能小于0，0为根
		if ($fid < 0)
		{
			self::halt($this->lang["fid_err"]);

			return false;
		}

		//判断新增的名称是否以在数据库中
		if ($check_exist)
		{
			$sql = "SELECT {$this->id_name} FROM {$this->opt} WHERE {$this->alias_name} = '{$name}' ;";
			$this->_db->query($sql);
			if ($this->_db->num_rows() > 0) //找到了这个行业，说明先前登记过
			{
				self::error($name . $this->lang["name_exist"]);

				return false;
			}
		}

		$sql = "INSERT INTO {$this->opt} SET {$this->alias_name} = '{$name}', {$this->fid_name} = $fid, {$this->son_num_name} = 0{$data} ;";
		$this->_db->query($sql);

		/**
		 * 更改行业的等级
		 */
		$insert_id = $this->_db->insert_id(); //获取自动编号
		if ($fid == 0)
		{
			$set_rank = "|{$insert_id}.";
		}
		else
		{
			$fsn      = self::get_father_rank_son_num($fid);
			$fr       = $fsn[$this->rank_name]; //获取父亲的等级
			$fnum     = $fsn[$this->son_num_name] + 1; //获取父亲的儿子数目
			$set_rank = "{$fr}|{$insert_id}.{$fnum}";
			self::update_son_num($fid); //更新父亲的儿子数目,确定下次的数字
		}
		$sql = "UPDATE {$this->opt} SET {$this->rank_name} = '{$set_rank}' WHERE {$this->id_name} = {$insert_id};";
		$this->_db->query($sql);

		return ($this->_db->affected_rows() > 0) ? $insert_id : false;
	}

	/**
	 * 删除分类
	 *
	 * @param string $ob 要是的唯一编号或分类名称
	 * @return boolean 删除成功返回true,反之则为false
	 */
	public function del($ob)
	{
		if ($ob == "")
		{
			self::halt($this->lang["del_nothing"]);
		}
		if (!is_numeric($ob)) //决定是以编号为条件删除还是以名称
		{
			$sql = "SELECT {$this->id_name} FROM {$this->opt} WHERE {$this->alias_name} = '{$ob}' ;";
			$this->_db->query($sql);
			if ($this->_db->num_rows() > 0) //找到了这个行业，说明先前登记过
			{
				$result = $this->_db->fetch_row();
				$ob     = $result[0];
			}
			else
			{
				return false;
			}
		}
		$sql = "DELETE FROM {$this->opt} WHERE {$this->rank_name} LIKE '%{$ob}.%' ;";
		$this->_db->query($sql);

		return ($this->_db->affected_rows() > 0) ? true : false;
	}

	/**
	 * 移动分类
	 *
	 * @param string  $cid  要是的唯一编号
	 * @param integer $fid  为要移动到哪个父亲下面
	 * @param string  $data 为要增加的SQL附加数据
	 * @return boolean 移动成功返回true,反之则为false
	 */
	public function move($cid, $fid, $data = "")
	{
		if ($cid == "") //判断要移动的编号有没有
		{
			self::halt($this->lang["move_scans_cid"]);
		}
		if ($fid < 0) //父亲编号不能小于0，0为根
		{
			self::halt($this->lang["fid_err"]);
		}
		$fsn     = self::get_father_rank_son_num($cid);
		$cid_fid = $fsn[$this->fid_name]; //获取cid的父亲编号
		/*____________________________判断父亲是否会移到儿子类下面_______________begin________________________*/
		$sql = "SELECT {$this->fid_name} FROM {$this->opt} WHERE {$this->id_name} = {$fid} AND {$this->rank_name} LIKE '%{$cid}.%' ;";
		$this->_db->query($sql);
		if ($this->_db->num_rows() > 0)
		{
			self::error($this->lang["son_can_not_over_father"]);

			return false;
		}
		/*____________________________判断父亲是否会移到儿子类下面_________________end________________________*/
		if ($fid == $cid_fid || $cid == $fid) //若未变动父亲编号则移动不成功
		{
			return false;
		}
		else if ($fid == 0)
		{
			$sql = "UPDATE {$this->opt} SET {$this->rank_name} = CONCAT('|', {$this->id_name}, '.'), {$this->son_num_name} = 0 ;"; //更新所有等级
			$this->_db->query($sql);
		}
		$sql = "UPDATE {$this->opt} SET {$this->fid_name} = {$fid} {$data} WHERE {$this->id_name} = $cid;";
		$this->_db->query($sql);
		if ($this->_db->affected_rows() > 0)
		{
			self::update_rank($fid);

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * 获取父亲编号的等级与儿子数目
	 *
	 * @param string $cid 要是的唯一编号
	 * @return array 内容
	 */
	private function get_father_rank_son_num($cid)
	{
		$result = array(
			"{$this->fid_name}"     => 0,
			"{$this->rank_name}"    => "",
			"{$this->son_num_name}" => 0
		);
		$sql    = "SELECT {$this->fid_name}, {$this->rank_name}, {$this->son_num_name} FROM {$this->opt} WHERE {$this->id_name} = {$cid} ;";
		$this->_db->query($sql);
		if ($this->_db->num_rows() > 0)
		{
			$result = $this->_db->fetch_row();
		}
		$this->_db->free();

		return $result;
	}

	/**
	 * 更新父亲的儿子数目,确定下次的数字
	 *
	 * @param string $cid    要是的唯一编号
	 * @param string $symbol 决定是增加还是减
	 */
	private function update_son_num($cid, $symbol = "+")
	{
		$sql = "UPDATE {$this->opt} SET {$this->son_num_name} = {$this->son_num_name} {$symbol} 1 WHERE {$this->id_name} = $cid;";
		$this->_db->query($sql);
	}

	/**
	 * 递归更新rank
	 *
	 * @param integer $fid 要指定的父亲编号为多少的地方开始更新
	 */
	public function update_rank($fid)
	{
		$sql = "SELECT {$this->id_name} FROM {$this->opt} WHERE {$this->fid_name} = $fid ORDER BY {$this->id_name} ASC;"; //获取属于父亲的儿子
		$this->_db->query($sql);
		if ($this->_db->num_rows() > 0)
		{
			$result = $this->_db->fetch_all();
			for ($i = 0; $i < count($result); ++$i)
			{
				$son_cid = $result[$i]["{$this->id_name}"]; //获取行业代号
				$fsn     = self::get_father_rank_son_num($fid); //获取
				$fr      = $fsn[$this->rank_name]; //获取父亲的等级
				$fnum    = $fsn[$this->son_num_name] + 1; //获取父亲的儿子数目
				if (!$fr) //若父亲为0时，所获取的等级也就为空，就得把儿子的等级设置为他的编号
				{
					$rank = "|{$son_cid}.";
				}
				else
				{
					$rank = "{$fr}|{$son_cid}.{$fnum}";
				}
				$sql = "UPDATE {$this->opt} SET {$this->rank_name} = '{$rank}' WHERE {$this->id_name} = $son_cid;";
				$this->_db->query($sql);
				self::update_son_num($fid); //更新父亲的儿子数目,确定下次的数字
				self::update_rank($son_cid); //递归更新
			}
		}
	}

	/**
	 * 递归列出所有分类
	 *
	 * @param integer $pid       要是的父亲编号开始地方
	 * @param integer $fid       是父亲ID
	 * @param string  $show_type 以什么型式显示
	 * @param string  $tn        为以树显示时的名称
	 * @param boolean $oa        是否全展开
	 */
	private function list_sort1($pid, $fid, $show_type, $tn, $oa = "false")
	{
		$sql = "SELECT {$this->id_name}, {$this->alias_name}, {$this->rank_name}, {$this->son_num_name} FROM {$this->opt} WHERE {$this->fid_name} = {$pid} ORDER BY {$this->id_name} ASC;";
		$this->_db->query($sql);
		if ($this->_db->num_rows() > 0)
		{
			$result = $this->_db->fetch_all();
			for ($i = 0; $i < count($result); ++$i)
			{
				$get_cid = $result[$i]["{$this->id_name}"]; //获取代号
				$sname   = $result[$i]["{$this->alias_name}"]; //获取名称
				if ($show_type == "select")
				{
					$num      = substr_count($result[$i]["{$this->rank_name}"], "|");
					$space    = str_repeat("&nbsp;&nbsp;", $num) . "|-"; //形象显示空多少空格
					$selected = ($fid == $get_cid) ? " selected" : "";
					$this->rv .= "<option value=\"{$get_cid}\"{$selected}>{$space}{$sname}</option>\n";
				}
				else
				{
					$root = ($pid == 0) ? "root" : "node{$pid}";
					if ($fid == $get_cid)
					{
						$checked = " checked";
						$sname   = "<font color='red'>{$sname}</font>";
					}
					else
					{
						$checked = "";
					}
					if (false == strstr($tn, '.')) //判断是以超链接展示还是以单选框
					{
						$this->rv .= "	MyObj = \"<input type='radio' value='{$get_cid}' name='{$tn}'{$checked} class='rtree'>{$sname}\";\n";
					}
					else
					{
						$this->rv .= "	MyObj = { label: \"{$sname}\", href: \"{$tn}{$get_cid}\" } ;\n";
					}
					$this->rv .= "	var node{$get_cid} = new YAHOO.widget.TextNode(MyObj, {$root}, {$oa});\n";
				}
				if ($result[$i]["{$this->son_num_name}"] != 0)
				{
					self::list_sort1($get_cid, $fid, $show_type, $tn, $oa);
				}
			}
		}
	}

	/**
	 * 返回递归列出所有分类 -  以select方式显示数据
	 *
	 * @param integer $pid       要是的父亲编号开始地方
	 * @param integer $fid       是父亲ID
	 * @param string  $show_type 以什么型式显示
	 * @param string  $tn        为以树显示时的名称
	 * @param boolean $oa        是否全展开
	 */
	public function list_sort($pid, $fid = 0, $show_type = "select", $tn = "TreeId", $oa = "false")
	{
		$this->rv = "";
		self::list_sort1($pid, $fid, $show_type, $tn, $oa);

		return $this->rv;
	}

	/**
	 * 获取分类等级，按根到子排列,此列是反排列的
	 *
	 * @param integer $cid 为要显示的等级编号
	 */
	private function get_sort1($cid)
	{
		$sql = "SELECT {$this->id_name}, {$this->alias_name}, {$this->fid_name} FROM {$this->opt} WHERE {$this->id_name} = {$cid} ;";
		$this->_db->query($sql);
		if ($this->_db->num_rows() > 0)
		{
			$result          = $this->_db->fetch_row();
			$get_cid         = $result["{$this->id_name}"]; //获取代号
			$sname           = $result["{$this->alias_name}"]; //获取名称
			$fid             = $result["{$this->fid_name}"]; //获取父亲编号
			$this->show_rv[] = "{$get_cid}|{$sname}";
			if ($fid != 0)
			{
				self::get_sort1($fid);
			}
		}
	}

	/**
	 * 获取分类等级，按根到子排列
	 *
	 * @param integer $cid 为要显示的等级编号
	 * @param string  $url
	 * @param string  $cleft
	 * @return string 一个等级字符串
	 */
	public function get_sort($cid, $url = "", $cleft = " -> ")
	{
		$this->show_rv = array();
		self::get_sort1($cid);
		$rv  = array_reverse($this->show_rv);
		$str = "";
		for ($i = 0; $i < count($rv); ++$i)
		{
			$id_name = explode("|", $rv[$i]);
			if ($url)
			{
				$str .= "<a href=\"{$url}{$id_name[0]}\">{$id_name[1]}</a>{$cleft}";
			}
			else
			{
				$str .= "{$id_name[1]}{$cleft}";
			}
		}

		return substr($str, 0, strlen($str) - strlen($cleft));
	}
}
?>