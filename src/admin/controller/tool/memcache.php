<?php
class ControllerToolMemcache extends Controller
{
	private $vrs = array();

	public function flush()
	{
		if (!$this->user->hasPermission('modify', 'tool/memcache'))
		{
			return $this->registry->exectrl('error/permission');
		}

		$this->mem->flush();
		$this->registry->redirect($this->url->link('common/home'));
	}

	public function index()
	{
		define('DATE_FORMAT', 'Y-m-d H:i:s');
		define('GRAPH_SIZE', 200);
		define('MAX_ITEM_DUMP', 50);
		$mem_servers            = json_decode(MEM_SERVERS, true);
		$GLOBALS['mem_servers'] = is_array($mem_servers) ? $mem_servers : array($mem_servers);

		// don't cache this page
		header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache"); // HTTP/1.0

		$_GET['op']          = !isset($_GET['op']) ? 1 : intval($_GET['op']);
		$GLOBALS['time']     = $time = time();
		$GLOBALS['self_url'] = $self_url = '/tool/memcache?';

		// sanitize _GET
		foreach ($_GET as $key => $g)
		{
			$_GET[$key] = htmlentities($g);
		}

		// singleout when singleout is set, it only gives details for that server.
		if (isset($_GET['singleout']) && $_GET['singleout'] >= 0 && $_GET['singleout'] < count($mem_servers))
		{
			$mem_servers            = array($mem_servers[$_GET['singleout']]);
			$GLOBALS['mem_servers'] = $mem_servers;
		}

		// display images
		if (isset($_GET['IMG']))
		{
			$this->displayImg($_GET['IMG']);
		}

		$this->vrs['menu']    = '';
		$this->vrs['content'] = '';
		$this->getMenu($self_url);

		//check modify permission
		if (intval($_GET['op']) >= 5)
		{
			if (!$this->user->hasPermission('modify', 'tool/memcache'))
			{
				return $this->registry->exectrl('error/permission');
			}
		}

		$this->execDoCommend(intval($_GET['op']));
		$this->document->title('Memcached Management 缓冲管理');
		$this->vrs['heading_title'] = 'Memcached Management 缓冲管理';

		/**
		 * 导航栏组合
		 */
		$this->vrs['breadcrumbs']   = array();
		$this->vrs['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
			'separator' => false
		);

		$this->vrs['breadcrumbs'][] = array(
			'text'      => $this->vrs['heading_title'],
			'href'      => $this->url->link('tool/memcache'),
			'separator' => ' :: '
		);

		/**
		 * 模板处理
		 */
		$this->vrs['page_header'] = $this->registry->exectrl('common/page_header');
		$this->vrs['page_footer'] = $this->registry->exectrl('common/page_footer');

		return $this->view('template/tool/memcache.tpl', $this->vrs);
	}

