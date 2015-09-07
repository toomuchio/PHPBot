<?php

//Connect
function connect($i) {
	global $sockets, $bot;

	$context = stream_context_create();
	stream_context_set_option($context, array('ssl' => array('verify_peer' => false, 'allow_self_signed' => true)));

	if (($sockets[$i] = stream_socket_client("ssl://".$bot['servers'][$i]['address'].":".$bot['servers'][$i]['port'], $errno, $errstr, ini_get("default_socket_timeout"), STREAM_CLIENT_CONNECT, $context)) !== false) {
		echo "Connected to ".$bot['servers'][$i]['name']." successfully...\n";

		$bot['servers'][$i]['connected'] = true;
		$bot['servers'][$i]['nextconnect'] = 0;

		if ($bot['servers'][$i]['password'] != "") { send_raw("PASS ".$bot['servers'][$i]['password'], $i); }

		send_raw("NICK ".$bot['servers'][$i]['nick']."\nUSER ".$bot['servers'][$i]['nick']." - - :".$bot['servers'][$i]['realname'], $i);

		return true;
	} else {
		echo "Connection to ".$bot['servers'][$i]['name']." failed...\n";
		return false;
	}
}

//Rehasher
function rehash() {
	global $binds;
	$binds = array();
	$dir = "include";

	if (is_dir($dir)) {
		$handler = opendir($dir);
		while ($file = readdir($handler)) {
			if ($file == '.' || $file == '..') { continue; }
			require($dir."/".$file);
			echo 'Loaded: '.$file."\n";
		}
		closedir($handler);
	}

	require("config.php");
}

//Messages
function send_quickmsg($text, $channel, $server) {
      send_raw("PRIVMSG ".$channel." :".$text, $server);
}

function send_raw($text, $server) {
      global $sockets;

      fwrite($sockets[$server], $text."\n");
}

//EvalCatch
function rawEval($raw) {
	ob_start();
	$error = "";

    if (!@eval($raw)) {
		$error = error_get_last()['message'];
    }

    if ($error == "Only variables should be passed by reference" || empty($error)) {
		$error = ob_get_contents();
		if (empty($error)) {
			$error = "OK";
		}
	}
	ob_end_clean();

	return $error;
}
?>
