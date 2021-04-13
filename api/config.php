<?php
// This file is for configuration options for the API. Included by all other files that handle requests.

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

// VPN Checker Auth Key - Set to "none" to disable VPN checking and return all IPs as safe. Service used is ipinfo.io
$vpnAuth = "none";

// Assoc list of servers and their IP + port + (optional tgs instance ID for map switching.), tied to an ID
$servers = [
	1 => ['ip' => "127.0.0.1", 'port' => 4975, 'instance' => null]
];

// URL that the TGS API lives at. Used for the map switcher to recompile a map. Uses the latest TGS API spec. (I'll try to keep this up to date.) Set to "none" to disable the map switcher.
// Needs *ONE* trailing /
$tgsUrl = "none";

// TGS Username
$tgsUser = "Admin";

// TGS Pass
$tgsPass = "Admin";

// TGS API Version - Check if code changes are needed before changing
$tgsApiVersion = "Tgstation.Server.Api/8.1.2";

// Path to the youtube-dl executable. Used for song downloads. Set to false to disable functionality.
$youtubedlPath = "C:\\youtube-dl";

// Directory to output downloaded audio to. Should be a webserver directory.
$youtubeAudioOutput = "C:/inetpub/wwwroot-rsc/youtube-audio";

// Web address audio is accessible at
$youtubeAudioWeb = "http://rsc.austation.net/youtube-audio";

// Verbose logging toggle. Toggles trace logs in the log.txt file. Turn this off in production environments (as if anyone would use this for a real server LMAO, Right?)
$verbose = true;

// Authentication Toggle - DEBUG ONLY, KEEP ENABLED ON PRODUCTION SERVERS!!!
$authentication = true;

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
	'Battle Royale' => ['battler'], // no actual weighting, but gotta get that sweet data, yo!
	'Everyone-Is-A-Traitor Mode' => ['traitor', 'wraith']
];
?>