	private function execDoCommend($op)
	{
		global $mem_servers, $time, $self_url;

		switch ($op)
		{
			case 1: // host stats
				$phpversion          = phpversion();
				$memcacheStats       = $this->getMemcacheStats();
				$memcacheStatsSingle = $this->getMemcacheStats(false);

				$mem_size  = $memcacheStats['limit_maxbytes'];
				$mem_used  = $memcacheStats['bytes'];
				$mem_avail = $mem_size - $mem_used;
				$startTime = time() - array_sum($memcacheStats['uptime']);

				$curr_items  = $memcacheStats['curr_items'];
				$total_items = $memcacheStats['total_items'];
				$hits        = ($memcacheStats['get_hits'] == 0) ? 1 : $memcacheStats['get_hits'];
				$misses      = ($memcacheStats['get_misses'] == 0) ? 1 : $memcacheStats['get_misses'];
				$sets        = $memcacheStats['cmd_set'];

				$req_rate  = sprintf("%.2f", ($hits + $misses) / ($time - $startTime));
				$hit_rate  = sprintf("%.2f", ($hits) / ($time - $startTime));
				$miss_rate = sprintf("%.2f", ($misses) / ($time - $startTime));
				$set_rate  = sprintf("%.2f", ($sets) / ($time - $startTime));

				$this->vrs['content'] .= '<div class="info"><h2>General Cache Information</h2>';
				$this->vrs['content'] .= '<table class="list"><tbody><tr><td width="28%">PHP Version</td><td>' . $phpversion . '</td></tr>';
				$this->vrs['content'] .= "<tr><td>Memcached Host" . ((count($mem_servers) > 1) ? 's' : '') . "</td><td>";
				$i = 0;
				if (!isset($_GET['singleout']) && count($mem_servers) > 1)
				{
					foreach ($mem_servers as $server)
					{
						$this->vrs['content'] .= ($i + 1) . '. <a href="' . $self_url . '&singleout=' . $i++ . '">' . $server . '</a><br/>';
					}
				}
				else
				{
					$this->vrs['content'] .= '1.' . $mem_servers[0];
				}

				if (isset($_GET['singleout']))
				{
					$this->vrs['content'] .= '<a href="' . $self_url . '">(all servers)</a><br/>';
				}

				$this->vrs['content'] .= "</td></tr>\n";
				$this->vrs['content'] .= "<tr><td>Total Memcache Cache</td><td>" . $this->bsize($memcacheStats['limit_maxbytes']) . "</td></tr>\n";
				$this->vrs['content'] .= '</tbody></table></div><div class="info div1"><h2>Memcache Server Information</h2>';

				foreach ($mem_servers as $server)
				{
					$this->vrs['content'] .= '<table class="list"><tbody>';
					$this->vrs['content'] .= '<tr><td width="28%">' . $server . '</td><td><a href="' . $self_url . '&server=' . array_search($server, $mem_servers) . '&op=6">[<b>Flush this server</b>]</a></td></tr>';
					$this->vrs['content'] .= '<tr><td>Start Time</td><td>' . date(DATE_FORMAT, $memcacheStatsSingle[$server]['STAT']['time'] - $memcacheStatsSingle[$server]['STAT']['uptime']) . '</td></tr>';
					$this->vrs['content'] .= '<tr><td>Uptime</td><td>' . $this->duration($memcacheStatsSingle[$server]['STAT']['time'] - $memcacheStatsSingle[$server]['STAT']['uptime']) . '</td></tr>';
					$this->vrs['content'] .= '<tr><td>Memcached Server Version</td><td>' . $memcacheStatsSingle[$server]['STAT']['version'] . '</td></tr>';
					$this->vrs['content'] .= '<tr><td>Used Cache Size</td><td>' . $this->bsize($memcacheStatsSingle[$server]['STAT']['bytes']) . '</td></tr>';
					$this->vrs['content'] .= '<tr><td>Total Cache Size</td><td>' . $this->bsize($memcacheStatsSingle[$server]['STAT']['limit_maxbytes']) . '</td></tr>';
					$this->vrs['content'] .= '</tbody></table>';
				}

				$this->vrs['content'] .= '</div><div class="graph div3"><h2>Host Status Diagrams</h2><table class="list"><tbody>';
				$size = 'width=' . (GRAPH_SIZE + 50) . ' height=' . (GRAPH_SIZE + 10);
				$this->vrs['content'] .= '<tr><td>Cache Usage</td><td>Hits &amp; Misses</td></tr>';

				if ($this->graphics_avail())
				{
					$this->vrs['content'] .= "<tr><td><img {$size} src=\"{$self_url}&IMG=1&" . (isset($_GET['singleout']) ? 'singleout = ' . $_GET['singleout'] . ' & ' : '') . "{$time}\"></td><td><img {$size} src=\"{$self_url}&IMG=2&" . (isset($_GET['singleout']) ? 'singleout = ' . $_GET['singleout'] . ' & ' : '') . "{$time}\"></td></tr>\n";
				}

				$this->vrs['content'] .= '<tr><td><span class="green box" >&nbsp; </span>　Free: ' . $this->bsize($mem_avail) . sprintf(" (%.1f%%)", $mem_avail * 100 / $mem_size) . "</td>\n" . '<td><span class="green box" >&nbsp; </span>　Hits: ' . $hits . sprintf(" (%.1f%%)", $hits * 100 / ($hits + $misses)) . "</td>\n";
				$this->vrs['content'] .= '</tr><tr><td><span class="red box" >&nbsp; </span>　Used: ' . $this->bsize($mem_used) . sprintf(" (%.1f%%)", $mem_used * 100 / $mem_size) . "</td>\n" . '<td><span class="red box" >&nbsp; </span>　Misses: ' . $misses . sprintf(" (%.1f%%)", $misses * 100 / ($hits + $misses)) . "</td>\n";
				$this->vrs['content'] .= "</tr></tbody></table>";

				$this->vrs['content'] .= "<div class=\"info\"><h2>Cache Information</h2>";
				$this->vrs['content'] .= "<table class=\"list\"><tbody>";
				$this->vrs['content'] .= "<tr><td>Current Items(total)</td><td>$curr_items ({$total_items})</td></tr>";
				$this->vrs['content'] .= "<tr><td>Hits</td><td>{$hits}</td></tr>";
				$this->vrs['content'] .= "<tr><td>Misses</td><td>{$misses}</td></tr>";
				$this->vrs['content'] .= "<tr><td>Request Rate (hits, misses)</td><td>{$req_rate} cache requests/second</td></tr>";
				$this->vrs['content'] .= "<tr><td>Hit Rate</td><td>{$hit_rate} cache requests/second</td></tr>";
				$this->vrs['content'] .= "<tr><td>Miss Rate</td><td>{$miss_rate} cache requests/second</td></tr>";
				$this->vrs['content'] .= "<tr><td>Set Rate</td><td>{$set_rate} cache requests/second</td></tr>";
				$this->vrs['content'] .= "</tbody></table>";
				$this->vrs['content'] .= "</div>";
				break;
			case 2: // variables
				$m          = 0;
				$cacheItems = $this->getCacheItems();
				$items      = $cacheItems['items'];
				$totals     = $cacheItems['counts'];
				$maxDump    = MAX_ITEM_DUMP;
				foreach ($items as $server => $entries)
				{
					$this->vrs['content'] .= '<div class="info"><table class="list"><thead><tr><th colspan="2"><b>' . $server . '</b></th></tr><tr><td>Slab Id</td><td>Info</td></tr></thead><tbody>';
					foreach ($entries as $slabId => $slab)
					{
						$dumpUrl = $self_url . '&op=2&server=' . (array_search($server, $mem_servers)) . '&dumpslab=' . $slabId;
						$this->vrs['content'] .= "<tr><td><center>" . '<a href="' . $dumpUrl . '">' . $slabId . '</a>' . "</center></td><td class=td-last><b>Item count:</b> ";
						$this->vrs['content'] .= $slab['number'] . '<br/><b>Age:</b>' . $this->duration($time - $slab['age']) . '<br/> <b>Evicted:</b>' . ((isset($slab['evicted']) && $slab['evicted'] == 1) ? 'Yes' : 'No');
						if ((isset($_GET['dumpslab']) && $_GET['dumpslab'] == $slabId) && (isset($_GET['server']) && $_GET['server'] == array_search($server, $mem_servers)))
						{
							$this->vrs['content'] .= "<br/><b>Items: item</b><br/>";
							$items = $this->dumpCacheSlab($server, $slabId, $slab['number']);
							// maybe someone likes to do a pagination here :)
							$i = 1;
							foreach ($items['ITEM'] as $itemKey => $itemInfo)
							{
								$itemInfo = trim($itemInfo, '[ ]');
								$this->vrs['content'] .= '<a href="' . $self_url . '&op=4&server=' . (array_search($server, $mem_servers)) . '&key=' . base64_encode($itemKey) . '">' . $itemKey . '</a>';
								if ($i++ % 10 == 0)
								{
									$this->vrs['content'] .= '<br/>';
								}
								elseif ($i != $slab['number'] + 1)
								{
									$this->vrs['content'] .= '<br/>';
								}
							}
						}

						$this->vrs['content'] .= "</td></tr>";
						$m = 1 - $m;
					}
					$this->vrs['content'] .= '</tbody></table></div>';
				}
				break;
			case 4: //item dump
				if (!isset($_GET['key']) || !isset($_GET['server']))
				{
					$this->vrs['content'] .= '<div style="padding:20px;">No key set !</div>';
					break;
				}

				// probably an exploit can be written to delete all the files in key=base64_encode("\n\r delete all").
				$theKey    = htmlentities(base64_decode($_GET['key']));
				$theServer = $mem_servers[(int)$_GET['server']];
				list($h, $p) = $this->get_host_port_from_server($theServer);
				$r = $this->sendMemcacheCommand($h, $p, 'get ' . $theKey);
				$this->vrs['content'] .= '<div class="info"><table class="list"><thead><tr><td>Server<td>Key</td><td>Value</td><td>Delete</td></tr></thead>';
				if (!isset($r['VALUE']))
				{
					$this->vrs['content'] .= "<tbody><tr><td>{$theServer}</td><td>{$theKey}<br/>flag:null <br/>Size: 0</td><td>NULL</td>";
				}
				else
				{
					$this->vrs['content'] .= "<tbody><tr><td>{$theServer}</td><td>{$theKey}<br/>flag:{$r['VALUE'][$theKey]['stat']['flag']} <br/>Size:";
					$this->vrs['content'] .= $this->bsize($r['VALUE'][$theKey]['stat']['size']) . "</td><td>" . chunk_split($r['VALUE'][$theKey]['value'], 40) . "</td>";
				}
				$this->vrs['content'] .= "<td><a href=\"{$self_url}&op=5&server=" . (int)$_GET['server'] . '&key=' . base64_encode($theKey) . "\">Delete</a></td></tr>";
				$this->vrs['content'] .= '</tbody></table></div>';
				break;
			case 5: // item delete
				if (!isset($_GET['key']) || !isset($_GET['server']))
				{
					$this->vrs['content'] .= '<div style="padding:20px;">No key set !</div>';
					break;
				}
				$theKey    = htmlentities(base64_decode($_GET['key']));
				$theServer = $mem_servers[(int)$_GET['server']];
				list($h, $p) = $this->get_host_port_from_server($theServer);
				$r = $this->sendMemcacheCommand($h, $p, 'delete ' . $theKey);
				$this->vrs['content'] .= '<div style="padding:20px;">Deleting Memcache Key <br/><br/>' . $theKey . '<br/><br/>' . $r . '</div>';
				break;
			case 6: // flush server
				$theServer = $mem_servers[(int)$_GET['server']];
				$r         = $this->flushServer($theServer);
				$this->vrs['content'] .= '<div style="padding:20px;">Flush  ' . $theServer . " : " . $r . '</div>';
				break;
		}
	}

