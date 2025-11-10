<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get popular products for comparison examples
$productIds = [323, 324, 383, 384, 416, 417];

$products = \App\Models\Product::whereIn('id', $productIds)
    ->get(['id', 'brand', 'name', 'image_url'])
    ->keyBy('id');

echo json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
