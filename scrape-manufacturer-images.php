<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use GuzzleHttp\Client;

echo "📥 Downloading REAL images from manufacturer websites...\n\n";

$imageDir = __DIR__ . '/public/images/products';
if (!file_exists($imageDir)) {
    mkdir($imageDir, 0755, true);
}

$client = new Client([
    'verify' => false,
    'timeout' => 30,
    'headers' => [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
        'Accept-Language' => 'ro-RO,ro;q=0.9,en-US;q=0.8,en;q=0.7',
        'Accept-Encoding' => 'gzip, deflate, br',
        'Connection' => 'keep-alive',
        'Upgrade-Insecure-Requests' => '1',
        'Sec-Fetch-Dest' => 'document',
        'Sec-Fetch-Mode' => 'navigate',
        'Sec-Fetch-Site' => 'none',
    ]
]);

// Direct product page URLs from manufacturers
$productPages = [
    'Samsung Galaxy S24 Ultra' => 'https://www.samsung.com/ro/smartphones/galaxy-s24-ultra/',
    'Samsung Galaxy S23 FE' => 'https://www.samsung.com/ro/smartphones/galaxy-s23-fe/',
    'Samsung Galaxy A55 5G' => 'https://www.samsung.com/ro/smartphones/galaxy-a55-5g/',
    'Samsung Galaxy A35 5G' => 'https://www.samsung.com/ro/smartphones/galaxy-a35-5g/',
    'Samsung Galaxy Z Fold5' => 'https://www.samsung.com/ro/smartphones/galaxy-z-fold5/',
    
    'OPPO Find X7 Ultra' => 'https://www.oppo.com/en/smartphones/series-find-x/find-x7-ultra/',
    'OPPO Reno 12 Pro 5G' => 'https://www.oppo.com/en/smartphones/series-reno/reno12-pro-5g/',
    'OPPO Reno 12 5G' => 'https://www.oppo.com/en/smartphones/series-reno/reno12-5g/',
    'OPPO A3 Pro 5G' => 'https://www.oppo.com/en/smartphones/series-a/a3-pro-5g/',
    'OPPO A79 5G' => 'https://www.oppo.com/en/smartphones/series-a/a79-5g/',
    
    'Huawei Pura 70 Ultra' => 'https://consumer.huawei.com/en/phones/pura-70-ultra/',
    'Huawei Pura 70 Pro' => 'https://consumer.huawei.com/en/phones/pura-70-pro/',
    'Huawei Pura 70' => 'https://consumer.huawei.com/en/phones/pura-70/',
    'Huawei Mate 60 Pro' => 'https://consumer.huawei.com/en/phones/mate60-pro/',
    'Huawei Mate X5' => 'https://consumer.huawei.com/en/phones/mate-x5/',
    'Huawei Nova 12 SE' => 'https://consumer.huawei.com/en/phones/nova-12-se/',
    'Huawei Nova 12i' => 'https://consumer.huawei.com/en/phones/nova-12i/',
    'Huawei P60 Pro' => 'https://consumer.huawei.com/en/phones/p60-pro/',
];

$downloaded = 0;
$failed = 0;

foreach ($productPages as $productName => $pageUrl) {
    $product = Product::where('name', $productName)->first();
    
    if (!$product) {
        echo "Product not found: {$productName}\n\n";
        continue;
    }
    
    echo "Processing: {$productName}\n";
    echo "  Fetching page: {$pageUrl}\n";
    
    try {
        // Download the product page
        $response = $client->get($pageUrl);
        $html = $response->getBody()->getContents();
        
        // Extract image URLs from the page
        $imageUrls = [];
        
        // Pattern 1: Samsung images
        preg_match_all('/https:\/\/images\.samsung\.com\/[^"\']+\.(?:jpg|jpeg|png|webp)/i', $html, $matches);
        $imageUrls = array_merge($imageUrls, $matches[0]);
        
        // Pattern 2: OPPO images
        preg_match_all('/https?:\/\/(?:image|static)\.oppo\.com\/[^"\']+\.(?:jpg|jpeg|png|webp)/i', $html, $matches);
        $imageUrls = array_merge($imageUrls, $matches[0]);
        
        // Pattern 3: Huawei images
        preg_match_all('/https?:\/\/consumer[^"\']*huawei\.com\/[^"\']+\.(?:jpg|jpeg|png|webp)/i', $html, $matches);
        $imageUrls = array_merge($imageUrls, $matches[0]);
        
        // Pattern 4: Any CDN images
        preg_match_all('/https?:\/\/[^"\']*cdn[^"\']*\/[^"\']+(?:phone|product|device)[^"\']+\.(?:jpg|jpeg|png|webp)/i', $html, $matches);
        $imageUrls = array_merge($imageUrls, $matches[0]);
        
        // Remove duplicates and filter for main product images
        $imageUrls = array_unique($imageUrls);
        $imageUrls = array_filter($imageUrls, function($url) {
            // Filter out icons, thumbnails, accessories
            return !preg_match('/(icon|thumb|banner|logo|accessory|charger|case)/i', $url) &&
                   preg_match('/(gallery|product|kv|hero|main|front|back)/i', $url);
        });
        
        if (empty($imageUrls)) {
            echo "  ⚠ No images found on page\n\n";
            $failed++;
            continue;
        }
        
        // Try the first suitable image
        $imageUrl = reset($imageUrls);
        echo "  Found image: {$imageUrl}\n";
        
        // Download the image
        $imageResponse = $client->get($imageUrl);
        $imageData = $imageResponse->getBody()->getContents();
        
        if (strlen($imageData) > 5000) {
            $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
                $extension = 'jpg';
            }
            
            $fileName = preg_replace('/[^a-z0-9-_]/i', '-', strtolower($productName)) . '.' . $extension;
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
            echo "  ✗ Image too small\n";
            $failed++;
        }
        
    } catch (\Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
        $failed++;
    }
    
    echo "\n";
    sleep(2); // 2 second delay between requests to be respectful
}

echo "\n✅ Done!\n";
echo "   ✓ Downloaded: {$downloaded}\n";
echo "   ✗ Failed: {$failed}\n";
echo "   📱 Total: " . ($downloaded + $failed) . "\n";
