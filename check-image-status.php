<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

echo "📊 Product Images Status Report\n";
echo "================================\n\n";

$products = Product::orderBy('brand')->orderBy('name')->get();

$withRealImages = 0;
$withPlaceholders = 0;

foreach ($products as $product) {
    $imageUrl = $product->image_url;
    $isLocal = strpos($imageUrl, '/images/products/') === 0;
    $isRealImage = $isLocal && (
        strpos($imageUrl, '.jpg') !== false || 
        strpos($imageUrl, '.png') !== false
    );
    
    $status = $isRealImage ? '✓ Real Image' : '⚠ Placeholder';
    if ($isRealImage) {
        $withRealImages++;
    } else {
        $withPlaceholders++;
    }
    
    echo "{$status} - {$product->brand} {$product->name}\n";
    echo "   {$imageUrl}\n\n";
}

echo "\n================================\n";
echo "Summary:\n";
echo "  ✓ Real Images: {$withRealImages}\n";
echo "  ⚠ Placeholders: {$withPlaceholders}\n";
echo "  📱 Total Products: " . $products->count() . "\n";
