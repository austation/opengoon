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

if(!check_params(['ckeys', 'round_mode'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

$curIndex = 0;
foreach($_GET['ckeys'] as $ckey) {
	if(!sql_query("SELECT * FROM `player` WHERE `ckey` = ?", ['s', $ckey])) {
		continue; // they don't exist? lol f, try again later champ - if this ever happens, we shouldn't fail completely. just move on to the next ckey.
	}

	sql_query("UPDATE `player` SET `participations` = `participations` + 1 WHERE `ckey` = ?", ['s', $ckey]);

	if(key_exists($_GET['round_mode'], $modes)) { // the mode has roundstart antags attached. let's update those for weighting.
		foreach($modes[$_GET['round_mode']] as $antag) { // loop over each possible antag for the mode
			// check if the DB has the mode or not
			if(!sql_query("SELECT * FROM `antag` WHERE `ckey` = ? AND `role` = ?", ['ss', $ckey, $antag])) { // it doesn't exist in the db
				sql_query("INSERT INTO `antag` (`ckey`, `role`, `selected`, `ignored`, `seen`) VALUES (?, ?, 0, 0, 1)", ['ss', $ckey, $antag]); // create a row for the antag
			} else { // does exist
				sql_query("UPDATE `antag` SET `seen` = `seen` + 1 WHERE `ckey` = ? AND `role` = ?", ['ss', $ckey, $antag]); // update existing row
			}
		}
	}

	$curIndex++;
}

echo JSON_SUCCESS;

?>
