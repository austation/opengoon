<?php
// Boilerplate Request Validation and Includes Code
require '../../config.php';
require '../../utils.php';

header("Content-Type: application/json");

if(!check_auth()) {
	http_response_code(401);
	return;
}

if(!check_params(['ckey'], $_GET)) {
	echo json_error("Malformed request to the API. Missing params.");
	return;
}

$token = "";

// It's easy, generate the header
$token .= base64url_encode(json_encode(["alg" => "HS256", "typ" => "JWT"])) . '.';

// Generate the payload. Give it 2 hours expiry
$token .= base64url_encode(json_encode(["exp" => time() + 7200, "sub" => $_GET['ckey']]));

$token .= '.' . base64url_encode(hash_hmac("sha256", $token, $jwtSecret, true));

echo json_encode(["token" => $token]);
?>
