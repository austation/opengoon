<?php
// Boilerplate Request Validation and Includes Code
require '../../config.php';
require '../../utils.php';

header("Content-Type: application/json");

if(!check_auth()) {
	http_response_code(401);
	return;
}

if(!check_params(['id', 'ckey', 'compID', 'ip', 'akey'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

$serverKey = $_GET['data_server'];

// Send the response to the client - this code is for efficiency, and lets callbacks happen in the background without the server waiting for a response (the whole point of a callback is to run in the background lmao)
// This is just boilerplate
ob_start();
echo JSON_SUCCESS; // Tidbit of json so the server won't screech because no body
$size = ob_get_length();
header("Content-Encoding: none");
header("Content-Length: {$size}");
header("Connection: close");
http_response_code(202);
ob_end_flush();
ob_flush();
flush();
if(session_id()) session_write_close();

// Vars for error checking
$dbStatus;
$error = false;

// If we're deleting an evasion ban, follow the IDs back and we'll delete each of them.
$ids = array();
// Var for loop shittery
$curId = $_GET['id'];

// Infinite loop. We'll break out when we can't find any more bans
while(true) {
	$dbStatus = sql_query("SELECT * FROM `bans` WHERE `removed` = FALSE AND `id` = ?", ['i', $curId], true, true);
	if($dbStatus === 0) {
		// couldn't find a matching ban for that ID. If it's the id we were given, error out, else we have all the IDs we can get
		if($curId == $_GET['id']) {
			$error = "Given ban id doesn't exist in the database";
		}
		break;
	} elseif($dbStatus === false) {
		// database error. panic.
		$error = "Failed to select from database.";
		break;
	} else {
		// we found the matching ban. Add to the list. Next up, let's look for another chained id
		$ids[] = $curId;
		$nextId = $dbStatus[0]['previous'];
		if($nextId === 0) {
			// This is an admin applied ban, not chained. We're done.
			break;
		} else {
			// another ban came before. let's check that ID now.
			$curId = $nextId;
		}
	}
}

// Now we should have a full list of every ban to remove. Loop over them and run a deletion query.
if(!$error) {
	foreach($ids as $id) {
		$dbStatus = sql_query("UPDATE `bans` SET `removed` = TRUE WHERE `id` = ?", ['i', $id]);
		if($dbStatus === false) {
			$error = "Failed to delete ban id {$id} from the DB. Aborting.";
			break;
		}
	}
}

// Build our response to the server via callback
$response;
if($error) {
	$response = ['error' => $error];
} else {
	$response = ['ban' => [
		'ckey' => $_GET['ckey'],
		'ip' => $_GET['ip'],
		'compID' => $_GET['compID'],
		'akey' => $_GET['akey'],
	]];
}

callback($servers[$serverKey]['ip'], $servers[$serverKey]['port'], $response, "deleteBan");

?>
