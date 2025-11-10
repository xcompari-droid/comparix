<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

echo "📥 Downloading images for remaining 6 products...\n\n";

$imageDir = __DIR__ . '/public/images/products';

// Alternative high-quality sources
$remainingImages = [
    'Huawei Pura 70 Ultra' => [
        'https://consumer-tkbdownload.huawei.com/ctkbfm/servlet/download/downloadHandle/P020240411416821086889.png',
        'https://phonesdata.com/files/models/Huawei-Pura-70-Ultra-1300.jpg',
        'https://www.devicespecifications.com/images/model/e7aa5ed7/320/main.jpg',
    ],
    
    'Huawei Pura 70 Pro' => [
        'https://consumer-tkbdownload.huawei.com/ctkbfm/servlet/download/downloadHandle/P020240411416821085788.png',
        'https://phonesdata.com/files/models/Huawei-Pura-70-Pro-1299.jpg',
        'https://www.devicespecifications.com/images/model/0a8e5ed6/320/main.jpg',
    ],
    
    'Huawei Pura 70' => [
        'https://consumer-tkbdownload.huawei.com/ctkbfm/servlet/download/downloadHandle/P020240411416821084687.png',
        'https://phonesdata.com/files/models/Huawei-Pura-70-1298.jpg',
        'https://www.devicespecifications.com/images/model/315e5ed5/320/main.jpg',
    ],
    
    'OPPO A3 Pro 5G' => [
        'https://phonesdata.com/files/models/OPPO-A3-Pro-5G-1423.jpg',
        'https://www.devicespecifications.com/images/model/9e8d5eca/320/main.jpg',
        'https://cdn.devicespecifications.com/images/model/9e8d5eca/main.jpg',
    ],
    
    'OPPO Reno 12 5G' => [
        'https://phonesdata.com/files/models/OPPO-Reno-12-5G-1442.jpg',
        'https://www.devicespecifications.com/images/model/e0eb5ee4/320/main.jpg',
        'https://cdn.devicespecifications.com/images/model/e0eb5ee4/main.jpg',
    ],
    
    'OPPO Reno 12 Pro 5G' => [
        'https://phonesdata.com/files/models/OPPO-Reno-12-Pro-5G-1443.jpg',
        'https://www.devicespecifications.com/images/model/5eeb5ee5/320/main.jpg',
        'https://cdn.devicespecifications.com/images/model/5eeb5ee5/main.jpg',
    ],
];

$downloaded = 0;
$failed = 0;

foreach ($remainingImages as $productName => $imageUrls) {
    $product = Product::where('name', $productName)->first();
    
    if (!$product) {
        echo "Product not found: {$productName}\n\n";
        continue;
    }
    
    echo "Processing: {$productName}\n";
    
    $success = false;
    
    // Try each URL until one works
    foreach ($imageUrls as $index => $imageUrl) {
        echo "  Trying source " . ($index + 1) . "...\n";
        
        $extension = strpos($imageUrl, '.png') !== false ? 'png' : 'jpg';
        $fileName = preg_replace('/[^a-z0-9-_]/i', '-', strtolower($productName)) . '.' . $extension;
        $localPath = $imageDir . '/' . $fileName;
        $publicUrl = '/images/products/' . $fileName;
        
        // Download with curl
        $ch = curl_init($imageUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: image/webp,image/apng,image/jpeg,image/png,image/*,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
        ]);
        
        $imageData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($imageData && $httpCode == 200 && strlen($imageData) > 3000) {
            file_put_contents($localPath, $imageData);
            
            // Update database
            Product::withoutSyncingToSearch(function() use ($product, $publicUrl) {
                $product->update(['image_url' => $publicUrl]);
            });
            
            $size = round(strlen($imageData) / 1024, 2);
            echo "  ✓ Downloaded ({$size} KB) → {$publicUrl}\n";
            $downloaded++;
            $success = true;
            break;
        }
    }
    
    if (!$success) {
        echo "  ✗ All sources failed\n";
        $failed++;
    }
    
    echo "\n";
    usleep(1000000); // 1 second delay
}

echo "\n✅ Done!\n";
echo "   ✓ Downloaded: {$downloaded}\n";
echo "   ✗ Failed: {$failed}\n";
