<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Product;

echo "Checking OPPO products images..." . PHP_EOL . PHP_EOL;

$products = Product::where('brand', 'OPPO')->get();

foreach ($products as $product) {
    echo "ID: {$product->id}" . PHP_EOL;
    echo "Product: {$product->name}" . PHP_EOL;
    echo "Image URL: " . ($product->image_url ?? 'NULL') . PHP_EOL;
    if ($product->image_url) {
        echo "URL Length: " . strlen($product->image_url) . PHP_EOL;
        echo "URL starts with: " . substr($product->image_url, 0, 30) . "..." . PHP_EOL;
    }
    echo str_repeat('-', 60) . PHP_EOL . PHP_EOL;
}
