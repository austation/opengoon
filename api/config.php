<?php
// This file is for configuration options for the API. Included by all other files that handle requests.

// Key used to authenticate communications between the BYOND server and the API. Keep this secure.
$authKey = "auth_key";

// Address used to access the database. SQL file for easy setup included.
$databaseAddress = "127.0.0.1";
// DB username
$databaseUser = "root";
// DB pass
$databasePassword = "root";
// DB name
$databaseName = "goonhub";

// VPN Checker Auth Key - Set to "none" to disable VPN checking and return all IPs as safe. Service used is ipinfo.io
$vpnAuth = "none";

// Assoc list of servers and their IP + port, tied to an ID
$servers = [
	1 => ['ip' => "127.0.0.1", 'port' => 2337]
];




// This associated list pairs a mode with all the possible roundstart antags it can have. You probably shouldn't edit this.
// If a key is not present, the mode has no roundstart antags
$modes = [
	'traitor' => ['traitor', 'wraith'],
	'wizard' => ['wizard'],
	'waldo' => ['waldo'],
	'vampire' => ['vampire', 'wraith', 'grinch'],
	'conspiracy' => ['spy', 'conspirator'], // the funky one. actually covers two modes with the same internal name. Spy and Conspiracy
	'spy_thief' => ['spy_thief'],
	'revolution' => ['head_rev'],
	'nuclear emergency' => ['nukeop'],
	'mixed (action)' => ['traitor', 'changeling', 'vampire', 'spy_thief', 'werewolf', 'wizard', 'blob', 'wraith', 'grinch'],
	'mixed (mild)' => ['traitor', 'changeling', 'vampire', 'spy_thief', 'wraith', 'grinch'],
	'gang' => ['gang_leader'],
	'flock' => ['flockmind'],
	'changeling' => ['changeling'],
	'blob' => ['blob'],
	'Battle Royale' => ['battler'],
	'Everyone-Is-A-Traitor Mode' => ['traitor', 'wraith']
];
?>
