<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use GuzzleHttp\Client;

echo "📥 Final attempt: Downloading last 3 products...\n\n";

$imageDir = __DIR__ . '/public/images/products';

$client = new Client([
    'verify' => false,
    'timeout' => 30,
    'allow_redirects' => true,
]);

// Multiple alternative sources for these 3 specific products
$lastThree = [
    'Samsung Galaxy S24 Ultra' => [
        'https://www.androidauthority.com/wp-content/uploads/2024/01/Samsung-Galaxy-S24-Ultra-in-hand-showing-back-panel.jpg',
        'https://m.media-amazon.com/images/I/71gFHrL50dL._AC_SL1500_.jpg',
        'https://fdn.gsmarena.com/imgroot/news/24/01/samsung-galaxy-s24-ultra-review/-1200/gsmarena_001.jpg',
    ],
    'OPPO Reno 12 Pro 5G' => [
        'https://www.androidauthority.com/wp-content/uploads/2024/05/OPPO-Reno-12-Pro-featured.jpg',
        'https://m.media-amazon.com/images/I/61qI3vqHFKL._AC_SL1500_.jpg',
        'https://fdn.gsmarena.com/imgroot/news/24/05/oppo-reno12-pro/-1200/gsmarena_001.jpg',
    ],
    'OPPO Reno 12 5G' => [
        'https://www.androidauthority.com/wp-content/uploads/2024/05/OPPO-Reno-12-featured.jpg',
        'https://m.media-amazon.com/images/I/61N9mP8FYBL._AC_SL1500_.jpg',
        'https://fdn.gsmarena.com/imgroot/news/24/05/oppo-reno12/-1200/gsmarena_001.jpg',
    ],
];

$downloaded = 0;

foreach ($lastThree as $productName => $imageUrls) {
    $product = Product::where('name', $productName)->first();
    
    if (!$product) continue;
    
    echo "Processing: {$productName}\n";
    
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
            
            if (strlen($imageData) > 10000) {
                $fileName = preg_replace('/[^a-z0-9-_]/i', '-', strtolower($productName)) . '.jpg';
                $localPath = $imageDir . '/' . $fileName;
                $publicUrl = '/images/products/' . $fileName;
                
                file_put_contents($localPath, $imageData);
                
                Product::withoutSyncingToSearch(function() use ($product, $publicUrl) {
                    $product->update(['image_url' => $publicUrl]);
                });
                
                $size = round(strlen($imageData) / 1024, 2);
                echo "  ✓ SUCCESS! Downloaded ({$size} KB)\n";
                $downloaded++;
                break;
            }
        } catch (\Exception $e) {
            // Continue to next URL
        }
    }
    echo "\n";
    sleep(1);
}

echo "\n════════════════════════════════════════════\n";
echo "Downloaded {$downloaded} out of 3 remaining products\n";
echo "════════════════════════════════════════════\n\n";

passthru('php check-image-status.php');
