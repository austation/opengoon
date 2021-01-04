<?php
// Boilerplate Request Validation and Includes Code
require '../../config.php';
require '../../utils.php';

header("Content-Type: application/json");

if(!check_auth()) {
	http_response_code(401);
	return;
}

if(!check_params(['ckey', 'userAgent', 'byondMajor', 'byondMinor'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

if(!sql_query("SELECT * FROM `player` WHERE `ckey` = ?", ['s', $_GET['ckey']])) {
	echo json_error("Ckey doesn't exist in database.");
	return;
}

sql_query("UPDATE `player` SET `ua` = ?, `byond_major` = ?, `byond_minor` = ? WHERE `ckey` = ?", ['siis', $_GET['userAgent'], $_GET['byondMajor'], $_GET['byondMinor'], $_GET['ckey']]);

echo JSON_SUCCESS;

?>
