<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/fso.php
 * 简述: 专门用于提供各种操作文件系统的函数
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: fso.php 1150 2017-07-24 02:10:33Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_fso
{
	/**
	 * 加载小模板文件
	 *
	 * @param array  $data 要处理的数组值
	 * @param string $file 要加载的小模板文件
	 * @return string
	 */
	public static function file_tpl($data, $file) //加载文件小模板
	{
		if (!is_array($data) || !$file)
		{
			return '';
		}
		if (is_array($data))
		{
			extract($data, EXTR_PREFIX_SAME, 'woods'); //以数组键名为变量名
		}

		//获取文件的内容
		ob_start();
		$result = @include($file);
		if (!$result)
		{
			exit("include: {$file} without exist, Error Line: " . __LINE__);
		}
		$content = ob_get_contents();
		ob_end_clean();

		//分析并组合内容
		$content = addslashes($content);
		preg_replace('/[act]/e', "\$content = \"{$content}\";", 'act'); //进行替换变量生成HTML模板

		return stripcslashes($content);
	}

	/**
	 * 获取加载文件的内容
	 *
	 * @param string $filename 文件名
	 * @return string 内容
	 */
	function &get_include_contents($filename)
	{
		$content = '';
		if (!is_file($filename))
		{
			return $content;
		}

		ob_start();
		require($filename);
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	/**
	 * 自动创建目录,可递归创建
	 *
	 * @param    string $path 要创建的目录地址
	 * @return    boolean    创建成功返回true失败为false
	 */
	public static function make_dir($path)
	{
		if (empty($path))
		{
			return false;
		}

		if (!file_exists($path))
		{
			wcore_fso::make_dir(dirname($path));
			@mkdir($path, 0777);
		}

		return true;
	}

	/**
	 * 递归删除文件夹
	 *
	 * @param    string $path 要删除的文件夹路径
	 * @return    boolean    删除成功为true失败为false
	 */
	public static function rm_dir($path)
	{
		if (empty($path))
		{
			return false;
		}

		if ($objs = glob("{$path}/*"))
		{
			foreach ($objs as $obj)
			{
				is_dir($obj) ? wcore_fso::rm_dir($obj) : unlink($obj);
			}
		}

		return rmdir($path);
	}

	/**
	 * 取二进制文件头快速判断文件类型
	 *
	 * @param string $file 文件路径
	 * @return string 文件类型
	 */
	public static function get_file_type($file)
	{
		$fp  = fopen($file, 'rb');
		$bin = fread($fp, 2); //只读2字节
		fclose($fp);

		$str_info  = @unpack('C2chars', $bin);
		$type_code = intval($str_info['chars1'] . $str_info['chars2']);
		switch ($type_code)
		{
			case 7790:
				$file_type = 'exe';
				break;
			case 7784:
				$file_type = 'midi';
				break;
			case 8075:
				$file_type = 'zip';
				break;
			case 8297:
				$file_type = 'rar';
				break;
			case 255216:
				$file_type = 'jpg';
				break;
			case 7173:
				$file_type = 'gif';
				break;
			case 6677:
				$file_type = 'bmp';
				break;
			case 13780:
				$file_type = 'png';
				break;
			default:
				$file_type = 'unknown';
				break;
		}

		return $file_type;
	}

	/**
	 * 支持断点续传上传文件
	 *
	 * @return array　保存成功后的信息
	 */
	public static function upload()
	{
		/**
		 * 判断是否上传了文件
		 */
		$tmp_file = isset($_FILES['file']['tmp_name']) ? $_FILES['file']['tmp_name'] : ''; //临时文件完整路径
		if (empty($tmp_file) || !file_exists($tmp_file))
		{
			return array(
				'status' => 0,
				'msg'    => 'without upload files'
			);
		}

		/**
		 * 判断是否有断点续传
		 */
		$fileext = substr($_FILES['file']['name'], strrpos($_FILES['file']['name'], '.'));
		if (empty($_SERVER['HTTP_RANGE']))//非断点续传,直接返回上传数据
		{
			return array(
				'status'   => 2,
				'fileext'  => $fileext,
				'filetag'  => $_FILES['file']['tmp_name'],
				'filename' => uniqid(rand()) . $fileext,
				'msg'      => 'upload file completed'
			);
		}

		/**
		 * 创建文件并指定文件缓冲大小
		 */
		$filesize = intval(substr($_SERVER['HTTP_RANGE'], strpos($_SERVER['HTTP_RANGE'], '/') + 1));
		$rangepos = intval(substr($_SERVER['HTTP_RANGE'], 6, strpos($_SERVER['HTTP_RANGE'], '-') - 6));
		$filetag  = dirname($_FILES['file']['name']) . '/' . md5("{$filesize}{$_FILES['file']['name']}");
		if (!file_exists($filetag))
		{
			$hfile = fopen($filetag, 'wb');
			if (!$hfile)
			{
				return array(
					'status' => 0,
					'msg'    => 'create file buffer failed, pls checking file permissions'
				);
			}
			ftruncate($hfile, $filesize);
			fclose($hfile);
		}

		/**
		 * 写入数据到文件
		 */
		$hfile = fopen($filetag, 'r+b'); //写入数据
		fseek($hfile, $rangepos, SEEK_SET); //定位到续传位置
		fwrite($hfile, file_get_contents($tmp_file)); //写入数据内容
		fclose($hfile);

		/**
		 * 判断文件是否完全上传成功
		 */
		if (($rangepos + intval($_FILES['file']['size'])) == $filesize)
		{
			return array(
				'status'   => 2,
				'fileext'  => $fileext,
				'filetag'  => $filetag,
				'filename' => uniqid(rand()) . $fileext,
				'msg'      => 'upload file completed'
			);
		}

		return array(
			'status' => 1,
			'msg'    => 'upload file success'
		);//文件片段上传成功
	}

	/**
	 * 断点下载文件
	 *
	 * @param string $file       要下载的文件路径
	 * @param string $name       输出下载的文件名
	 * @param bool   $breakpoint 是否开启断点续传
	 * @return bool
	 */
	public static function download($file, $name = '', $breakpoint = true)
	{
		if (!file_exists($file))
		{
			return false;
		}

		if (empty($name))
		{
			$name = basename($file);
		}

		$rangepos = 0;//文件读取位置
		$filesize = filesize($file);
		header('Cache-Control: public');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=' . $name);

		/**
		 * 判断是否使用断点续传功能
		 */
		if ($breakpoint && isset($_SERVER['HTTP_RANGE']))
		{
			$rangepos = intval(substr($_SERVER['HTTP_RANGE'], 6, strpos($_SERVER['HTTP_RANGE'], '-') - 6));
			header('HTTP/1.1 206 Partial Content');
			header('Accept-Ranges: bytes');
			header(sprintf('Content-Length: %u', $filesize - $rangepos));//剩余长度
			header(sprintf('Content-Range: bytes %s-%s/%s', $rangepos, $filesize - 1, $filesize));//RANGE信息
		}
		else
		{
			header('HTTP/1.1 200 OK');
			header('Content-Length: ' . $filesize);
		}

		/**
		 * 根据断点位置读取文件
		 */
		$fp = fopen($file, 'rb');
		fseek($fp, sprintf('%u', $rangepos));
		while (!feof($fp))
		{
			echo fread($fp, 2048);
			ob_flush();
		}

		($fp != null) && fclose($fp);

		return true;
	}
}
?>