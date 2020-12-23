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

if(!check_params(['key'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

$serverKey = $_GET['data_server'];

// Send the response to the client - this code is for efficiency, and lets callbacks happen in the background without the server waiting for a response (the whole point of a callback is to run in the background lmao)
// This is just boilerplate
ob_start();
echo JSON_SUCCESS; // Tidbit of json so the server won't screech because no body
$size = ob_get_length();
header("Content-Encoding: none");
header("Content-Length: {$size}");
header("Connection: close");
http_response_code(202);
ob_end_flush();
ob_flush();
flush();
if(session_id()) session_write_close();

$result = sql_query("SELECT `amount` FROM `gauntlet` WHERE `ckey` = ?", ['s', "INSERT INTO `gauntlet` VALUES (?, 1)"]);

if($result) {
	sql_query("UPDATE `gauntlet` SET `amount` = `amount` + 1 WHERE `ckey` = ?", ['is', $_GET['key']]);
} else {
	sql_query("INSERT INTO `gauntlet` VALUES (?, 1)", ['s', $_GET['key']]);
}

$result = ['keys' => [$_GET['key']], $_GET['key'] => $result[0]['amount']]; // format result
callback($servers[$serverKey]['ip'], $servers[$serverKey]['port'], $result, "queryGauntletMatches");
?>
