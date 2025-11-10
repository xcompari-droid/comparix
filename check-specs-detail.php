<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\SpecValue;

echo "=== CHECKING SPECIFICATIONS ===\n\n";

// Check a few products
$products = [
    'Apple AirPods Max',
    'Apple AirPods Pro',
    'Apple Watch Series 10',
    'Sony WF-1000XM5'
];

foreach ($products as $name) {
    $product = Product::where('name', $name)->first();
    
    if ($product) {
        echo "Product: {$product->name}\n";
        echo "  ID: {$product->id}\n";
        echo "  SpecValues count (query): " . SpecValue::where('product_id', $product->id)->count() . "\n";
        echo "  SpecValues count (relation): " . $product->specValues()->count() . "\n";
        echo "  Via loaded relation: " . $product->specValues->count() . "\n";
        
        // Show first 3 specs
        $specs = SpecValue::where('product_id', $product->id)->take(3)->get();
        if ($specs->count() > 0) {
            echo "  First 3 specs:\n";
            foreach ($specs as $spec) {
                echo "    - {$spec->specKey->name}: {$spec->value}\n";
            }
        }
        echo "\n";
    }
}
