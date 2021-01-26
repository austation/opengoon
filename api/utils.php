<?php
require 'config.php';

const JSON_SUCCESS = "{\"status\":\"OK\"}"; // success value because I'm lazy af and don't wanna type it. We just use this to add a small body to requests with no return, so the server doesn't die.

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

// Authentication check function. Takes some self-explanatory settings. Note, checks $_GET global for values.
function check_auth($checkVesion = true, $customAuthKey = false, $iterateIps = false, $jwt = false) {
	global $authKey, $servers, $authentication, $apiVersion;
	// Debug check - Don't abuse for god sake!
	if(!$authentication) {
		return true;
	}

	// Handle JWT. Keep it strict with no custom params or anything.
	if($jwt) {
		if(key_exists('auth', $_GET)) {
			$result = verify_jwt($_GET['auth']);
			if($result) {
				log_info("Successful JWT authentication by ckey {$result} and IP address {$_SERVER['REMOTE_ADDR']}");
				return true;
			} else {
				log_error("JWT authentication FAILED with token {$_GET['auth']} and IP address {$_SERVER['REMOTE_ADDR']}");
				return false;
			}
		} else {
			log_error("JWT authentication failed due to missing param, IP address {$_SERVER['REMOTE_ADDR']}");
			return false;
		}
	}

	// Auth key checks
	if($customAuthKey && key_exists($customAuthKey, $_GET)) {
		if($_GET[$customAuthKey] !== md5($authKey)) {
			log_error("Auth failed, custom key {$customAuthKey} with param value {$_GET[$customAuthKey]} and IP address {$_SERVER['REMOTE_ADDR']}");
			return false;
		}
	} elseif(key_exists('auth', $_GET)) {
		if($_GET['auth'] !== md5($authKey)) {
			log_error("Auth failed with param value {$_GET['auth']} and IP address {$_SERVER['REMOTE_ADDR']}");
			return false;
		}
	} else {
		log_error("Auth failed due to missing param, IP address {$_SERVER['REMOTE_ADDR']}");
		return false;
	}

	// IP checks
	if($iterateIps) {
		$valid = false;
		foreach($servers as $key => $value) {
			if($value['ip'] === $_SERVER['REMOTE_ADDR']) {
				// found the IP
				$valid = true;
			}
		}
		if(!$valid) {
			log_error("Unauthorized IP address tried to use API, with IP {$_SERVER['REMOTE_ADDR']}");
			return false;
		}
	} elseif(key_exists('data_server', $_GET) && key_exists($_GET['data_server'], $servers)) {
		if($servers[$_GET['data_server']]['ip'] !== $_SERVER['REMOTE_ADDR']) {
			log_error("Unauthorized IP address tried to use API, with IP {$_SERVER['REMOTE_ADDR']}");
			return false;
		}
	} else {
		log_error("Auth failed due to missing data_server param, IP address {$_SERVER['REMOTE_ADDR']}");
		return false;
	}

	// Optional version checks (compatibility)
	if($checkVesion) {
		if($_GET['data_version'] != $apiVersion) {
			log_error("Server using invalid API version tried to use API, with target version {$_GET['data_version']}");
			return false;
		}
	}

	// Finally return true if all auth checks passed
	log_trace("Authentication passed from IP address {$_SERVER['REMOTE_ADDR']}");
	return true;
}

// Gets a simple JSON error string for the API.
function json_error($message) {
	return json_encode(['status' => 'error', 'error' => $message]);
}

// Le funny base64url functions for JWT
function base64url_encode($data)
{
	$b64 = base64_encode($data);

	if ($b64 === false) {
		return false;
	}

	$url = strtr($b64, '+/', '-_');

	return rtrim($url, '=');
}

function base64url_decode($data, $strict = false)
{
	$b64 = strtr($data, '-_', '+/');

	return base64_decode($b64, $strict);
}

// Verify a JWT token. False if invalid, otherwise returns the encoded ckey
function verify_jwt($token) {
	global $jwtSecret;

	$arr = explode('.', $token);
	if(count($arr) !== 3) {
		return false;
	}

	// Take first two parts and check if they hash to the right value
	$result = base64url_encode(hash_hmac("sha256", $arr[0] . '.' . $arr[1], $jwtSecret, true));

	if($result !== $arr[2]) {
		return false;
	}

	$payload = json_decode(base64_decode($arr[1]), true);

	// Check expiry
	if(!key_exists('exp', $payload) || $payload['exp'] < time()) {
		return false;
	}

	// Must have an associated ckey
	return key_exists('sub', $payload) ? $payload['sub'] : false;
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
