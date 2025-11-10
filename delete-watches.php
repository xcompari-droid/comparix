<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Product;
use App\Models\ProductType;

$productType = ProductType::where('slug', 'smartwatch')->first();

if ($productType) {
    $deleted = Product::where('product_type_id', $productType->id)->delete();
    echo "✓ Deleted {$deleted} smartwatches\n";
} else {
    echo "✗ ProductType 'smartwatch' not found\n";
}
