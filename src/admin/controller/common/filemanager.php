<?php
class ControllerCommonFileManager extends Controller
{
	private $error = array();

	public function index()
	{
		$this->registry->language('common/filemanager');
		$vrs['title']             = $this->language->get('heading_title');
		$vrs['entry_folder']      = $this->language->get('entry_folder');
		$vrs['entry_move']        = $this->language->get('entry_move');
		$vrs['entry_copy']        = $this->language->get('entry_copy');
		$vrs['entry_rename']      = $this->language->get('entry_rename');
		$vrs['button_folder']     = $this->language->get('button_folder');
		$vrs['button_delete']     = $this->language->get('button_delete');
		$vrs['button_move']       = $this->language->get('button_move');
		$vrs['button_copy']       = $this->language->get('button_copy');
		$vrs['button_rename']     = $this->language->get('button_rename');
		$vrs['button_upload']     = $this->language->get('button_upload');
		$vrs['button_refresh']    = $this->language->get('button_refresh');
		$vrs['button_submit']     = $this->language->get('button_submit');
		$vrs['wmark_title']       = $this->language->get('wmark_title');
		$vrs['wmark_none']        = $this->language->get('wmark_none');
		$vrs['wmark_center']      = $this->language->get('wmark_center');
		$vrs['wmark_topleft']     = $this->language->get('wmark_topleft');
		$vrs['wmark_topright']    = $this->language->get('wmark_topright');
		$vrs['wmark_bottomleft']  = $this->language->get('wmark_bottomleft');
		$vrs['wmark_bottomright'] = $this->language->get('wmark_bottomright');
		$vrs['error_select']      = $this->language->get('error_select');
		$vrs['error_directory']   = $this->language->get('error_directory');
		$vrs['directory']         = $this->registry->execdn('', IMAGES_PATH);

		$this->registry->model('tool/image');
		$vrs['no_image']  = $this->model_tool_image->resize('no_image.jpg', 100, 100);
		$vrs['field']     = isset($this->request->get['field']) ? $this->request->get['field'] : '';
		$vrs['fckeditor'] = isset($this->request->get['CKEditorFuncNum']) ? $this->request->get['CKEditorFuncNum'] : false;

		return $this->view('template/filemanager.tpl', $vrs);
	}

	/**
	 * 获取文件夹
	 */
	public function directory()
	{
		$json = array();
		if (isset($this->request->post['directory']))
		{
			$directories = glob(rtrim(DIR_IMAGE . 'data/' . str_replace('../', '', $this->request->post['directory']), '/') . '/*', GLOB_ONLYDIR);
			if ($directories)
			{
				$i = 0;
				foreach ($directories as $directory)
				{
					$json[$i]['data']                    = basename($directory);
					$json[$i]['attributes']['directory'] = mb_substr($directory, strlen(DIR_IMAGE . 'data/'));

					$children = glob(rtrim($directory, '/') . '/*', GLOB_ONLYDIR);
					if ($children)
					{
						$json[$i]['children'] = ' ';
					}

					$i++;
				}
			}
		}

		return json_encode($json);
	}

	/**
	 * 获取文件
	 */
	public function files()
	{
		$json = array();
		if (!empty($this->request->post['directory']))
		{
			$directory = DIR_IMAGE . 'data/' . str_replace('../', '', $this->request->post['directory']);
		}
		else
		{
			$directory = DIR_IMAGE . 'data/';
		}

		$allowed = array(
			'.jpg',
			'.jpeg',
			'.png',
			'.gif'
		);

		$this->registry->model('tool/image');
		$files = glob(rtrim($directory, '/') . '/*');
		if ($files)
		{
			foreach ($files as $file)
			{
				$ext = '';
				if (is_file($file))
				{
					$ext = strrchr($file, '.');
				}

				if (in_array(strtolower($ext), $allowed))
				{
					$i      = 0;
					$size   = filesize($file);
					$suffix = array(
						'B',
						'KB',
						'MB',
						'GB',
						'TB',
						'PB',
						'EB',
						'ZB',
						'YB'
					);

					while (($size / 1024) > 1)
					{
						$size = $size / 1024;
						$i++;
					}

					$sfile  = mb_substr($file, mb_strlen(DIR_IMAGE));
					$json[] = array(
						'filename' => basename($file),
						'file'     => mb_substr($sfile, 5),
						'url'      => $this->model_tool_image->resize($sfile, 100, 100),
						'size'     => round(mb_substr($size, 0, mb_strpos($size, '.') + 4), 2) . $suffix[$i]
					);
				}
			}
		}

		return json_encode($json);
	}

