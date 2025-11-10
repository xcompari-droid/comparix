<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Product;

// Get all products without images or with local paths
$products = Product::all();

echo "Updating product images with API URLs..." . PHP_EOL . PHP_EOL;

foreach ($products as $product) {
    echo "Processing: {$product->name}" . PHP_EOL;
    
    // Generate a consistent image from placeholder API
    // Using ui-avatars.com or placeholder.com
    $productName = urlencode($product->name);
    $brand = urlencode($product->brand);
    
    // Option 1: Use ui-avatars.com for branded placeholders
    $colors = [
        'Samsung' => ['bg' => '1428A0', 'color' => 'ffffff'],
        'OPPO' => ['bg' => '0891b2', 'color' => 'ffffff'],
        'Apple' => ['bg' => '000000', 'color' => 'ffffff'],
        'Xiaomi' => ['bg' => 'ff6900', 'color' => 'ffffff'],
    ];
    
    $colorScheme = $colors[$product->brand] ?? ['bg' => '6366f1', 'color' => 'ffffff'];
    
    // Create placeholder with product info
    $shortName = str_replace([$product->brand . ' ', 'Galaxy ', 'iPhone '], '', $product->name);
    $shortName = substr($shortName, 0, 15);
    
    // Using placeholder.com for better looking images
    $imageUrl = "https://placehold.co/400x400/{$colorScheme['bg']}/{$colorScheme['color']}/png?text=" . urlencode($shortName);
    
    // Alternative: Using via.placeholder.com
    // $imageUrl = "https://via.placeholder.com/400x400/{$colorScheme['bg']}/{$colorScheme['color']}?text=" . urlencode($shortName);
    
    echo "  Image URL: {$imageUrl}" . PHP_EOL;
    
    // Update database without Scout syncing
    Product::withoutSyncingToSearch(function () use ($product, $imageUrl) {
        $product->image_url = $imageUrl;
        $product->save();
    });
    
    echo "  ✓ Updated database" . PHP_EOL;
    echo PHP_EOL;
}

echo "✅ All product images updated!" . PHP_EOL;
echo "Images will be served from: https://placehold.co" . PHP_EOL;
