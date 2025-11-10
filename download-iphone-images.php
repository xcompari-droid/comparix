<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use GuzzleHttp\Client;

echo "📱 Downloading iPhone images...\n\n";

$imageDir = __DIR__ . '/public/images/products';

$client = new Client([
    'verify' => false,
    'timeout' => 30,
    'allow_redirects' => true,
]);

// iPhone images from various public sources
$iphoneImages = [
    'iPhone 15 Pro Max' => [
        'https://www.gizmochina.com/wp-content/uploads/2023/09/iPhone-15-Pro-Max.jpg',
        'https://specs-tech.com/wp-content/uploads/2023/09/Apple-iPhone-15-Pro-Max.webp',
        'https://fdn.gsmarena.com/imgroot/reviews/23/apple-iphone-15-pro-max/lifestyle/-1024w2/gsmarena_001.jpg',
    ],
    'iPhone 15 Pro' => [
        'https://www.gizmochina.com/wp-content/uploads/2023/09/iPhone-15-Pro.jpg',
        'https://specs-tech.com/wp-content/uploads/2023/09/Apple-iPhone-15-Pro.webp',
        'https://fdn.gsmarena.com/imgroot/reviews/23/apple-iphone-15-pro/lifestyle/-1024w2/gsmarena_001.jpg',
    ],
    'iPhone 15 Plus' => [
        'https://www.gizmochina.com/wp-content/uploads/2023/09/iPhone-15-Plus.jpg',
        'https://specs-tech.com/wp-content/uploads/2023/09/Apple-iPhone-15-Plus.webp',
        'https://fdn.gsmarena.com/imgroot/reviews/23/apple-iphone-15-plus/lifestyle/-1024w2/gsmarena_001.jpg',
    ],
    'iPhone 15' => [
        'https://www.gizmochina.com/wp-content/uploads/2023/09/iPhone-15.jpg',
        'https://specs-tech.com/wp-content/uploads/2023/09/Apple-iPhone-15.webp',
        'https://fdn.gsmarena.com/imgroot/reviews/23/apple-iphone-15/lifestyle/-1024w2/gsmarena_001.jpg',
    ],
    'iPhone 14' => [
        'https://www.gizmochina.com/wp-content/uploads/2022/09/iPhone-14.jpg',
        'https://specs-tech.com/wp-content/uploads/2022/09/Apple-iPhone-14.webp',
        'https://fdn.gsmarena.com/imgroot/reviews/22/apple-iphone-14/lifestyle/-1024w2/gsmarena_001.jpg',
    ],
    'iPhone SE (2022)' => [
        'https://www.gizmochina.com/wp-content/uploads/2022/03/iPhone-SE-2022.jpg',
        'https://specs-tech.com/wp-content/uploads/2022/03/Apple-iPhone-SE-2022.webp',
        'https://fdn.gsmarena.com/imgroot/reviews/22/apple-iphone-se-2022/lifestyle/-1024w2/gsmarena_001.jpg',
    ],
];

$downloaded = 0;
$failed = 0;

$appleProducts = Product::where('brand', 'Apple')->get();

foreach ($appleProducts as $product) {
    echo "Processing: {$product->name}\n";
    
    if (!isset($iphoneImages[$product->name])) {
        echo "  ⚠ No URLs configured\n\n";
        $failed++;
        continue;
    }
    
    $imageUrls = $iphoneImages[$product->name];
    $success = false;
    
    foreach ($imageUrls as $index => $imageUrl) {
        echo "  Attempt " . ($index + 1) . ": " . substr($imageUrl, 0, 60) . "...\n";
        
        try {
            $response = $client->get($imageUrl, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept' => 'image/*,*/*;q=0.8',
                    'Referer' => 'https://www.google.com/',
                ]
            ]);
            
            $imageData = $response->getBody()->getContents();
            $contentType = $response->getHeader('Content-Type')[0] ?? '';
            
            if (strlen($imageData) > 10000 && strpos($contentType, 'image') !== false) {
                $extension = 'jpg';
                if (strpos($contentType, 'png') !== false || strpos($imageUrl, '.png') !== false) {
                    $extension = 'png';
                } elseif (strpos($contentType, 'webp') !== false || strpos($imageUrl, '.webp') !== false) {
                    $extension = 'webp';
                }
                
                $fileName = preg_replace('/[^a-z0-9-_]/i', '-', strtolower($product->name)) . '.' . $extension;
                $localPath = $imageDir . '/' . $fileName;
                $publicUrl = '/images/products/' . $fileName;
                
                file_put_contents($localPath, $imageData);
                
                // Convert WebP to JPG for better compatibility
                if ($extension === 'webp' && function_exists('imagecreatefromwebp')) {
                    try {
                        $im = imagecreatefromwebp($localPath);
                        $jpgPath = str_replace('.webp', '.jpg', $localPath);
                        imagejpeg($im, $jpgPath, 90);
                        imagedestroy($im);
                        unlink($localPath);
                        $publicUrl = str_replace('.webp', '.jpg', $publicUrl);
                    } catch (\Exception $e) {
                        // Keep webp if conversion fails
                    }
                }
                
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
            // Try next URL
        }
    }
    
    if (!$success) {
        echo "  ✗ All sources failed\n";
        $failed++;
    }
    
    echo "\n";
    sleep(1);
}

echo "\n════════════════════════════════════════════\n";
echo "✅ iPhone Images Downloaded!\n";
echo "════════════════════════════════════════════\n";
echo "   ✓ Downloaded: {$downloaded}\n";
echo "   ✗ Failed: {$failed}\n";
echo "   📱 Total iPhones: " . $appleProducts->count() . "\n";
echo "════════════════════════════════════════════\n\n";

echo "🔍 Checking all products now...\n\n";
passthru('php check-image-status.php');
