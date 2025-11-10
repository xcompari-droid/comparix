<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ProductType;
use App\Models\Product;

echo "=== PRODUCT TYPES ===\n\n";

$types = ProductType::all();

foreach ($types as $type) {
    $count = Product::where('product_type_id', $type->id)->count();
    echo "{$type->id}: {$type->name} ({$type->slug}) - {$count} products\n";
}
