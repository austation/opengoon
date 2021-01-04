<?php
// Boilerplate Request Validation and Includes Code
require '../../config.php';
require '../../utils.php';
require '../../userAgentParser.php'; // it just works

header("Content-Type: application/json");

if(!check_auth()) {
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

$parser = new parseUserAgentStringClass();
$parser->includeAndroidName = true;
$parser->includeWindowsName = true;
$parser->includeMacOSName = true;
$parser->parseUserAgentString($player['ua']);

$response = [
	'seen' => $player['connections'],
	'participated' => $player['participations'],
	'byondMajor' => $player['byond_major'],
	'byondMinor' => $player['byond_minor'],
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
