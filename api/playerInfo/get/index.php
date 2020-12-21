<?php
// Boilerplate Request Validation and Includes Code
require '../../config.php';
require '../../utils.php';
require '../../userAgentParser.php'; // it just works

header("Content-Type: application/json");

// Whew that's a lot of checks for authentication, including IP, auth key and checking the server key
if(!key_exists('auth', $_GET) || $_GET['auth'] !== md5($authKey) || !key_exists('data_server', $_GET) || !key_exists((int)$_GET['data_server'], $servers) || $servers[$_GET['data_server']]['ip'] !== $_SERVER['REMOTE_ADDR']) {
	http_response_code(401);
	return;
}

if(!check_params(['ckey'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

$player = sql_query("SELECT * FROM `player` WHERE `ckey` = ?", ['s', $_GET['ckey']], true);
if(!$player) {
	echo json_error("Ckey doesn't exist in database.");
	return;
}
$player = $player[0]; // sneaky simplification

$participated = 0;
$return = sql_query("SELECT `seen` FROM `participated` WHERE `ckey` = ?", ['s', $_GET['ckey']], true);
if($return) {
	foreach($return as $row) {
		$participated += $row['seen'];
	}
}

$parser = new parseUserAgentStringClass();
$parser->includeAndroidName = true;
$parser->includeWindowsName = true;
$parser->includeMacOSName = true;
$parser->parseUserAgentString($player['ua']);

$response = [
	'seen' => $player['connections'],
	'participated' => $participated,
	'byondMajor' => $player['byondMajor'],
	'byondMinor' => $player['byondMinor'],
	'platform' => $parser->osname,
	'browser' => $parser->browsername,
	'browserVersion' => $parser->browserversion,
	//'browserMode' => null, - ommited because the parser doesn't supply a value for it
	'last_ip' => int_to_ip((int)$player['ip']), // decode
	'last_compID' => $player['compid']
];

// We got the response, after all that lmao, print it out
echo json_encode($response);

?>
