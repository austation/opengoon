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

// Do a query to check if there's a row already; we can't whitelist if they already have an entry.
$db = mysqli_connect($databaseAddress, $databaseUser, $databasePassword, $databaseName);
if(mysqli_connect_errno()) {
	echo json_error("Failed to connect to the database.");
	return;
}

$stmt = $db->stmt_init();
$stmt->prepare("DELETE FROM `vpn_whitelist` WHERE `ckey` = ?");
$stmt->bind_param('ss', $_GET['ckey']);
if(!$stmt->execute()) {
	echo json_error("Failed to delete values from DB.");
	return;
}

$stmt->close();
$db->close();

echo $JSON_SUCCESS;
?>
