<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/oci.php
 * 简述: 专门用于操作ORACLE数据的函数集
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: oci.php 1150 2017-07-24 02:10:33Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_oci extends wcore_db
{
	/**
	 * 连接oracle 数据库服务器主机
	 *
	 * @var string 主机名
	 */
	private $_db_host = 'localhost';

	/**
	 * 连接oracle 数据库的端口号
	 *
	 * @var integer
	 */
	private $_db_port = 1521;

	/**
	 * 连接oracle 数据库服务器的用户名
	 *
	 * @var string 用户名
	 */
	private $_db_user = 'root';

	/**
	 * 连接oracle 数据库服务器的用户密码
	 *
	 * @var string 密码
	 */
	private $_db_key = '';

	/**
	 * 连接oracle 数据库服务器的用户密码
	 *
	 * @var string 数据库名
	 */
	private $_db_name = 'oracle ';

	/**
	 * 数据库连接句柄
	 *
	 * @var object 连接句柄
	 */
	public $db_link = null;

	/**
	 * 执行SQL语句的资源句柄
	 *
	 * @var object 资源句柄
	 */
	public $result = null;

	/**
	 * 获取数据可以接受以下值：OCI_ASSOC，OCI_NUM 和 OCI_BOTH
	 *
	 * @var integer 数组类型
	 */
	public $result_type = OCI_ASSOC;

	/**
	 * 增加记录时自动编号的最后一个ID号
	 *
	 * @var integer
	 */
	public $insert_id = 0;

	/**
	 * 获取单条数据(一维数组)
	 *
	 * @var array 执行SQL语句的数据组结果
	 */
	public $fetch_row = array();

	/**
	 * 获取所有记录内容(多维数组)
	 *
	 * @var array 执行SQL语句的数据组结果
	 */
	public $fetch_all = array();

	/**
	 * 执行SELECT语句时获取了多少条记录
	 *
	 * @var integer 记录数
	 */
	public $num_rows = 0; //执行SELECT语句时获取了多少条记录
	/**
	 * 执行除SELECT语句所影响的记录行数
	 *
	 * @var integer 影响行数
	 */
	public $affected_rows = 0; //执行除SELECT语句所影响的记录行数

	/**
	 * 连接数据库主机
	 *
	 * @param string  $db_host  连接MYSQL数据库服务器地址
	 * @param string  $db_user  连接MYSQL数据库服务器的用户名
	 * @param string  $db_key   连接MYSQL数据库服务器的用户密码
	 * @param string  $db_name  连接MYSQL数据库服务数据库名
	 * @param string  $charset  连接MYSQL数据库所采用的字符集
	 * @param int     $db_port  连接MYSQL数据库服务器端口号
	 * @param boolean $pconnect 连接MYSQL数据库服务数据库名
	 */
	public function __construct($db_host = 'localhost', $db_user = 'root', $db_key = '', $db_name = 'oracle ', $charset = 'UTF-8', $db_port = 1521, $pconnect = false)
	{
		$this->charset   = $charset;//所使用的字符集
		$this->_db_host  = $db_host; //连接MYSQL数据库服务器主机
		$this->_db_port  = $db_port; //连接MYSQL数据的端口号
		$this->_db_user  = $db_user; //连接MYSQL数据库服务器的用户名
		$this->_db_key   = $db_key; //连接MYSQL数据库服务器的用户密码
		$this->_db_name  = $db_name; //连接MYSQL数据库服务器的用户密码
		$this->_pconnect = $pconnect;
		$this->open_db($db_name); //打开数据库
	}

	/**
	 * 析构函数
	 *
	 */
	public function __destruct()
	{
		$this->free();
		$this->close();
	}

	/**
	 * 释放查询值,释放资源
	 *
	 * @return boolean 成功为true失败为flase
	 */
	public function free()
	{
		if (is_object($this->result))
		{
			oci_free_statement($this->result);
			unset($this->result);
		}

		return true;
	}

	/**
	 * 关闭数据库连接
	 *
	 */
	public function close()
	{
		if (is_object($this->db_link))
		{
			return oci_close($this->db_link);
		}

		return true;
	}

	/**
	 * 获取服务器信息
	 *
	 * @return string 服务器信息
	 */
	public function server_info()
	{
		return oci_server_version($this->db_link);
	}

	/**
	 * 打开数据连接，并选择数据库
	 *
	 * @param string $db_name 数据库名
	 * @return object 连接成功的句柄
	 */
	private function &open_db($db_name = 'oracle')
	{
		/**
		 * 判断是否为长连接
		 * 如果max_execution_time小于等于0代表SHELL下执行程序，永不超时，需采用长连接
		 */
		if (ini_get('max_execution_time') <= 0)
		{
			$this->_pconnect = true;
		}

		$dbname = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST={$this->_db_host})(PORT={$this->_db_port}))(CONNECT_DATA=(SERVER=DEDICATED)(SERVICE_NAME={$db_name})))";
		if ($this->_pconnect) //以持久连接数据方式打开
		{
			$this->db_link = @oci_pconnect($this->_db_user, $this->_db_key, $dbname, $this->charset);
		}
		else
		{
			$this->db_link = @oci_connect($this->_db_user, $this->_db_key, $dbname, $this->charset);
		}

		if (!$this->db_link)
		{
			$this->halt('Connect Oracle Server Error.', __LINE__, $this->error_msg());
		}

		return $this->db_link;
	}

	/**
	 * 获取出错信息
	 *
	 * @return string 错误信息
	 */
	public function error_msg()
	{
		$res = oci_error();

		return isset($res['message']) ? $res['message'] : '';
	}

	/**
	 * 选择数据库
	 *
	 * @param string $db_name 数据库名
	 * @return boolean 选择成功为true失败为flase
	 */
	public function select_db($db_name)
	{
		return false;
	}

	/**
	 * 返回oracle 连接句柄
	 *
	 * @return object 数据库连接句柄
	 */
	public function &db_link() { return $this->db_link; }

	/**
	 * 执行SQL语句，并返回结果
	 *
	 * @param string  $sql      要执行的sql语句
	 * @param boolean $show_err 若有错误是否提示
	 * @return mixed 有结果才返回数据
	 */
	public function &query($sql, $show_err = true)
	{
		if (empty($sql))
		{
			$false = false;

			return $false;
		}

		if ($show_err)
		{
			$this->result = @oci_parse($this->db_link, $sql) or $this->halt($sql, __LINE__, $this->error_msg());
		}
		else
		{
			$this->result = @oci_parse($this->db_link, $sql);
		}
		oci_execute($this->result, OCI_COMMIT_ON_SUCCESS) or $this->halt($sql, __LINE__, $this->error_msg());

		return $this->result;
	}

	/**
	 * 执行事务SQL语句，不返回结果
	 *
	 * @param string  $sql      要执行的sql语句
	 * @param boolean $show_err 若有错误是否提示
	 * @return mixed 执行成功有数据则返回数组,失败返回flase
	 */
	public function dbexec($sql, $show_err = true)
	{
		if (empty($sql))
		{
			return false;
		}

		if ($show_err)
		{
			$this->result = @oci_parse($this->db_link, $sql) or die($this->halt($sql, __LINE__, $this->error_msg()));
		}
		else
		{
			$this->result = @oci_parse($this->db_link, $sql);
		}
		oci_execute($this->result, OCI_COMMIT_ON_SUCCESS) or $this->halt($sql, __LINE__, $this->error_msg());

		return true;
	}

	/**
	 * 获取一条记录数据内容存入数组中
	 *
	 * @param string $sql SQL语句
	 * @return mixed 执行成功有数据则返回数组,失败返回flase
	 */
	public function &fetch_row($sql = '')
	{
		if ($sql)
		{
			$this->query($sql);
		}

		$data = array();
		if (empty($this->result) || is_bool($this->result))
		{
			return $data;
		}

		$this->fetch_row = oci_fetch_assoc($this->result);
		if (!is_array($this->fetch_row))
		{
			$this->fetch_row = $data;
		}
		$this->free();

		return $this->fetch_row;
	}

	/**
	 * 获取所有数据内容存入数组中
	 *
	 * @param string $sql SQL语句
	 * @return mixed 执行成功有数据则返回数组,失败返回flase
	 */
	public function &fetch_all($sql = '')
	{
		if ($sql)
		{
			$this->query($sql);
		}

		$this->fetch_all = array();
		if (!empty($this->result) && !is_bool($this->result))
		{
			while ($row = oci_fetch_assoc($this->result))
			{
				$this->fetch_all[] = $row;
			}
		}
		$this->free();

		return $this->fetch_all;
	}

	/**
	 * 移动内部行指针
	 *
	 * @param integer $nu 移到哪一条记录
	 * @return boolean 如果成功则返回 TRUE，失败则返回 FALSE。
	 */
	public function seek($nu)
	{
		return true;
	}

	/**
	 * 获取最后增加记录的自动编号
	 *
	 * @param string $t
	 * @return int|mixed 自动编号
	 */
	public function insert_id($t = '')
	{
		if (!empty($t))
		{
			$this->insert_id = $this->fetch_one("SELECT {$t}.CURRVAL FROM DUAL");

			return $this->insert_id;
		}

		return 0;
	}

	/**
	 * 执行非SELECT语句所影响的记录行数
	 *
	 * @return integer 影响记录行数
	 */
	public function affected_rows()
	{
		$this->affected_rows = oci_num_rows($this->result);

		return $this->affected_rows;
	}

	/**
	 * 执行SELECT语句所得的记录行数
	 *
	 * @return integer 记录数据数
	 */
	public function num_rows()
	{
		if (!$this->result)
		{
			return false;
		}
		$this->num_rows = count($this->fetch_all);

		return $this->num_rows;
	}

	/**
	 * 设置MYSQL所使用的字符集
	 *
	 * @param string $charset 字符集 utf8 gb2312
	 * @return boolean FALSE
	 */
	public function charset($charset) { return false; }

	/**
	 * 转义特殊字符在一个字符串中使用的SQL语句,考虑到当前连接的字符集
	 *
	 * @param string $value 字符串
	 * @return string 转义后的字符串
	 */
	public function escape($value) { return $value; }

	/**
	 * 获取表的所有字段并返回默认值
	 *
	 * @param string $opt 表名, 若为空则是当前操作表
	 * @return array　表的字段相关数组
	 */
	public function &columns($opt = '')
	{
		$field = array();
		if (empty($opt))
		{
			return $field;
		}

		$stmt = oci_parse($this->db_link, "SELECT * FROM {$opt} WHERE ROWNUM = 1");
		oci_execute($stmt);
		$ncols = oci_num_fields($stmt);
		for ($i = 1; $i <= $ncols; $i++)
		{
			$field[oci_field_name($stmt, $i)] = '';
		}
		oci_free_statement($stmt);

		return $field;
	}
}
?>