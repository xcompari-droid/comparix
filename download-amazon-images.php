<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

echo "📥 Finding alternative images for remaining products...\n\n";

$imageDir = __DIR__ . '/public/images/products';

// Try DeviantArt phone mockups and tech blogs
$productImages = [
    'Samsung Galaxy S24 Ultra' => 'https://m.media-amazon.com/images/I/71RxSvUO1gL._AC_SL1500_.jpg',
    'OPPO Reno 12 Pro 5G' => 'https://m.media-amazon.com/images/I/61qI3vqHFKL._AC_SL1500_.jpg',
    'OPPO Reno 12 5G' => 'https://m.media-amazon.com/images/I/61N9mP8FYBL._AC_SL1500_.jpg',
    'OPPO A3 Pro 5G' => 'https://m.media-amazon.com/images/I/61JzZ8VvP7L._AC_SL1500_.jpg',
    'Huawei Pura 70 Ultra' => 'https://m.media-amazon.com/images/I/51qXoNQp7WL._AC_SL1000_.jpg',
    'Huawei Pura 70 Pro' => 'https://m.media-amazon.com/images/I/51Y-YEmrMgL._AC_SL1000_.jpg',
    'Huawei Pura 70' => 'https://m.media-amazon.com/images/I/51bTgYuJ9YL._AC_SL1000_.jpg',
    'Huawei Nova 12 SE' => 'https://m.media-amazon.com/images/I/51k3qgwrQYL._AC_SL1000_.jpg',
];

$downloaded = 0;
$failed = 0;

foreach ($productImages as $productName => $imageUrl) {
    $product = Product::where('name', $productName)->first();
    
    if (!$product) {
        echo "Product not found: {$productName}\n\n";
        continue;
    }
    
    echo "Processing: {$productName}\n";
    
    $extension = strpos($imageUrl, '.png') !== false ? 'png' : 'jpg';
    $fileName = preg_replace('/[^a-z0-9-_]/i', '-', strtolower($productName)) . '.' . $extension;
    $localPath = $imageDir . '/' . $fileName;
    $publicUrl = '/images/products/' . $fileName;
    
    // Try to download with curl
    $ch = curl_init($imageUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: image/webp,image/apng,image/*,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.9',
        'Referer: https://www.amazon.com/',
    ]);
    
    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($imageData && $httpCode == 200 && strlen($imageData) > 1000) {
        file_put_contents($localPath, $imageData);
        
        // Update database
        Product::withoutSyncingToSearch(function() use ($product, $publicUrl) {
            $product->update(['image_url' => $publicUrl]);
        });
        
        $size = round(strlen($imageData) / 1024, 2);
        echo "  ✓ Downloaded ({$size} KB) → {$publicUrl}\n";
        $downloaded++;
    } else {
        echo "  ✗ Failed (HTTP {$httpCode}";
        if ($error) echo " - {$error}";
        echo ")\n";
        $failed++;
    }
    
    echo "\n";
    usleep(1500000); // 1.5 second delay between requests
}

echo "\n✅ Done!\n";
echo "   Downloaded: {$downloaded}\n";
echo "   Failed: {$failed}\n";
echo "\n💡 For failed images, you can:\n";
echo "   1. Upload manually via /admin panel\n";
echo "   2. Add image URLs in CSV imports\n";
echo "   3. Use a proper product image API\n";
