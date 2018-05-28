<?php
class ModelCommonZone extends Model
{
	public function getZone($zone_id)
	{
		return $this->sdb()->fetch_row('SELECT * FROM ' . DB_PREFIX . "zone WHERE zone_id = '{$zone_id}' AND status = '1'");
	}

	public function getZonesByCountryId($country_id)
	{
		return $this->sdb()->fetch_all("SELECT zone_id, name FROM " . DB_PREFIX . "zone WHERE country_id = '{$country_id}' AND status = '1'");
	}

	public function getCitysByZoneId($zone_id)
	{
		return $this->sdb()->fetch_all("SELECT city_id, name FROM " . DB_PREFIX . "city WHERE zone_id = '{$zone_id}' AND status = '1'");
	}
}
?>