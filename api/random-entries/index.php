<?php
header("Content-Type: application/json");

// Unimplemented method

http_response_code(501);
echo json_error("Endpoint not implemented");

?>
