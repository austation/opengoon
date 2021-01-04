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
if(key_exists('put', $_GET)) {
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
} else if(key_exists('delete', $_GET)) {
	// Check params
	if(!check_params(['ckey', 'name'], $_GET)) {
		echo json_error("Missing params");
		return;
	}

	// Run a delete query and that's it
	sql_query("DELETE FROM `cloudsaves` WHERE `ckey` = ? AND `name` = ?", ['ss', $_GET['ckey'], $_GET['name']]);

	// Done!

// Get a play's savefil from DB
} else if(key_exists('get', $_GET)) {
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

/* PERSISTENT DATA METHODS */
// Put a value in the DB
} else if(key_exists('dataput', $_GET)) {
	// Check params
	if(!check_params(['ckey', 'key', 'value'], $_GET)) {
		echo json_error("Missing params");
		return;
	}

	// Check if a row exists already
	$result = sql_query("SELECT * FROM `persistent` WHERE `ckey` = ? AND `key` = ?", ['ss', $_GET['ckey'], $_GET['key']]);
	// Row exists, so we'll overwrite it
	if($result) {
		sql_query("UPDATE `persistent` SET `value` = ? WHERE `ckey` = ? AND `key` = ?", ['sss', $_GET['value'], $_GET['ckey'], $_GET['key']]);
	// Row doesn't exist, so make a new one
	} else {
		sql_query("INSERT INTO `persistent` (`ckey`, `key`, `value`) VALUES (?, ?, ?)", ['sss', $_GET['ckey'], $_GET['key'], $_GET['value']]);
	}

	// Done!

// Get persistent data and list of cloud saves from DB
} else if(key_exists('list', $_GET)) {
	// Check params
	if(!check_params(['ckey'], $_GET)) {
		echo json_error("Missing params");
		return;
	}

	// Now fetch the data from the DB
	// Let's do savefiles first.
	$sav_res = sql_query("SELECT * FROM `cloudsaves` WHERE `ckey` = ?", ['s', $_GET['ckey']], true);

	// Now get the persistent data
	$persist_res = sql_query("SELECT * FROM `persistent` WHERE `ckey` = ?", ['s', $_GET['ckey']], true);

	// Build initial response structure
	$response = [
		'saves' => array(),
		'cdata' => array()
	];

	// Deal with cloudsave data
	if($sav_res) {
		foreach($sav_res as $row) {
			$response['saves'][$row['name']] = strlen($row['data']);
		}
	}

	// Now persistent
	if($persist_res) {
		foreach($persist_res as $row) {
			$response['cdata'][$row['key']] = $row['value'];
		}
	}

	// Finally json encode and return.
	echo json_encode($response);
	return;
} else {
	json_error("Missing params");
	return;
}

// Default API return for stuff that doesn't give a response.
echo JSON_SUCCESS;

?>
