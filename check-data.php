<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Offers count: " . App\Models\Offer::count() . PHP_EOL;
echo "SpecValues count: " . App\Models\SpecValue::count() . PHP_EOL;

$product = App\Models\Product::with('specValues.specKey', 'offers')->first();

if ($product) {
    echo "\nProduct: " . $product->name . PHP_EOL;
    echo "Specs: " . $product->specValues->count() . PHP_EOL;
    echo "Offers: " . $product->offers->count() . PHP_EOL;
    
    if ($product->specValues->count() > 0) {
        echo "\nFirst 3 specs:\n";
        foreach ($product->specValues->take(3) as $spec) {
            echo "  - " . $spec->specKey->name . ": " . $spec->value . PHP_EOL;
        }
    }
}
