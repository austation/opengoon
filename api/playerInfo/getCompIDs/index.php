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

if(!check_params(['ckey'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

$player = sql_query("SELECT * FROM `player` WHERE `ckey` = ?", ['s', $_GET['ckey']], true);
if(!$player) {
	echo json_error("Ckey doesn't exist in database.");
	return;
}
$player = $player[0]; // sneaky simplification

$cid_history = sql_query("SELECT * FROM `compid_history` WHERE `ckey` = ?", ['s', $_GET['ckey']]);

$response = array();

// Construct the response array
foreach($cid_history as $row) {
	$response[] = array('compID' => $row['compid'], 'times' => $row['count']);
}

// Finishing touches(tm)
$response[0]['last_seen'] = $player['compid'];

echo json_encode($response);

?>
