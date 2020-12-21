<?php
require('config.php');

// Make a callback to a server. Takes a server address, port and associative array of arguments to pass, along with a proc and optional datum to call
function callback($addr, $port, $args, $proc, $datum = false) {
	global $authKey;
	$queryStr = "?auth=" . md5($authKey) . "&proc=" . $proc . ($datum !== false ? "&datum=" . $datum : "") . "&data=" . json_encode($args);
	$query = "\x00\x83" . pack('n', strlen($queryStr) + 6) . "\x00\x00\x00\x00\x00" . $queryStr . "\x00";

	$server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or exit("Could not create TCP socket");
	if(!socket_connect($server, $addr, $port)) {
		log_error("Connection to remote server failed!");
		return "ERROR: Connection to remote server failed!";
	}

	log_info("Sending callback query to server address " . $addr . " on port " . $port . " with query string " . $queryStr);

	$bytes_sent = 0;
	while($bytes_sent < strlen($query)) {
		$result = socket_write($server, substr($query, $bytes_sent));
		if($result === false) {
			log_error("ERROR: Failed to send data to remote server!");
			return "ERROR: Failed to send data to remote server!";
		}
		$bytes_sent += $result;
	}

	$response = socket_read($server, 100000);

	if($response != "") {
		if($response[0] == "\x00" || $response[1] == "\x83") { // make sure it's the right packet format

			// Actually begin reading the output:
			$sizebytes = unpack('n', $response[2] . $response[3]); // array size of the type identifier and content
			$size = $sizebytes[1] - 1; // size of the string/floating-point (minus the size of the identifier byte)

			if($response[4] == "\x2a") { // 4-byte big-endian floating-point
				$unpackint = unpack('f', $response[5] . $response[6] . $response[7] . $response[8]); // 4 possible bytes: add them up together, unpack them as a floating-point
				log_info("Server returned floating point value " . $unpackint[1]);
				return $unpackint[1];
			}
			else if($response[4] == "\x06") { // ASCII string
				$unpackstr = ""; // result string
				$index = 5; // string index

				while($size > 0) { // loop through the entire ASCII string
					$size--;
					$unpackstr .= $response[$index]; // add the string position to return string
					$index++;
				}
				log_info("Server returned string value " . $unpackstr);
				return $unpackstr;
			}
		}
	}

}

// Gets a simple JSON error string for the API.
function json_error($message) {
	return "{\"error\": \"{$message}\"}";
}

// Simple logging functions
function log_info($message) {
	$file_handle = fopen("log.txt", 'w');
	fwrite($file_handle, date("[Y-m-d H:i:s: INFO]") . $message . "\n");
	fclose($file_handle);
}

function log_error($message) {
	$file_handle = fopen("log.txt", 'w');
	fwrite($file_handle, date("[Y-m-d H:i:s: ERROR]") . $message . "\n");
	fclose($file_handle);
}
?>
