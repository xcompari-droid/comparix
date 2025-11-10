<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

echo "📥 Downloading product images locally...\n\n";

// Create images directory if it doesn't exist
$imageDir = __DIR__ . '/public/images/products';
if (!file_exists($imageDir)) {
    mkdir($imageDir, 0755, true);
    echo "Created directory: {$imageDir}\n\n";
}

// Alternative image sources - using global tech retailers and review sites
$productImages = [
    // Samsung - using GSMArena cached images (more reliable)
    'Samsung Galaxy S24 Ultra' => 'https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-s24-ultra-5g.jpg',
    'Samsung Galaxy S23 FE' => 'https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-s23-fe.jpg',
    'Samsung Galaxy A55 5G' => 'https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-a55.jpg',
    'Samsung Galaxy A35 5G' => 'https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-a35.jpg',
    'Samsung Galaxy Z Fold5' => 'https://fdn2.gsmarena.com/vv/bigpic/samsung-galaxy-z-fold5.jpg',
    
    // OPPO
    'OPPO Find X7 Ultra' => 'https://fdn2.gsmarena.com/vv/bigpic/oppo-find-x7-ultra.jpg',
    'OPPO Reno 12 Pro 5G' => 'https://fdn2.gsmarena.com/vv/bigpic/oppo-reno12-pro-5g.jpg',
    'OPPO Reno 12 5G' => 'https://fdn2.gsmarena.com/vv/bigpic/oppo-reno12-5g.jpg',
    'OPPO A3 Pro 5G' => 'https://fdn2.gsmarena.com/vv/bigpic/oppo-a3-pro-5g.jpg',
    'OPPO A79 5G' => 'https://fdn2.gsmarena.com/vv/bigpic/oppo-a79-5g.jpg',
    
    // Huawei
    'Huawei Pura 70 Ultra' => 'https://fdn2.gsmarena.com/vv/bigpic/huawei-pura-70-ultra.jpg',
    'Huawei Pura 70 Pro' => 'https://fdn2.gsmarena.com/vv/bigpic/huawei-pura-70-pro.jpg',
    'Huawei Pura 70' => 'https://fdn2.gsmarena.com/vv/bigpic/huawei-pura-70.jpg',
    'Huawei Mate 60 Pro' => 'https://fdn2.gsmarena.com/vv/bigpic/huawei-mate-60-pro.jpg',
    'Huawei Mate X5' => 'https://fdn2.gsmarena.com/vv/bigpic/huawei-mate-x5.jpg',
    'Huawei Nova 12 SE' => 'https://fdn2.gsmarena.com/vv/bigpic/huawei-nova-12-se.jpg',
    'Huawei Nova 12i' => 'https://fdn2.gsmarena.com/vv/bigpic/huawei-nova-12i.jpg',
    'Huawei P60 Pro' => 'https://fdn2.gsmarena.com/vv/bigpic/huawei-p60-pro.jpg',
];

$allProducts = Product::all();
$downloaded = 0;
$failed = 0;

foreach ($allProducts as $product) {
    echo "Processing: {$product->name}\n";
    
    if (!isset($productImages[$product->name])) {
        echo "  ⚠ No image URL configured\n\n";
        $failed++;
        continue;
    }
    
    $imageUrl = $productImages[$product->name];
    $fileName = preg_replace('/[^a-z0-9-_]/i', '-', strtolower($product->name)) . '.jpg';
    $localPath = $imageDir . '/' . $fileName;
    $publicUrl = '/images/products/' . $fileName;
    
    // Try to download with curl
    $ch = curl_init($imageUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
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
    usleep(500000); // 0.5 second delay between requests
}

echo "\n✅ Done!\n";
echo "   Downloaded: {$downloaded}\n";
echo "   Failed: {$failed}\n";
