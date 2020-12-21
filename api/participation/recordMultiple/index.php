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

if(!check_params(['ckeys[0]', 'round_mode'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

$db = mysqli_connect($databaseAddress, $databaseUser, $databasePassword, $databaseName);
if(mysqli_connect_errno()) {
	echo json_error("Failed to connect to the database.");
	return;
}

$curIndex = 0;
while(key_exists("ckeys[{$curIndex}]", $_GET)) {
	$stmt = $db->stmt_init();
	$stmt->prepare("SELECT * FROM `player` WHERE `ckey` = ?");
	$stmt->bind_param('s', $_GET["ckeys[{$curIndex}]"]);
	if($stmt->execute()) {
		if(!$stmt->num_rows()) { // they don't exist? lol f, try again later champ - if this ever happens, we shouldn't fail completely. just move on to the next ckey.
			continue;
		}
	} else {
		echo json_error("Failed to query database.");
		return;
	}

	$stmt->close();
	$seen = 0;
	$stmt = $db->stmt_init();
	$stmt->prepare("SELECT `seen` FROM `participation` WHERE `ckey` = ? AND `mode` = ?");
	$stmt->bind_param('ss', $_GET["ckeys[{$curIndex}]"], $_GET['round_mode']);
	if($stmt->execute()) {
		if($stmt->num_rows()) {
			$stmt->bind_result($seen);
			$stmt->fetch();
		}
	} else {
		echo json_error("Failed to query database.");
		return;
	}

	$stmt->close();
	// Now update the player table
	$stmt = $db->stmt_init();
	$stmt->prepare("UPDATE `player` SET `lastmode` = ? WHERE `ckey` = ?");
	$stmt->bind_param('ss', $_GET['round_mode'], $_GET["ckeys[{$curIndex}]"]);
	if(!$stmt->execute()) {
		echo json_error("Failed to query database.");
		return;
	}

	$stmt->close();
	$stmt = $db->stmt_init();
	if($seen) {
		$stmt->prepare("UPDATE `participation` SET `seen` = ? WHERE `ckey` = ? AND `mode` = ?");
		$stmt->bind_param('iss', $seen + 1, $_GET["ckeys[{$curIndex}]"], $_GET['round_mode']);
	} else {
		$stmt->prepare("INSERT INTO `participation` VALUES (?, ?, 1)");
		$stmt->bind_param('ss', $_GET["ckeys[{$curIndex}]"], $_GET['round_mode']);
	}
	if(!$stmt->execute()) {
		echo json_error("Failed to query database.");
		return;
	}

	$curIndex++;
}

echo $JSON_SUCCESS;

?>
