<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/tpl.php
 * 简述: 操作模版文件,此模板采用数组传值
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: tpl.php 1150 2017-07-24 02:10:33Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_tpl
{
	/**
	 * 设置模版文件的操作目录
	 *
	 * @var string
	 */
	private $t_root;

	/**
	 * 设置模版文件的扩展名
	 *
	 * @var string
	 */
	private $ext = '.html';

	/**
	 * 分析成功的内容
	 *
	 * @var string
	 */
	public $content = '';

	/**
	 * 构造函数
	 *
	 * @param string $t_root 模板文件所在的文件夹地址
	 * @param string $ext    模板文件的扩展名
	 */
	public function __construct($t_root = '', $ext = '.html')
	{
		self::set_root($t_root, $ext);
		self::set_file();
	}

	/**
	 * 析构函数
	 *
	 */
	public function __destruct()
	{
		unset($this->content);
		unset($this->t_root, $this->ext);
	}

	/**
	 * 类与到严重错误时停执行
	 *
	 * @param string  $msg  提示的内容
	 * @param integer $line 出错行号
	 */
	private function halt($msg, $line = 0)
	{
		echo("<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><title>{$msg}</title>");
		echo("</head><body><fieldset><legend><b>Mistake Line</b></legend>{$_SERVER['SCRIPT_NAME']} -> {$line}</fieldset>");
		echo("<br><fieldset><legend><b>Error Content Following</b></legend><pre>{$msg}</pre></fieldset><br>");
		if (ini_get('display_errors'))
		{
			echo($this->get_debug_backtrace(debug_backtrace()));
		}
		exit('</body></html>');
	}

	/**
	 * 获取出错信息返回HTML展示内容
	 *
	 * @param    array $res 出错资源数组
	 * @return    string            HTML内容
	 */
	protected function &get_debug_backtrace($res)
	{
		krsort($res);
		$i    = 1;
		$html = '';
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

	/**
	 * 设置要操作的模板文件夹
	 *
	 * @param string $t_root 模板文件所在的文件夹地址
	 * @param string $ext    模板文件的扩展名
	 */
	public function set_root($t_root = '', $ext = '.html')
	{
		if ($t_root)
		{
			if (false !== strpos($t_root, '/') && is_dir($t_root)) //判断是否指定的相对路径
			{
				//判断最事一个是否加上了符号[/]
				if ($t_root[strlen($t_root) - 1] != '/')
				{
					$t_root = "{$t_root}/";
				}
			}
			else //不是相对路径则组合绝对路径
			{
				if (!defined('DIR_ROOT'))
				{
					define('DIR_ROOT', dirname($_SERVER['DOCUMENT_ROOT']));
				}
				$t_root = DIR_ROOT . "/{$t_root}";
				$t_root .= dirname($_SERVER['SCRIPT_NAME']) . '/';
			}
		}
		else
		{
			if (!defined('DIR_ROOT'))
			{
				define('DIR_ROOT', dirname($_SERVER['DOCUMENT_ROOT']));
			}
			$t_root = DIR_ROOT . '/view';
			$t_root .= dirname($_SERVER['SCRIPT_NAME']) . '/';
		}

		$this->t_root = $t_root; //设置要操作的文件夹
		$this->ext    = $ext; //设置模板文件的扩展名
	}

	/**
	 * 加载其它文件
	 *
	 * @param string  $file 要加的文件路径
	 * @param boolean $join 是否连接原来的数据内容
	 * @return string
	 */
	private function &_include($file, $join = false)
	{
		$file = self::check_filename($file); //判断文件的路径并组合完整的路径
		ob_start();
		$result  = include($file);
		$content = ob_get_contents();
		ob_end_clean();
		if (!$result)
		{
			self::halt("Include [{$file}] inexistence.", __LINE__);
		}

		//是否连接原来的数据内容
		if ($join)
		{
			$this->content .= $content;
		}

		return $content;
	}

	/**
	 * 判断文件路径是否正确若不正确则组合成正确的路径
	 *
	 * @param string $filename 文件名或路径
	 * @return string
	 */
	private function check_filename($filename)
	{
		if (!strstr($filename, $this->ext)) //判断是否带扩展名了
		{
			$filename = "{$filename}{$this->ext}";
		}
		if (false === strpos($filename, "/")) //说明此处为相对路径
		{
			$filename = "{$this->t_root}{$filename}";
		}

		return $filename;
	}

	/**
	 * 设置模版文件
	 *
	 * @param string $file 当$file为空时，程序自动根据目前执行的文件名来名命获取HTML文件
	 */
	public function set_file($file = '')
	{
		$this->content = '';
		if (is_array($file)) //以数组加载多个模板文件
		{
			foreach ($file as $filename)
			{
				self::_include($filename, true);
			}
		}
		else //加载单个模板文件
		{
			if (!$file) //加载与执行文件名相同的单个模板文件
			{
				$filename = basename($_SERVER['SCRIPT_NAME']);
				$file     = substr($filename, 0, strrpos($filename, '.'));
			}
			self::_include($file, true);
		}

		//加载模板中的 include
		$reg = "/<!--\s+include[(=|\s+=\s)]+\"(.+?)\"\s+-->/ism";
		preg_match_all($reg, $this->content, $m);
		$len = count($m[1]);
		for ($i = 0; $i < $len; ++$i)
		{
			$str           = & self::_include($this->t_root . $m[1][$i]);
			$this->content = str_replace($m[0][$i], $str, $this->content); //将块的区域内容替换为块名
		}
	}

	/**
	 * 执行替换操作
	 *
	 * @param array  $data          要分解的数组
	 * @param string $__eval_string 要替换的字符串，此处名称定不能与分析的名称的变量相同，若相同数据将不对
	 * @return string 替换好的字符串
	 */
	private function &_eval_string(&$data, &$__eval_string)
	{
		if (!is_array($data))
		{
			return $__eval_string;
		}
		foreach ($data as $key => $vd)
		{
			${$key} = $vd; //以数组键名为变量名
		}

		unset($data); //release data array
		$str = addslashes($__eval_string); //替换前先将数据格式化
		preg_replace('/[act]/e', "\$str = \"{$str}\";", 'act'); //进行替换变量生成HTML模板
		if (false !== strpos($str, '\''))
		{
			$str = stripcslashes($str);
		}

		return $str;
	}

	/**
	 * 数据块的分析
	 *
	 * @param string  $handle 块的名称(区分大小写)
	 * @param array   $value  是一个数组值
	 * @param integer $i      序号开始值
	 * @param boolean $embed  是否调用嵌入的模板分析
	 * @return boolean 成功为true失败为false
	 */
	public function block($handle, &$value = array(), $i = 1, $embed = false)
	{
		$reg = "/<!--\s+BEGIN {$handle}\s+-->(.+?)<!--\s+END {$handle}\s+-->/sm";
		preg_match($reg, $this->content, $m);

		//说明用户未定义指定的标签
		if (!$m)
		{
			return false;
		}

		//说明没有数据则把块标签删除
		if (!is_array($value))
		{
			$this->content = preg_replace($reg, '', $this->content);

			return false;
		}

		if ($embed)
		{
			$str = self::dispose_embed($value, $m[1], $i, $handle); //生成数据的值(调用嵌入模板分析)
		}
		else
		{
			$str = self::dispose($value, $m[1], $i); //生成数据的值
		}

		//防止相冲突
		if (false !== strpos($str, '$'))
		{
			$str = str_replace('$', '$\w', $str);
		}
		$this->content = preg_replace($reg, $str, $this->content); //将块的区域内容替换

		return true;
	}

	/**
	 * 进行替换变量生成HTML模板 处理并分析数组
	 *
	 * @param array   $value 数据组
	 * @param string  $bstr  处理的字符串
	 * @param integer $i     序号开始值
	 * @return string 组合好的字符串
	 */
	private function &dispose(&$value, $bstr, $i)
	{
		if (!is_array($value))
		{
			return '';
		}
		$str = ''; //生成数据的值
		foreach ($value as $v)
		{
			if (!is_array($v))
			{
				continue;
			}
			$v['num'] = $i;
			$v['css'] = ($i % 2 === 0) ? 'bgc1' : 'bgc2';
			$str .= $this->_eval_string($v, $bstr); //执行替换处理
			$i++;
		}

		return $str;
	}

	/**
	 * 嵌入模板式的进行替换变量生成HTML模板 处理并分析数组
	 *
	 * @param array   $value  数据组
	 * @param string  $bstr   处理的字符串
	 * @param integer $i      序号开始值
	 * @param string  $handle 块的名称(区分大小写)
	 * @return string 组合好的字符串
	 */
	private function &dispose_embed(&$value, $bstr, $i, $handle = '')
	{
		if (!is_array($value))
		{
			return '';
		}
		$str      = ''; //生成数据的值
		$rule     = array(); //嵌入的模板规则
		$rule_str = array(); //嵌入的模板规则字符串
		foreach ($value as $v)
		{
			if (!is_array($v))
			{
				continue;
			}

			$content = $bstr; //此处主要是为了下次循环时的模板的值不变
			foreach ($v as $k => $sv) //处理嵌入的模板
			{
				if (!is_array($sv))
				{
					continue;
				}

				//将规则存储在规则数据组中方便下次使用
				if (!isset($rule[$k]))
				{
					$reg = "/<!--\s+BEGIN {$handle}\-{$k}\s+-->(.+?)<!--\s+END {$handle}\-{$k}\s+-->/sm";
					preg_match($reg, $content, $m);
					if ($m)
					{
						$rule[$k]     = $reg;
						$rule_str[$k] = $m[1];
					}
					else
					{
						$rule[$k] = $rule_str[$k] = '';
					}
				}

				//判断某嵌入数组是否有相对应的模板
				if (!$rule[$k])
				{
					unset($v[$k]);
					continue;
				}
				$matter  = self::dispose_embed($v[$k], $rule_str[$k], 1); //嵌入的模板需处理
				$content = preg_replace($rule[$k], $matter, $content); //将嵌入的模板替换
				unset($v[$k]);
			}

			//清空子模块当中的模板标签中的内容
			if (false !== strpos($content, "BEGIN {$handle}-"))
			{
				$reg     = "/<!--\s+BEGIN {$handle}\-(.*)-->(.+?)<!--\s+END {$handle}\-(.*)-->/sm";
				$content = preg_replace($reg, '', $content);
			}
			$v['num'] = $i;
			$v['css'] = ($i % 2 === 0) ? 'bgc1' : 'bgc2';
			$str .= $this->_eval_string($v, $content); //执行替换处理
			$i++;
		}

		return $str;
	}

	/**
	 * 分析数据，进行合理的替换,返加分析完的值
	 *
	 * @param array   $data   数据体
	 * @param boolean $output 是否马上输出分析好的内容
	 * @return string 分析好的内容
	 */
	public function &parse(&$data = array(), $output = true)
	{
		if (is_array($data) && !empty($data))
		{
			$this->content = $this->_eval_string($data, $this->content);
		}
		if ($output)
		{
			echo($this->content);
			flush();
			unset($this->content);
		}

		return $this->content;
	}

	/**
	 * 加载小模板文件
	 * @param array  $data 要处理的数组值
	 * @param string $file 要加载的小模板文件
	 * @return string 处理好的内容
	 */
	public function &file_tpl(&$data, $file) //加载文件小模板
	{
		if (!is_array($data) || empty($file))
		{
			return '';
		}

		return $this->_eval_string($data, self::_include($file));
	}

	/**
	 * 获取模板中的内容
	 *
	 * @return string 模板内容
	 */
	public function &content() { return $this->content; }

	/**
	 * 增加内容到模板
	 *
	 * @param string $tip 内容体
	 */
	public function input($tip = '') { $this->content .= $tip; }

	/**
	 * 增加一些内容并输出
	 *
	 * @param string $tip 内容体
	 */
	public function output($tip = '')
	{
		echo($this->content);
		echo($tip);
		flush();
	}
}
?>