	private function displayImg($img)
	{
		$memcacheStats       = $this->getMemcacheStats();
		$memcacheStatsSingle = $this->getMemcacheStats(false);

		if (!$this->graphics_avail())
		{
			exit(0);
		}

		$size      = GRAPH_SIZE; // image size
		$image     = imagecreate($size + 50, $size + 10);
		$col_white = imagecolorallocate($image, 0xFF, 0xFF, 0xFF);
		$col_red   = imagecolorallocate($image, 0xD0, 0x60, 0x30);
		$col_green = imagecolorallocate($image, 0x60, 0xF0, 0x60);
		$col_black = imagecolorallocate($image, 0, 0, 0);
		imagecolortransparent($image, $col_white);

		switch ($img)
		{
			case 1: // pie chart
				$tsize      = $memcacheStats['limit_maxbytes'];
				$avail      = $tsize - $memcacheStats['bytes'];
				$x          = $y = $size / 2;
				$angle_from = 0;
				$fuzz       = 0.000001;

				foreach ($memcacheStatsSingle as $serv => $mcs)
				{
					$free = $mcs['STAT']['limit_maxbytes'] - $mcs['STAT']['bytes'];
					$used = $mcs['STAT']['bytes'];

					if ($free > 0)
					{
						// draw free
						$angle_to = ($free * 360) / $tsize;
						$perc     = sprintf("%.2f%%", ($free * 100) / $tsize);

						$this->fill_arc($image, $x, $y, $size, $angle_from, $angle_from + $angle_to, $col_black, $col_green, $perc);
						$angle_from = $angle_from + $angle_to;
					}

					if ($used > 0)
					{
						// draw used
						$angle_to = ($used * 360) / $tsize;
						$perc     = sprintf("%.2f%%", ($used * 100) / $tsize);
						$this->fill_arc($image, $x, $y, $size, $angle_from, $angle_from + $angle_to, $col_black, $col_red, '(' . $perc . ')');
						$angle_from = $angle_from + $angle_to;
					}
				}
				break;
			case 2: // hit miss
				$hits   = ($memcacheStats['get_hits'] == 0) ? 1 : $memcacheStats['get_hits'];
				$misses = ($memcacheStats['get_misses'] == 0) ? 1 : $memcacheStats['get_misses'];
				$total  = $hits + $misses;

				$this->fill_box($image, 30, $size, 50, -$hits * ($size - 21) / $total, $col_black, $col_green, sprintf("%.1f%%", $hits * 100 / $total));
				$this->fill_box($image, 130, $size, 50, -max(4, ($total - $hits) * ($size - 21) / $total), $col_black, $col_red, sprintf("%.1f%%", $misses * 100 / $total));
				break;
		}

		header("Content-type: image/png");
		imagepng($image);
		exit;
	}

