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

if(!check_params(['ckey'], $_GET)) { // I'm so god tier smart that I don't even need the mode anymore, but still make sure it's there for correctness
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

// Get their history from the DB
$result = sql_query("SELECT * FROM `antag` WHERE `ckey` = ?", ['s', $_GET['ckey']], true);

// Parse each entry and start building our response
$response = ['history' => array()];
foreach($result as $row) {
	$response['history'][$row['role']] = ['selected' => $row['selected'], 'seen' => $row['seen'], 'percent' => round(($row['selected'] / $row['seen']) * 100)];
}

echo json_encode($response);

?>
