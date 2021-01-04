<?php
// Boilerplate Request Validation and Includes Code
require '../../config.php';
require '../../utils.php';

header("Content-Type: application/json");

if(!check_auth()) {
	http_response_code(401);
	return;
}

if(!check_params(['ckey', 'akey', 'rank', 'applicable_server'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

// Do a quick duplicate entry check
$rows = sql_query("SELECT * FROM `jobbans` WHERE `ckey` = ? AND `role` = ?", ['ss', $_GET['ckey'], $_GET['rank']]);
if($rows) {
	echo json_error("Ckey already has a jobban for provided role");
	return;
}

// Now we can insert the ban into the DB
if(empty($_GET['applicable_server'])) { // slightly different syntax if that's not set
	sql_query("INSERT INTO `jobbans` (`ckey`, `role`, `akey`, `server`) VALUES (?, ?, ?, NULL)", ['sss', $_GET['ckey'], $_GET['rank'], $_GET['akey']]);
} else {
	sql_query("INSERT INTO `jobbans` (`ckey`, `role`, `akey`, `server`) VALUES (?, ?, ?, ?)", ['ssss', $_GET['ckey'], $_GET['rank'], $_GET['akey'], $_GET['applicable_server']]);
}

// we're done!
echo JSON_SUCCESS;

?>