	private function get_host_port_from_server($server)
	{
		$v = explode(':', $server);
		if (($v[0] == 'unix') && (!is_numeric($v[1])))
		{
			return array(
				$server,
				0
			);
		}

		return $v;
	}

	private function sendMemcacheCommands($command)
	{
		global $mem_servers;
		$result = array();
		foreach ($mem_servers as $server)
		{
			$strs            = $this->get_host_port_from_server($server);
			$host            = $strs[0];
			$port            = $strs[1];
			$result[$server] = $this->sendMemcacheCommand($host, $port, $command);
		}

		return $result;
	}

	private function sendMemcacheCommand($server, $port, $command)
	{
		$s = @fsockopen($server, $port);
		if (!$s)
		{
			die("Cant connect to:" . $server . ':' . $port);
		}

		fwrite($s, $command . "\r\n");
		$buf = '';
		while ((!feof($s)))
		{
			$buf .= fgets($s, 256);

			if (strpos($buf, "END\r\n") !== false)
			{
				break; // stat says end
			}

			if (strpos($buf, "DELETED\r\n") !== false || strpos($buf, "NOT_FOUND\r\n") !== false)
			{
				break; // delete says these
			}

			if (strpos($buf, "OK\r\n") !== false)
			{
				break; // flush_all says ok
			}
		}
		fclose($s);

		return $this->parseMemcacheResults($buf);
	}

