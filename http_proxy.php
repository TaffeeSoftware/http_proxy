<?php
/**
 * http_proxy.php
 *
 * A secure HTTPS-to-HTTP proxy for bridging communication with legacy or non-SSL APIs.
 * 
 * @author C. P. Baaijens - Taffee Software
 * @version 1.1
 * @license MIT
 */

// === CONFIGURATION ===
// You only need to change these two lines to adapt this proxy
$httpDomain = 'legacy.domain.local';              // Target HTTP server (no protocol)
$httpApiPath = '/api/request.php';                // Path to the HTTP API endpoint

// === CORS HEADERS ===
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE, PATCH");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// === HANDLE OPTIONS REQUEST (CORS preflight) ===
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// === VALIDATE MINIMUM PARAMS ===
if (!isset($_GET['type'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required 'type' parameter"]);
    exit();
}

// === CONSTRUCT TARGET URL ===
$queryParams = http_build_query($_GET);
$targetUrl = "http://{$httpDomain}{$httpApiPath}?" . $queryParams;

// === CAPTURE REQUEST ===
$method = $_SERVER['REQUEST_METHOD'];
$requestBody = file_get_contents('php://input');
$requestHeaders = getallheaders();

// === SETUP CURL ===
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $targetUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Forward request body if needed
if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
}

// Forward headers
$forwardedHeaders = [];
foreach ($requestHeaders as $key => $value) {
    $forwardedHeaders[] = "$key: $value";
}
curl_setopt($ch, CURLOPT_HTTPHEADER, $forwardedHeaders);

// Disable SSL checks (proxying to HTTP)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// === EXECUTE ===
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// === HANDLE ERRORS ===
if ($response === false) {
    http_response_code(500);
    echo json_encode([
        "error" => "cURL error occurred",
        "curl_error" => curl_error($ch),
        "curl_info" => curl_getinfo($ch),
    ]);
    curl_close($ch);
    exit();
}

// === OUTPUT RESPONSE ===
http_response_code($httpCode);
echo $response;

curl_close($ch);
?>
