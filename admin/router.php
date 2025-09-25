<?php
// Router script for PHP built-in server

// Remove the `.php` extension for clean URLs
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve the file directly if it exists
if (file_exists(__DIR__ . $requestUri)) {
    return false;
}

// Rewrite requests without `.php` extensions
if (file_exists(__DIR__ . $requestUri . '.php')) {
    include __DIR__ . $requestUri . '.php';
    exit;
}
