<?php
// Boilerplate Request Validation and Includes Code
require '../../config.php';
require '../../utils.php';

header("Content-Type: application/json");

if(!check_auth()) {
	http_response_code(401);
	return;
}

if(!check_params(['ckey', 'type', 'value'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

// Check if entry exists
if(!sql_query("SELECT * FROM `exp` WHERE `ckey` = ? AND `type` = ?", ['ss', $_GET['ckey'], $_GET['type']])) {
	// It doesn't so we make a new entry
	sql_query("INSERT INTO `exp` (`ckey`, `type`, `value`) VALUES (?, ?, ?)", ['ssi', $_GET['ckey'], $_GET['type'], $_GET['value']]);
} else {
	// Add it
	sql_query("UPDATE `exp` SET `value` = ? WHERE `ckey` = ? AND `type` = ?", ['iss', $_GET['value'], $_GET['ckey'], $_GET['type']]);
}

echo JSON_SUCCESS;

?>
