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

$updated = false;

// This encoding SUCKS!
foreach($_GET as $key => $value) {
	if(!preg_match('/ckeys/', $key)) {
		continue;
	}

	$split = preg_split('/[\[\]]/', $key); // WHYYYY
	$ckey = $split[1];

	$response = sql_query("SELECT `playtime` FROM `player` WHERE `ckey` = ?", ['s', $ckey], true);

	$newTime = (int)$value + (int)$response[0]['playtime'];

	sql_query("UPDATE `player` SET `playtime` = ? WHERE `ckey` = ?", ['is', $newTime, $ckey]);

	$updated = true;
}

if(!$updated) {
	echo json_error("No ckeys and playtimes specified.");
	return;
}

echo JSON_SUCCESS;

?>
