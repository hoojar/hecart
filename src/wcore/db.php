<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/db.php
 * 简述: 操作数库表基础库
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: db.php 1273 2017-09-28 06:18:03Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_db
{
	/**
	 * 编码设置,请不要随意更改此项，否则将可能导致论坛出现乱码现象
	 *
	 * @var string 字符集,可以接受的值有:utf8 gb2312 gbk
	 */
	protected $charset = 'utf8';

	/**
	 * 要操作的数据表
	 *
	 * @var string
	 */
	private $_opt = '';

	/**
	 * 重新设置要操作的表名
	 *
	 * @param string $opt 表名
	 */
	public function opt_set($opt) { $this->_opt = $opt; }

	/**
	 * 获取当前操作的表名
	 *
	 * @return string $opt 表名
	 */
	public function opt_get() { return $this->_opt; }

	/**
	 * 组合成SQL语句(非SQL直接操作,需根据表名组合)
	 *
	 * @param string $field 要获取的字段列表
	 * @param string $where 查询条件
	 * @param string $order 排序
	 * @return string SQL
	 */
	private function &opt_where($field = '*', $where = '', $order = '')
	{
		/**
		 * 如果要操作的表为空，那就是SELECT某些函数 SELECT MD5('hoojar')
		 */
		if (empty($this->_opt))
		{
			return "SELECT {$field}";
		}

		/**
		 * 若有条件查询则自动加上WHERE关键字
		 */
		if (!empty($where))
		{
			if (false === stripos($where, ' JOIN ')) //判断是否存在关系查询
			{
				$where = "WHERE {$where}";
			}
			$where = " {$where}";
		}

		/**
		 * 若有排序若分组则分析是否需要组合
		 */
		if (!empty($order))
		{
			if (false === stripos($order, 'ORDER BY')) //判断排序关键字是否存在
			{
				if (false === stripos($order, 'GROUP BY')) //判断分组关键字是否存在
				{
					$order = "ORDER BY {$order}";
				}
			}
			$order = " {$order}";
		}

		/**
		 * 组合SQL并判断是否有调试输出SQL，是否定义了自动连接数据库常量，最终返回SQL
		 */
		$sql = "SELECT {$field} FROM {$this->_opt}{$where}{$order}";

		return $sql;
	}

	/**
	 * 获取一行单条记录(非SQL直接操作,需根据表名组合)
	 *
	 * @param string $field 要获取的字段列表
	 * @param string $where 查询条件
	 * @param string $order 排序
	 * @return array 有数据获取返回数组没有则返回空数组
	 */
	public function &opt_row($field = '*', $where = '', $order = '')
	{
		$sql = self::opt_where($field, $where, $order);

		return $this->fetch_row($sql);
	}

	/**
	 * 获取多行所有记录(非SQL直接操作,需根据表名组合)
	 *
	 * @param string $field 要获取的字段列表
	 * @param string $where 查询条件
	 * @param string $order 排序
	 * @return array 有数据获取返回数组没有则返回空数组
	 */
	public function &opt_all($field = '*', $where = '', $order = '')
	{
		$sql = self::opt_where($field, $where, $order);

		return $this->fetch_all($sql);
	}

	/**
	 * 只取回第一个字段值,适合 COUNT MAX MIN等操作(非SQL直接操作,需根据表名组合)
	 *
	 * @param string $field 要获取的字段列表
	 * @param string $where 查询条件
	 * @param string $order 排序
	 * @return string 有数据获取返回数据没有则返回空
	 */
	public function opt_one($field = '*', $where = '', $order = '')
	{
		if (!$res = $this->opt_row($field, $where, $order))
		{
			return '';
		}

		$fields = array_keys($res); //把字段名当值
		return $res[$fields[0]];
	}

	/**
	 * 取回一个相关数组, 第一个字段值为码 第二个字段为值(非SQL直接操作,需根据表名组合)
	 *
	 * @param string $field 要获取的字段列表
	 * @param string $where 查询条件
	 * @param string $order 排序
	 * @return array 有数据获取返回数组没有则返回空数组
	 */
	public function &opt_pairs($field = '*', $where = '', $order = '')
	{
		$data = array();
		if (!$res = $this->opt_all($field, $where, $order))
		{
			return $data;
		}

		$len    = count($res);
		$fields = array_keys($res[0]); //把字段名当值
		for ($i = 0; $i < $len; ++$i)
		{
			$data[$res[$i][$fields[0]]] = $res[$i][$fields[1]];
		}

		return $data;
	}

	/**
	 * 增加记录到当前操作的表中，生成INSERT SQL 语句(非SQL直接操作,需根据表名组合)
	 *
	 * @param array $data 要增加的数据内容
	 * @return integer　失败成返回0，若表有自动编号则返回自动编号，若无则返回影响多少行
	 */
	public function opt_insert(&$data)
	{
		if (!is_array($data))
		{
			return 0;
		}

		$this->dbexec("INSERT INTO {$this->_opt} {$this->make_sql($data)}");
		$insert_id = $this->insert_id();

		return $insert_id ? $insert_id : $this->affected_rows();
	}

	/**
	 * 更新表中的数据(非SQL直接操作,需根据表名组合)
	 *
	 * @param array  $data  要更新的数据
	 * @param string $where 更新条件
	 * @return integer 更新成功返回影响多少行
	 */
	public function opt_update(&$data, $where = '')
	{
		if (!is_array($data))
		{
			return 0;
		}

		$sql = "UPDATE {$this->_opt} SET {$this->make_sql($data, 'update')}";
		if (!empty($where))
		{
			$sql = "{$sql} WHERE {$where}";
		}
		$this->dbexec($sql);

		return $this->affected_rows();
	}

	/**
	 * 删除数据或清空表(非SQL直接操作,需根据表名组合)
	 *
	 * @param string $where 当此条件为空时则清空表
	 * @return mixed　删除成功影响多少行，清空表成功返回TRUE失败返回FALSE
	 */
	public function opt_del($where)
	{
		if (!empty($where)) //删除记录
		{
			$this->dbexec("DELETE QUICK FROM {$this->_opt} WHERE {$where}");

			return $this->affected_rows();
		}

		return $this->truncate($this->_opt); //清空表
	}

	/**
	 * 获取表的所有字段并返回默认值
	 *
	 * @param string $opt 表名, 若为空则是当前操作表
	 * @return array　表的字段相关数组
	 */
	public function &columns($opt = '')
	{
		if (empty($opt))
		{
			$opt = $this->_opt;
		}

		$field = array();
		$res   = $this->fetch_all("SHOW COLUMNS FROM {$opt}");
		foreach ($res as $v)
		{
			$field[$v['Field']] = $v['Default'];
		}

		return $field;
	}

	/**
	 * 将数组数据组合成 SQL语句
	 *
	 * @param array  $arr  数据组
	 * @param string $type 组合成哪种类型，i 为insert u 为update
	 * @return string sql数据语句
	 */
	public static function &make_sql(&$arr, $type = 'i')
	{
		$key   = '';
		$value = ''; //组合成 INSERT INTO talbe (field) VALUES ($value)
		$set   = ''; //组合成 UPDATE SET field = value
		foreach ($arr as $k => $v)
		{
			if (!is_string($v))
			{
				$v = is_null($v) ? 'NULL,' : (is_bool($v) ? intval($v) . ',' : "{$v},");
			}
			else
			{
				$v = (substr($v, 0, 4) == 'dbf|') ? (substr($v, 4) . ',') : "'{$v}',";
			}

			$key .= "{$k},";
			$value .= $v; //INSERT INTO SQL
			$set .= "{$k}={$v}"; //UPDATE SET field = value
		}

		if ($type == 'i' || $type == 'insert') //INSERT INTO SQL
		{
			$key[strlen($key) - 1]     = ')';
			$value[strlen($value) - 1] = ')';
			$value                     = "({$key} VALUES ({$value}";

			return $value;
		}
		else //UPDATE SET SQL
		{
			$set[strlen($set) - 1] = ' ';

			return $set;
		}
	}

	/**
	 * 只取回第一个字段值,适合 COUNT MAX MIN等操作
	 *
	 * @param string $sql SQL语句
	 * @return string 执行成功有数据则返回数据,失败返回空
	 */
	public function fetch_one($sql = '')
	{
		if (empty($sql))
		{
			return '';
		}

		if (!$res = $this->fetch_row($sql))
		{
			return '';
		}

		$fields = array_keys($res); //把字段名当值
		return $res[$fields[0]];
	}

	/**
	 * 取回一个相关数组, 第一个字段值为码 第二个字段为值
	 *
	 * @param string $sql SQL语句
	 * @return mixed 有数据获取返回数组没有则返回flase
	 */
	public function &fetch_pairs($sql = '')
	{
		$data = array();
		if (empty($sql))
		{
			return $data;
		}

		if (!$res = $this->fetch_all($sql))
		{
			return $data;
		}

		$len    = count($res);
		$fields = array_keys($res[0]); //把字段名当值
		for ($i = 0; $i < $len; ++$i)
		{
			$data[$res[$i][$fields[0]]] = $res[$i][$fields[1]];
		}

		return $data;
	}

	/**
	 * 插入数据到表中
	 *
	 * @param string $opt      要操作的表名
	 * @param array  $data     要增中的数据
	 * @param bool   $show_err 执行insert语句时若出错是否报错
	 * @return integer 失败成返回0，若表有自动编号则返回自动编号，若无则返回影响多少行
	 */
	public function insert($opt, &$data, $show_err = true)
	{
		if (empty($opt) || !is_array($data))
		{
			return 0;
		}

		$this->dbexec("INSERT INTO {$opt} {$this->make_sql($data)}", $show_err);
		$insert_id = $this->insert_id();

		return $insert_id ? $insert_id : $this->affected_rows();
	}

	/**
	 * 更新表中的数据
	 *
	 * @param string $opt   要操作的表名
	 * @param array  $data  要更新的数据
	 * @param string $where 更新条件
	 * @return integer 返回影响的行数
	 */
	public function update($opt, &$data, $where = '')
	{
		if (empty($opt) || !is_array($data))
		{
			return 0;
		}

		$sql = "UPDATE {$opt} SET {$this->make_sql($data, 'update')}";
		if (!empty($where))
		{
			$sql = "{$sql} WHERE {$where}";
		}
		$this->dbexec($sql);

		return $this->affected_rows();
	}

	/**
	 * 删除数据或清空表
	 *
	 * @param string $opt   要操作的表名
	 * @param string $where 当此条件为空时则清空表
	 * @return integer 删除成功返回非零失败为零
	 */
	public function del($opt, $where = '')
	{
		if (!empty($opt) && !empty($where))
		{
			$this->dbexec("DELETE FROM {$opt} WHERE {$where}");

			return $this->affected_rows();
		}

		return 0;
	}

	/**
	 * 检查字符串中是否有要block的字,
	 *
	 * @param string $name
	 * @return bool 查到则返回false不正常反之则为true
	 */
	private function is_normal($name)
	{
		if (!$name)
		{
			return false;
		}
		$str = "/\*|\"|\'|\\\|\/|\(|\)|\||\[|\]|\.|\\$|\^|;|\{|\}|@|`|&|\?|,|\:|\||\+/";

		return preg_match($str, $name) ? false : true;
	}

	/**
	 * 创建数据
	 *
	 * @param string $db_name 数据库名
	 * @return bool 创建成功返回true失败返回flase
	 */
	public function create_db($db_name)
	{
		if (!$this->is_normal($db_name))
		{
			return false;
		}

		return ($this->query("CREATE DATABASE {$db_name}")) ? true : false;
	}

	/**
	 * 删除数据库
	 *
	 * @param string $db_name 数据库名
	 * @return bool 删除成功返回true失败返回flase
	 */
	public function drop_db($db_name)
	{
		if (!$this->is_normal($db_name))
		{
			return false;
		}

		return ($this->query("DROP DATABASE {$db_name}")) ? true : false;
	}

	/**
	 * 清空表
	 *
	 * @param string $t_name 表名
	 * @return bool 删除成功返回true失败为false
	 */
	public function truncate($t_name)
	{
		if (!$this->is_normal($t_name))
		{
			return false;
		}

		return ($this->query("TRUNCATE TABLE {$t_name}")) ? true : false;
	}

	/**
	 * 优化表
	 *
	 * @param string $t_name 表名
	 * @return bool 优化表成功返回true失败为false
	 */
	public function optimize($t_name)
	{
		if (!$this->is_normal($t_name))
		{
			return false;
		}

		return ($this->query("OPTIMIZE TABLE {$t_name}")) ? true : false;
	}

	/**
	 * 类与到严重错误时停执行
	 *
	 * @param string  $msg    提示的信息
	 * @param integer $line   出错在哪一行
	 * @param string  $db_err 数据库返回出错信息
	 */
	public function halt($msg, $line = 0, $db_err = '')
	{
		$charset = ($this->charset == 'utf8') ? 'utf-8' : $this->charset;
		$db_err  = empty($db_err) ? $db_err : iconv('GB2312', "{$charset}//IGNORE", $db_err);

		echo('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html>');
		echo("<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset={$charset}\"><title>{$db_err}</title>");
		echo("<style type=\"text/css\">body,table{font-size:12px;}table{width:100%;background:#669999;}td{height:20px;padding-left:5px;}");
		echo(".bgc1{background-color: #FFFFFF;}.bgc2{background-color:#F4F4F4;}");
		echo("fieldset{border:1px solid #54ACFE;-moz-border-radius:5px; -webkit-border-radius:5px; border-radius:5px;padding-top:3px;}</style>");
		echo("</head><body><fieldset><legend><b>Mistake Line</b></legend>{$_SERVER['SCRIPT_NAME']} -> {$line}</fieldset>");
		echo("<br><fieldset><legend><b>Mysql Feedback Information</b></legend>{$db_err}</fieldset>");
		echo("<br><fieldset><legend><b>Error Content Of SQL</b></legend><pre>{$msg}</pre></fieldset><br>");

		if (ini_get('display_errors'))
		{
			echo($this->get_debug_backtrace(debug_backtrace()));
		}

		if (defined('DEBUG_LOG') && DEBUG_LOG)
		{
			file_put_contents(DIR_ROOT . '/logs/db.log', date('Y-m-d H:i:s') . " - Error: {$db_err}\nContent: {$msg}\n\n", FILE_APPEND);
		}

		exit('</body></html>');
	}

	/**
	 * 获取出错信息返回HTML展示内容
	 *
	 * @param array $res 出错资源数组
	 * @return string       HTML内容
	 */
	protected function &get_debug_backtrace($res)
	{
		$i    = 1;
		$html = '';
		if (empty($res))
		{
			return $html;
		}

		krsort($res);
		foreach ($res as $v)
		{
			$html .= "<fieldset><legend><b>Error File {$i}</b></legend>";
			$html .= '<table cellspacing="1" cellpadding="0" border="0">';
			if (isset($v['file']))
			{
				$html .= "<tr class='bgc1'><td width='100'><b>File:</b></td><td>{$v['file']}</td></tr>";
			}
			if (isset($v['line']))
			{
				$html .= "<tr class='bgc2'><td width='100'><b>Line:</b></td><td>{$v['line']}</td></tr>";
			}
			if (isset($v['class']))
			{
				$html .= "<tr class='bgc1'><td width='100'><b>Class:</b></td><td>{$v['class']}</td></tr>";
			}
			if (isset($v['function']))
			{
				$html .= "<tr class='bgc2'><td width='100'><b>Function:</b></td><td>{$v['function']}</td></tr>";
			}
			if (isset($v['args']) && isset($_GET['debug']))
			{
				$html .= "<tr class='bgc1'><td width='100'><b>Args:</b></td><td>" . implode('<br/>', $v['args']) . "</td></tr>";
			}
			$html .= '</table></fieldset><br/>';
			++$i;
		}

		return $html;
	}
}
?>