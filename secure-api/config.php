<?php
// This file is for configuration options for the API. Included by all other files that handle requests.
// Note: This is a stripped down config file for the secure endpoints.

// Current API Version
$apiVersion = 1.6;

// Key used to authenticate communications between the BYOND server and the API. Keep this secure.
$authKey = "auth_key";

// Secret used to sign JWT tokens for verification
$jwtSecret = "jwt_secret";

// Address used to access the database. SQL file for easy setup included.
$databaseAddress = "127.0.0.1";
// DB username
$databaseUser = "opengoon";
// DB pass
$databasePassword = "opengoon";
// DB name
$databaseName = "opengoon-api";

// Assoc list of servers and their IP + port + (optional tgs instance ID for map switching.), tied to an ID
$servers = [
	1 => ['ip' => "127.0.0.1", 'port' => 4975, 'instance' => null]
];

// Verbose logging toggle. Toggles trace logs in the log.txt file. Turn this off in production environments (as if anyone would use this for a real server LMAO, Right?)
$verbose = true;

// Authentication Toggle - DEBUG ONLY, KEEP ENABLED ON PRODUCTION SERVERS!!!
$authentication = true;
?>
