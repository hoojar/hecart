<?php
class ModelToolBackup extends Model
{
	public function restore($sql)
	{
		foreach (explode(";\n", $sql) as $sql)
		{
			$sql = trim($sql);
			if ($sql)
			{
				$this->mdb()->query($sql);
			}
		}
	}

	public function getTables()
	{
		$db_servers = json_decode(DB_SERVERS, true);
		$dbname     = $db_servers['slave']['dbname'];
		unset($db_servers);
		$data = array();
		$rows = $this->sdb()->fetch_all("SHOW TABLE STATUS FROM `" . $dbname . "`");
		foreach ($rows as $v)
		{
			$data['tables'][]             = $v['Name'];
			$data['comments'][$v['Name']] = $v['Comment'];
		}

		return $data;
	}

	public function backup($tables)
	{
		$output = '';

		foreach ($tables as $table)
		{
			if (DB_PREFIX)
			{
				$status = (strpos($table, DB_PREFIX) === false) ? false : true;
			}
			else
			{
				$status = true;
			}

			if ($status)
			{
				$output .= 'TRUNCATE TABLE `' . $table . '`;' . "\n\n";
				$rows = $this->sdb()->fetch_all('SELECT * FROM `' . $table . "`");
				foreach ($rows as $result)
				{
					$fields = '';
					foreach (array_keys($result) as $value)
					{
						$fields .= '`' . $value . '`, ';
					}

					$values = '';
					foreach (array_values($result) as $value)
					{
						$value = str_replace(array(
							"\x00",
							"\x0a",
							"\x0d",
							"\x1a"
						), array(
							'\0',
							'\n',
							'\r',
							'\Z'
						), $value);
						$value = str_replace(array(
							"\n",
							"\r",
							"\t"
						), array(
							'\n',
							'\r',
							'\t'
						), $value);
						$value = str_replace('\\', '\\\\', $value);
						$value = str_replace('\'', '\\\'', $value);
						$value = str_replace('\\\n', '\n', $value);
						$value = str_replace('\\\r', '\r', $value);
						$value = str_replace('\\\t', '\t', $value);
						$values .= '\'' . $value . '\', ';
					}

					$output .= 'INSERT INTO `' . $table . '` (' . preg_replace('/, $/', '', $fields) . ') VALUES (' . preg_replace('/, $/', '', $values) . ');' . "\n";
				}

				$output .= "\n\n";
			}
		}

		return $output;
	}
}
?>