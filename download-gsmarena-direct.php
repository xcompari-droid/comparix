<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

echo "📥 Downloading missing images from GSMArena...\n\n";

$imageDir = __DIR__ . '/public/images/products';

// Direct image URLs from GSMArena (they allow direct access)
$gsmarenaImages = [
    'Samsung Galaxy S24 Ultra' => 'https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-s24-ultra-5g.jpg',
    'OPPO Reno 12 Pro 5G' => 'https://fdn2.gsmarena.com/vv/bigpic/oppo-reno12-pro-5g.jpg',
    'OPPO Reno 12 5G' => 'https://fdn2.gsmarena.com/vv/bigpic/oppo-reno12-5g.jpg',
    'OPPO A3 Pro 5G' => 'https://fdn2.gsmarena.com/vv/bigpic/oppo-a3-pro-5g.jpg',
    'Huawei Pura 70 Ultra' => 'https://fdn2.gsmarena.com/vv/bigpic/huawei-pura-70-ultra.jpg',
    'Huawei Pura 70 Pro' => 'https://fdn2.gsmarena.com/vv/bigpic/huawei-pura-70-pro.jpg',
    'Huawei Pura 70' => 'https://fdn2.gsmarena.com/vv/bigpic/huawei-pura-70.jpg',
];

$downloaded = 0;
$failed = 0;

foreach ($gsmarenaImages as $productName => $imageUrl) {
    $product = Product::where('name', $productName)->first();
    
    if (!$product) {
        echo "Product not found: {$productName}\n\n";
        continue;
    }
    
    echo "Processing: {$productName}\n";
    echo "  URL: {$imageUrl}\n";
    
    // Download with curl
    $ch = curl_init($imageUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: image/webp,image/apng,image/*,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.9',
        'Referer: https://www.gsmarena.com/',
        'Sec-Fetch-Dest: image',
        'Sec-Fetch-Mode: no-cors',
        'Sec-Fetch-Site: same-site',
    ]);
    
    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    
    if ($imageData && $httpCode == 200 && strlen($imageData) > 5000) {
        $fileName = preg_replace('/[^a-z0-9-_]/i', '-', strtolower($productName)) . '.jpg';
        $localPath = $imageDir . '/' . $fileName;
        $publicUrl = '/images/products/' . $fileName;
        
        file_put_contents($localPath, $imageData);
        
        // Update database
        Product::withoutSyncingToSearch(function() use ($product, $publicUrl) {
            $product->update(['image_url' => $publicUrl]);
        });
        
        $size = round(strlen($imageData) / 1024, 2);
        echo "  ✓ Downloaded ({$size} KB) → {$publicUrl}\n";
        $downloaded++;
    } else {
        echo "  ✗ Failed (HTTP {$httpCode}, size: " . strlen($imageData) . ", type: {$contentType})\n";
        $failed++;
    }
    
    echo "\n";
    usleep(800000); // 0.8 second delay
}

echo "\n✅ Done!\n";
echo "   ✓ Downloaded: {$downloaded}\n";
echo "   ✗ Failed: {$failed}\n";

if ($downloaded > 0) {
    echo "\n🎉 Running status check...\n\n";
    passthru('php check-image-status.php');
}
