<?php
class ControllerCommonArea extends Controller
{
	/**
	 * 获取国家下的区域信息
	 *
	 */
	public function country()
	{
		$country_id = $this->request->get_var('country_id', 'i');
		$mkey       = __CLASS__ . __FUNCTION__ . $country_id;
		$res        = $this->mem_get($mkey);

		if (empty($res))
		{
			$json = array();
			$this->registry->model('common/country');
			$country_info = $this->model_common_country->getCountry($country_id);
			if ($country_info)
			{
				$this->registry->model('common/zone');
				$json = array(
					'country_id'        => $country_info['country_id'],
					'name'              => $country_info['name'],
					'iso_code_2'        => $country_info['iso_code_2'],
					'iso_code_3'        => $country_info['iso_code_3'],
					'address_format'    => $country_info['address_format'],
					'postcode_required' => $country_info['postcode_required'],
					'zone'              => $this->model_common_zone->getZonesByCountryId($country_id),
					'status'            => $country_info['status']
				);
			}

			$res = json_encode($json);
			$this->mem_set($mkey, $res);
		}

		return $res;
	}

	/**
	 * 获取区域下的城市信息
	 *
	 */
	public function zone()
	{
		$zone_id = $this->request->get_var('zone_id', 'i');
		$mkey    = __CLASS__ . __FUNCTION__ . $zone_id;
		$res     = $this->mem_get($mkey);

		if (empty($res))
		{
			$this->registry->model('common/zone');
			$json = $this->model_common_zone->getCitysByZoneId($zone_id);
			$res  = json_encode($json);
			$this->mem_set($mkey, $res);
		}

		return ($res);
	}
}
?>