	/**
	 * 创建文件夹
	 */
	public function create()
	{
		$json = array();
		$this->registry->language('common/filemanager');

		if (!$this->user->hasPermission('modify', 'common/filemanager'))
		{
			$json['error'] = $this->language->get('error_permission');
		}

		if (isset($this->request->post['directory']))
		{
			if (isset($this->request->post['name']) || $this->request->post['name'])
			{
				$directory = rtrim(DIR_IMAGE . 'data/' . str_replace('../', '', $this->request->post['directory']), '/');
				if (!is_dir($directory))
				{
					$json['error'] = $this->language->get('error_directory');
				}

				if (file_exists($directory . '/' . str_replace('../', '', $this->request->post['name'])))
				{
					$json['error'] = $this->language->get('error_exists');
				}

				if (!isset($json['error']))
				{
					mkdir($directory . '/' . str_replace('../', '', $this->request->post['name']), 0777);
					$json['success'] = $this->language->get('text_create');
				}
			}
			else
			{
				$json['error'] = $this->language->get('error_name');
			}
		}
		else
		{
			$json['error'] = $this->language->get('error_directory');
		}

		return json_encode($json);
	}

	/**
	 * 删除文件夹或文件
	 */
	public function delete()
	{
		$json = array();
		$this->registry->language('common/filemanager');

		if (!$this->user->hasPermission('modify', 'common/filemanager'))
		{
			$json['error'] = $this->language->get('error_permission');
		}

		if (isset($this->request->post['path']))
		{
			$path = rtrim(DIR_IMAGE . 'data/' . str_replace('../', '', html_entity_decode($this->request->post['path'], ENT_QUOTES, 'UTF-8')), '/');
			if (!file_exists($path))
			{
				$json['error'] = $this->language->get('error_select');
			}

			if ($path == rtrim(DIR_IMAGE . 'data/', '/'))
			{
				$json['error'] = $this->language->get('error_delete');
			}

			if (!isset($json['error']))
			{
				if (is_file($path))
				{
					unlink($path);
				}
				elseif (is_dir($path))
				{
					$this->recursiveDelete($path);
				}

				$json['success'] = $this->language->get('text_delete');
			}
		}
		else
		{
			$json['error'] = $this->language->get('error_select');
		}

		return json_encode($json);
	}

	/**
	 * 递归删除
	 *
	 * @param $directory
	 * @return bool
	 */
	protected function recursiveDelete($directory)
	{
		if (is_dir($directory))
		{
			$handle = opendir($directory);
			if (!$handle)
			{
				return false;
			}
		}
		else
		{
			return false;
		}

		while (false !== ($file = readdir($handle)))
		{
			if ($file != '.' && $file != '..')
			{
				if (!is_dir($directory . '/' . $file))
				{
					unlink($directory . '/' . $file);
				}
				else
				{
					$this->recursiveDelete($directory . '/' . $file);
				}
			}
		}

		closedir($handle);
		rmdir($directory);

		return true;
	}

