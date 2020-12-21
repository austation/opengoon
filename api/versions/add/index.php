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

if(!check_params(['ckey', 'ua', 'byondMajor', 'byondMinor'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

$db = mysqli_connect($databaseAddress, $databaseUser, $databasePassword, $databaseName);
if(mysqli_connect_errno()) {
	echo json_error("Failed to connect to the database.");
	return;
}

$stmt = $db->stmt_init();
$stmt->prepare("SELECT * FROM `player` WHERE `ckey` = ?");
$stmt->bind_param('s', $_GET['ckey']);
if($stmt->execute()) {
	if(!$stmt->num_rows()) { // they don't exist? lol f, try again later champ
		echo json_error("Ckey doesn't exist in database.");
		return;
	}
} else {
	echo json_error("Failed to query database.");
	return;
}

$stmt->close();
$stmt = $db->stmt_init();
$stmt->prepare("UPDATE `player` SET `ua` = ?, `byondMajor` = ?, `byondMinor` = ? WHERE `ckey` = ?");
$stmt->bind_param('sii', $_GET[`ua`], $_GET['byondMajor'], $_GET['byondMinor'], $_GET['ckey']);
if(!$stmt->execute()) {
	echo json_error("Failed to execute query.");
	return;
}

$stmt->close();
$db->close();

echo $JSON_SUCCESS;
?>
