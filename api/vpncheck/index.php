<?php
// Boilerplate Request Validation and Includes Code
require '../config.php';
require '../utils.php';

header("Content-Type: application/json");

if(!check_auth()) {
	http_response_code(401);
	return;
}

if(!check_params(['ckey', 'ip'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

// Don't kill the whole endpoint if DB dead. Fail safe and tell the server they're not whitelisted, if DB can't be reached
$output = sql_query("SELECT * FROM `vpn_whitelist` WHERE `ckey` = ?", ['s', $_GET['ckey']], false, true);

$result = ['whitelisted' => boolval($output), 'response' => ''];
// We have no authorization to make a query, so just tell the server the IP is safe.
if($vpnAuth === "none") {
	$result['response'] = json_encode(['security' => ['vpn' => false, 'tor' => false, 'proxy' => false, 'hosting' => false]]);
} else { //we're authorized, query the API
	$result['response'] = file_get_contents("https://ipinfo.io/{$_GET['ip']}/privacy?token={$vpnAuth}");
}

// We get the response, at last, echo it out and we're done.
echo json_encode($result);

?>
