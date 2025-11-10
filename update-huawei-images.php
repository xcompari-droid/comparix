<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Product;

echo "Updating Huawei product images..." . PHP_EOL . PHP_EOL;

$products = Product::where('brand', 'Huawei')->get();

foreach ($products as $product) {
    echo "Processing: {$product->name}" . PHP_EOL;
    
    $shortName = str_replace(['Huawei ', 'Pura ', 'Mate ', 'Nova ', 'P'], '', $product->name);
    $shortName = substr($shortName, 0, 20);
    $text = urlencode($shortName);
    
    // Huawei brand color - red/black
    $imageUrl = "https://dummyimage.com/400x400/d60000/ffffff&text={$text}";
    
    echo "  New URL: {$imageUrl}" . PHP_EOL;
    
    Product::withoutSyncingToSearch(function () use ($product, $imageUrl) {
        $product->image_url = $imageUrl;
        $product->save();
    });
    
    echo "  ✓ Updated" . PHP_EOL . PHP_EOL;
}

echo "✅ Done! Updated " . $products->count() . " Huawei products" . PHP_EOL;
