<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use GuzzleHttp\Client;

echo "📥 Searching and downloading REAL manufacturer images...\n\n";

$imageDir = __DIR__ . '/public/images/products';

$client = new Client([
    'verify' => false,
    'timeout' => 30,
]);

// Use Bing Image Search to find official product images
$products = Product::all();
$downloaded = 0;
$failed = 0;

foreach ($products as $product) {
    echo "Processing: {$product->name}\n";
    
    // Search for official product image
    $searchQuery = urlencode($product->brand . ' ' . $product->name . ' official product image png');
    $searchUrl = "https://www.bing.com/images/search?q={$searchQuery}&first=1";
    
    try {
        $response = $client->get($searchUrl, [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ]
        ]);
        
        $html = $response->getBody()->getContents();
        
        // Extract image URLs from Bing results
        preg_match_all('/"murl":"([^"]+)"/', $html, $matches);
        
        if (!empty($matches[1])) {
            $imageUrls = array_slice($matches[1], 0, 5); // Try first 5 images
            
            foreach ($imageUrls as $imageUrl) {
                $imageUrl = str_replace('\/', '/', $imageUrl);
                
                // Skip non-product images
                if (preg_match('/(icon|logo|banner|thumb)/i', $imageUrl)) {
                    continue;
                }
                
                echo "  Trying: " . substr($imageUrl, 0, 80) . "...\n";
                
                try {
                    $imgResponse = $client->get($imageUrl, [
                        'headers' => [
                            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                            'Referer' => 'https://www.google.com/',
                        ],
                        'timeout' => 15,
                    ]);
                    
                    $imageData = $imgResponse->getBody()->getContents();
                    
                    if (strlen($imageData) > 10000) { // At least 10KB
                        $extension = 'jpg';
                        if (preg_match('/\.png$/i', $imageUrl)) {
                            $extension = 'png';
                        } elseif (preg_match('/\.webp$/i', $imageUrl)) {
                            $extension = 'webp';
                        }
                        
                        $fileName = preg_replace('/[^a-z0-9-_]/i', '-', strtolower($product->name)) . '.' . $extension;
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
                        break; // Got one, move to next product
                    }
                } catch (\Exception $e) {
                    // Try next image
                    continue;
                }
            }
        }
        
        if (!isset($imageData) || strlen($imageData) < 10000) {
            echo "  ✗ No suitable image found\n";
            $failed++;
        }
        
    } catch (\Exception $e) {
        echo "  ✗ Search failed: " . $e->getMessage() . "\n";
        $failed++;
    }
    
    echo "\n";
    sleep(3); // 3 second delay to avoid rate limiting
}

echo "\n✅ Done!\n";
echo "   ✓ Downloaded: {$downloaded}\n";
echo "   ✗ Failed: {$failed}\n";
