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

if(!check_params(['id', 'ckey', 'compID', 'ip', 'reason', 'akey', 'timestamp'], $_GET)) {
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

// Vars for error checking
$dbStatus;
$error;

// Check if the ban exists or not
$dbStatus = sql_query("SELECT * FROM `bans` WHERE `id` = ?", ['i', $_GET['id']], false, true);
if($dbStatus === 0) {
	$error = "Ban doesn't exist in DB";
} elseif($dbStatus === false) {
	$error = "Failed to select from database.";
}

if($dbStatus && !$error) {
	if(!key_exists('server', $_GET)) {
		$dbStatus = sql_query("UPDATE `bans` SET `ckey` = ?, `ip` = ?, `compID` = ?, `reason` = ?, `akey` = ?, `timestamp` = ?, `server` = NULL WHERE `id` = ?", ['sisssii',
			$_GET['ckey'],
			ip_to_int($_GET['ip']),
			$_GET['compID'],
			$_GET['reason'],
			$_GET['akey'],
			$_GET['timestamp'],
			$_GET['id']
		]);
	} else {
		$dbStatus = sql_query("UPDATE `bans` SET `ckey` = ?, `ip` = ?, `compID` = ?, `reason` = ?, `akey` = ?, `timestamp` = ?, `server` = ? WHERE `id` = ?", ['sisssii',
			$_GET['ckey'],
			ip_to_int($_GET['ip']),
			$_GET['compID'],
			$_GET['reason'],
			$_GET['akey'],
			$_GET['timestamp'],
			$_GET['id'],
			$_GET['server']
		]);
	}
}

// Build response
$response;
if($error) {
	$response = ['error' => $error];
} elseif($dbStatus === false) {
	$response = ['error' => "Error modifying database. Couldn't update entry."];
} else {
	$response = ['ban' => [
		'ckey' => $_GET['ckey'],
		'ip' => $_GET['ip'],
		'compID' => $_GET['compID'],
		'reason' => $_GET['reason'],
		'akey' => $_GET['akey'],
		'timestamp' => $_GET['timestamp'],
	]];
	if(key_exists('server', $_GET)) {
		$response['ban']['server'] = $_GET['server'];
	}
}

callback($servers[$serverKey]['ip'], $servers[$serverKey]['port'], $response, "editBan");

?>
