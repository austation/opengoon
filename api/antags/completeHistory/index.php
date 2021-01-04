<?php
// Boilerplate Request Validation and Includes Code
require '../../config.php';
require '../../utils.php';

header("Content-Type: application/json");

if(!check_auth()) {
	http_response_code(401);
	return;
}

if(!check_params(['player'], $_GET)) { // I'm so god tier smart that I don't even need the mode anymore, but still make sure it's there for correctness
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

// Get their history from the DB
$result = sql_query("SELECT * FROM `antag` WHERE `ckey` = ?", ['s', $_GET['player']], true);

// Parse each entry and start building our response
$response = ['history' => array()];
if($response) {
	foreach($result as $row) {
		$response['history'][$row['role']] = ['selected' => $row['selected'], 'seen' => $row['seen'], 'percent' => round(($row['selected'] / $row['seen']) * 100)];
	}
}

echo json_encode($response);

?>
