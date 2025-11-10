<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProductType;
use App\Models\Product;

$type = ProductType::find(4);

if ($type) {
    echo "\n=== PRODUCT TYPE ID 4 ===\n";
    echo "Name: {$type->name}\n";
    echo "URL: {$type->url}\n";
    echo "Total products: " . Product::where('product_type_id', 4)->count() . "\n\n";
    
    echo "Sample products:\n";
    $samples = Product::where('product_type_id', 4)->limit(5)->get();
    foreach ($samples as $product) {
        echo "- {$product->brand} {$product->name}\n";
    }
} else {
    echo "Product type ID 4 not found\n";
}
