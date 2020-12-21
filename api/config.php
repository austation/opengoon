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

// Assoc list of servers and their IP + port, tied to an ID
$servers = [
	1 => ['ip' => "127.0.0.1", 'port' => 2337]
];
?>
