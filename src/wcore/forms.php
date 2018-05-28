<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/forms.php
 * 简述: 专门用于表单的处理
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: forms.php 1150 2017-07-24 02:10:33Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_forms
{
	/**
	 * 产生SELECT options HTML代表
	 *
	 * @param array  $data     一维数组(若数组的KEY= ogs 或oge代表分组)
	 *                         $data = array('ogs1' => '性别', 'male' => '男士', 'female' => '女士', 'oge1' => 'end', 'ogs2' => '排名', 'rank1' => '1名', 'rank2' => '2名', 'oge2' => 'end');
	 * @param string $selected 定位选中的值
	 * @return string HTML
	 */
	public static function &options(&$data, $selected = '')
	{
		$str = '';
		if (!is_array($data))
		{
			return $str;
		}
		foreach ($data as $k => $v)
		{
			$s = ($selected == $k) ? ' selected' : '';
			if (stristr($k, 'ogs')) //opt group start
			{
				$str .= "<optgroup label=\"{$v}\">\n";
			}
			else if (stristr($k, 'oge')) //opt group end
			{
				$str .= "</optgroup>\n";
			}
			else
			{
				$str .= "<option value=\"{$k}\" label=\"{$v}\"{$s}>{$v}</option>\n";
			}
		}

		return $str;
	}

	/**
	 * 产生 radio HTML代码
	 *
	 * $data = array("zhang1" => '张林', "zhangdw" => "张大爱", 'woods' => 'zhang.woods');
	 * $atpl['radio'] = wcore_forms::radio("woods", $data, 'woods', 'bgc1');
	 *
	 * @param string $name  radio 的名字
	 * @param array  $data  一维数组 array('0' => '男', '1' => '女');
	 * @param string $selected
	 * @param string $class radio 所要的class
	 * @return string HTML
	 */
	public static function &radio($name, &$data, $checked = '', $class = '')
	{
		$str = '';
		if (!is_array($data))
		{
			return $str;
		}
		$i = 0;
		foreach ($data as $k => $v)
		{
			$str .= "<input type=\"radio\" name=\"{$name}\" id=\"{$name}{$i}\" value=\"{$k}\"";
			if ($checked == $k)
			{
				$str .= ' checked';
			}
			if ($class)
			{
				$str .= " class=\"{$class}\"";
			}
			$str .= " /><label for=\"{$name}{$i}\">{$v}</label>\n";
			++$i;
		}

		return $str;
	}

	/**
	 * 产生 checkbox HTML代码
	 *
	 * $data = array(array("name" => 'myName', "value" => "woods", 'tip' => '张林', 'checked' => true),
	 * array("name" => 'myName1', "value" => "hoojar", 'tip' => '张振华', 'checked' => false),
	 * array("name" => 'myName2', "value" => "on", 'tip' => '张大大', 'checked' => true));
	 * $atpl['checkbox'] = wcore_forms::checkbox($data, 'woods', 'bgc1');
	 *
	 * @param array  $data   二维数组要包涵的KEY有四个
	 *                       string name 名称 $data[0]['name'] = 'id';
	 *                       string value 数据值 $data[0]['value'] = 1;
	 *                       boolean checked 是否被选中 $data[0]['checked'] = true;
	 *                       string tip 提示说明 $data[0]['tip'] = '要显示的内容';
	 *
	 * @param string $class  checkbox 所要的class
	 * @return string HTML
	 */
	public static function &checkbox(&$data, $class = '')
	{
		$str = '';
		if (!is_array($data))
		{
			return $str;
		}
		$i = 0;
		foreach ($data as $k => $v)
		{
			if (!isset($v['name']) && !isset($v['value']))
			{
				continue;
			}
			$name    = $v['name'];
			$value   = $v['value'];
			$checked = isset($v['checked']) ? $v['checked'] : false;
			$tip     = isset($v['tip']) ? $v['tip'] : '';
			$str .= "<input type=\"checkbox\" name=\"{$name}\" id=\"{$name}{$i}\" value=\"{$value}\"";
			if ($checked)
			{
				$str .= ' checked';
			}
			if ($class)
			{
				$str .= " class=\"{$class}\"";
			}
			$str .= " /><label for=\"{$name}{$i}\">{$tip}</label>\n";
			++$i;
		}

		return $str;
	}
}
?>