<?php
// Boilerplate Request Validation and Includes Code
require '../../config.php';
require '../../utils.php';

header("Content-Type: application/json");

// Whew that's a lot of checks for authentication, including IP, auth key and checking the server key
if(!key_exists('auth', $_GET) || $_GET['auth'] !== md5($authKey) || !key_exists('data_server', $_GET) || !key_exists((int)$_GET['data_server'], $servers) || $servers[$_GET['data_server']]['ip'] !== $_SERVER['REMOTE_ADDR']) {
	http_response_code(401);
	return;
}

if(!check_params(['ckeys[0]', 'round_mode'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

$curIndex = 0;
while(key_exists("ckeys[{$curIndex}]", $_GET)) {
	if(!sql_query("SELECT * FROM `player` WHERE `ckey` = ?", ['s', $_GET["ckeys[{$curIndex}]"]])) {
		continue; // they don't exist? lol f, try again later champ - if this ever happens, we shouldn't fail completely. just move on to the next ckey.
	}

	$result = sql_query("SELECT `seen` FROM `participation` WHERE `ckey` = ? AND `mode` = ?", ['ss', $_GET["ckeys[{$curIndex}]"], $_GET['round_mode']], true);

	sql_query("UPDATE `player` SET `lastmode` = ? WHERE `ckey` = ?", ['ss', $_GET['round_mode'], $_GET["ckeys[{$curIndex}]"]]);

	if($result) {
		sql_query("UPDATE `participation` SET `seen` = ? WHERE `ckey` = ? AND `mode` = ?", ['iss', $result[0]['seen'] + 1, $_GET["ckeys[{$curIndex}]"], $_GET['round_mode']]);
	} else {
		sql_query("INSERT INTO `participation` VALUES (?, ?, 1)", ['ss', $_GET["ckeys[{$curIndex}]"], $_GET['round_mode']]);
	}

	$curIndex++;
}

echo $JSON_SUCCESS;

?>
