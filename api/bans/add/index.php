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

if(!check_params(['ckey', 'compID', 'ip', 'reason', 'oakey', 'akey', 'timestamp', 'previous', 'chain'], $_GET)) {
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

// First thing's first, add the new ban to the database. also enable failsafe, because this is the incredibly rare callback that deals with errors.
// handle server-specific
$dbStatus;
if(key_exists('server', $_GET)) {
	$dbStatus = sql_query("INSERT INTO `bans` VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
		'sissssiiis',
		$_GET['ckey'],
		ip_to_int($_GET['ip']),
		$_GET['compID'],
		$_GET['reason'],
		$_GET['oakey'],
		$_GET['akey'],
		$_GET['timestamp'],
		$_GET['previous'],
		$_GET['chain'],
		$_GET['server']
	], false, true);
// handle global
} else {
	$dbStatus = sql_query("INSERT INTO `bans` VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL)", [
		'sissssiii',
		$_GET['ckey'],
		ip_to_int($_GET['ip']),
		$_GET['compID'],
		$_GET['reason'],
		$_GET['oakey'],
		$_GET['akey'],
		$_GET['timestamp'],
		$_GET['previous'],
		$_GET['chain'],
	], false, true);
}

// Ban added, now we make a callback with data to the server.
// Build the callback response (why does this even exist):
$response;
if($dbStatus === false) { // DB broke, return an error.
	$response = ['error' => "Error modifying database. Ban failed to add."];
} else {
	$response = ['ban' => [
		'ckey' => $_GET['ckey'],
		'ip' => $_GET['ip'],
		'compID' => $_GET['compID'],
		'reason' => $_GET['reason'],
		'oakay' => $_GET['oakey'],
		'akey' => $_GET['akey'],
		'timestamp' => $_GET['timestamp'],
		'previous' => $_GET['previous'],
		'chain' => $_GET['chain']
	]];
	if(key_exists('server', $_GET)) {
		$response['ban']['server'] = $_GET['server'];
	}
}

callback($servers[$serverKey]['ip'], $servers[$serverKey]['port'], $response, "addBan");

?>
