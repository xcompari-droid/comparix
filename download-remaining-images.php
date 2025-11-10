<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

echo "📥 Downloading remaining product images...\n\n";

$imageDir = __DIR__ . '/public/images/products';

// Alternative sources for missing products
$productImages = [
    'Samsung Galaxy S24 Ultra' => 'https://images.samsung.com/is/image/samsung/p6pim/ro/2401/gallery/ro-galaxy-s24-s928-sm-s928bzkgeue-539573226?$650_519_PNG$',
    'OPPO Reno 12 Pro 5G' => 'https://oasis.opstatics.com/content/dam/oasis/page/2024/na/reno-12-series/reno-12-pro-5g/specs/Reno12_Pro_Brown.png',
    'OPPO Reno 12 5G' => 'https://oasis.opstatics.com/content/dam/oasis/page/2024/na/reno-12-series/reno-12-5g/specs/Reno12_5G_Sunset_Peach.png',
    'OPPO A3 Pro 5G' => 'https://oasis.opstatics.com/content/dam/oasis/page/2024/in/a3-pro-5g/A3_Pro_5G_Moonlight_Purple.png',
    'Huawei Pura 70 Ultra' => 'https://consumer.huawei.com/content/dam/huawei-cbg-site/common/mkt/list-image/phones/pura70-ultra/pura70-ultra-green.png',
    'Huawei Pura 70 Pro' => 'https://consumer.huawei.com/content/dam/huawei-cbg-site/common/mkt/list-image/phones/pura70-pro/pura70-pro-white.png',
    'Huawei Pura 70' => 'https://consumer.huawei.com/content/dam/huawei-cbg-site/common/mkt/list-image/phones/pura70/pura70-pink.png',
    'Huawei Nova 12 SE' => 'https://consumer.huawei.com/content/dam/huawei-cbg-site/common/mkt/list-image/phones/nova12-se/nova12-se-green.png',
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
    
    $fileName = preg_replace('/[^a-z0-9-_]/i', '-', strtolower($productName)) . '.png';
    $localPath = $imageDir . '/' . $fileName;
    $publicUrl = '/images/products/' . $fileName;
    
    // Try to download with curl
    $ch = curl_init($imageUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: image/webp,image/apng,image/*,*/*;q=0.8',
        'Accept-Language: ro-RO,ro;q=0.9,en-US;q=0.8,en;q=0.7',
        'Referer: https://www.google.com/',
    ]);
    
    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
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
        echo "  ✗ Failed (HTTP {$httpCode})\n";
        $failed++;
    }
    
    echo "\n";
    usleep(1000000); // 1 second delay between requests
}

echo "\n✅ Done!\n";
echo "   Downloaded: {$downloaded}\n";
echo "   Failed: {$failed}\n";
