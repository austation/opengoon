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

if(!check_params(['cause', 'map', 'votedFor'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

// This one is left unimplemented for now. Once I get a server running, I'll tie it into a github workflow webhook or something, that can make a callback to the server.
echo json_error("Map switching is not implemented on the API");

?>
