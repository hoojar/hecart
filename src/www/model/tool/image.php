<?php
class ModelToolImage extends Model
{
	public function resize($filename, $width, $height)
	{
		if (empty($filename))
		{
			return '';
		}

		$mkey   = md5(__FUNCTION__ . $filename . $width . $height);
		$result = $this->mem_get($mkey);
		if (!empty($result))
		{
			return $result;
		}

		$info      = pathinfo($filename);
		$extension = $info['extension'];
		$old_image = $filename;
		$new_image = 'cache/' . mb_substr($filename, 0, mb_strrpos($filename, '.')) . '-' . $width . 'x' . $height . '.' . $extension;

		if (!file_exists(DIR_SITE . '/' . IMAGES_PATH . $filename) || !is_file(DIR_SITE . '/' . IMAGES_PATH . $filename))
		{
			return $this->registry->execdn($new_image, IMAGES_PATH);
		}

		if (!file_exists(DIR_SITE . '/' . IMAGES_PATH . $new_image) || (filemtime(DIR_SITE . '/' . IMAGES_PATH . $old_image) > filemtime(DIR_SITE . '/' . IMAGES_PATH . $new_image)))
		{
			$path        = '';
			$directories = explode('/', dirname(str_replace('../', '', $new_image)));
			foreach ($directories as $directory)
			{
				$path = $path . '/' . $directory;
				if (!file_exists(DIR_SITE . '/' . IMAGES_PATH . $path))
				{
					@mkdir(DIR_SITE . '/' . IMAGES_PATH . $path, 0777);
				}
			}

			list($width_orig, $height_orig) = getimagesize(DIR_SITE . '/' . IMAGES_PATH . $old_image);
			if ($width_orig != $width || $height_orig != $height)
			{
				$image = new Image(DIR_SITE . '/' . IMAGES_PATH . $old_image);
				$image->resize($width, $height);
				$image->save(DIR_SITE . '/' . IMAGES_PATH . $new_image);
			}
			else
			{
				copy(DIR_SITE . '/' . IMAGES_PATH . $old_image, DIR_SITE . '/' . IMAGES_PATH . $new_image);
			}
		}

		$result = $this->registry->execdn($new_image, IMAGES_PATH);
		$this->mem_set($mkey, $result);

		return $result;
	}
}
?>