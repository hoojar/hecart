<?php
class ModelToolImage extends Model
{
	public function resize($filename, $width, $height)
	{
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

		if (!file_exists(DIR_IMAGE . $filename) || !is_file(DIR_IMAGE . $filename))
		{
			return $this->registry->execdn($new_image, IMAGES_PATH);
		}

		if (!file_exists(DIR_IMAGE . $new_image) || (filemtime(DIR_IMAGE . $old_image) > filemtime(DIR_IMAGE . $new_image)))
		{
			$path        = '';
			$directories = explode('/', dirname(str_replace('../', '', $new_image)));

			foreach ($directories as $directory)
			{
				$path = $path . '/' . $directory;
				if (!file_exists(DIR_IMAGE . $path))
				{
					@mkdir(DIR_IMAGE . $path, 0777);
				}
			}

			$image = new Image(DIR_IMAGE . $old_image);
			$image->resize($width, $height);
			$image->save(DIR_IMAGE . $new_image);
		}

		$result = $this->registry->execdn($new_image, IMAGES_PATH);
		$this->mem_set($mkey, $result);

		return $result;
	}
}
?>