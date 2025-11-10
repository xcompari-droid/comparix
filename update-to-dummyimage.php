<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Product;

echo "Updating to use dummyimage.com..." . PHP_EOL . PHP_EOL;

$products = Product::all();

foreach ($products as $product) {
    echo "Processing: {$product->name}" . PHP_EOL;
    
    $colors = [
        'Samsung' => '1428A0',
        'OPPO' => '0891b2', 
        'Apple' => '000000',
        'Xiaomi' => 'ff6900',
    ];
    
    $bg = $colors[$product->brand] ?? '6366f1';
    
    $shortName = str_replace([$product->brand . ' ', 'Galaxy ', 'iPhone '], '', $product->name);
    $shortName = substr($shortName, 0, 20);
    $text = urlencode($shortName);
    
    // Use dummyimage.com - more reliable
    $imageUrl = "https://dummyimage.com/400x400/{$bg}/ffffff&text={$text}";
    
    echo "  New URL: {$imageUrl}" . PHP_EOL;
    
    Product::withoutSyncingToSearch(function () use ($product, $imageUrl) {
        $product->image_url = $imageUrl;
        $product->save();
    });
    
    echo "  ✓ Updated" . PHP_EOL . PHP_EOL;
}

echo "✅ Done!" . PHP_EOL;
