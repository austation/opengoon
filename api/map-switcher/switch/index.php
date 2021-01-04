<?php
// Boilerplate Request Validation and Includes Code
require '../../config.php';
require '../../utils.php';

header("Content-Type: application/json");

if(!check_auth()) {
	http_response_code(401);
	return;
}

if(!check_params(['cause', 'map', 'votedFor'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

// This one is left unimplemented for now. Once I get a server running, I'll tie it into a github workflow webhook or something, that can make a callback to the server.
echo json_error("Map switching is not implemented on the API");

?>
