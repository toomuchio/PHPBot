<?php
global $binds;

//!ping
$binds["!ping"] = create_function('$index, $channel, $nick, $text', '
		send_quickmsg("PONG!", $channel, $index);
');

?>

