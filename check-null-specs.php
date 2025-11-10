<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

echo "=== Checking products 416 & 417 for missing/null values ===\n\n";

$products = [416, 417];

foreach ($products as $productId) {
    $product = Product::with('specValues.specKey')->find($productId);
    echo "\n--- Product {$productId}: {$product->name} ---\n";
    
    echo "Total specs: {$product->specValues->count()}\n\n";
    
    // Specs cu toate valorile NULL
    $nullSpecs = $product->specValues->filter(function($spec) {
        return $spec->value_bool === null && 
               ($spec->value_string === null || $spec->value_string === '') && 
               $spec->value_number === null;
    });
    
    if ($nullSpecs->count() > 0) {
        echo "Specs with ALL NULL values ({$nullSpecs->count()}):\n";
        foreach ($nullSpecs as $spec) {
            echo "  ID {$spec->id}: {$spec->specKey->name}\n";
        }
    } else {
        echo "No specs with all NULL values\n";
    }
    
    echo "\n";
    
    // Listează toate specs și valorile lor
    echo "All specs:\n";
    foreach ($product->specValues->sortBy(fn($s) => $s->specKey->name) as $spec) {
        $value = '';
        if ($spec->value_bool !== null) {
            $value = $spec->value_bool ? 'Da' : 'Nu';
        } elseif ($spec->value_string) {
            $value = $spec->value_string;
        } elseif ($spec->value_number !== null) {
            $value = $spec->value_number;
        } else {
            $value = 'NULL/EMPTY';
        }
        
        echo "  {$spec->specKey->name}: {$value}\n";
    }
}
