<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Product;

echo "Checking product images in database..." . PHP_EOL . PHP_EOL;

$products = Product::all();

foreach ($products as $product) {
    echo "ID: {$product->id} - {$product->name}" . PHP_EOL;
    echo "Image URL: " . ($product->image_url ?? 'NULL') . PHP_EOL;
    echo PHP_EOL;
}
