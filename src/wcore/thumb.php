<?php
/**
 * 慧佳工作室 -> hoojar studio
 *
 * 模块: wcore/thumb.php
 * 简述: 将图片生成缩略图
 * 作者: woods·zhang  ->  hoojar@163.com -> http://www.hecart.com/
 * 版本: $Id: thumb.php 1150 2017-07-24 02:10:33Z zhangsl $
 *
 * 版权 2006-2018, 慧佳工作室拥有此系统所有版权等知识产权
 * Copyright 2006-2018, Hoojar Studio All Rights Reserved.
 */
class wcore_thumb
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
	 * 构造函数
	 *
	 * @param string  $save_path   文件保存路径
	 * @param array   $file_format 文件格式限制数组
	 * @param boolean $overwrite   是否覆盖true允许覆盖 flase 禁止覆盖
	 */
	public function __construct($save_path, $file_format = null, $overwrite = false)
	{
		$this->set_save_path($save_path); //设置存储路径

		//设置文件格式限定@param $file_format 文件格式数组
		if (!empty($file_format) && is_array($file_format))
		{
			$this->_ext_type = $file_format;
		}
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
	 * @param string $file        图片文件完整路径与文件名
	 * @param string $change_name 新文件名
	 * @return boolean 是否缩略成功为true失败为false
	 */
	public function run($file, $change_name = 'default')
	{
		/**
		 * 判断文件是否存在,没有的话则返回false
		 */
		$this->file = '';
		if (empty($file) || !file_exists($file))
		{
			$this->error_id = 13;

			return false;
		}
		$this->file = $file;

		/**
		 * 获取上传文件的扩展名与分配新的存储文件名
		 */
		$this->_set_filename($this->file, $change_name);

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

		return $this->make_thumb(); //生成缩略图
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
		$original_width  = imagesx($original);
		$original_height = imagesy($original);

		/**
		 * 组合缩略图相关文件与路径
		 */
		$this->thumb_file_name = $this->_thumb_prefix . $this->file_name; //缩略图相对路径与文件名
		$this->thumb_file      = $this->_save_path . $this->_thumb_prefix . $this->file_name; //缩略图文件全路径

		/**
		 * 判断文件是否已经存在
		 */
		if (file_exists($this->thumb_file) && !$this->overwrite)
		{
			$this->error_id = 13;

			return false;
		}

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
	 * @param string $fname       文件名
	 * @param        $change_name 要改变的名字
	 */
	private function _set_filename($fname, $change_name)
	{
		$this->_get_ext($fname);
		switch (strtolower($change_name))
		{
			case 'default': //设置上传文件名为自身的名称
				$name = basename($fname);
				break;
			case 'auto': //系统自动生成连续的以时间为名称
				$name = date('YmdHis') . '_' . rand(100, 999) . '.' . $this->file_ext;
				break;
			default: //用户指定的名字
				$name = "{$change_name}.{$this->file_ext}";
				break;
		}
		$this->file_name = $name; //仅仅文件名称与扩展名
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