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

if(!check_params(['role', 'mode', 'players'], $_GET)) { // I'm so god tier smart that I don't even need the mode anymore, but still make sure it's there for correctness
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

$response = ['history' => array()]; // init our response object
foreach($_GET['players'] as $ckey) {
	// get number of antag rounds
	$selected = 0;
	$seen = 0;
	$result = sql_query("SELECT * FROM `antag` WHERE `ckey` = ? AND `role` = ?", ['ss', $ckey, $_GET['role']], true);
	if($result) {// they have antag rounds rounds on record for this mode and role
		$selected = $result[0]['selected'];
		$seen = max($result[0]['seen'] - $result[0]['ignored'], 0); // because I'm a chad, unlike some people I account for ass day.
	}

	// We have the data now, we can create an entry in the response data.
	$response['history'][$ckey]['selected'] = $selected;
	$response['history'][$ckey]['seen'] = $seen;
}

// Now we should have our full list of history built. Return it.
echo json_encode($response);

?>
