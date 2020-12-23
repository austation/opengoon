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

if(!check_params(['ckey', 'role'], $_GET)) { // I'm so god tier smart that I don't even need the mode anymore, but still make sure it's there for correctness
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

// Alright, so multiple players or single players are squashed into a single endpoint here. Let's deal with singles first
if(key_exists('players', $_GET)) {
	// Check if there's an entry already
	$result = sql_query("SELECT * FROM `antag` WHERE `ckey` = ? AND `role` = ?", ['ss', $_GET['player'], $_GET['role']]);
	if($result) { // row exists
		if($_GET['latejoin']) { // late join antag
			sql_query("UPDATE `antag` SET `ignored` = `ignored` + 1 WHERE `ckey` = ? AND `role` = ?", ['ss', $_GET['player'], $_GET['role']]);
		} else {
			sql_query("UPDATE `antag` SET `selected` = `selected` + 1 WHERE `ckey` = ? AND `role` = ?", ['ss', $_GET['player'], $_GET['role']]);
		}
	} else { // row doesn't exist
		if($_GET['latejoin']) { // late join antag
			sql_query("INSERT INTO `antag` VALUES (?, ?, 1, 0, 0) WHERE `ckey` = ? AND `role` = ?", ['ss', $_GET['player'], $_GET['role']]);
		} else {
			sql_query("INSERT INTO `antag` VALUES (?, ?, 0, 1, 0) WHERE `ckey` = ? AND `role` = ?", ['ss', $_GET['player'], $_GET['role']]);
		}
	}
// Deal with multiples
} elseif(key_exists('players[0][ckey]', $_GET)) {
	$curIndex = 0;
	// Loop over all the players
	while(key_exists("players[{$curIndex}][ckey]", $_GET)) {
		$result = sql_query("SELECT * FROM `antag` WHERE `ckey` = ? AND `role` = ?", ['ss', $_GET["players[{$curIndex}][ckey]"], $_GET["players[{$curIndex}][role]"]]);
		if($result) { // row exists
			if($_GET['assday']) { // late join antag
				sql_query("UPDATE `antag` SET `ignored` = `ignored` + 1 WHERE `ckey` = ? AND `role` = ?", ['ss', $_GET["players[{$curIndex}][ckey]"], $_GET["players[{$curIndex}][role]"]]);
			} else {
				sql_query("UPDATE `antag` SET `selected` = `selected` + 1 WHERE `ckey` = ? AND `role` = ?", ['ss', $_GET["players[{$curIndex}][ckey]"], $_GET["players[{$curIndex}][role]"]]);
			}
		} else { // row doesn't exist
			if($_GET['assday']) { // late join antag
				sql_query("INSERT INTO `antag` VALUES (?, ?, 1, 0, 0) WHERE `ckey` = ? AND `role` = ?", ['ss', $_GET["players[{$curIndex}][ckey]"], $_GET["players[{$curIndex}][role]"]]);
			} else {
				sql_query("INSERT INTO `antag` VALUES (?, ?, 0, 1, 0) WHERE `ckey` = ? AND `role` = ?", ['ss', $_GET["players[{$curIndex}][ckey]"], $_GET["players[{$curIndex}][role]"]]);
			}
		}

		$curIndex++;
	}
// Lolwut we're missing either option what on earth is going on down there
} else {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

echo JSON_SUCCESS;

?>
