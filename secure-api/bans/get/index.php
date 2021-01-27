<?php
// Boilerplate Request Validation and Includes Code
require '../../config.php';
require '../../utils.php';

header("Content-Type: application/json");

if(!check_auth(true, false, false, true)) {
	http_response_code(401);
	return;
}

if(!check_params(['search', 'sort', 'order', 'offset', 'limit', 'removed'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

// Because I can't specify the sort order with prepared statements, I need to sanitize the order param, on the off chance that someone manages to compromize the server AND get the API key AND make a valid request
$saniOrder = sql_escape("`{$_GET['sort']}` {$_GET['order']}");
// Var to store number of bans selected
$totalBans = 0;
// Universal var to store our results to. We can avoid some copypasta with this.
$rows = array();
// Do some stuff with the IP address


// Handle each different type of search
if(key_exists('all', $_GET['search'])) {
	$term = $_GET['search']['all'];

	// First get total number of bans from the selection
	$totalBans = sql_query("SELECT * FROM `bans` WHERE `removed` = ? AND (`ckey` LIKE CONCAT('%', ?, '%') OR `akey` LIKE CONCAT('%', ?, '%') OR `reason` LIKE CONCAT('%', ?, '%') OR `compid` = ? OR `ip` = ?)",
	[
		'issssi',
		$_GET['removed'],
		$term,
		$term,
		$term,
		$term,
		ip_to_int($term),
	]);

	// Now through the power of copypasta, get required amount of rows, god it's so ugly
	$rows = sql_query("SELECT * FROM `bans` WHERE `removed` = ? AND (`ckey` LIKE CONCAT('%', ?, '%') OR `akey` LIKE CONCAT('%', ?, '%') OR `reason` LIKE CONCAT('%', ?, '%') OR `compid` = ? OR `ip` = ?) ORDER BY {$saniOrder} LIMIT ? OFFSET ?",
	[
		'issssiii',
		$_GET['removed'],
		$term,
		$term,
		$term,
		$term,
		ip_to_int($term),
		$_GET['limit'],
		$_GET['offset']
	], true);
} elseif(key_exists('ckey', $_GET['search'])) {
	$term = $_GET['search']['ckey'];

	$totalBans = sql_query("SELECT * FROM `bans` WHERE `removed` = ? AND `ckey` LIKE CONCAT('%', ?, '%')", ['is', $_GET['removed'], $term]);

	$rows = sql_query("SELECT * FROM `bans` WHERE `removed` = ? AND `ckey` LIKE CONCAT('%', ?, '%') ORDER BY {$saniOrder} LIMIT ? OFFSET ?", ['isii', $_GET['removed'], $term, $_GET['limit'], $_GET['offset']], true);
} elseif(key_exists('akey', $_GET['search'])) {
	$term = $_GET['search']['akey'];

	$totalBans = sql_query("SELECT * FROM `bans` WHERE `removed` = ? AND `akey` LIKE CONCAT('%', ?, '%')", ['is', $_GET['removed'], $term]);

	$rows = sql_query("SELECT * FROM `bans` WHERE `removed` = ? AND `akey` LIKE CONCAT('%', ?, '%') ORDER BY {$saniOrder} LIMIT ? OFFSET ?", ['isii', $_GET['removed'], $term, $_GET['limit'], $_GET['offset']], true);
} elseif(key_exists('reason', $_GET['search'])) {
	$term = $_GET['search']['reason'];

	$totalBans = sql_query("SELECT * FROM `bans` WHERE `removed` = ? AND `reason` LIKE CONCAT('%', ?, '%')", ['is', $_GET['removed'], $term]);

	$rows = sql_query("SELECT * FROM `bans` WHERE `removed` = ? AND `reason` LIKE CONCAT('%', ?, '%') ORDER BY {$saniOrder} LIMIT ? OFFSET ?", ['isii', $_GET['removed'], $term, $_GET['limit'], $_GET['offset']], true);
} elseif(key_exists('compID', $_GET['search'])) {
	$term = $_GET['search']['compID'];

	$totalBans = sql_query("SELECT * FROM `bans` WHERE `removed` = ? AND `compid` = ?", ['is', $_GET['removed'], $term]);

	$rows = sql_query("SELECT * FROM `bans` WHERE `removed` = ? AND `compid` = ? ORDER BY {$saniOrder} LIMIT ? OFFSET ?", ['isii', $_GET['removed'], $term, $_GET['limit'], $_GET['offset']], true);
} elseif(key_exists('ip', $_GET['search'])) {
	$term = $_GET['search']['ip'];

	$totalBans = sql_query("SELECT * FROM `bans` WHERE `removed` = ? AND `ip` = ?", ['ii', $_GET['removed'], ip_to_int($term)]);

	$rows = sql_query("SELECT * FROM `bans` WHERE `removed` = ? AND `ip` = ? ORDER BY {$saniOrder} LIMIT ? OFFSET ?", ['iiii', $_GET['removed'], ip_to_int($term), $_GET['limit'], $_GET['offset']], true);
} else {
	// wut
	echo json_error("No valid search term provided");
	return;
}

// copypasta finally over, build response from the bans array.
$response = array();
$response['total'] = $totalBans;

if($rows) {
	foreach($rows as $row) {
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

// Should have full response now, convert to json and print out
echo json_encode($response, JSON_FORCE_OBJECT);

?>
