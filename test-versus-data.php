<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test with real IDs
$productIds = [323, 324, 325]; // Smartwatches

$products = App\Models\Product::whereIn('id', $productIds)
    ->with(['offers', 'specValues.specKey', 'productType.category', 'category'])
    ->get();

echo "📊 TESTARE DATE VERSUS\n";
echo "======================\n\n";
echo "Produse găsite: " . $products->count() . "\n\n";

if ($products->isEmpty()) {
    echo "❌ NU EXISTĂ PRODUSE CU ACESTE ID-uri!\n";
    exit;
}

$colors = ['#76b900', '#ed1c24', '#0071c5', '#f7931e', '#8e44ad', '#16a085'];

foreach ($products as $index => $product) {
    echo "📦 Produs {$product->id}: {$product->name}\n";
    echo "   Brand: {$product->brand}\n";
    echo "   Image: " . ($product->image_url ? '✅' : '❌') . "\n";
    echo "   SpecValues: {$product->specValues->count()}\n";
    
    $metrics = [];
    foreach ($product->specValues as $specValue) {
        $key = strtolower(str_replace(' ', '_', $specValue->specKey->name));
        $value = $specValue->value_number ?? $specValue->value_string ?? $specValue->value_bool;
        if ($value !== null && is_numeric($value)) {
            $metrics[$key] = (float)$value;
        }
    }
    
    echo "   Metrics extrași: " . count($metrics) . "\n";
    if (!empty($metrics)) {
        echo "   Primele 3: " . implode(', ', array_slice(array_keys($metrics), 0, 3)) . "\n";
    }
    echo "\n";
}

echo "\n✅ Datele par OK. Problema e în componenta Vue.\n";
