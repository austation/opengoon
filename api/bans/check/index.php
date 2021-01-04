<?php
// Boilerplate Request Validation and Includes Code
require '../../config.php';
require '../../utils.php';

header("Content-Type: application/json");

if(!check_auth()) {
	http_response_code(401);
	return;
}

if(!check_params(['ckey', 'compID', 'ip', 'record', 'data_id'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

// This endpoint is the big one. Handles a heap of shit other than exclusively bans.

// First, check if they exist in the players DB
if(!sql_query("SELECT * FROM `player` WHERE `ckey` = ?", ['s', $_GET['ckey']])) {
	// If they don't, add them.
	sql_query("INSERT INTO `player` (`ckey`, `ip`, `compID`, `connections`) VALUES (?, ?, ?, 1)", ['sis', $_GET['ckey'], ip_to_int($_GET['ip']), $_GET['compID']]);
} elseif($_GET['record']) {
	// If we didn't need to add them, and record is specified, update the connection count and latest ip/compID
	sql_query("UPDATE `player` SET `ip` = ?, `compID` = ?, `connections` = `connections` + 1 WHERE `ckey` = ?", ['iss', ip_to_int($_GET['ip']), $_GET['compID'], $_GET['ckey']]);
}

// If record specified, update the ip and compID history
if($_GET['record']) {
	// IP - check row exists
	if(!sql_query("SELECT * FROM `ip_history` WHERE `ip` = ? AND `ckey` = ?", ['is', ip_to_int($_GET['ip']), $_GET['ckey']])) {
		// it doesn't, add one
		sql_query("INSERT INTO `ip_history` (`ckey`, `ip`, `count`) VALUES (?, ?, 1)", ['si', $_GET['ckey'], ip_to_int($_GET['ip'])]);
	} else {
		// it does, update it
		sql_query("UPDATE `ip_history` SET `count` = `count` + 1 WHERE `ckey` = ? AND `ip` = ?", ['si', $_GET['ckey'], ip_to_int($_GET['ip'])]);
	}

	// compID
	if(!sql_query("SELECT * FROM `compid_history` WHERE `compid` = ? AND `ckey` = ?", ['ss', $_GET['compID'], $_GET['ckey']])) {
		// it doesn't, add one
		sql_query("INSERT INTO `compid_history` (`ckey`, `compID`, `count`) VALUES (?, ?, 1)", ['ss', $_GET['ckey'], $_GET['compID']]);
	} else {
		// it does, update it
		sql_query("UPDATE `compid_history` SET `count` = `count` + 1 WHERE `ckey` = ? AND `compid` = ?", ['ss', $_GET['ckey'], $_GET['compID']]);
	}
}

// Now with all that connection stuff done, get the bans applicable to the server and build response
$response = array();
$bans = sql_query("SELECT * FROM `bans` WHERE `removed` = FALSE AND (`ckey` = ? OR `ip` = ? OR `compID` = ?) AND (`server` = ? OR `server` IS NULL) ORDER BY `id` DESC", ['siss', $_GET['ckey'], ip_to_int($_GET['ip']), $_GET['compID'], $_GET['data_id']], true);
if(!$bans) { // wow, no bans to speak of! you chad!
	$response['none'] = true;
} else { // you fucked up, there's bans on record lole
	// loop over them and put in the response
	foreach($bans as $row) {
		$response[$row['id']] = [
			'ckey' => $row['ckey'],
			'ip' => int_to_ip($row['ip']),
			'compID' => $row['compid'],
			'id' => $row['id'],
			'previous' => $row['previous'],
			'chain' => $row['chain'],
			'timestamp' => $row['timestamp'],
			'reason' => $row['reason'],
			'oakey' => $row['oakey'],
			'akey' => $row['akey']
		];

		// Add server if needed
		if($row['server']) {
			$response[$row['id']]['server'] = $row['server'];
		}
	}
}

// Finally, return it, oh boyy - forcing object in case by some miracle the whole thing is interpreted as an array, instead of as an object
echo json_encode($response, JSON_FORCE_OBJECT);

?>
