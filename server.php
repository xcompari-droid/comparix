<?php

// Laravel router for built-in PHP server
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');

// Serve static files directly
if ($uri !== '/' && preg_match('/\.(?:css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$/', $uri)) {
    $filePath = __DIR__ . '/public' . $uri;
    
    if (file_exists($filePath) && is_file($filePath)) {
        // Set correct content type
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject'
        ];
        
        if (isset($mimeTypes[$ext])) {
            header('Content-Type: ' . $mimeTypes[$ext]);
        }
        
        readfile($filePath);
        exit;
    }
    
    // File not found
    http_response_code(404);
    echo "404 Not Found: $uri";
    exit;
}

// Route everything else through Laravel
require_once __DIR__ . '/public/index.php';
