<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\SpecValue;
use App\Models\ProductType;

echo "=== PRODUCT SPECIFICATIONS VERIFICATION ===\n\n";

// Get all product types
$productTypes = [
    'Smartphone' => 1,
    'Smartwatch' => 2,
    'Placă video' => 3,
    'Căști wireless' => 5,
    'Frigider' => 6,
];

$totalProducts = 0;
$totalSpecs = 0;

foreach ($productTypes as $typeName => $typeId) {
    $productType = ProductType::find($typeId);
    if (!$productType) {
        continue;
    }
    
    $products = Product::where('product_type_id', $typeId)->get();
    $count = $products->count();
    $totalProducts += $count;
    
    $specsCount = 0;
    $withSpecs = 0;
    
    foreach ($products as $product) {
        $productSpecCount = SpecValue::where('product_id', $product->id)->count();
        $specsCount += $productSpecCount;
        if ($productSpecCount > 0) {
            $withSpecs++;
        }
    }
    
    $totalSpecs += $specsCount;
    $avgSpecs = $count > 0 ? round($specsCount / $count, 1) : 0;
    
    echo str_pad($typeName, 20) . ": ";
    echo str_pad($count, 3, ' ', STR_PAD_LEFT) . " products | ";
    echo str_pad($specsCount, 5, ' ', STR_PAD_LEFT) . " specs | ";
    echo "Avg: " . str_pad($avgSpecs, 4, ' ', STR_PAD_LEFT) . " specs/product | ";
    echo "Coverage: " . str_pad($withSpecs, 3, ' ', STR_PAD_LEFT) . "/" . str_pad($count, 3, ' ', STR_PAD_LEFT) . " (" . ($count > 0 ? round($withSpecs/$count*100) : 0) . "%)\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "TOTAL: " . str_pad($totalProducts, 3, ' ', STR_PAD_LEFT) . " products | ";
echo str_pad($totalSpecs, 5, ' ', STR_PAD_LEFT) . " specifications | ";
echo "Avg: " . ($totalProducts > 0 ? round($totalSpecs / $totalProducts, 1) : 0) . " specs/product\n";
echo str_repeat("=", 80) . "\n\n";

// Detailed breakdown for recently updated products
echo "=== RECENT UPDATES (Last 10 GPUs) ===\n\n";

$recentGPUs = Product::where('product_type_id', 3)
    ->orderBy('updated_at', 'desc')
    ->limit(10)
    ->get();

foreach ($recentGPUs as $gpu) {
    $specCount = SpecValue::where('product_id', $gpu->id)->count();
    echo "✓ " . str_pad($gpu->name, 50) . " : " . str_pad($specCount, 2, ' ', STR_PAD_LEFT) . " specs\n";
}

echo "\n";
