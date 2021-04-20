<?php
// Boilerplate Request Validation and Includes Code
require '../config.php';
require '../utils.php';

// Endpoint returns plaintext URL for the game server to play.
header("Content-Type: text/plain");

// Note: specific errors can't be given because the goon server doesn't handle them.
// For this endpoint, all it cares about are status codes.
if(!check_auth(false, 'api_key', true)) {
	http_response_code(401);
	return;
}

if(!check_params(['dectalk'], $_GET)) {
	http_response_code(400);
	return;
}

// NOTE: THIS ENDPOINT IS WINDOWS ONLY! DECTALK IS ONLY AVAILABLE AS AN EXE FILE
// Though ngl, this whole API is built around windows anyway. Look at cloverfield for a linux solution.
if(!$dectalkPath) {
	http_response_code(501);
	return;
}

// Decode the input text.
$urlDecoded = urldecode($_GET['dectalk']);

// Now, for efficiency we take the md5 hash of the input string, and use it as the filename so we don't regenerate files we already have.
$urlDecoded = trim($urlDecoded);
$urlDecoded = strip_tags($urlDecoded);
$hash = md5($urlDecoded);

if(!file_exists("{$audioOutput}/{$hash}.mp3")) {
	// Generate the audio
	chdir($dectalkPath);
	$command = "say.exe -w " . escapeshellarg("{$audioOutput}/{$hash}.wav") . " " . escapeshellarg($urlDecoded);
	shell_exec($command);
	// Now convert to mp3 with ffmpeg
	$command = "ffmpeg -y -guess_layout_max 0 -i " . escapeshellarg("{$audioOutput}/{$hash}.wav") . " -acodec libmp3lame -ac 2 -b:a 128k " . escapeshellarg("{$audioOutput}/{$hash}.mp3");
	shell_exec($command);
	// Delete the original wav file
	unlink("{$audioOutput}/{$hash}.wav");
}

// Now all we have to do is send back the URL.
echo "{$audioWeb}/{$hash}.mp3"

?>
