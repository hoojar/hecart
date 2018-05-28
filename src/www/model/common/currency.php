<?php
class ModelLocalisationCurrency extends Model
{
	public function getCurrencyByCode($currency)
	{
		return $this->sdb()->fetch_row('SELECT DISTINCT * FROM ' . DB_PREFIX . "currency WHERE code = '{$currency}'");
	}

	public function getCurrencies()
	{
		$currency_data = $this->mem_get('currency');
		if (!$currency_data)
		{
			$currency_data = array();
			$currency_rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "currency ORDER BY title ASC");
			foreach ($currency_rows as $result)
			{
				$currency_data[$result['code']] = array(
					'currency_id'   => $result['currency_id'],
					'title'         => $result['title'],
					'code'          => $result['code'],
					'symbol_left'   => $result['symbol_left'],
					'symbol_right'  => $result['symbol_right'],
					'decimal_place' => $result['decimal_place'],
					'value'         => $result['value'],
					'status'        => $result['status'],
					'date_modified' => $result['date_modified']
				);
			}

			$this->mem_set('currency', $currency_data);
		}

		return $currency_data;
	}
}
?>