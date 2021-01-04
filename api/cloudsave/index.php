<?php
// This is a big endpoint that handles ALL cloudsave data

// Boilerplate Request Validation and Includes Code
require '../config.php';
require '../utils.php';

// Check auth key. I can't check the IP address since there is no server id passed in.
if(!key_exists('api_key', $_GET) || $_GET['api_key'] !== md5($authKey)) {
	http_response_code(401);
	return;
}

// Because the server doesn't give us an ID we can use as a handle to retrieve the expected IP
// We'll just loop through the list of servers and find one that matches
$valid = false;
foreach($servers as $key => $value) {
	if($value['ip'] === $_SERVER['REMOTE_ADDR']) {
		// found the IP
		$valid = true;
	}
}
if(!$valid) {
	http_response_code(401);
	return;
}

/* SAVEFILE METHODS */
// Store a player's savefile to the DB
if(key_exists("put", $_GET)) {
	// Check required params
	if(!check_params(['ckey', 'name', 'data'], $_GET)) {
		echo json_error("Missing params");
		return;
	}

	// Check if a row exists already
	$result = sql_query("SELECT * FROM `cloudsaves` WHERE `ckey` = ? AND `name` = ?", ['ss', $_GET['ckey'], $_GET['name']]);
	// Row exists, so we'll overwrite it
	if($result) {
		sql_query("UPDATE `cloudsaves` SET `data` = ? WHERE `ckey` = ? AND `name` = ?", ['sss', $_GET['data'], $_GET['ckey'], $_GET['name']]);
	// Row doesn't exist, so make a new one
	} else {
		sql_query("INSERT INTO `cloudsaves` (`ckey`, `name`, `data`) VALUES (?, ?, ?)", ['sss', $_GET['ckey'], $_GET['name'], $_GET['data']]);
	}

	// That's it!

// Delete a player's savefile from DB
} else if(key_exists("delete", $_GET)) {
	// Check params
	if(!check_params(['ckey', 'name'], $_GET)) {
		echo json_error("Missing params");
		return;
	}

	// Run a delete query and that's it
	sql_query("DELETE FROM `cloudsaves` WHERE `ckey` = ? AND `name` = ?", ['ss', $_GET['ckey'], $_GET['name']]);

	// Done!

// Get a play's savefil from DB
} else if(key_exists("get", $_GET)) {
	// Check params
	if(!check_params(['ckey', 'name'], $_GET)) {
		echo json_error("Missing params");
		return;
	}

	// Run a select query with these params
	$response = sql_query("SELECT * FROM `cloudsaves` WHERE `ckey` = ? AND `name` = ?", ['ss', $_GET['ckey'], $_GET['name']], true);

	// We didn't get any rows as a response
	if($response === 0) {
		echo json_error("Couldn't find a matching savefile");
		return;
	}

	// Format response and return it
	$return = ['savefile' => $response[0]['data']];
	echo json_encode($return);
	return;
}

// Default API return for stuff that doesn't give anything back.
echo JSON_SUCCESS;

?>