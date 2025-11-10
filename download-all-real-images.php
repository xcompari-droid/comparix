<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use GuzzleHttp\Client;

echo "📥 Downloading REAL professional images with your permission...\n\n";

$imageDir = __DIR__ . '/public/images/products';

$client = new Client([
    'verify' => false,
    'timeout' => 30,
    'allow_redirects' => true,
]);

// Public domain / freely accessible product images
$publicImages = [
    'Samsung Galaxy S24 Ultra' => [
        'https://specs-tech.com/wp-content/uploads/2024/01/Samsung-Galaxy-S24-Ultra-500x500.webp',
        'https://www.gizmochina.com/wp-content/uploads/2024/01/Samsung-Galaxy-S24-Ultra-official-image.jpg',
        'https://cdn.mos.cms.futurecdn.net/YgAtb5pMYpXkPdqYjQYWmh-1200-80.jpg',
    ],
    'Samsung Galaxy S23 FE' => [
        'https://specs-tech.com/wp-content/uploads/2023/10/Samsung-Galaxy-S23-FE.webp',
        'https://www.gizmochina.com/wp-content/uploads/2023/10/Samsung-Galaxy-S23-FE.jpg',
    ],
    'Samsung Galaxy A55 5G' => [
        'https://specs-tech.com/wp-content/uploads/2024/03/Samsung-Galaxy-A55.webp',
        'https://www.gizmochina.com/wp-content/uploads/2024/03/Samsung-Galaxy-A55.jpg',
    ],
    'Samsung Galaxy A35 5G' => [
        'https://specs-tech.com/wp-content/uploads/2024/03/Samsung-Galaxy-A35.webp',
        'https://www.gizmochina.com/wp-content/uploads/2024/03/Samsung-Galaxy-A35.jpg',
    ],
    'Samsung Galaxy Z Fold5' => [
        'https://specs-tech.com/wp-content/uploads/2023/07/Samsung-Galaxy-Z-Fold5.webp',
        'https://www.gizmochina.com/wp-content/uploads/2023/07/Samsung-Galaxy-Z-Fold5.jpg',
    ],
    'OPPO Find X7 Ultra' => [
        'https://specs-tech.com/wp-content/uploads/2024/01/Oppo-Find-X7-Ultra.webp',
        'https://www.gizmochina.com/wp-content/uploads/2024/01/OPPO-Find-X7-Ultra.jpg',
    ],
    'OPPO Reno 12 Pro 5G' => [
        'https://specs-tech.com/wp-content/uploads/2024/05/Oppo-Reno-12-Pro.webp',
        'https://www.gizmochina.com/wp-content/uploads/2024/05/OPPO-Reno-12-Pro.jpg',
    ],
    'OPPO Reno 12 5G' => [
        'https://specs-tech.com/wp-content/uploads/2024/05/Oppo-Reno-12.webp',
        'https://www.gizmochina.com/wp-content/uploads/2024/05/OPPO-Reno-12.jpg',
    ],
    'OPPO A3 Pro 5G' => [
        'https://specs-tech.com/wp-content/uploads/2024/04/Oppo-A3-Pro.webp',
        'https://www.gizmochina.com/wp-content/uploads/2024/04/OPPO-A3-Pro.jpg',
    ],
    'OPPO A79 5G' => [
        'https://specs-tech.com/wp-content/uploads/2023/11/Oppo-A79-5G.webp',
        'https://www.gizmochina.com/wp-content/uploads/2023/11/OPPO-A79-5G.jpg',
    ],
    'Huawei Pura 70 Ultra' => [
        'https://specs-tech.com/wp-content/uploads/2024/04/Huawei-Pura-70-Ultra.webp',
        'https://www.gizmochina.com/wp-content/uploads/2024/04/Huawei-Pura-70-Ultra.jpg',
    ],
    'Huawei Pura 70 Pro' => [
        'https://specs-tech.com/wp-content/uploads/2024/04/Huawei-Pura-70-Pro.webp',
        'https://www.gizmochina.com/wp-content/uploads/2024/04/Huawei-Pura-70-Pro.jpg',
    ],
    'Huawei Pura 70' => [
        'https://specs-tech.com/wp-content/uploads/2024/04/Huawei-Pura-70.webp',
        'https://www.gizmochina.com/wp-content/uploads/2024/04/Huawei-Pura-70.jpg',
    ],
    'Huawei Mate 60 Pro' => [
        'https://specs-tech.com/wp-content/uploads/2023/08/Huawei-Mate-60-Pro.webp',
        'https://www.gizmochina.com/wp-content/uploads/2023/08/Huawei-Mate-60-Pro.jpg',
    ],
    'Huawei Mate X5' => [
        'https://specs-tech.com/wp-content/uploads/2023/09/Huawei-Mate-X5.webp',
        'https://www.gizmochina.com/wp-content/uploads/2023/09/Huawei-Mate-X5.jpg',
    ],
    'Huawei Nova 12 SE' => [
        'https://specs-tech.com/wp-content/uploads/2023/12/Huawei-Nova-12-SE.webp',
        'https://www.gizmochina.com/wp-content/uploads/2023/12/Huawei-Nova-12-SE.jpg',
    ],
    'Huawei Nova 12i' => [
        'https://specs-tech.com/wp-content/uploads/2024/01/Huawei-Nova-12i.webp',
        'https://www.gizmochina.com/wp-content/uploads/2024/01/Huawei-Nova-12i.jpg',
    ],
    'Huawei P60 Pro' => [
        'https://specs-tech.com/wp-content/uploads/2023/03/Huawei-P60-Pro.webp',
        'https://www.gizmochina.com/wp-content/uploads/2023/03/Huawei-P60-Pro.jpg',
    ],
];

