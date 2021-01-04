<?php
// Boilerplate Request Validation and Includes Code
require '../../../config.php';
require '../../../utils.php';

header("Content-Type: application/json");

if(!check_auth()) {
	http_response_code(401);
	return;
}

if(!check_params(['ckey', 'data_id'], $_GET)) { // data_id is the server's name, i.e. main, rp etc
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

// Query the DB for all jobbans
$result = sql_query("SELECT * FROM `jobbans` WHERE `ckey` = ? AND (`server` = ? OR `server` IS NULL)", ['ss', $_GET['ckey'], $_GET['data_id']], true);

// Now loop through them and add everything to a list
$bans = array();
if($result) {
	foreach($result as $row) {
		$bans[] = $row['role'];
	}
}

// Now we can build our response and return it
$response = [
	$_GET['ckey'] => $bans
];

echo json_encode($response);

?>
