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
ini_set('user_agent', "opengoon-api / {$apiVersion}");

// Build a stream with the headers we need
$auth = base64_encode("{$tgsUser}:{$tgsPass}");
$instance = $servers[$_GET['data_server']]['instance'];
$options = [
	'http' => [
		'method' => 'GET',
		'header' => "Api: {$tgsApiVersion}\r\n" .
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
$response = json_decode($response);
$bearer = $response['bearer'];

// Update our context accordingly
$options = [
	'http' => [
		'method' => 'GET',
		'header' => "Api: {$tgsApiVersion}\r\n" .
			"Authorization: Bearer {$bearers}\r\n" .
			"Instance: {$instance}"
	]
];

$context = stream_context_create($options);

// Next order of business is to instruct TGS to update the map override file in _std/__map.dm, via code overrides.
// First, we're going to need to check if the file exists or not.
$fileHash = null;
$response = file_get_contents($tgsUrl . "Config/File/CodeModifications/_std/__map.dm", false, $context);
if($response) {
	// File exists
	$response = json_decode($response);
	$fileHash = $response['lastReadHash'];
}

// We have the file's previous hash now. We can send a request for a file ticket to write to it...
// Update our context accordingly
$content = ['path' => '/CodeModifications/_std/__map.dm', 'accessDenied' => null, 'isDirectory' => null, 'lastReadHash' => $fileHash];
$options = [
	'http' => [
		'method' => 'POST',
		'header' => "Api: {$tgsApiVersion}\r\n" .
			"Authorization: Bearer {$bearers}\r\n" .
			"Instance: {$instance}",
		'content' => json_encode($content)
	]
];

$context = stream_context_create($options);

// Yoink the result from the server
$response = file_get_contents($tgsUrl . "Config", false, $context);
if(!$response) {
	json_error("Failed to get a file ticket for map file");
	return;
}

// We should have a file ticket to write with now. Let's grab that.
$response = json_decode($response);
$fileTicket = urlencode($response['fileTicket']);

// Build up our desired map file define.
$fileContent = "#define MAP_OVERRIDE_{$_GET['map']}";

// Now do a put query - update context
$options = [
	'http' => [
		'method' => 'PUT',
		'header' => "Api: {$tgsApiVersion}\r\n" .
			"Authorization: Bearer {$bearers}\r\n" .
			"Instance: {$instance}",
		'content' => $fileContent
	]
];

$context = stream_context_create($options);

// Run the query
$response = file_get_contents($tgsUrl . "Transfer?ticket={$fileTicket}", false, $context);
if(!$response) {
	echo json_error("Failed to update the file on TGS");
	return;
}

// All going according to plan, we should now have the file in place with the right map override.
// Tell TGS to recompile
// Context
$options = [
	'http' => [
		'method' => 'PUT',
		'header' => "Api: {$tgsApiVersion}\r\n" .
			"Authorization: Bearer {$bearers}\r\n" .
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
$return = ['response' => 201];
echo json_encode($return);

?>