	private function parseMemcacheResults($str)
	{
		$res   = array();
		$lines = explode("\r\n", $str);
		$cnt   = count($lines);
		for ($i = 0; $i < $cnt; $i++)
		{
			$line = $lines[$i];
			$l    = explode(' ', $line, 3);
			if (count($l) == 3)
			{
				$res[$l[0]][$l[1]] = $l[2];
				if ($l[0] == 'VALUE') // next line is the value
				{
					$res[$l[0]][$l[1]] = array();
					list ($flag, $size) = explode(' ', $l[2]);
					$res[$l[0]][$l[1]]['stat']  = array(
						'flag' => $flag,
						'size' => $size
					);
					$res[$l[0]][$l[1]]['value'] = $lines[++$i];
				}
			}
			elseif ($line == 'DELETED' || $line == 'NOT_FOUND' || $line == 'OK')
			{
				return $line;
			}
		}

		return $res;
	}

	private function dumpCacheSlab($server, $slabId, $limit)
	{
		list($host, $port) = $this->get_host_port_from_server($server);
		$resp = $this->sendMemcacheCommand($host, $port, 'stats cachedump ' . $slabId . ' ' . $limit);

		return $resp;
	}

	private function flushServer($server)
	{
		list($host, $port) = $this->get_host_port_from_server($server);

		return $this->sendMemcacheCommand($host, $port, 'flush_all');
	}

	private function getCacheItems()
	{
		$items       = $this->sendMemcacheCommands('stats items');
		$serverItems = array();
		$totalItems  = array();
		foreach ($items as $server => $itemlist)
		{
			$serverItems[$server] = array();
			$totalItems[$server]  = 0;
			if (!isset($itemlist['STAT']))
			{
				continue;
			}

			$iteminfo = $itemlist['STAT'];
			foreach ($iteminfo as $keyinfo => $value)
			{
				if (preg_match('/items\:(\d+?)\:(.+?)$/', $keyinfo, $matches))
				{
					$serverItems[$server][$matches[1]][$matches[2]] = $value;
					if ($matches[2] == 'number')
					{
						$totalItems[$server] += $value;
					}
				}
			}
		}

		return array(
			'items'  => $serverItems,
			'counts' => $totalItems
		);
	}

