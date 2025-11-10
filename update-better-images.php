<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\File;

// Create directory
$imageDir = public_path('images/products');
if (!File::exists($imageDir)) {
    File::makeDirectory($imageDir, 0755, true);
}

echo "🖼️  Updating product images with better quality..." . PHP_EOL . PHP_EOL;

// Map of real product image URLs (from official sources or CDNs)
$imageMap = [
    // Samsung
    'Samsung Galaxy S24 Ultra' => 'https://images.samsung.com/is/image/samsung/p6pim/ro/2401/gallery/ro-galaxy-s24-s928-sm-s928bzkgeue-thumb-539573218',
    'Samsung Galaxy S23 FE' => 'https://images.samsung.com/is/image/samsung/p6pim/ro/2310/gallery/ro-galaxy-s23-fe-s711-sm-s711blbdeue-thumb-538340638',
    'Samsung Galaxy A55 5G' => 'https://images.samsung.com/is/image/samsung/p6pim/ro/2403/gallery/ro-galaxy-a55-5g-a556-sm-a556elvdeue-thumb-540772063',
    'Samsung Galaxy A35 5G' => 'https://images.samsung.com/is/image/samsung/p6pim/ro/2403/gallery/ro-galaxy-a35-5g-a356-sm-a356elvdeue-thumb-540772165',
    'Samsung Galaxy Z Fold5' => 'https://images.samsung.com/is/image/samsung/p6pim/ro/2307/gallery/ro-galaxy-z-fold5-f946-sm-f946bzkbeue-thumb-537481389',
    
    // For others, we'll use a generic phone icon with brand colors
];

$products = Product::all();

foreach ($products as $product) {
    echo "Processing: {$product->name}" . PHP_EOL;
    
    if (isset($imageMap[$product->name])) {
        // Try to use real image
        $realImageUrl = $imageMap[$product->name];
        echo "  → Using real image: {$realImageUrl}" . PHP_EOL;
        
        Product::withoutSyncingToSearch(function () use ($product, $realImageUrl) {
            $product->image_url = $realImageUrl;
            $product->save();
        });
        
    } else {
        // Keep placeholder but make it look better
        $shortName = str_replace([$product->brand . ' ', 'Galaxy ', 'Pura ', 'Mate ', 'Nova '], '', $product->name);
        $shortName = substr($shortName, 0, 15);
        
        // Better placeholder with brand-specific styling
        $colors = [
            'Samsung' => '1428A0', // Samsung blue
            'OPPO' => '0891b2',    // Cyan
            'Huawei' => 'd60000',  // Red
            'Apple' => '000000',   // Black
            'Xiaomi' => 'ff6900',  // Orange
        ];
        
        $color = $colors[$product->brand] ?? '6366f1';
        
        // Use placekitten for a more visual placeholder (or via.placeholder with better styling)
        $imageUrl = "https://dummyimage.com/600x600/{$color}/ffffff.png&text=" . urlencode($shortName);
        
        echo "  → Using placeholder: {$imageUrl}" . PHP_EOL;
        
        Product::withoutSyncingToSearch(function () use ($product, $imageUrl) {
            $product->image_url = $imageUrl;
            $product->save();
        });
    }
    
    echo "  ✓ Updated" . PHP_EOL . PHP_EOL;
}

echo "✅ Done!" . PHP_EOL;
echo PHP_EOL;
echo "ℹ️  Note: Placeholder-urile arată doar culoare + text." . PHP_EOL;
echo "Pentru imagini reale:" . PHP_EOL;
echo "  1. Adaugă URL-uri de imagini reale în CSV" . PHP_EOL;
echo "  2. Sau încarcă imagini prin admin panel (/admin)" . PHP_EOL;
echo "  3. Sau descarcă imagini local în public/images/products/" . PHP_EOL;
