<?php
// Boilerplate Request Validation and Includes Code
require '../../config.php';
require '../../utils.php';

header("Content-Type: application/json");

if(!check_auth(false, false, true)) {
	http_response_code(401);
	return;
}

if(!check_params(['server', 'key', 'video'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

if(!$youtubedlPath) {
	echo json_error("YouTube DL is disabled");
	return;
}

// GNARLY, BRUH!
$youtubeRegex = '/^(?:(?:https:\/\/)|)(?:(?:(?:(?:www\.)|)youtube\.com\/watch\?v=)|(?:youtu\.be\/)|)((?:[a-z]|[A-Z]|[0-9]|[-_]){11})$/';
$urlDecoded = urldecode($_GET['video']);

// Extract the ID from the text
if(!preg_match($youtubeRegex, $urlDecoded, $matches)) {
	echo json_error("Invalid URL format");
	return;
}

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

$id = $matches[1];
// Be efficient; have a plan to kill everyone you meet
if(!file_exists("{$audioOutput}/{$id}.mp3")) {
	$command = "python " . $youtubedlPath . " -x --audio-format mp3 -o \"{$audioOutput}/%(id)s.%(ext)s\" " . escapeshellarg($id) . " 2>&1";
	shell_exec($command);
}

// We do a little bit of trolling... and cache the video's json data too
if(!file_exists("{$audioOutput}/{$id}.json")) {
	$command = "python " . $youtubedlPath . " -j " . escapeshellarg($id) . " 1>" . escapeshellarg("{$audioOutput}/{$id}.json") . " 2>nul";
	shell_exec($command);
}

// Now retrieve the json data from file
$data = json_decode(file_get_contents("{$audioOutput}/{$id}.json"), true);

// Grab the filesize in KiB for funnies
$size = round(filesize("{$audioOutput}/{$id}.mp3") / 1024);

// Should have the file downloaded now, can do our callback
$data = [
	"file" => "{$audioWeb}/{$id}.mp3",
	"key" => $_GET['key'],
	"title" => $data['title'], // Todo actually get song title
	"filesize" => "{$size}KiB",
	"duration" => "{$data['duration']}s" // Also todo
];

callback($servers[$_GET['server']]['ip'], $servers[$_GET['server']]['port'], $data, false, false, "youtube")

?>
