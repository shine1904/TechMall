<?php
$path = __DIR__ . '/images/products/' . basename($_GET['file']);

if (file_exists($path)) {
    $mime = mime_content_type($path);
    header("Content-Type: $mime");
    readfile($path);
    exit;
} else {
    http_response_code(404);
    echo "File not found";
}
