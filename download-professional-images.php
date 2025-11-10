<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

echo "📥 Downloading professional product images...\n\n";

$imageDir = __DIR__ . '/public/images/products';
if (!file_exists($imageDir)) {
    mkdir($imageDir, 0755, true);
}

// Professional images from multiple sources (GSMArena, PhoneArena, official press kits)
$professionalImages = [
    // Samsung - High quality press images
    'Samsung Galaxy S24 Ultra' => [
        'https://images.samsung.com/ro/smartphones/galaxy-s24-ultra/images/galaxy-s24-ultra-highlights-kv.jpg',
        'https://fdn2.gsmarena.com/vv/pics/samsung/samsung-galaxy-s24-ultra-5g-1.jpg',
        'https://image-us.samsung.com/SamsungUS/home/mobile/phones/gallery/MB-S928-Titanium-Gray-Back-DT.jpg',
    ],
    
    'Samsung Galaxy S23 FE' => [
        'https://images.samsung.com/ro/smartphones/galaxy-s23-fe/images/galaxy-s23-fe-highlights-kv.jpg',
        'https://fdn2.gsmarena.com/vv/pics/samsung/samsung-galaxy-s23-fe-5g-1.jpg',
    ],
    
    'Samsung Galaxy A55 5G' => [
        'https://images.samsung.com/ro/smartphones/galaxy-a55-5g/images/galaxy-a55-5g-highlights-color-navy-back.jpg',
        'https://fdn2.gsmarena.com/vv/pics/samsung/samsung-galaxy-a55-5g-1.jpg',
    ],
    
    'Samsung Galaxy A35 5G' => [
        'https://images.samsung.com/ro/smartphones/galaxy-a35-5g/images/galaxy-a35-5g-highlights-color-navy-back.jpg',
        'https://fdn2.gsmarena.com/vv/pics/samsung/samsung-galaxy-a35-5g-1.jpg',
    ],
    
    'Samsung Galaxy Z Fold5' => [
        'https://images.samsung.com/ro/smartphones/galaxy-z-fold5/images/galaxy-z-fold5-highlights-kv.jpg',
        'https://fdn2.gsmarena.com/vv/pics/samsung/samsung-galaxy-z-fold5-1.jpg',
    ],
    
    // OPPO - Professional product shots
    'OPPO Find X7 Ultra' => [
        'https://fdn2.gsmarena.com/vv/pics/oppo/oppo-find-x7-ultra-1.jpg',
        'https://fdn2.gsmarena.com/vv/pics/oppo/oppo-find-x7-ultra-2.jpg',
    ],
    
    'OPPO Reno 12 Pro 5G' => [
        'https://fdn2.gsmarena.com/vv/pics/oppo/oppo-reno12-pro-5g-1.jpg',
        'https://fdn2.gsmarena.com/vv/pics/oppo/oppo-reno12-pro-5g-2.jpg',
    ],
    
    'OPPO Reno 12 5G' => [
        'https://fdn2.gsmarena.com/vv/pics/oppo/oppo-reno12-5g-1.jpg',
        'https://fdn2.gsmarena.com/vv/pics/oppo/oppo-reno12-5g-2.jpg',
    ],
    
    'OPPO A3 Pro 5G' => [
        'https://fdn2.gsmarena.com/vv/pics/oppo/oppo-a3-pro-5g-1.jpg',
        'https://fdn2.gsmarena.com/vv/pics/oppo/oppo-a3-pro-5g-2.jpg',
    ],
    
    'OPPO A79 5G' => [
        'https://fdn2.gsmarena.com/vv/pics/oppo/oppo-a79-5g-1.jpg',
        'https://fdn2.gsmarena.com/vv/pics/oppo/oppo-a79-5g-2.jpg',
    ],
    
    // Huawei - Professional shots
    'Huawei Pura 70 Ultra' => [
        'https://fdn2.gsmarena.com/vv/pics/huawei/huawei-pura-70-ultra-1.jpg',
        'https://fdn2.gsmarena.com/vv/pics/huawei/huawei-pura-70-ultra-2.jpg',
    ],
    
    'Huawei Pura 70 Pro' => [
        'https://fdn2.gsmarena.com/vv/pics/huawei/huawei-pura-70-pro-1.jpg',
        'https://fdn2.gsmarena.com/vv/pics/huawei/huawei-pura-70-pro-2.jpg',
    ],
    
    'Huawei Pura 70' => [
        'https://fdn2.gsmarena.com/vv/pics/huawei/huawei-pura-70-1.jpg',
        'https://fdn2.gsmarena.com/vv/pics/huawei/huawei-pura-70-2.jpg',
    ],
    
    'Huawei Mate 60 Pro' => [
        'https://fdn2.gsmarena.com/vv/pics/huawei/huawei-mate-60-pro-1.jpg',
        'https://fdn2.gsmarena.com/vv/pics/huawei/huawei-mate-60-pro-2.jpg',
    ],
    
    'Huawei Mate X5' => [
        'https://fdn2.gsmarena.com/vv/pics/huawei/huawei-mate-x5-1.jpg',
        'https://fdn2.gsmarena.com/vv/pics/huawei/huawei-mate-x5-2.jpg',
    ],
    
    'Huawei Nova 12 SE' => [
        'https://fdn2.gsmarena.com/vv/pics/huawei/huawei-nova-12-se-1.jpg',
        'https://fdn2.gsmarena.com/vv/pics/huawei/huawei-nova-12-se-2.jpg',
    ],
    
    'Huawei Nova 12i' => [
        'https://fdn2.gsmarena.com/vv/pics/huawei/huawei-nova-12i-1.jpg',
        'https://fdn2.gsmarena.com/vv/pics/huawei/huawei-nova-12i-2.jpg',
    ],
    
    'Huawei P60 Pro' => [
        'https://fdn2.gsmarena.com/vv/pics/huawei/huawei-p60-pro-1.jpg',
        'https://fdn2.gsmarena.com/vv/pics/huawei/huawei-p60-pro-2.jpg',
    ],
];

$allProducts = Product::all();
$downloaded = 0;
$failed = 0;

foreach ($allProducts as $product) {
    echo "Processing: {$product->name}\n";
    
    if (!isset($professionalImages[$product->name])) {
        echo "  ⚠ No image URLs configured\n\n";
        $failed++;
        continue;
    }
    
    $imageUrls = $professionalImages[$product->name];
    $success = false;
    
    // Try each URL until one works
    foreach ($imageUrls as $index => $imageUrl) {
        echo "  Trying source " . ($index + 1) . "...\n";
        
        $extension = strpos($imageUrl, '.png') !== false ? 'png' : 'jpg';
        $fileName = preg_replace('/[^a-z0-9-_]/i', '-', strtolower($product->name)) . '.' . $extension;
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
            'Referer: https://www.gsmarena.com/',
        ]);
        
        $imageData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($imageData && $httpCode == 200 && strlen($imageData) > 5000) {
            file_put_contents($localPath, $imageData);
            
            // Update database
            Product::withoutSyncingToSearch(function() use ($product, $publicUrl) {
                $product->update(['image_url' => $publicUrl]);
            });
            
            $size = round(strlen($imageData) / 1024, 2);
            echo "  ✓ Downloaded professional image ({$size} KB) → {$publicUrl}\n";
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
    usleep(800000); // 0.8 second delay between products
}

echo "\n✅ Done!\n";
echo "   ✓ Downloaded: {$downloaded}\n";
echo "   ✗ Failed: {$failed}\n";
echo "   📱 Total: " . ($downloaded + $failed) . "\n";
