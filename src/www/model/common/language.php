<?php
class ModelLocalisationLanguage extends Model
{
	public function getLanguage($language_id)
	{
		return $this->sdb()->fetch_row('SELECT * FROM ' . DB_PREFIX . "language WHERE language_id = '{$language_id}'");
	}

	public function getLanguages()
	{
		$language_data = $this->mem_get('language');
		if (!$language_data)
		{
			$language_data = array();
			$language_rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "language ORDER BY sort_order, name");
			foreach ($language_rows as $result)
			{
				$language_data[$result['language_id']] = array(
					'language_id' => $result['language_id'],
					'name'        => $result['name'],
					'code'        => $result['code'],
					'locale'      => $result['locale'],
					'image'       => $result['image'],
					'directory'   => $result['directory'],
					'filename'    => $result['filename'],
					'sort_order'  => $result['sort_order'],
					'status'      => $result['status']
				);
			}

			$this->mem_set('language', $language_data);
		}

		return $language_data;
	}
}
?>