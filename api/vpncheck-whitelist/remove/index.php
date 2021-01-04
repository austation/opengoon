<?php
// Boilerplate Request Validation and Includes Code
require '../../config.php';
require '../../utils.php';

header("Content-Type: application/json");

if(!check_auth()) {
	http_response_code(401);
	return;
}

if(!check_params(['ckey', 'akey'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

sql_query("DELETE FROM `vpn_whitelist` WHERE `ckey` = ?", ['s', $_GET['ckey']]);

echo JSON_SUCCESS;

?>
