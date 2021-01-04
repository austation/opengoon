<?php
// Boilerplate Request Validation and Includes Code
require '../../config.php';
require '../../utils.php';

header("Content-Type: application/json");

if(!check_auth()) {
	http_response_code(401);
	return;
}

if(!check_params(['ckey', 'rank'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

// just do the delete query
sql_query("DELETE FROM `jobbans` WHERE `ckey` = ? AND `role` = ?", ['ss', $_GET['ckey'], $_GET['rank']]);

echo JSON_SUCCESS;

?>
