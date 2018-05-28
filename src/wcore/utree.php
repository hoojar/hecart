<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/utree.php
 * 简述: 无限级分类
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: utree.php 1150 2017-07-24 02:10:33Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_utree
{
	public $is_order = true; //是否更新排序
	private $_db; //数据库操作对象
	private $_opt; //要操作的树表
	private $id, $pid, $left, $right, $level, $order; //树形表要操作的字段名

	/**
	 * 用于存储树状结构的信息
	 * $arr_tree[$this->pid] = $this->id
	 *
	 * @var array
	 */
	private $arr_tree;

	/**
	 * 构造函数
	 *
	 * @param string $_opt  要操作的树形表
	 * @param string $id    表的自动编号字段
	 * @param string $pid   父编号字段名
	 * @param string $left  左编号字段名
	 * @param string $right 右编号字段名
	 * @param string $level 层次字段名
	 * @param string $order 序号字段名
	 */
	public function __construct($_opt, $id = "id", $pid = "f_id", $left = "f_left", $right = "f_right", $level = "f_level", $order = "f_order")
	{
		if (empty($_opt))
		{
			self::halt("未指定树形表名", __LINE__);
		}

		$this->_opt  = $_opt;
		$this->_db   = wcore_object::db();
		$this->id    = $id; //表的自动编号字段
		$this->pid   = $pid; //父编号字段名
		$this->left  = $left; //左编号字段名
		$this->right = $right; //右编号字段名
		$this->level = $level; //层次字段名
		$this->order = $order; //序号字段名
	}

	function __destruct()
	{
		unset($this->arr_tree);
	}

	/**
	 * @param int   $p_node_id 指定要插在哪个parentNode下面, default 0;如果$p_node_id = 0,说明是一棵新树;
	 * @param array $array
	 * @return bool
	 */
	function insert($p_node_id = 0, $array = array())
	{
		//如果$p_node_id = 0,说明是一棵新树
		if ($p_node_id == 0)
		{
			$rdata = array();
			if (sizeof($array) > 0)
			{
				foreach ($array as $fieldname => $value)
				{
					$rdata[$fieldname] = $value;
				}
			}

			//建一个根节点
			$rdata[$this->pid]   = 0;
			$rdata[$this->left]  = 1;
			$rdata[$this->right] = 2;
			$rdata[$this->level] = 1;
			$rdata[$this->order] = 1;

			return $this->_db->insert($this->_opt, $rdata);
		}
		else //normal
		{
			$idata = array();
			if (sizeof($array) > 0)
			{
				foreach ($array as $fieldname => $value)
				{
					$idata[$fieldname] = $value;
				}
			}
			$res = $this->_db->fetch_row("SELECT {$this->right}, {$this->level} FROM {$this->_opt} WHERE {$this->id} = {$p_node_id}");
			if (!$res)
			{
				return false;
			}

			$p_right = $res[$this->right];
			$p_level = $res[$this->level];

			//取所有子节点的最大seqno值
			$res    = $this->_db->fetch_row("SELECT MAX({$this->order}) AS data FROM {$this->_opt} WHERE {$this->pid} = {$p_node_id}");
			$subcnt = (!$res) ? 0 : $res["data"];

			//UPDATE old nodes property, right nodes
			$this->_db->dbexec("UPDATE {$this->_opt} SET {$this->right} = {$this->right} + 2 WHERE {$this->right} >= {$p_right}");

			//UPDATE old nodes property, left nodes
			$this->_db->dbexec("UPDATE {$this->_opt} SET {$this->left} = {$this->left} + 2 WHERE {$this->left} > {$p_right}");

			$idata[$this->pid]   = $p_node_id;
			$idata[$this->left]  = $p_right;
			$idata[$this->right] = $p_right + 1;
			$idata[$this->level] = $p_level + 1;
			$idata[$this->order] = $subcnt + 1;

			return $this->_db->insert($this->_opt, $idata);
		}
	}

	/**
	 * delete all subnodes FROM a specified node
	 *
	 * @param int $node_id 指定要删的顶节点
	 * @return bool true 操作成功; false 操作失败
	 */
	function delete($node_id)
	{
		if (empty($node_id))
		{
			self::halt("未指定要删除的节点", __LINE__);
		}

		// store parent node info
		$res = $this->_db->fetch_row("SELECT {$this->pid}, {$this->left}, {$this->right} FROM {$this->_opt} WHERE {$this->id} = {$node_id}");
		if (!$res)
		{
			return false;
		}

		$parentid = $res[$this->pid];
		$left     = $res[$this->left];
		$right    = $res[$this->right];

		//delete all subnodes under this node
		$this->_db->dbexec("DELETE FROM {$this->_opt} WHERE {$this->left} >= {$left} AND {$this->right} <= {$right}");
		$del_rec_cnt = ($right - $left + 1); //store the count of nodes*2 which been deleted.

		//更新除去parent节点后的节点的右节点
		$this->_db->dbexec("UPDATE {$this->_opt} SET {$this->right} = {$this->right} - {$del_rec_cnt} WHERE {$this->right} > {$right}");

		//左节点
		$this->_db->dbexec("UPDATE {$this->_opt} SET {$this->left} = {$this->left} - {$del_rec_cnt} WHERE {$this->left} > {$right}");

		return true;
	}

	/**
	 * FROMNode 是否在parentNode节点所在子树里面
	 *
	 * @param int $from_node_id   FROM节点;
	 * @param int $parent_node_id to节点
	 * @return bool true 在subtree里; false 不在subtree里
	 */
	function in_sub_tree($from_node_id, $parent_node_id)
	{
		if (empty($from_node_id) || empty($parent_node_id))
		{
			self::halt("null parameter exception", __LINE__);
		}

		//store to node info
		$sql = "SELECT {$this->left}, {$this->right}, {$this->level} FROM {$this->_opt} WHERE {$this->id} = {$parent_node_id}";
		$res = $this->_db->fetch_row($sql);
		if (!$res)
		{
			return false;
		}

		$to_left  = $res[$this->left];
		$to_right = $res[$this->right];
		$to_level = $res[$this->level];

		return (self::is_in_sub_tree($curr_left, $curr_right, $to_left, $to_right)) ? true : false;
	}

	/**
	 * move all subnodes FROM a node to a specified parent node
	 *
	 * @param int $from_node_id   FROM节点;
	 * @param int $parent_node_id to节点
	 * @return bool true 操作成功; false 操作失败
	 */
	function move($from_node_id, $parent_node_id)
	{
		if (!is_numeric($from_node_id) && !is_numeric($parent_node_id))
		{
			self::halt("null parameter exception, input data type must integer", __LINE__);
		}

		//store current node info
		$sql = "SELECT {$this->left}, {$this->right}, {$this->level} FROM {$this->_opt} WHERE {$this->id} = {$from_node_id}";
		$res = $this->_db->fetch_row($sql);
		if (!$res)
		{
			return false;
		}
		$curr_left  = $res[$this->left];
		$curr_right = $res[$this->right];
		$curr_level = $res[$this->level];

		//store to node info
		$sql = "SELECT {$this->left}, {$this->right}, {$this->level} FROM {$this->_opt} WHERE {$this->id} = {$parent_node_id}";
		$res = $this->_db->fetch_row($sql);

		//field value must be got!
		if (!$res)
		{
			return false;
		}

		$to_left  = $res[$this->left];
		$to_right = $res[$this->right];
		$to_level = $res[$this->level];

		//检查是否转移到subtree里面了，如果是的话异常处理
		if (self::is_in_sub_tree($curr_left, $curr_right, $to_left, $to_right))
		{
			self::halt('父节点不能移至子节点中', __LINE__);
		}

		//更新to节点的parentid
		$this->_db->dbexec("UPDATE {$this->_opt} SET {$this->pid} = {$parent_node_id} WHERE {$this->id} = {$from_node_id}");

		return true;
	}

	/**
	 * 把当前节点转移至指定节点,当前节点的子节点上移当前节点的父节点上(move Personal)
	 *
	 * @param int $from_node_id   FROM节点;
	 * @param int $parent_node_id to节点
	 * @return bool true 操作成功; false 操作失败
	 */
	function move_up($from_node_id, $parent_node_id)
	{
		if (!is_numeric($from_node_id) && !is_numeric($parent_node_id))
		{
			self::halt("null parameter exception, input data type must integer", __LINE__);
		}

		//得到当前节点的父节点编号id
		$nowParent = $this->_db->fetch_row("SELECT {$this->pid} FROM {$this->_opt} WHERE {$this->id} = {$from_node_id}");
		if (!$nowParent)
		{
			return false;
		}
		self::move($from_node_id, $parent_node_id); //将节点移到指定节点

		//得到当前节点的子集curr_child_n
		$res = $this->_db->fetch_all("SELECT {$this->id} FROM {$this->_opt} WHERE {$this->pid} = {$from_node_id}");

		/**
		 * 将当前节点下的所有子节点移至当前节点的父节点
		 */
		if ($res)
		{
			$resLen = count($res);
			for ($i = 0; $i < $resLen; ++$i)
			{ //把所有子集转移
				self::move($res[$i][$this->id], $nowParent);
			}
		}

		self::rebuild();

		return true;
	}

	/**
	 * 把当前节点与下属的子节点转移到指定的节点上(move Downline)
	 *
	 * @param int $from_node_id   FROM节点;
	 * @param int $parent_node_id to节点
	 * @return bool true 操作成功; false 操作失败
	 */
	function move_child($from_node_id, $parent_node_id)
	{
		if (empty($from_node_id) || empty($parent_node_id))
		{
			self::halt("null parameter exception", __LINE__);
		}

		//当前节点子集
		$res = $this->_db->fetch_all("SELECT {$this->id} FROM {$this->_opt} WHERE {$this->pid} = {$from_node_id}");
		if (!$res)
		{
			return false;
		}

		$resLen = count($res);
		for ($i = 0; $i < $resLen; ++$i)
		{
			self::move($res[$i][$this->id], $parent_node_id);
		}
		self::rebuild();

		return true;
	}

	/**
	 * 把当前节点删除,子节点上移(like move Personal but delete current node)
	 *
	 * @param int $node_id 指定的节点
	 * @return bool true 操作成功; false 操作失败
	 */
	function delete_up($node_id)
	{
		if (!is_numeric($node_id))
		{
			self::halt("null parameter exception, input data type must integer", __LINE__);
		}

		$res = $this->_db->fetch_row("SELECT {$this->pid} FROM {$this->_opt} WHERE {$this->id} = {$node_id}");
		if (!$res)
		{
			return false;
		}

		$node = $res[$this->pid];
		self::move_up($node_id, $node);
		self::delete($node_id);

		return true;
	}

	/**
	 *  对树进行重建
	 */
	function rebuild()
	{
		$res = $this->_db->fetch_all("SELECT {$this->id}, {$this->pid} FROM {$this->_opt} ORDER BY {$this->id}");
		if (!$res)
		{
			return false;
		}

		$this->arr_tree = array();
		$resLen         = count($res);
		for ($i = 0; $i < $resLen; ++$i)
		{
			$parent_id                    = $res[$i][$this->pid];
			$this->arr_tree[$parent_id][] = $res[$i][$this->id];
		}

		$root_id = 0;
		if ((isset($this->arr_tree[0])) && (sizeof($this->arr_tree[0]) > 0))
		{
			$root_id = $this->arr_tree[0][0];
		}

		if ($root_id <= 0)
		{
			return false;
		}
		self::rebuild_tree($root_id, 1, 1, 1);

		return true;
	}

	/**
	 * 真正重建操作数据库
	 *
	 * @param integer $parent_id 父节点编号
	 * @param integer $left      节点左是多少
	 * @param integer $nlevel    节点所属哪一层
	 * @param integer $seq       节点的序号
	 * @return integer 返回节点右边
	 */
	private function rebuild_tree($parent_id, $left, $nlevel, $seq)
	{
		// the right value of this node is the left value + 1
		$right = $left + 1;

		// 得到parentID的子节点
		$subNodeInfo = isset($this->arr_tree[$parent_id]) ? $this->arr_tree[$parent_id] : array();
		if (count($subNodeInfo) > 0)
		{
			$order = 0;
			foreach ($subNodeInfo as $key => $node_id)
			{
				$right = self::rebuild_tree($node_id, $right, $nlevel + 1, $order + 1);
				$order++;
			}
		}

		$updateOrder = ($this->is_order) ? ", {$this->order} = {$seq}" : "";
		$this->_db->dbexec("UPDATE {$this->_opt} SET {$this->left} = {$left}, {$this->right} = {$right}, {$this->level} = {$nlevel} {$updateOrder} WHERE {$this->id} = {$parent_id}");

		return $right + 1;
	}

	/**
	 * 检查left,right是否正确
	 *
	 * @return bool true 操作成功; false 操作失败
	 */
	public function is_valid()
	{
		$res = $this->_db->fetch_row("SELECT MIN({$this->left}) AS mleft, MAX({$this->right}) AS mright FROM {$this->_opt} WHERE {$this->pid} = 0");
		if (!$res)
		{
			return false;
		}
		$mleft  = $res['mleft'];
		$mright = $res['mright'];

		//find next node left or right,can not find ,compare with top.right
		return (self::valid_tree($mleft) != $mright) ? false : true;
	}

	/**
	 * 得到当前节点所有的子节点
	 * @param int curr_node 当前节点id
	 * @return array 当前节点所有子节点的id数组
	 */
	public function get_sub_node($curr_node)
	{
		if (!is_numeric($curr_node))
		{
			self::halt("null parameter exception, input data type must integer", __LINE__);
		}
		$res = $this->_db->fetch_row("SELECT {$this->left}, {$this->right} FROM {$this->_opt} WHERE {$this->id} = {$curr_node}");
		if (!$res)
		{
			return false;
		}
		$left  = $res[$this->left];
		$right = $res[$this->right];

		return $this->_db->fetch_all("SELECT {$this->id} FROM {$this->_opt} WHERE {$this->left} BETWEEN {$left} AND {$right}");
	}

	/**
	 * 效验节点左右的数是否正确
	 *
	 * @param integer $curr_node
	 * @return mixed 0为false其他的为真
	 */
	private function valid_tree($curr_node)
	{
		$y = 0;
		if (!($curr = $this->found_in_left($curr_node)))
		{
			if (!$curr = $this->found_in_right($curr_node))
			{
				$y = $curr_node;
			}
			else
			{
				$y = self::valid_tree($curr);
			}
		}
		else
		{
			$y = self::valid_tree($curr);
		}

		return $y;
	}

	/**
	 * 查找是否在树的左边
	 *
	 * @param integer $curr
	 * @return mixed
	 */
	private function found_in_left($curr)
	{
		$res = $this->_db->fetch_row("SELECT {$this->left} FROM {$this->_opt} WHERE {$this->left} = {$curr} + 1");

		return ($res) ? $res : false;
	}

	/**
	 * 查找是否在树的右边
	 *
	 * @param integer $curr
	 * @return mixed
	 */
	private function found_in_right($curr)
	{
		$res = $this->_db->fetch_row("SELECT {$this->right} FROM {$this->_opt} WHERE {$this->right} = {$curr} + 1");

		return ($res) ? $res : false;
	}

	/**
	 * 获取父结点的子节点数
	 *
	 * @param integer $p_node_id
	 * @return integer
	 */
	private function get_sub_node_cnt($p_node_id)
	{
		if (!is_numeric($p_node_id))
		{
			self::halt("null parameter exception, input data type must integer", __LINE__);
		}
		$res = $this->_db->fetch_row("SELECT COUNT(*) AS CNUM FROM {$this->_opt} WHERE {$this->pid} = {$p_node_id}");

		return ($res) ? $res["CNUM"] : false;
	}

	/**
	 * 判断是否在子节点中
	 * @param $topLeft
	 * @param $topRight
	 * @param $parentLeft
	 * @param $parentRight
	 * @return bool
	 */
	private function is_in_sub_tree($topLeft, $topRight, $parentLeft, $parentRight)
	{
		return (($parentLeft > $topLeft) && ($parentRight < $topRight)) ? true : false;
	}

	/**
	 * 类与到严重错误时停执行
	 *
	 * @param string  $msg  出错信息
	 * @param integer $line 出错在哪一行
	 */
	private function halt($msg, $line = 0)
	{
		exit("<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><title>{$msg}</title><style>.bgc{background-color:#E5E5E5;}</style></head><body><fieldset><legend>Mistake line</legend>{$line}</fieldset><br><fieldset><legend>Error content following</legend><pre class=\"bgc\">{$msg}</pre></fieldset></body></html>");
	}
}
?>