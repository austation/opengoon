<?php
require 'config.php';

const JSON_SUCCESS = "{\"success\":true}"; // success value because I'm lazy af and don't wanna type it. We just use this to add a small body to requests with no return, so the server doesn't die.

// Make a callback to a server. Takes a server address, port and associative array of arguments to pass, along with a proc and optional datum to call
function callback($addr, $port, $args, $proc, $datum = false) {
	global $authKey;
	$queryStr = "?type=hubCallback&auth=" . md5($authKey) . "&proc=" . $proc . ($datum !== false ? "&datum=" . $datum : "") . "&data=" . json_encode($args);
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

// Performs an SQL query using prepared statements, optionally binding and returning results
// Murders everything on fail
function sql_query($query, $params, $returnValues = false, $failSafe = false) {
	global $databaseAddress, $databaseUser, $databasePassword, $databaseName;
	$result = false;
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	// Only murder stuff if I ask for it
	try {
		$db = mysqli_connect($databaseAddress, $databaseUser, $databasePassword, $databaseName);
		$stmt = $db->stmt_init();
		$stmt->prepare($query);
		call_user_func_array(array($stmt, 'bind_param'), ref_values($params)); // this is CHAD

		$stmt->execute();
		// Weird juggling of row values
		$stmt_result = $stmt->get_result();
		if(!$returnValues || $stmt->affected_rows == 0 || $stmt_result->num_rows == 0) {
			if($stmt->affected_rows != -1) {
				$result = $stmt->affected_rows;
			} elseif($stmt_result) {
				$result = $stmt_result->num_rows;
			}
		} else {
			$result = array();
			while($row = $stmt_result->fetch_assoc()) {
				$result[] = $row;
			}
		}

		$stmt->close();
		$db->close();
	} catch(mysqli_sql_exception $e) {
		log_error($e->getMessage());
		log_error($e->getTraceAsString());
		if($failSafe) {
			return false;
		}
		echo json_error("Database error: " . $e->getMessage());
		exit;
	}
	return $result;
}

// Connects to the DB and escapes a string for (probably) safe use.
function sql_escape($str) {
	global $databaseAddress, $databaseUser, $databasePassword, $databaseName;
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	$db = mysqli_connect($databaseAddress, $databaseUser, $databasePassword, $databaseName);
	$out = $db->real_escape_string($str);
	$db->close();
	return $out;
}

// Referenceize arrays for arg passing
function ref_values($arr){
	if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
	{
		$refs = array();
		foreach($arr as $key => $value)
			$refs[$key] = &$arr[$key];
		return $refs;
	}
	return $arr;
}

function ip_to_int($ipStr) {
	if($ipStr == "N/A") {
		return 0;
	}
	$arr = preg_split('/\./', $ipStr);
	if(count($arr) != 4) {
		return -1;
	}
	foreach($arr as $val) {
		if(!is_numeric($val)) {
			return -1;
		}
	}
	return ((int)$arr[0] * (256 ** 3)) + ((int)$arr[1] * (256 ** 2)) + ((int)$arr[2] * 256) + ((int)$arr[3]);
}

function int_to_ip($ipInt) {
	if($ipInt == 0) {
		return "N/A";
	}
	$one = $ipInt & 255;
	$two = ($ipInt & 65280) >> 8;
	$three = ($ipInt & 16711680) >> 16;
	$four = ($ipInt & 4278190080) >> 24;
	return $four . '.' . $three . '.' . $two . '.' . $one;
}

// Takes an array of keys to look for, and an associated list to check.
// Returns true if all keys present, false otherwise
function check_params($keys, $assocList) {
	foreach($keys as $key) {
		if(!key_exists($key, $assocList)) return false;
	}
	return true;
}

// Gets a simple JSON error string for the API.
function json_error($message) {
	return json_encode(['error' => $message]);
}

// Simple logging functions
function log_info($message) {
	$file_handle = fopen($_SERVER['DOCUMENT_ROOT'] . "/log.txt", 'a');
	fwrite($file_handle, date("[Y-m-d H:i:s") . ": INFO] " . $message . "\n");
	fclose($file_handle);
}

function log_error($message) {
	$file_handle = fopen($_SERVER['DOCUMENT_ROOT'] . "/log.txt", 'a');
	fwrite($file_handle, date("[Y-m-d H:i:s") . ": ERROR] " . $message . "\n");
	fclose($file_handle);
}

function log_trace($message) {
	global $verbose;
	if(!$verbose) {
		return;
	}

	$file_handle = fopen($_SERVER['DOCUMENT_ROOT'] . "/log.txt", 'a');
	fwrite($file_handle, date("[Y-m-d H:i:s") . ": DEBUG] " . $message . "\n");
	fclose($file_handle);
}
?>
