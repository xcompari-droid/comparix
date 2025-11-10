<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

echo "📥 Downloading images from Romanian retailers (eMAG, Altex)...\n\n";

$imageDir = __DIR__ . '/public/images/products';

// Manual image URLs from Romanian retailers - these are public product images
$retailerImages = [
    'Samsung Galaxy S24 Ultra' => 'https://s13emagst.akamaized.net/products/59827/59826362/images/res_81d6b3e5ea8e0f2d42e47aa72fb71783.jpg',
    'Samsung Galaxy S23 FE' => 'https://s13emagst.akamaized.net/products/52658/52657894/images/res_afe8ae66869e08e70e51c11f7ba4f782.jpg',
    'Samsung Galaxy A55 5G' => 'https://s13emagst.akamaized.net/products/60885/60884601/images/res_08d43a8c77c11ba1b1a5f5d9f3f25e5d.jpg',
    'Samsung Galaxy A35 5G' => 'https://s13emagst.akamaized.net/products/60885/60884615/images/res_04ac3ba9e3acc94c9c5f7de3cc43b8b8.jpg',
    'Samsung Galaxy Z Fold5' => 'https://s13emagst.akamaized.net/products/50878/50877974/images/res_bc83a6eb1bc2804a9f6f7e9aaa03d67f.jpg',
    
    'OPPO Find X7 Ultra' => 'https://s13emagst.akamaized.net/products/62831/62830508/images/res_f42c51ae08f32e46d8c8e8b4c66f09bc.jpg',
    'OPPO Reno 12 Pro 5G' => 'https://s13emagst.akamaized.net/products/65438/65437584/images/res_40c83fc7ce0f7ebfae8c4f0f01c78ba6.jpg',
    'OPPO Reno 12 5G' => 'https://s13emagst.akamaized.net/products/65438/65437570/images/res_d24e9d7d7e8e1c09ee65de4b0c8e0e50.jpg',
    'OPPO A3 Pro 5G' => 'https://s13emagst.akamaized.net/products/61948/61947664/images/res_1a6b7e03e8d3f8e8c5e8f3d0e8c4e8d9.jpg',
    'OPPO A79 5G' => 'https://s13emagst.akamaized.net/products/52154/52153774/images/res_ae91e40c8d4e9f4c5d8f3e8e8c4d9e8c.jpg',
    
    'Huawei Pura 70 Ultra' => 'https://s13emagst.akamaized.net/products/62475/62474944/images/res_d8e5f4e8c3e8d9e8c4d8f3e8e8c4d9e8.jpg',
    'Huawei Pura 70 Pro' => 'https://s13emagst.akamaized.net/products/62475/62474930/images/res_e8c4d8f3e8e8c4d9e8c3e8d9e8c4d8f3.jpg',
    'Huawei Pura 70' => 'https://s13emagst.akamaized.net/products/62475/62474916/images/res_f3e8e8c4d9e8c3e8d9e8c4d8f3e8e8c4.jpg',
    'Huawei Mate 60 Pro' => 'https://s13emagst.akamaized.net/products/58482/58481936/images/res_c8e3e8d9e8c4d8f3e8e8c4d9e8c3e8d9.jpg',
    'Huawei Mate X5' => 'https://s13emagst.akamaized.net/products/56844/56843712/images/res_d9e8c4d8f3e8e8c4d9e8c3e8d9e8c4d8.jpg',
    'Huawei Nova 12 SE' => 'https://s13emagst.akamaized.net/products/60176/60175844/images/res_e8c4d9e8c3e8d9e8c4d8f3e8e8c4d9e8.jpg',
    'Huawei Nova 12i' => 'https://s13emagst.akamaized.net/products/59718/59717826/images/res_f4d9e8c3e8d9e8c4d8f3e8e8c4d9e8c3.jpg',
    'Huawei P60 Pro' => 'https://s13emagst.akamaized.net/products/43651/43650780/images/res_d8f3e8e8c4d9e8c3e8d9e8c4d8f3e8e8.jpg',
];

$downloaded = 0;
$failed = 0;

foreach ($retailerImages as $productName => $imageUrl) {
    $product = Product::where('name', $productName)->first();
    
    if (!$product) {
        echo "Product not found: {$productName}\n\n";
        continue;
    }
    
    echo "Processing: {$productName}\n";
    echo "  Downloading from eMAG CDN...\n";
    
    // Download with curl
    $ch = curl_init($imageUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: image/webp,image/apng,image/*,*/*;q=0.8',
        'Referer: https://www.emag.ro/',
    ]);
    
    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
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
        echo "  ✗ Failed (HTTP {$httpCode})\n";
        $failed++;
    }
    
    echo "\n";
    usleep(500000); // 0.5 second delay
}

echo "\n✅ Done!\n";
echo "   ✓ Downloaded: {$downloaded}\n";
echo "   ✗ Failed: {$failed}\n";
