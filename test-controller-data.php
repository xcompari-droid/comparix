<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;

echo "=== TEST CONTROLLER DATA ===\n\n";

// Test 1: Ce primește controller-ul acum
$product = Product::with(['offers', 'productType.category'])->first();

echo "Product: {$product->name}\n";
echo "Image URL în DB: {$product->image_url}\n";
echo "Image URL prin relationship: " . ($product->image_url ?? 'NULL') . "\n";

// Test 2: Are specifications?
$productWithSpecs = Product::with(['specValues.specKey'])->first();
echo "\nSpecs count: " . $productWithSpecs->specValues->count() . "\n";

if ($productWithSpecs->specValues->count() > 0) {
    echo "Prima spec: " . $productWithSpecs->specValues->first()->specKey->name . " = " . 
         $productWithSpecs->specValues->first()->value . "\n";
}

// Test 3: Verifică dacă image_url e în atribute
echo "\nAtribute disponibile:\n";
echo "- name: " . ($product->name ? 'DA' : 'NU') . "\n";
echo "- brand: " . ($product->brand ? 'DA' : 'NU') . "\n";
echo "- image_url: " . ($product->image_url ? 'DA' : 'NU') . "\n";
echo "- description: " . ($product->description ? 'DA' : 'NU') . "\n";

// Test 4: Verifică ce are Product model în $fillable sau $hidden
echo "\n\nModel attributes:\n";
print_r(array_keys($product->getAttributes()));
