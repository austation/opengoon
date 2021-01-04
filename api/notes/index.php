<?php
// Boilerplate Request Validation and Includes Code
require '../config.php';
require '../utils.php';

// This returns plaintext, containing some HTML tags the server interprets.
header("Content-Type: text/plain");

// Check auth key. I can't check the IP address since there is no server id passed in.
if(!key_exists('auth', $_GET) || $_GET['auth'] !== md5($authKey)) {
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

if(!check_params(['action'], $_GET)) {
	http_response_code(400);
	return;
}

// This is a snowflake endpoint used to handle player notes. It works a bit differently to the others.
if($_GET['action'] == "get") {
	// Check ckey param is given
	if(!check_params(['ckey'], $_GET)) {
		http_response_code(400);
		return;
	}

	// Get their notes
	$result = sql_query("SELECT * FROM `notes` WHERE `ckey` = ?", ['s', $_GET['ckey']], true);

	// Nothing found, return early
	if(!$result) {
		echo "<b>No Notes Found</b>";
	}

	// Now iterate through and print each one out
	foreach($result as $row) {
		// Echo it out with some fancy formatting
		printf("<b>Timestamp:</b> %s <b>Admin:</b> %s <b>Server:</b> %s <b>Note:</b> %s !!ID%s\n", $row['timestamp'], $row['akey'], $row['server'], $row['note'], $row['id']);
	}
} elseif($_GET['action'] == "add") {
	// Check required params exist
	if(!check_params(['server', 'server_id', 'ckey', 'akey', 'note'], $_GET)) {
		http_response_code(400);
		return;
	}

	// Insert into the DB
	sql_query("INSERT INTO `notes` (`ckey`, `akey`, `server`, `note`) VALUES (?, ?, ?, ?)", ['ssss', $_GET['ckey'], $_GET['akey'], $_GET['server_id'], $_GET['note']]);

	// Echo something out so an error doesn't trip
	echo "Success";
} elseif($_GET['action'] == "delete") {
	// Check id param
	if(!check_params(['id'], $_GET)) {
		http_response_code(400);
		return;
	}

	// delete from DB
	sql_query("DELETE FROM `notes` WHERE `id` = ?", ['i', $_GET['id']]);

	echo "Success";
} else {
	http_response_code(400);
}

?>