	/**
	 * 移动文件夹或文件
	 */
	public function move()
	{
		$json = array();
		$this->registry->language('common/filemanager');

		if (!$this->user->hasPermission('modify', 'common/filemanager'))
		{
			$json['error'] = $this->language->get('error_permission');
		}

		if (isset($this->request->post['from']) && isset($this->request->post['to']))
		{
			$from = rtrim(DIR_IMAGE . 'data/' . str_replace('../', '', html_entity_decode($this->request->post['from'], ENT_QUOTES, 'UTF-8')), '/');
			if (!file_exists($from))
			{
				$json['error'] = $this->language->get('error_missing');
			}

			if ($from == DIR_IMAGE . 'data')
			{
				$json['error'] = $this->language->get('error_default');
			}

			$to = rtrim(DIR_IMAGE . 'data/' . str_replace('../', '', html_entity_decode($this->request->post['to'], ENT_QUOTES, 'UTF-8')), '/');
			if (!file_exists($to))
			{
				$json['error'] = $this->language->get('error_move');
			}

			if (file_exists($to . '/' . basename($from)))
			{
				$json['error'] = $this->language->get('error_exists');
			}

			if (!isset($json['error']))
			{
				rename($from, $to . '/' . basename($from));
				$json['success'] = $this->language->get('text_move');
			}
		}
		else
		{
			$json['error'] = $this->language->get('error_directory');
		}

		return json_encode($json);
	}

	/**
	 * 复制文件夹或文件
	 */
	public function copy()
	{
		$json = array();
		$this->registry->language('common/filemanager');

		if (!$this->user->hasPermission('modify', 'common/filemanager'))
		{
			$json['error'] = $this->language->get('error_permission');
		}

		if (isset($this->request->post['path']) && isset($this->request->post['name']))
		{
			if ((mb_strlen($this->request->post['name']) < 3) || (mb_strlen($this->request->post['name']) > 255))
			{
				$json['error'] = $this->language->get('error_filename');
			}

			$old_name = rtrim(DIR_IMAGE . 'data/' . str_replace('../', '', html_entity_decode($this->request->post['path'], ENT_QUOTES, 'UTF-8')), '/');
			if (!file_exists($old_name) || $old_name == DIR_IMAGE . 'data')
			{
				$json['error'] = $this->language->get('error_copy');
			}

			$ext = '';
			if (is_file($old_name))
			{
				$ext = strrchr($old_name, '.');
			}

			$new_name = dirname($old_name) . '/' . str_replace('../', '', html_entity_decode($this->request->post['name'], ENT_QUOTES, 'UTF-8') . $ext);
			if (file_exists($new_name))
			{
				$json['error'] = $this->language->get('error_exists');
			}

			if (!isset($json['error']))
			{
				if (is_file($old_name))
				{
					copy($old_name, $new_name);
				}
				else
				{
					$this->recursiveCopy($old_name, $new_name);
				}

				$json['success'] = $this->language->get('text_copy');
			}
		}
		else
		{
			$json['error'] = $this->language->get('error_select');
		}

		return json_encode($json);
	}

	/**
	 * 递归复制
	 *
	 * @param $source
	 * @param $destination
	 */
	function recursiveCopy($source, $destination)
	{
		$directory = opendir($source);
		@mkdir($destination);

		while (false !== ($file = readdir($directory)))
		{
			if (($file != '.') && ($file != '..'))
			{
				if (is_dir($source . '/' . $file))
				{
					$this->recursiveCopy($source . '/' . $file, $destination . '/' . $file);
				}
				else
				{
					copy($source . '/' . $file, $destination . '/' . $file);
				}
			}
		}

		closedir($directory);
	}

	/**
	 * 列表文件夹
	 */
	public function folders()
	{
		return ($this->recursiveFolders(DIR_IMAGE . 'data/'));
	}

	protected function recursiveFolders($directory)
	{
		$output = '';
		$output .= '<option value="' . mb_substr($directory, strlen(DIR_IMAGE . 'data/')) . '">' . mb_substr($directory, strlen(DIR_IMAGE . 'data/')) . '</option>';
		$directories = glob(rtrim(str_replace('../', '', $directory), '/') . '/*', GLOB_ONLYDIR);
		foreach ($directories as $directory)
		{
			$output .= $this->recursiveFolders($directory);
		}

		return $output;
	}

