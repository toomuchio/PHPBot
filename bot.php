<?php
	//Defines
	error_reporting(-1);
	ini_set("html_errors", false);
	set_time_limit(0);
	ob_implicit_flush();

	$binds = array();
	$bot = array();

	//Load dependences
	if (file_exists('functions.php')) {
		require_once('functions.php');
	} else {
		die("[*] functions.php missing!\n");
	}

	if (file_exists('config.php')) {
		require_once('config.php');
	} else {
		die("[*] config.php missing!\n");
	}

	rehash();

	//Prim the arrays
	$sockets = array();
	for ($i=0; $i < count($bot['servers']); $i++) {
		$bot['servers'][$i]['connected'] = false;
		$bot['servers'][$i]['nextconnect'] = time()+2;
	}

	//Start the connection
	$quit = false;
	while (!$quit) {
		usleep(100000);
		
		foreach ($bot['servers'] as $index => $data) {
			if (!$data['connected'] && $data['nextconnect'] < time()) {
				connect($index);
			}
		}

		$changed_sockets = $sockets;
		$num_changed_sockets = @stream_select($changed_sockets, $write = NULL, $except = NULL, 0, 15);
		foreach($changed_sockets as $socket) {
			$index = array_search($socket, $sockets);
			if ($bot['servers'][$index]['connected'] == false) { continue; }

			if (feof($sockets[$index])) {
				echo "Lost connection to ".$bot['servers'][$i]['name']."...\n";
				$bot['servers'][$index]['connected'] = false;
				$bot['servers'][$index]['nextconnect'] = time()+10;
			}

			$ircresponce = fread($sockets[$index], 2048);
			if ($ircresponce === false) {
				echo "Lost connection to ".$bot['servers'][$i]['name']."...\n";
				$bot['servers'][$index]['connected'] = false;
				$bot['servers'][$index]['nextconnect'] = time()+10;
				continue;
			}

			$msgs = explode("\n", $ircresponce);
			foreach ($msgs as $buffer) {
				$buffer = trim(str_replace("\r", '', $buffer));
				if (empty($buffer)) continue;

				$parts = explode(' ', $buffer);

				if ($parts[0] == 'PING') {
					$msg = "PONG {$parts[1]}\n";
					fwrite($sockets[$index], $msg);
				} elseif ($parts[1] == '001') {
					$bot['servers'][$index]['connected'] = true;
					foreach($bot['servers'][$index]['channels'] as $channel => $data) {
						send_raw("JOIN ".$channel." ".$data['key']."\n", $index);
					}
				} elseif ($parts[1] == '433') {
					$bot['servers'][$index]['nick'] = $bot['servers'][$index]['nick'].mt_rand(0, 9);
					send_raw("NICK {$bot['servers'][$index]['nick']}\n", $index);
				} elseif ($parts[1] == "PRIVMSG") {
					preg_match("/\:(.*) PRIVMSG (.*) \:(.*)/im", $buffer, $subpart);

					$user = stristr($subpart[1], "!", true);
					$channel = $subpart[2];

					if ($bot['nick'] == $channel) { // PM
						$channel = $user;
						continue;
					}

					$message = trim($subpart[3]);
					$msgparts = explode(" ", $message);

					if ($msgparts[0] == "!raw") {
						send_quickmsg(rawEval(str_replace("!raw ", "", trim((string)$message))), $channel, $index);
					} elseif ($msgparts[0] == "!die") {
						send_quickmsg("Yes, Master...", $channel, $index);
						$quit = true;
						break;
					} elseif (function_exists($binds[$msgparts[0]])) {
						$binds[$msgparts[0]]($index, $channel, str_replace(':', '', $parts[0]), $message);
					}
				} elseif ($parts[1] == 'INVITE') {
					$channel = str_replace(':', '', $parts[3]);
					if (isset($bot['servers'][$index]['channels'][strtolower($channel)])) {
						fwrite($sockets[$index], "JOIN ".$channel."\n");
					}
				} elseif ($parts[1] == 'JOIN') {
					$user = str_replace(':', '', substr($parts[0], 0, strpos($parts[0], '!')));
					$host = str_replace(':', '', $parts[0]);
					$channel = str_replace(':', '', $parts[2]);
				} else {
					//echo "UNKNOWN IRC RESPONSE >> ".$index." > ".$parts[1]."\n";
				}
			}
		}
	}

	foreach ($sockets as $x) {
		fclose($x);
	}
?>
