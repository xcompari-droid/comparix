<?php

// Simple router for built-in PHP server
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve static files
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

// Route to Laravel
require_once __DIR__ . '/public/index.php';