	/**
	 * 重命名文件夹或文件
	 */
	public function rename()
	{
		$json = array();
		$this->registry->language('common/filemanager');

		if (!$this->user->hasPermission('modify', 'common/filemanager'))
		{
			$json['error'] = $this->language->get('error_permission');
		}

		if (isset($this->request->post['path']) && isset($this->request->post['name']))
		{
			if ((mb_strlen($this->request->post['name']) < 2) || (mb_strlen($this->request->post['name']) > 255))
			{
				$json['error'] = $this->language->get('error_filename');
			}

			$old_name = rtrim(DIR_IMAGE . 'data/' . str_replace('../', '', html_entity_decode($this->request->post['path'], ENT_QUOTES, 'UTF-8')), '/');
			if (!file_exists($old_name) || $old_name == DIR_IMAGE . 'data')
			{
				$json['error'] = $this->language->get('error_rename');
			}

			$ext = '';
			if (is_file($old_name))
			{
				$ext = strrchr($old_name, '.');
			}

			$new_name = dirname($old_name) . '/' . str_replace('../', '', html_entity_decode($this->request->post['name'], ENT_QUOTES, 'UTF-8') . $ext);
			if (file_exists($new_name))
			{
				$json['error'] = $this->language->get('error_exists');
			}

			if (!isset($json['error']))
			{
				rename($old_name, $new_name);
				$json['success'] = $this->language->get('text_rename');
			}
		}

		return json_encode($json);
	}

	/**
	 * 上传文件
	 */
	public function upload()
	{
		$json = array();
		$this->registry->language('common/filemanager');
		if (!$this->user->hasPermission('modify', 'common/filemanager'))
		{
			$json['error'] = $this->language->get('error_permission');
		}

		if (isset($this->request->post['directory']))
		{
			$directory = rtrim(DIR_IMAGE . 'data/' . str_replace('../', '', $this->request->post['directory']), '/');
			if (!is_dir($directory))
			{
				$json['error'] = $this->language->get('error_directory');
			}

			if (!isset($json['error']) && isset($this->request->files['image']) && $this->request->files['image']['tmp_name'])
			{
				$img_num = count($this->request->files['image']['tmp_name']);
				$maxsize = floatval($this->config->get('config_image_maxsize')) * 1024;
				for ($i = 0; $i < $img_num; $i++)
				{
					$filename = basename(html_entity_decode($this->request->files['image']['name'][$i], ENT_QUOTES, 'UTF-8'));
					if ((strlen($filename) < 3) || (strlen($filename) > 255))
					{
						$json['error'] = $this->language->get('error_filename');
					}

					if ($this->request->files['image']['size'][$i] > $maxsize)
					{
						$json['error'] = $this->language->get('error_file_size') . ($maxsize / 1024) . ' KB';
					}

					$allowed = array(
						'image/jpeg',
						'image/pjpeg',
						'image/png',
						'image/x-png',
						'image/gif',
						'application/x-shockwave-flash'
					);

					if (!in_array($this->request->files['image']['type'][$i], $allowed))
					{
						$json['error'] = $this->language->get('error_file_type');
					}

					$allowed = array(
						'.jpg',
						'.jpeg',
						'.gif',
						'.png',
						'.flv'
					);

					if (!in_array(strtolower(strrchr($filename, '.')), $allowed))
					{
						$json['error'] = $this->language->get('error_file_type');
					}

					if ($this->request->files['image']['error'][$i] != UPLOAD_ERR_OK)
					{
						$json['error'] = 'error_upload_' . $this->request->files['image']['error'][$i];
					}

					if (!isset($json['error']))
					{
						if (@move_uploaded_file($this->request->files['image']['tmp_name'][$i], $directory . '/' . $filename))
						{
							/**
							 * 判断是否需要加水印
							 */
							$watermark = isset($this->request->post['watermark']) ? $this->request->post['watermark'] : '';
							if (!empty($watermark))
							{
								$this->registry->model('tool/image');
								$image = new Image($directory . '/' . $filename);
								$image->watermark(DIR_IMAGE . 'watermark.png', $watermark);
								$image->save($directory . '/' . $filename);
							}

							$json['success'] = $this->language->get('text_uploaded');
						}
						else
						{
							$json['error'] = $this->language->get('error_uploaded');
						}
					}
				}
			}
			else
			{
				$json['error'] = $this->language->get('error_file');
			}
		}
		else
		{
			$json['error'] = $this->language->get('error_directory');
		}

		return json_encode($json);
	}
}
?>