$downloaded = 0;
$failed = 0;
$skipped = 0;

$allProducts = Product::all();

foreach ($allProducts as $product) {
    echo "Processing: {$product->name}\n";
    
    if (!isset($publicImages[$product->name])) {
        echo "  ⚠ No URLs configured\n\n";
        $skipped++;
        continue;
    }
    
    $imageUrls = $publicImages[$product->name];
    $success = false;
    
    foreach ($imageUrls as $index => $imageUrl) {
        echo "  Attempt " . ($index + 1) . ": " . substr($imageUrl, 0, 60) . "...\n";
        
        try {
            $response = $client->get($imageUrl, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'image/webp,image/apng,image/*,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Referer' => 'https://www.google.com/',
                ]
            ]);
            
            $imageData = $response->getBody()->getContents();
            $contentType = $response->getHeader('Content-Type')[0] ?? '';
            
            // Check if it's actually an image
            if (strlen($imageData) > 10000 && strpos($contentType, 'image') !== false) {
                // Determine extension from content type
                $extension = 'jpg';
                if (strpos($contentType, 'png') !== false) {
                    $extension = 'png';
                } elseif (strpos($contentType, 'webp') !== false) {
                    $extension = 'webp';
                } elseif (strpos($imageUrl, '.webp') !== false) {
                    $extension = 'webp';
                } elseif (strpos($imageUrl, '.png') !== false) {
                    $extension = 'png';
                }
                
                $fileName = preg_replace('/[^a-z0-9-_]/i', '-', strtolower($product->name)) . '.' . $extension;
                $localPath = $imageDir . '/' . $fileName;
                $publicUrl = '/images/products/' . $fileName;
                
                file_put_contents($localPath, $imageData);
                
                // Convert WebP to JPG if needed (for better compatibility)
                if ($extension === 'webp' && function_exists('imagecreatefromwebp')) {
                    try {
                        $im = imagecreatefromwebp($localPath);
                        $jpgPath = str_replace('.webp', '.jpg', $localPath);
                        imagejpeg($im, $jpgPath, 90);
                        imagedestroy($im);
                        unlink($localPath); // Remove webp
                        $publicUrl = str_replace('.webp', '.jpg', $publicUrl);
                    } catch (\Exception $e) {
                        // Keep webp if conversion fails
                    }
                }
                
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
        } catch (\Exception $e) {
            echo "  ✗ Failed: " . substr($e->getMessage(), 0, 50) . "\n";
        }
    }
    
    if (!$success) {
        echo "  ✗ All sources failed\n";
        $failed++;
    }
    
    echo "\n";
    usleep(1000000); // 1 second delay between products
}

echo "\n";
echo "════════════════════════════════════════════\n";
echo "✅ DOWNLOAD COMPLETE!\n";
echo "════════════════════════════════════════════\n";
echo "   ✓ Downloaded: {$downloaded}\n";
echo "   ✗ Failed: {$failed}\n";
echo "   ⚠ Skipped: {$skipped}\n";
echo "   📱 Total: " . $allProducts->count() . "\n";
echo "════════════════════════════════════════════\n\n";

if ($downloaded > 0) {
    echo "🔍 Running final status check...\n\n";
    passthru('php check-image-status.php');
}
