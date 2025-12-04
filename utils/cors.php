<?php
// utils/cors.php

// Cho phép một số origin thông dụng khi dev FE
$allowed_origins = [
    'http://127.0.0.1:5500',
    'http://127.0.0.1:5501',
    'http://localhost:3000',
    'http://127.0.0.1:3000',
    'http://localhost:5173',
    'http://127.0.0.1:5173',
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// Debug: Log origin để kiểm tra vấn đề CORS
error_log("CORS: Origin received: " . $origin);

if (!empty($origin) && in_array($origin, $allowed_origins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Vary: Origin");
    error_log("CORS: Allowed origin: " . $origin);
} else {
    // Cho phép tất cả origin khi development (không an toàn cho production)
    header("Access-Control-Allow-Origin: *");
    error_log("CORS: Using wildcard for origin: " . $origin);
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Cache-Control, Pragma");
header("Access-Control-Allow-Credentials: true");

// Nếu là preflight request (OPTIONS) thì trả về 200 ngay
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