	private function getMemcacheStats($total = true)
	{
		$resp = $this->sendMemcacheCommands('stats');
		if ($total)
		{
			$res = array();
			foreach ($resp as $server => $r)
			{
				foreach ($r['STAT'] as $key => $row)
				{
					if (!isset($res[$key]))
					{
						$res[$key] = null;
					}
					switch ($key)
					{
						case 'pid':
							$res['pid'][$server] = $row;
							break;
						case 'uptime':
							$res['uptime'][$server] = $row;
							break;
						case 'time':
							$res['time'][$server] = $row;
							break;
						case 'version':
							$res['version'][$server] = $row;
							break;
						case 'pointer_size':
							$res['pointer_size'][$server] = $row;
							break;
						case 'rusage_user':
							$res['rusage_user'][$server] = $row;
							break;
						case 'rusage_system':
							$res['rusage_system'][$server] = $row;
							break;
						case 'curr_items':
							$res['curr_items'] += $row;
							break;
						case 'total_items':
							$res['total_items'] += $row;
							break;
						case 'bytes':
							$res['bytes'] += $row;
							break;
						case 'curr_connections':
							$res['curr_connections'] += $row;
							break;
						case 'total_connections':
							$res['total_connections'] += $row;
							break;
						case 'connection_structures':
							$res['connection_structures'] += $row;
							break;
						case 'cmd_get':
							$res['cmd_get'] += $row;
							break;
						case 'cmd_set':
							$res['cmd_set'] += $row;
							break;
						case 'get_hits':
							$res['get_hits'] += $row;
							break;
						case 'get_misses':
							$res['get_misses'] += $row;
							break;
						case 'evictions':
							$res['evictions'] += $row;
							break;
						case 'bytes_read':
							$res['bytes_read'] += $row;
							break;
						case 'bytes_written':
							$res['bytes_written'] += $row;
							break;
						case 'limit_maxbytes':
							$res['limit_maxbytes'] += $row;
							break;
						case 'threads':
							$res['rusage_system'][$server] = $row;
							break;
					}
				}
			}

			return $res;
		}

		return $resp;
	}

	private function duration($ts)
	{
		global $time;
		$years = (int)((($time - $ts) / (7 * 86400)) / 52.177457);
		$rem   = (int)(($time - $ts) - ($years * 52.177457 * 7 * 86400));
		$weeks = (int)(($rem) / (7 * 86400));
		$days  = (int)(($rem) / 86400) - $weeks * 7;
		$hours = (int)(($rem) / 3600) - $days * 24 - $weeks * 7 * 24;
		$mins  = (int)(($rem) / 60) - $hours * 60 - $days * 24 * 60 - $weeks * 7 * 24 * 60;
		$str   = '';
		if ($years == 1)
		{
			$str .= "$years year, ";
		}
		if ($years > 1)
		{
			$str .= "$years years, ";
		}
		if ($weeks == 1)
		{
			$str .= "$weeks week, ";
		}
		if ($weeks > 1)
		{
			$str .= "$weeks weeks, ";
		}
		if ($days == 1)
		{
			$str .= "$days day,";
		}
		if ($days > 1)
		{
			$str .= "$days days,";
		}
		if ($hours == 1)
		{
			$str .= " $hours hour and";
		}
		if ($hours > 1)
		{
			$str .= " $hours hours and";
		}
		if ($mins == 1)
		{
			$str .= " 1 minute";
		}
		else
		{
			$str .= " $mins minutes";
		}

		return $str;
	}

	// create graphics
	private function graphics_avail()
	{
		return extension_loaded('gd');
	}

	private function bsize($s)
	{
		$res = array(
			'',
			'K',
			'M',
			'G'
		);
		foreach ($res as $i => $k)
		{
			if ($s < 1024)
			{
				break;
			}
			$s /= 1024;
		}

		return sprintf("%5.1f %sBytes", $s, $k);
	}

	private function menu_entry($ob, $title)
	{
		global $self_url;
		if ($ob == $_GET['op'])
		{
			return "<a class='button child_active btn-blue' href='{$self_url}&op=$ob'>{$title}</a>";
		}

		return "<a class='button active btn-green' href='{$self_url}&op=$ob'>{$title}</a>";
	}

	private function getMenu($self_url)
	{
		$this->vrs['menu'] .= "<a href='/tool/memcache/flush' class='button btn-yellow'>Flush All 清空缓冲</a>";

		if ($_GET['op'] != 4)
		{
			$this->vrs['menu'] .= "<a href='{$self_url}&op={$_GET['op']}' class='button btn-red'>Refresh 刷新</a>";
		}
		else
		{
			$this->vrs['menu'] .= "<a href='{$self_url}&op=2}' class='button btn-darkblue'>Back 后退</a>";
		}

		$this->vrs['menu'] .= $this->menu_entry(1, 'Host Stats 主机状态') . $this->menu_entry(2, 'Variables 变量');
	}

