<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$products = App\Models\Product::whereIn('id', [323, 324, 325])
    ->with('specValues.specKey')
    ->get();

echo "📊 DEBUGGING GROSIME\n\n";

foreach ($products as $product) {
    echo "📦 " . $product->name . ":\n";
    
    $hasGrosime = false;
    foreach ($product->specValues as $spec) {
        $key = $spec->specKey->name;
        $value = $spec->value_number ?? $spec->value_string ?? $spec->value_bool;
        
        if (stripos($key, 'gros') !== false || stripos($key, 'thick') !== false) {
            echo "  ✓ {$key}: {$value}\n";
            $hasGrosime = true;
        }
    }
    
    if (!$hasGrosime) {
        echo "  ❌ NU ARE GROSIME\n";
    }
    
    echo "\n";
}
