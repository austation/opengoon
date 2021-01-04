<?php
// numbers:get - this api just makes a callback to the server with a set of numbers for the numbers station adventure zone.

// Boilerplate Request Validation and Includes Code
require '../../config.php';
require '../../utils.php';

header("Content-Type: application/json");

if(!check_auth()) {
	http_response_code(401);
	return;
}

$serverKey = $_GET['data_server'];

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

// This API endpoint takes no params, just makes a callback. Let's generate our required output first...
$numbers = "";
for($i = 0; $i <= 20; $i++) {
	if($i == 20) {
		$numbers .= rand(0, 99);
	} else {
		$numbers .= rand(0, 99) . ' ';
	}
}

log_trace("Numbers string generated value: " . $numbers);

// Next, make a callback with the required data.
callback($servers[$serverKey]['ip'], $servers[$serverKey]['port'], ['numbers' => $numbers], "lincolnshire_numbers");

// And we're done!

?>
