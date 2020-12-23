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

if(!check_params(['ckey', 'akey'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

if(sql_query("SELECT * FROM `vpn_whitelist` WHERE `ckey` = ?", ['s', $_GET['ckey']])) {
	echo json_error("Ckey already exists.");
	return;
}

sql_query("INSERT INTO `vpn_whitelist` VALUES (?, ?)", ['ss', $_GET['ckey'], $_GET['akey']]);

echo JSON_SUCCESS;

?>
