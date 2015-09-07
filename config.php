<?php
	global $bot;

	//Globals
	$i = 0;
	$bot['nick'] = 'PHPBot';
	$bot['realname'] = 'PHPBot';

	//Network 1
	$bot['servers'][$i]['nick'] = $bot['nick'];
	$bot['servers'][$i]['realname'] = $bot['realname'];

	$bot['servers'][$i]['name'] = 'EFNet';
	$bot['servers'][$i]['address'] = 'irc.efnet.org';
	$bot['servers'][$i]['password'] = '';
	$bot['servers'][$i]['port'] = 6697;

	$bot['servers'][$i]['channels']['#test']['key'] = '';

	//Network 2
	$i++;
	$bot['servers'][$i]['nick'] = $bot['nick'];
	$bot['servers'][$i]['realname'] = $bot['realname'];

	$bot['servers'][$i]['name'] = 'FreeNode';
	$bot['servers'][$i]['address'] = 'irc.freenode.org';
	$bot['servers'][$i]['password'] = '';
	$bot['servers'][$i]['port'] = 6697;

	$bot['servers'][$i]['channels']['#test']['key'] = '';
?>
