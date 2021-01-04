<?php
// Boilerplate Request Validation and Includes Code
require '../../config.php';
require '../../utils.php';

header("Content-Type: application/json");

if(!check_auth()) {
	http_response_code(401);
	return;
}

if(!check_params(['players'], $_GET)) { // I'm so god tier smart that I don't even need the mode anymore, but still make sure it's there for correctness
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

// Alright, so multiple players or single players are squashed into a single endpoint here. Let's deal with singles first
if(!is_array($_GET['players']) && key_exists('role', $_GET)) {
	// Check if there's an entry already
	$result = sql_query("SELECT * FROM `antag` WHERE `ckey` = ? AND `role` = ?", ['ss', $_GET['player'], $_GET['role']]);
	if($result) { // row exists
		if(key_exists('latejoin', $_GET)) { // late join antag
			sql_query("UPDATE `antag` SET `ignored` = `ignored` + 1 WHERE `ckey` = ? AND `role` = ?", ['ss', $_GET['player'], $_GET['role']]);
		} else {
			sql_query("UPDATE `antag` SET `selected` = `selected` + 1 WHERE `ckey` = ? AND `role` = ?", ['ss', $_GET['player'], $_GET['role']]);
		}
	} else { // row doesn't exist
		if(key_exists('latejoin', $_GET)) { // late join antag
			sql_query("INSERT INTO `antag` (`ckey`, `role`, `selected`, `ignored`, `seen`) VALUES (?, ?, 1, 0, 0)", ['ss', $_GET['player'], $_GET['role']]);
		} else {
			sql_query("INSERT INTO `antag` (`ckey`, `role`, `selected`, `ignored`, `seen`) VALUES (?, ?, 0, 1, 0)", ['ss', $_GET['player'], $_GET['role']]);
		}
	}
// Deal with multiples
} elseif(is_array($_GET['players'])) {
	// Loop over all the players
	foreach($_GET['players'] as $player) {
		$result = sql_query("SELECT * FROM `antag` WHERE `ckey` = ? AND `role` = ?", ['ss', $player['ckey'], $player['role']]);
		if($result) { // row exists
			if(key_exists('assday', $_GET)) { // late join antag
				sql_query("UPDATE `antag` SET `ignored` = `ignored` + 1 WHERE `ckey` = ? AND `role` = ?", ['ss', $player['ckey'], $player['role']]);
			} else {
				sql_query("UPDATE `antag` SET `selected` = `selected` + 1 WHERE `ckey` = ? AND `role` = ?", ['ss', $player['ckey'], $player['role']]);
			}
		} else { // row doesn't exist
			if(key_exists('assday', $_GET)) { // late join antag
				sql_query("INSERT INTO `antag` (`ckey`, `role`, `selected`, `ignored`, `seen`) VALUES (?, ?, 1, 0, 0)", ['ss', $player['ckey'], $player['role']]);
			} else {
				sql_query("INSERT INTO `antag` (`ckey`, `role`, `selected`, `ignored`, `seen`) VALUES (?, ?, 0, 1, 0)", ['ss', $player['ckey'], $player['role']]);
			}
		}

	}
// Lolwut we're missing either option what on earth is going on down there
} else {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

echo JSON_SUCCESS;

?>
