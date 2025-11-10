<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

echo "🖼️  Updating OPPO products with real images...\n\n";

// Real OPPO product images from various reliable sources
$oppoImages = [
    'OPPO Find X7 Ultra' => 'https://image.oppo.com/content/dam/oppo/product-asset-library/find/find-x7-ultra/v1/assets/find-x7-ultra-brown-front-back-720x720.png',
    'OPPO Reno 12 Pro 5G' => 'https://image.oppo.com/content/dam/oppo/product-asset-library/reno/reno-12-pro-5g/v1/assets/reno-12-pro-black-front-back-720x720.png',
    'OPPO Reno 12 5G' => 'https://image.oppo.com/content/dam/oppo/product-asset-library/reno/reno-12-5g/v1/assets/reno-12-black-front-back-720x720.png',
    'OPPO A3 Pro 5G' => 'https://image.oppo.com/content/dam/oppo/product-asset-library/a-series/a3-pro-5g/v1/assets/a3-pro-black-front-back-720x720.png',
    'OPPO A79 5G' => 'https://image.oppo.com/content/dam/oppo/product-asset-library/a-series/a79-5g/v2/assets/a79-purple-front-back-720x720.png',
];

$oppoProducts = Product::where('brand', 'OPPO')->get();

foreach ($oppoProducts as $product) {
    echo "Processing: {$product->name}\n";
    
    if (isset($oppoImages[$product->name])) {
        $imageUrl = $oppoImages[$product->name];
        
        // Test if image is accessible
        $headers = @get_headers($imageUrl);
        if ($headers && strpos($headers[0], '200')) {
            Product::withoutSyncingToSearch(function() use ($product, $imageUrl) {
                $product->update(['image_url' => $imageUrl]);
            });
            echo "  ✓ Updated with real image\n";
        } else {
            // Fallback to higher quality placeholder
            $shortName = str_replace('OPPO ', '', $product->name);
            $imageUrl = "https://dummyimage.com/600x600/0891b2/ffffff.png&text=" . urlencode($shortName);
            Product::withoutSyncingToSearch(function() use ($product, $imageUrl) {
                $product->update(['image_url' => $imageUrl]);
            });
            echo "  ⚠ Image not accessible, using placeholder\n";
        }
    } else {
        $shortName = str_replace('OPPO ', '', $product->name);
        $imageUrl = "https://dummyimage.com/600x600/0891b2/ffffff.png&text=" . urlencode($shortName);
        Product::withoutSyncingToSearch(function() use ($product, $imageUrl) {
            $product->update(['image_url' => $imageUrl]);
        });
        echo "  → Using placeholder\n";
    }
    
    echo "\n";
}

echo "✅ Done!\n";
