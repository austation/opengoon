<?php
// Boilerplate Request Validation and Includes Code
require '../../config.php';
require '../../utils.php';

header("Content-Type: application/json");

if(!check_auth()) {
	http_response_code(401);
	return;
}

if(!check_params(['token'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

if(verify_jwt($_GET['token'])) {
	echo JSON_SUCCESS;
} else {
	echo json_error("JWT token invalid");
}
?>
