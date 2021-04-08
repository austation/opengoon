<?php
// Boilerplate Request Validation and Includes Code
require '../../config.php';
require '../../utils.php';

header("Content-Type: application/json");

if(!check_auth(true, false, false, true)) {
	http_response_code(401);
	return;
}

if(!check_params(['id'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

// Mostly copypasta from the ban deletion code
$ids = array();
// Var for loop shittery
$curId = $_GET['id'];

// Infinite loop. We'll break out when we can't find any more bans
while(true) {
	$dbStatus = sql_query("SELECT * FROM `bans` WHERE `id` = ?", ['i', $curId], true, true);
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

// Build our response
$response = array();
if($error) {
	$response['error'] = $error;
} else {
	$response['total'] = count($ids);
	foreach($ids as $id) {
		$result = sql_query("SELECT * FROM `bans` WHERE `id` = ?", ['i', $id], true);
		$row = $result[0];
		$response[$row['id']] = [
			'id' => $row['id'],
			'ckey' => $row['ckey'],
			'compID' => $row['compid'],
			'ip' => int_to_ip($row['ip']),
			'reason' => $row['reason'],
			'timestamp' => $row['timestamp'],
			'akey' => $row['akey'],
			'oakey' => $row['oakey'],
			'previous' => $row['previous'],
			'chain' => $row['chain']
		];

		if($row['server']) {
			$response[$row['id']]['server'] = $row['server'];
		}
	}
}

echo json_encode($response, JSON_FORCE_OBJECT);

?>
