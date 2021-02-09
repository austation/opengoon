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

$youtubeRegex = '/\b(?:(?:youtube\.com\/watch\?v=)|(?:youtu\.be\/)|)((?:[a-z]|[A-Z]|[0-9]|[-_]){11})\b/';
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
$command = "python " . $youtubedlPath . " -x --audio-format vorbis -o \"{$youtubeAudioOutput}/%(id)s.%(ext)s\" " . escapeshellarg($urlDecoded) . " 2>&1";

shell_exec($command);

// Grab the filesize in KiB for funnies
$size = filesize("{$youtubeAudioOutput}/{$urlDecoded}.ogg") / 1024;

// Should have the file downloaded now, can do our callback
$data = [
	"file" => "{$youtubeAudioWeb}/{$urlDecoded}.ogg",
	"key" => $_GET['key'],
	"title" => "YouTube Audio", // Todo actually get song title
	"filesize" => "{$size}KiB",
	"duration" => "Unknown Duration" // Also todo
];

callback($servers[$_GET['server']]['ip'], $servers[$_GET['server']]['port'], $data, false, false, "youtube")

?>
