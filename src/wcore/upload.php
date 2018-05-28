<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/upload.php
 * 简述: 文件上传库
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: upload.php 1150 2017-07-24 02:10:33Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_upload
{
	/**
	 * 文件(包含路径与文件名)
	 *
	 * @var string
	 */
	public $file = '';

	/**
	 * 文件名称
	 *
	 * @var string
	 */
	public $file_name = '';

	/**
	 * 文件扩展名
	 *
	 * @var string
	 */
	public $file_ext = '';

	/**
	 * 当上传的文件存在指定目录中时，是否覆盖
	 *
	 * @var boolean
	 */
	public $overwrite = false;

	/**
	 * 错误代号
	 *
	 * @var integer
	 */
	public $error_id = 0;

	/**
	 * 文件保存路径
	 *
	 * @var string
	 */
	private $_save_path = '';

	/**
	 * 设置水印的透明度(数字越小越透明)
	 *
	 * @var integer 1到100
	 */
	private $_opacity = 100;

	/**
	 * 水印文件路径与文件名
	 *
	 * @var string
	 */
	private $_watermark_file = '';

	/**
	 * 决定是否打水印true的false不打
	 *
	 * @var boolean
	 */
	private $_watermark = false;

	/**
	 * 合法文件类型扩展名
	 *
	 * @var array
	 */
	private $_ext_type = array(
		'gif',
		'jpg',
		'png'
	);

	/**
	 * 文件最大字节(0代表不限制)
	 *
	 * @var integer
	 */
	private $_max_size = 0;

	/**
	 * 是否生成缩略图
	 *
	 * @var boolean 生成为ture不生成为false
	 */
	private $_thumb = false;

	/**
	 * 缩略图文件(包含路径与文件名)
	 *
	 * @var string
	 */
	public $thumb_file = '';

	/**
	 * 缩略图文件名称
	 *
	 * @var string
	 */
	public $thumb_file_name = '';

	/**
	 * 缩略图宽
	 *
	 * @var integer 宽
	 */
	private $_thumb_width = 150;

	/**
	 * 缩略图高
	 *
	 * @var integer 高
	 */
	private $_thumb_height = 113;

	/**
	 * 缩略图前缀
	 *
	 * @var string
	 */
	private $_thumb_prefix = 'thumb-';

	/**
	 * 以数组形式存储每个文件信息
	 *
	 * @var array
	 */
	private $_rs_array = array();

	/**
	 * 以数据形式存储文件的信息
	 *
	 * @var array
	 */
	private $_rs_info = array();

	/**
	 * 构造函数
	 *
	 * @param string  $save_path   文件保存路径
	 * @param array   $file_format 文件格式限制数组
	 * @param integer $max_size    文件大小(K) 0:表示无限制设置上传文件的最大字节限制
	 * @param boolean $overwrite   是否覆盖true允许覆盖 flase 禁止覆盖
	 */
	public function __construct($save_path, $file_format = null, $max_size = 0, $overwrite = false)
	{
		$this->set_save_path($save_path); //设置存储路径
		//设置文件格式限定@param $file_format 文件格式数组
		if (!empty($file_format) && is_array($file_format))
		{
			$this->_ext_type = $file_format;
		}
		$this->_max_size = $max_size * 1024; //$max_size 文件大小(K) 0:表示无限制设置上传文件的最大字节限制
		$this->overwrite = $overwrite; //覆盖模式 true:允许覆盖 false:禁止覆盖
	}

	/**
	 * 设置存储路径
	 *
	 * @param string $save_path 存储路径
	 */
	public function set_save_path($save_path)
	{
		$save_path = substr(str_replace('\\', '/', $save_path), -1) == '/' ? $save_path : "{$save_path}/";
		$this->_make_dir($save_path); //$save_path 文件保存路径：以 '/' 结尾，若没有 '/'，则补上并创建存储目录
		$this->_save_path = $save_path;
	}

	/**
	 * 执行上传
	 *
	 * @param string $input_name  <input file name=''>中的name
	 * @param string $change_name 新文件名
	 * @return boolean 上传成功为true失败为false
	 */
	public function run($input_name, $change_name = 'default')
	{
		/**
		 * 判断是否有文件上传,没有的话则返回false
		 */
		if (empty($input_name))
		{
			return false;
		}

		if (!isset($_FILES[$input_name]))
		{
			$this->error_id = 10;

			return false;
		}

		/**
		 * 上传多个文件
		 */
		$fres = $_FILES[$input_name];
		if (is_array($fres['name']))
		{
			for ($i = 0; $i < count($fres['name']); $i++)
			{
				$ar['tmp_name'] = $fres['tmp_name'][$i];
				$ar['name']     = $fres['name'][$i];
				$ar['type']     = $fres['type'][$i];
				$ar['size']     = $fres['size'][$i];
				$ar['error']    = $fres['error'][$i];

				/**
				 * 获取上传文件的扩展名与分配新的存储文件名
				 */
				$this->_set_filename($ar['name'], $change_name);

				/**
				 * 上传文件
				 */
				if ($this->_copy_file($ar))
				{
					$this->_rs_array[] = $this->_rs_info;
				}
				else
				{
					$this->_rs_info['error'] = $this->error_msg($this->error_id);
					$this->_rs_array[]       = $this->_rs_info;
				}
			}

			return $this->error_id ? false : true;
		}

		/**
		 * 上传单个文件
		 */
		$this->_set_filename($fres['name'], $change_name); //获取上传文件的扩展名与分配新的存储文件名
		if ($this->_copy_file($fres))
		{
			$this->_rs_array[] = $this->_rs_info;
		}
		else
		{
			$this->_rs_info['error'] = $this->error_msg($this->error_id);
			$this->_rs_array[]       = $this->_rs_info;
		}

		return $this->error_id ? false : true;
	}

	/**
	 * 自动创建目录,可递归创建
	 *
	 * @param    string $path 要创建的目录地址
	 * @return    boolean    创建成功返回true失败为false
	 */
	private function _make_dir($path)
	{
		if (empty($path))
		{
			return false;
		}
		if (!file_exists($path))
		{
			$this->_make_dir(dirname($path));
			@mkdir($path, 0777);
		}

		return true;
	}

	/**
	 * 文件上传
	 *
	 * @param string $fname 上传中的临时文件对象
	 * @return boolean
	 */
	private function _copy_file($fname)
	{
		$this->_rs_info = array(
			'name'     => $fname['name'],
			'filename' => $this->file_name,
			'file'     => $this->file,
			'size'     => number_format(($fname['size']) / 1024, 0, '.', ' ') . ' KB',
			'type'     => $fname['type']
		);

		/**
		 * 验证文件扩展名是否符合
		 */
		if (!$this->_check_ext($this->file_ext))
		{
			$this->error_id = 11;

			return false;
		}

		/**
		 * 检查目录是否可写
		 */
		if (!@is_writable($this->_save_path))
		{
			$this->error_id = 12;

			return false;
		}

		/**
		 * 判断文件是否已经存在
		 */
		if (file_exists($this->file) && !$this->overwrite)
		{
			$this->error_id = 13;

			return false;
		}

		/**
		 * 判断文件大小，检查文件是否超过限制
		 */
		if ($this->_max_size != 0)
		{
			if ($fname['size'] > $this->_max_size)
			{
				$this->error_id = 14;

				return false;
			}
		}

		/**
		 * 上传文件
		 */
		if (!move_uploaded_file($fname['tmp_name'], $this->file))
		{
			$this->error_id = $fname['error'];

			return false;
		}

		//判断是否创建缩略图
		if ($this->_thumb)
		{
			$this->make_thumb();
		}

		//判断是否打水印
		if ($this->_watermark)
		{
			$this->watermark($this->file, $this->_watermark_file);
		}

		return true;
	}

	/**
	 * 文件格式检查是否合法
	 *
	 * @param string $file_ext 文件扩展名
	 * @return boolean
	 */
	private function _check_ext($file_ext)
	{
		$file_ext = strtolower($file_ext);

		return (in_array($file_ext, $this->_ext_type)) ? true : false;
	}

	/**
	 * 获取文件扩展名
	 *
	 * @param string $fname
	 */
	private function _get_ext($fname)
	{
		$ext            = explode('.', $fname);
		$ext            = $ext[count($ext) - 1];
		$this->file_ext = strtolower($ext);
	}

	/**
	 * 设置缩略图
	 *
	 * @param integer $thumb_width  宽
	 * @param integer $thumb_height 高
	 * @param string  $prefix       附件文件名
	 */
	public function set_thumb($thumb_width = 0, $thumb_height = 0, $prefix = '')
	{
		$this->_thumb = true;
		if ($thumb_width)
		{
			$this->_thumb_width = $thumb_width;
		}
		if ($thumb_height)
		{
			$this->_thumb_height = $thumb_height;
		}
		if ($prefix)
		{
			$this->_thumb_prefix = $prefix;
		}
	}

	/**
	 * 生成缩略图
	 *
	 * @param integer $thumb_width  宽
	 * @param integer $thumb_height 高
	 * @param string  $prefix       附件文件名
	 * @return boolean 是否执行成功
	 */
	public function make_thumb($thumb_width = 0, $thumb_height = 0, $prefix = '')
	{
		$this->set_thumb($thumb_width, $thumb_height, $prefix); //设置缩略图信息

		/**
		 * 分析图片类型并生成相应的GD函数
		 */
		$create_function = 'imagecreatefrom' . ($this->file_ext == 'jpg' ? 'jpeg' : $this->file_ext);
		$save_function   = 'image' . ($this->file_ext == 'jpg' ? 'jpeg' : $this->file_ext);
		if (strtolower($create_function) == 'imagecreatefromgif' && !function_exists('imagecreatefromgif'))
		{
			$this->error_id = 16;

			return false;
		}
		else if (strtolower($create_function) == 'imagecreatefromjpeg' && !function_exists('imagecreatefromjpeg'))
		{
			$this->error_id = 17;

			return false;
		}
		else if (!function_exists($create_function))
		{
			$this->error_id = 18;

			return false;
		}

		/**
		 * 创建缩略图
		 */
		$original = $create_function($this->file);
		if (!$original)
		{
			$this->error_id = 19;
			$this->error_id = 24;

			return false;
		}

		/**
		 * 获取原始图片的宽度与高度
		 */
		$original_width                    = imagesx($original);
		$original_height                   = imagesy($original);
		$this->_rs_info['original_width']  = $original_width;
		$this->_rs_info['original_height'] = $original_height;

		/**
		 * 组合缩略图相关文件与路径
		 */
		$this->thumb_file_name = $this->_thumb_prefix . $this->file_name; //缩略图相对路径与文件名
		$this->thumb_file      = $this->_save_path . $this->_thumb_prefix . $this->file_name; //缩略图文件全路径

		/**
		 * 如果比期望的缩略图小，那只Copy
		 */
		if (($original_height < $this->_thumb_height && $original_width < $this->_thumb_width))
		{
			copy($this->file, $this->thumb_file);

			return true;
		}

		/**
		 * 计算生成缩略图的大小
		 */
		$thumb_width  = $this->_thumb_width;
		$thumb_height = $this->_thumb_height;
		if ($original_width > $this->_thumb_width) // 宽 > 设定宽度
		{
			$thumb_height = $this->_thumb_width * ($original_height / $original_width);
			if ($thumb_height > $this->_thumb_height) //高 > 设定高度
			{
				$thumb_width = $this->_thumb_height * ($thumb_width / $thumb_height);
			}
		}
		else if ($original_height > $this->_thumb_height) //高 > 设定高度
		{
			$thumb_width = $this->_thumb_height * ($original_width / $original_height);
			if ($thumb_width > $this->_thumb_width) //宽 > 设定宽度
			{
				$thumb_height = $this->_thumb_width * ($thumb_height / $thumb_width);
			}
		}

		/**
		 * 创建真彩色画布
		 */
		$created_thumb = imagecreatetruecolor($thumb_width, $thumb_height);
		if (empty($created_thumb))
		{
			$this->error_id = 20;

			return false;
		}

		/**
		 * 复制图片到创建的画布上
		 */
		if (!imagecopyresampled($created_thumb, $original, 0, 0, 0, 0, $thumb_width, $thumb_height, $original_width, $original_height))
		{
			$this->error_id = 21;

			return false;
		}

		/**
		 * 将画布的内容存储成图片
		 */
		if (!$save_function($created_thumb, $this->thumb_file))
		{
			$this->error_id = 22;

			return false;
		}

		return true;
	}

	/**
	 * 文件保存名,如果为空，则系统自动生成一个随机的文件名
	 *
	 * @param string $fname 文件名
	 * @param        $change_name
	 */
	private function _set_filename($fname, $change_name)
	{
		$this->_get_ext($fname);
		switch (strtolower($change_name))
		{
			case 'default': //设置上传文件名为自身的名称
				$name = $fname;
				break;
			case 'auto': //系统自动生成连续的以时间为名称
				$name = date('YmdHis') . '_' . mt_rand(100, 999) . '.' . $this->file_ext;
				break;
			default: //用户指定的名字
				$name = "{$change_name}.{$this->file_ext}";
				break;
		}
		$this->file_name = $name; //仅仅文件名称与扩展名
		$this->file      = "{$this->_save_path}{$this->file_name}"; //完整的文件地址
	}

	/**
	 * 返回上传文件的信息
	 *
	 * @return array
	 */
	public function get_info() { return $this->_rs_array; }

	/**
	 * 设置水印
	 *
	 * @param string  $file    水印文件路径
	 * @param integer $opacity 透明度 1 -> 100
	 * @return boolean 水印文存在返回真失败为false
	 */
	public function set_watermark($file, $opacity = 100)
	{
		if (empty($file))
		{
			return false;
		}
		if (file_exists($file))
		{
			$this->_opacity        = $opacity; //打水印的透明度
			$this->_watermark      = true;
			$this->_watermark_file = $file;

			return true;
		}
		$this->error_id = 23;

		return false;
	}

	/**
	 * 给图片加水印，$dfile为要在其上增加的水印文件$tfile为水印文件
	 *
	 * @param string $dfile 要加的水印图片
	 * @param string $tfile 水印文件
	 * @return boolean
	 */
	public function watermark($dfile, $tfile)
	{
		$dinfo   = getimagesize($dfile);
		$dwidth  = $dinfo[0];
		$dheight = $dinfo[1];

		/**
		 * 创建哪种类型图片
		 */
		switch ($dinfo[2])
		{
			case 1: //gif
				$img = imagecreatefromgif($dfile);
				break;
			case 2: //jpg
				$img = imagecreatefromjpeg($dfile);
				break;
			case 3: //png
				$img = imagecreatefrompng($dfile);
				break;
			default:
				$this->error_id = 24;

				return false;
		}

		/**
		 * 创建画布
		 */
		if (function_exists('imagecreatetruecolor'))
		{
			$new = imagecreatetruecolor($dwidth, $dheight); //使用真彩色
			imagecopyresampled($new, $img, 0, 0, 0, 0, $dwidth, $dheight, $dwidth, $dheight);
		}
		else
		{
			$new = imagecreate($dwidth, $dheight); //使用一般彩色
			imagecopyresized($new, $img, 0, 0, 0, 0, $dwidth, $dheight, $dwidth, $dheight);
		}

		/**
		 * 复制图片到生成的像片上
		 */
		$tinfo   = getimagesize($tfile);
		$twidth  = $tinfo[0];
		$theight = $tinfo[1];
		switch ($tinfo[2])
		{
			case 1: //gif
				$img1 = imagecreatefromgif($tfile);
				break;
			case 2: //jpg
				$img1 = imagecreatefromjpeg($tfile);
				break;
			case 3: //png
				$img1 = imagecreatefrompng($tfile);
				break;
			default:
				$this->error_id = 24;

				return false;
		}

		imagecopymerge($new, $img1, $dwidth - $twidth, $dheight - $theight, 0, 0, $twidth, $theight, $this->_opacity);
		imagedestroy($img1);

		/**
		 * 生成相关类型的图片
		 */
		switch ($dinfo['2'])
		{
			case 1: //gif
				imagegif($new, $dfile);
				break;
			case 2: //jpg
				imagejpeg($new, $dfile);
				break;
			case 3: //png
				imagepng($new, $dfile);
				break;
			default:
				$this->error_id = 24;

				return false;
		}

		imagedestroy($new);
		imagedestroy($img);
	}

	/**
	 * 获取执行信息
	 *
	 * @param int $id 执行出错编号
	 * @return string 执行提示语
	 */
	public function error_msg($id = -1)
	{
		$class_error = array(
			0  => 'There is no error, the file uploaded with success.',
			1  => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
			2  => 'The uploaded file exceeds the MAX_FILE_SIZE that was specified in the HTML form.',
			3  => 'The uploaded file was only partially uploaded. ',
			4  => 'No file was uploaded. ',
			6  => 'Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3. ',
			7  => 'Failed to write file to disk. Introduced in PHP 5.1.0. ',
			10 => 'Input name is not unavailable!',
			11 => 'The uploaded file is Unallowable file format!',
			12 => 'Directory unwritable or Directory is not exist!',
			13 => 'File exist already!',
			14 => 'File is too big!',
			15 => 'Delete file unsuccessfully!',
			16 => 'Your version of PHP does not appear to have GIF thumbnailing support.',
			17 => 'Your version of PHP does not appear to have JPEG thumbnailing support.',
			18 => 'Your version of PHP does not appear to have pictures thumbnailing support.',
			19 => 'An error occurred while attempting to copy the source image ',
			20 => 'An error occurred while attempting to create a new image.',
			21 => 'An error occurred while copying the source image to the thumbnail image.',
			22 => 'An error occurred while saving the thumbnail image to the filesystem. Are you sure that PHP has been configured with both read and write access on this folder?',
			23 => 'Watermark file is not exist.',
			24 => 'Watermark file is not picture file.'
		);
		if ($id == -1)
		{
			$id = $this->error_id;
		}

		return isset($class_error[$id]) ? $class_error[$id] : '';
	}
}
?>