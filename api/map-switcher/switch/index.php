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

// First we'll check if TGS is enabled, and that the server has a provided instance ID.
if($tgsUrl === "none" || is_null($servers[$_GET['data_server']]['instance'])) {
	echo json_error("Map switching is not supported for the API or provided server");
	return;
}

// Sweet! TGS is (apparently) available and the server sending the request has an instance.
// First we'll need to authenticate and get our JWT token.

// Set our user agent so TGS knows we're not a browser...
ini_set('user_agent', "opengoon-api");

// Build a stream with the headers we need
$auth = base64_encode("{$tgsUser}:{$tgsPass}");
$instance = $servers[$_GET['data_server']]['instance'];
$options = [
	'http' => [
		'method' => 'POST',
		'header' => "Api: {$tgsApiVersion}\r\n" .
			"Accept: application/json\r\n" .
			"Content-Length: 0\r\n" .
			"Authorization: Basic {$auth}\r\n"
	]
];

$context = stream_context_create($options);

$response = file_get_contents($tgsUrl, false, $context);

if(!$response) {
	echo json_error("Failed to authenticate with TGS");
	return;
}

// All going well we have our token, so
$response = json_decode($response, true);
$bearer = $response['bearer'];

// Tell TGS to recompile
// Context
$options = [
	'http' => [
		'method' => 'PUT',
		'header' => "Api: {$tgsApiVersion}\r\n" .
			"Accept: application/json\r\n" .
			"Content-Length: 0\r\n" .
			"Authorization: Bearer {$bearer}\r\n" .
			"Instance: {$instance}"
	]
];

$context = stream_context_create($options);

// Run it
$response = file_get_contents($tgsUrl . "DreamMaker", false, $context);
if(!$response) {
	echo json_error("Failed to start compilation. Map changed but server not recompiling! Was an update already running?");
	return;
}

// Holy moly, finally got here. All done. Just return the right status code and we're okay, hopefully.
// Tell the server everything's good. TGS will callback to the map switcher when compilation finishes.
http_response_code(201);
$return = ['response' => "201"];
echo json_encode($return);

?>