	private function fill_box($im, $x, $y, $w, $h, $color1, $color2, $text = '', $placeindex = '')
	{
		global $col_black;
		$x1 = $x + $w - 1;
		$y1 = $y + $h - 1;

		imagerectangle($im, $x, $y1, $x1 + 1, $y + 1, $col_black);
		if ($y1 > $y)
		{
			imagefilledrectangle($im, $x, $y, $x1, $y1, $color2);
		}
		else
		{
			imagefilledrectangle($im, $x, $y1, $x1, $y, $color2);
		}
		imagerectangle($im, $x, $y1, $x1, $y, $color1);
		if ($text)
		{
			if ($placeindex > 0)
			{

				if ($placeindex < 16)
				{
					$px = 5;
					$py = $placeindex * 12 + 6;
					imagefilledrectangle($im, $px + 90, $py + 3, $px + 90 - 4, $py - 3, $color2);
					imageline($im, $x, $y + $h / 2, $px + 90, $py, $color2);
					imagestring($im, 2, $px, $py - 6, $text, $color1);
				}
				else
				{
					if ($placeindex < 31)
					{
						$px = $x + 40 * 2;
						$py = ($placeindex - 15) * 12 + 6;
					}
					else
					{
						$px = $x + 40 * 2 + 100 * intval(($placeindex - 15) / 15);
						$py = ($placeindex % 15) * 12 + 6;
					}
					imagefilledrectangle($im, $px, $py + 3, $px - 4, $py - 3, $color2);
					imageline($im, $x + $w, $y + $h / 2, $px, $py, $color2);
					imagestring($im, 2, $px + 2, $py - 6, $text, $color1);
				}
			}
			else
			{
				imagestring($im, 4, $x + 5, $y1 - 16, $text, $color1);
			}
		}
	}

	private function fill_arc($im, $centerX, $centerY, $diameter, $start, $end, $color1, $color2, $text = '', $placeindex = 0)
	{
		$r = $diameter / 2;
		$w = deg2rad((360 + $start + ($end - $start) / 2) % 360);

		if (function_exists("imagefilledarc"))
		{
			// exists only if GD 2.0.1 is avaliable
			imagefilledarc($im, $centerX + 1, $centerY + 1, $diameter, $diameter, $start, $end, $color1, IMG_ARC_PIE);
			imagefilledarc($im, $centerX, $centerY, $diameter, $diameter, $start, $end, $color2, IMG_ARC_PIE);
			imagefilledarc($im, $centerX, $centerY, $diameter, $diameter, $start, $end, $color1, IMG_ARC_NOFILL | IMG_ARC_EDGED);
		}
		else
		{
			imagearc($im, $centerX, $centerY, $diameter, $diameter, $start, $end, $color2);
			imageline($im, $centerX, $centerY, $centerX + cos(deg2rad($start)) * $r, $centerY + sin(deg2rad($start)) * $r, $color2);
			imageline($im, $centerX, $centerY, $centerX + cos(deg2rad($start + 1)) * $r, $centerY + sin(deg2rad($start)) * $r, $color2);
			imageline($im, $centerX, $centerY, $centerX + cos(deg2rad($end - 1)) * $r, $centerY + sin(deg2rad($end)) * $r, $color2);
			imageline($im, $centerX, $centerY, $centerX + cos(deg2rad($end)) * $r, $centerY + sin(deg2rad($end)) * $r, $color2);
			imagefill($im, $centerX + $r * cos($w) / 2, $centerY + $r * sin($w) / 2, $color2);
		}
		if ($text)
		{
			if ($placeindex > 0)
			{
				imageline($im, $centerX + $r * cos($w) / 2, $centerY + $r * sin($w) / 2, $diameter, $placeindex * 12, $color1);
				imagestring($im, 4, $diameter, $placeindex * 12, $text, $color1);
			}
			else
			{
				imagestring($im, 4, $centerX + $r * cos($w) / 2, $centerY + $r * sin($w) / 2, $text, $color1);
			}
		}
	}
}
?>