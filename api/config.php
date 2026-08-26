<?php
// Global CORS headers for React API requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database Connection Credentials (Hostinger Production & Local Fallback)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'u882069120_sga');
define('DB_PASS', getenv('DB_PASS') ?: 'Q#v8WK;8D');
define('DB_NAME', getenv('DB_NAME') ?: 'u882069120_sga');

define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', '/uploads/');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
