<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Product;
use App\Models\ProductType;

$productType = ProductType::where('slug', 'masina-de-spalat')->first();

if (!$productType) {
    echo "✗ ProductType 'masina-de-spalat' not found\n";
    exit(1);
}

$count = Product::where('product_type_id', $productType->id)->count();
echo "═══════════════════════════════════════════════════════════\n";
echo "          MAȘINI DE SPĂLAT - STATUS ACTUAL\n";
echo "═══════════════════════════════════════════════════════════\n\n";
echo "Total produse: {$count}\n\n";

$products = Product::where('product_type_id', $productType->id)
    ->with('specValues.specKey')
    ->get();

$totalSpecs = 0;
$specCounts = [];

foreach ($products as $product) {
    $specsCount = $product->specValues->count();
    $totalSpecs += $specsCount;
    $specCounts[] = $specsCount;
    
    $hasRealImage = !str_contains($product->image_url, 'ui-avatars.com') && 
                    !str_contains($product->image_url, 'placeholder');
    
    echo "{$product->brand} {$product->model}\n";
    echo "  └─ Specs: {$specsCount}\n";
    echo "  └─ Image: " . ($hasRealImage ? "✓ Real" : "✗ Placeholder") . "\n";
    
    if ($specsCount > 0 && $specsCount <= 5) {
        echo "  └─ Specs list:\n";
        foreach ($product->specValues as $spec) {
            $value = $spec->value_string ?? $spec->value_number ?? ($spec->value_bool ? 'Da' : 'Nu');
            echo "      • {$spec->specKey->name}: {$value}\n";
        }
    }
    echo "\n";
}

if ($count > 0) {
    $avgSpecs = round($totalSpecs / $count, 1);
    $minSpecs = min($specCounts);
    $maxSpecs = max($specCounts);
    
    echo "═══════════════════════════════════════════════════════════\n";
    echo "STATISTICI:\n";
    echo "  • Media specs/produs: {$avgSpecs}\n";
    echo "  • Range specs: {$minSpecs}-{$maxSpecs}\n";
    echo "═══════════════════════════════════════════════════════════\n";
}
