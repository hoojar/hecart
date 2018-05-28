<?php
class ModelCommonCountry extends Model
{
	public function getCountry($country_id)
	{
		return $this->sdb()->fetch_row('SELECT * FROM ' . DB_PREFIX . "country WHERE country_id = '{$country_id}' AND status = '1'");
	}

	public function getCountries()
	{
		$country_data = $this->mem_get('country.status');
		if (!$country_data)
		{
			$country_data = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "country WHERE status = '1' ORDER BY name ASC");
			$this->mem_set('country.status', $country_data);
		}

		return $country_data;
	}
}
?>