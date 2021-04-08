<?php
// Boilerplate Request Validation and Includes Code
require '../../config.php';
require '../../utils.php';

header("Content-Type: application/json");

if(!check_auth()) {
	http_response_code(401);
	return;
}

if(!check_params(['ckey', 'type'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

// Run the DB query
$result = sql_query("SELECT * FROM `exp` WHERE `ckey` = ? AND `type` = ?", ['ss', $_GET['ckey'], $_GET['type']], true);
$numero = 0;
if($result) {
	$numero = $result[0]['value'];
}

// This is literally the dumbest structure, thankyou franc
echo json_encode([$_GET['ckey'] => [$_GET['type'] => $numero]], JSON_FORCE_OBJECT);

?>
