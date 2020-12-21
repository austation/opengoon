<?php
// Boilerplate Request Validation and Includes Code
require '../config.php';
require '../utils.php';

header("Content-Type: application/json");

// Whew that's a lot of checks for authentication, including IP, auth key and checking the server key
if(!key_exists('auth', $_GET) || $_GET['auth'] !== md5($authKey) || !key_exists('data_server', $_GET) || !key_exists((int)$_GET['data_server'], $servers) || $servers[$_GET['data_server']]['ip'] !== $_SERVER['REMOTE_ADDR']) {
	http_response_code(401);
	return;
}

if(!check_params(['ckey', 'ip'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

// Don't kill the whole endpoint if DB dead. Fail safe and tell the server they're not whitelisted, if DB can't be reached
$whitelisted = false;
$db = mysqli_connect($databaseAddress, $databaseUser, $databasePassword, $databaseName);
if(!mysqli_connect_errno()) {
	$stmt = $db->stmt_init();
	$stmt->prepare("SELECT * FROM `vpn_whitelist` WHERE `ckey` = ?");
	$stmt->bind_param('s', $_GET['ckey']);
	if($stmt->execute()) {
		if($stmt->num_rows()) { // they're whitelisted
			$whitelisted = true;
		}
 	}
}

$result = ['whitelisted' => $whitelisted, 'response' => ''];
// We have no authorization to make a query, so just tell the server the IP is safe.
if($vpnAuth === "none") {
	$result['response'] = json_encode(['vpn' => false, 'tor' => false, 'proxy' => false, 'hosting' => false]);
} else { //we're authorized, query the API
	$result['response'] = file_get_contents("https://ipinfo.io/{$_GET['ip']}/privacy?token={$vpnAuth}");
}

// We get the response, at last, echo it out and we're done.
echo json_encode($result);

?>
