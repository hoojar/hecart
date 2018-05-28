<?php
class ModelSettingLanguage extends Model
{
	public function addLanguage($data)
	{
		$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "language SET name = '{$data['name']}', code = '{$data['code']}', locale = '{$data['locale']}', directory = '{$data['directory']}', filename = '{$data['filename']}', image = '{$data['image']}', sort_order = '{$data['sort_order']}', status = '{$data['status']}'");
		$language_id = $this->mdb()->insert_id();

		// Attribute
		$rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "attribute_description WHERE language_id = '{$this->language_id}'");
		foreach ($rows as $attribute)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "attribute_description SET attribute_id = '{$attribute['attribute_id']}', language_id = '{$language_id}', name = '{$attribute['name']}'");
		}

		// Attribute Group
		$rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "attribute_group_description WHERE language_id = '{$this->language_id}'");
		foreach ($rows as $attribute_group)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "attribute_group_description SET attribute_group_id = '{$attribute_group['attribute_group_id']}', language_id = '{$language_id}', name = '{$attribute_group['name']}'");
		}

		// Banner
		$rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "banner_image_description WHERE language_id = '{$this->language_id}'");
		foreach ($rows as $banner_image)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "banner_image_description SET banner_image_id = '{$banner_image['banner_image_id']}', banner_id = '{$banner_image['banner_id']}', language_id = '{$language_id}', title = '{$banner_image['title']}'");
		}

		// Category
		$rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "category_description WHERE language_id = '{$this->language_id}'");
		foreach ($rows as $category)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "category_description SET category_id = '{$category['category_id']}', language_id = '{$language_id}', name = '{$category['name']}', meta_description = '{$category['meta_description']}', meta_keyword = '{$category['meta_keyword']}', description = '{$category['description']}'");
		}

		// Customer Group
		$rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "customer_group_description WHERE language_id = '{$this->language_id}'");
		foreach ($rows as $customer_group)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "customer_group_description SET customer_group_id = '{$customer_group['customer_group_id']}', language_id = '{$language_id}', name = '{$customer_group['name']}', description = '{$customer_group['description']}'");
		}

		// Download
		$rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "download_description WHERE language_id = '{$this->language_id}'");
		foreach ($rows as $download)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "download_description SET download_id = '{$download['download_id']}', language_id = '{$language_id}', name = '{$download['name']}'");
		}

		// Information
		$rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "information_description WHERE language_id = '{$this->language_id}'");
		foreach ($rows as $information)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "information_description SET information_id = '{$information['information_id']}', language_id = '{$language_id}', title = '{$information['title']}', description = '{$information['description']}'");
		}

		// Information Group
		$rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "information_group_description WHERE language_id = '{$this->language_id}'");
		foreach ($rows as $information_group)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "information_group_description SET information_group_id = '{$information_group['information_group_id']}', language_id = '{$language_id}', name = '{$information_group['name']}'");
		}

		// Length
		$rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "length_class_description WHERE language_id = '{$this->language_id}'");
		foreach ($rows as $length)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "length_class_description SET length_class_id = '{$length['length_class_id']}', language_id = '{$language_id}', title = '{$length['title']}', unit = '{$length['unit']}'");
		}

		// Option
		$rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "option_description WHERE language_id = '{$this->language_id}'");
		foreach ($rows as $option)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "option_description SET option_id = '{$option['option_id']}', language_id = '{$language_id}', name = '{$option['name']}'");
		}

		// Option Value
		$rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "option_value_description WHERE language_id = '{$this->language_id}'");
		foreach ($rows as $option_value)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "option_value_description SET option_value_id = '{$option_value['option_value_id']}', language_id = '{$language_id}', option_id = '{$option_value['option_id']}', name = '{$option_value['name']}'");
		}

		// Order Status
		$rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "order_status WHERE language_id = '{$this->language_id}'");
		foreach ($rows as $order_status)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "order_status SET order_status_id = '{$order_status['order_status_id']}', language_id = '{$language_id}', name = '{$order_status['name']}'");
		}

		// Product
		$rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "product_description WHERE language_id = '{$this->language_id}'");
		foreach ($rows as $product)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "product_description SET product_id = '{$product['product_id']}', language_id = '{$language_id}', name = '{$product['name']}', meta_description = '{$product['meta_description']}', meta_keyword = '{$product['meta_keyword']}', description = '{$product['description']}', tag = '{$product['tag']}'");
		}

		// Product Attribute
		$rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "product_attribute WHERE language_id = '{$this->language_id}'");
		foreach ($rows as $product_attribute)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "product_attribute SET product_id = '{$product_attribute['product_id']}', attribute_id = '{$product_attribute['attribute_id']}', language_id = '{$language_id}', text = '{$product_attribute['text']}'");
		}

		// Return Action
		$rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "return_action WHERE language_id = '{$this->language_id}'");
		foreach ($rows as $return_action)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "return_action SET return_action_id = '{$return_action['return_action_id']}', language_id = '{$language_id}', name = '{$return_action['name']}'");
		}

		// Return Reason
		$rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "return_reason WHERE language_id = '{$this->language_id}'");
		foreach ($rows as $return_reason)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "return_reason SET return_reason_id = '{$return_reason['return_reason_id']}', language_id = '{$language_id}', name = '{$return_reason['name']}'");
		}

		// Return Status
		$rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "return_status WHERE language_id = '{$this->language_id}'");
		foreach ($rows as $return_status)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "return_status SET return_status_id = '{$return_status['return_status_id']}', language_id = '{$language_id}', name = '{$return_status['name']}'");
		}

		// Stock Status
		$rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "stock_status WHERE language_id = '{$this->language_id}'");
		foreach ($rows as $stock_status)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "stock_status SET stock_status_id = '{$stock_status['stock_status_id']}', language_id = '{$language_id}', name = '{$stock_status['name']}'");
		}

		// Voucher Theme
		$rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "voucher_theme_description WHERE language_id = '{$this->language_id}'");
		foreach ($rows as $voucher_theme)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "voucher_theme_description SET voucher_theme_id = '{$voucher_theme['voucher_theme_id']}', language_id = '{$language_id}', name = '{$voucher_theme['name']}'");
		}

		// Weight Class
		$rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "weight_class_description WHERE language_id = '{$this->language_id}'");
		foreach ($rows as $weight_class)
		{
			$this->mdb()->query('INSERT INTO ' . DB_PREFIX . "weight_class_description SET weight_class_id = '{$weight_class['weight_class_id']}', language_id = '{$language_id}', title = '{$weight_class['title']}', unit = '{$weight_class['unit']}'");
		}

		$this->mem->flush();
	}

	public function editLanguage($language_id, $data)
	{
		$this->mdb()->query('UPDATE ' . DB_PREFIX . "language SET name = '{$data['name']}', code = '{$data['code']}', locale = '{$data['locale']}', directory = '{$data['directory']}', filename = '{$data['filename']}', image = '{$data['image']}', sort_order = '{$data['sort_order']}', status = '{$data['status']}' WHERE language_id = '{$language_id}'");
	}

	public function deleteLanguage($language_id)
	{
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "language WHERE language_id = '{$language_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "attribute_description WHERE language_id = '{$language_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "attribute_group_description WHERE language_id = '{$language_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "banner_image_description WHERE language_id = '{$language_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "category_description WHERE language_id = '{$language_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "customer_group_description WHERE language_id = '{$language_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "download_description WHERE language_id = '{$language_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "information_description WHERE language_id = '{$language_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "information_group_description WHERE language_id = '{$language_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "length_class_description WHERE language_id = '{$language_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "option_description WHERE language_id = '{$language_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "option_value_description WHERE language_id = '{$language_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "order_status WHERE language_id = '{$language_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "product_attribute WHERE language_id = '{$language_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "product_description WHERE language_id = '{$language_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "return_action WHERE language_id = '{$language_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "return_reason WHERE language_id = '{$language_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "return_status WHERE language_id = '{$language_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "stock_status WHERE language_id = '{$language_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "voucher_theme_description WHERE language_id = '{$language_id}'");
		$this->mdb()->query('DELETE FROM ' . DB_PREFIX . "weight_class_description WHERE language_id = '{$language_id}'");
		$this->mem->flush();
	}

	public function getLanguage($language_id)
	{
		return $this->mem_sql('SELECT DISTINCT * FROM ' . DB_PREFIX . "language WHERE language_id = '{$language_id}'");
	}

	public function getLanguages($data = array())
	{
		if ($data)
		{
			$sql       = 'SELECT * FROM ' . DB_PREFIX . "language";
			$sort_data = array(
				'name',
				'code',
				'sort_order'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data))
			{
				$sql .= " ORDER BY {$data['sort']}";
			}
			else
			{
				$sql .= " ORDER BY sort_order, name";
			}

			$sql .= (isset($data['order']) && ($data['order'] == 'DESC')) ? ' DESC' : ' ASC';
			$sql .= (isset($data['start']) && isset($data['limit'])) ? " LIMIT {$data['start']}, {$data['limit']}" : '';

			return $this->sdb()->fetch_all($sql);
		}

		$language_data = $this->mem_get('language');
		if (!$language_data)
		{
			$language_data = array();
			$language_rows = $this->sdb()->fetch_all('SELECT * FROM ' . DB_PREFIX . "language ORDER BY sort_order, name");
			foreach ($language_rows as $result)
			{
				$language_data[$result['code']] = array(
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

	public function getTotalLanguages()
	{
		return $this->mem_sql('SELECT COUNT(*) AS total FROM ' . DB_PREFIX . "language", DB_GET_ONE);
	}
}
?>