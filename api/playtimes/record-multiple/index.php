<?php
// Boilerplate Request Validation and Includes Code
require '../../config.php';
require '../../utils.php';

header("Content-Type: application/json");

if(!check_auth()) {
	http_response_code(401);
	return;
}

if(!check_params(['ckeys'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

foreach($_GET['ckeys'] as $key => $value) {
	$response = sql_query("SELECT * FROM `player` WHERE `ckey` = ?", ['s', $key], true);

	$newTime = (int)$value + (int)$response[0]['playtime'];

	sql_query("UPDATE `player` SET `playtime` = ? WHERE `ckey` = ?", ['is', $newTime, $key]);

	$updated = true;
}

echo JSON_SUCCESS;

?>
