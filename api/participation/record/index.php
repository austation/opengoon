<?php
// Boilerplate Request Validation and Includes Code
require '../../config.php';
require '../../utils.php';

header("Content-Type: application/json");

if(!check_auth()) {
	http_response_code(401);
	return;
}

if(!check_params(['ckey', 'round_mode'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

if(!sql_query("SELECT * FROM `player` WHERE `ckey` = ?", ['s', $_GET['ckey']])) {
	echo json_error("Ckey doesn't exist in database.");
	return;
}

sql_query("UPDATE `player` SET `participations` = `participations` + 1 WHERE `ckey` = ?", ['s', $_GET['ckey']]);

if(key_exists($_GET['round_mode'], $modes)) { // the mode has roundstart antags attached. let's update those for weighting.
	foreach($modes[$_GET['round_mode']] as $antag) { // loop over each possible antag for the mode
		// check if the DB has the mode or not
		if(!sql_query("SELECT * FROM `antag` WHERE `ckey` = ? AND `role` = ?", ['ss', $_GET['ckey'], $antag])) { // it doesn't exist in the db
			sql_query("INSERT INTO `antag` (`ckey`, `role`, `selected`, `ignored`, `seen`) VALUES (?, ?, 0, 0, 1)", ['ss', $_GET['ckey'], $antag]); // create a row for the antag
		} else { // does exist
			sql_query("UPDATE `antag` SET `seen` = `seen` + 1 WHERE `ckey` = ? AND `role` = ?", ['ss', $_GET['ckey'], $antag]); // update existing row
		}
	}
}

echo JSON_SUCCESS;

?>
