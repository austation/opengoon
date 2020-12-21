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
echo $JSON_SUCCESS; // Tidbit of json so the server won't screech because no body
$size = ob_get_length();
header("Content-Encoding: none");
header("Content-Length: {$size}");
header("Connection: close");
http_response_code(202);
ob_end_flush();
ob_flush();
flush();
if(session_id()) session_write_close();

$db = mysqli_connect($databaseAddress, $databaseUser, $databasePassword, $databaseName);
if(mysqli_connect_errno()) {
	return;
}

$amount = 0;
$stmt = $db->stmt_init();
$stmt->prepare("SELECT `amount` FROM `gauntlet` WHERE `ckey` = ?");
$stmt->bind_param('s', $_GET['key']);
if($stmt->execute()) {
	if($stmt->num_rows()) { // they're in the DB, fetch number of matches
		$stmt->bind_result($amount);
		$stmt->fetch();
	}
} else {
	return;
}

$stmt->close();
$stmt = $db->stmt_init();
if($amount) {
	$stmt->prepare("UPDATE `gauntlet` SET `amount` = ? WHERE `ckey` = ?");
	$stmt->bind_param('is', (int)$amount + 1, $_GET['key']);
} else {
	$stmt->prepare("INSERT INTO `gauntlet` VALUES (?, 1)");
	$stmt->bind_param('si', $_GET['key']);
}
if(!$stmt->execute()) {
	return;
}

$stmt->close();
$db->close();

$result = ['keys' => [$_GET['key']], $_GET['key'] => (int)$amount]; // format result
callback($servers[$serverKey]['ip'], $servers[$serverKey]['port'], $result, "queryGauntletMatches");
?>
