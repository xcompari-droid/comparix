<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\File;

// Create directory if it doesn't exist
$imageDir = public_path('images/products');
if (!File::exists($imageDir)) {
    File::makeDirectory($imageDir, 0755, true);
    echo "Created directory: {$imageDir}" . PHP_EOL;
}

// Get all OPPO products
$products = Product::where('brand', 'OPPO')->get();

echo "Found {$products->count()} OPPO products" . PHP_EOL . PHP_EOL;

foreach ($products as $product) {
    echo "Processing: {$product->name}" . PHP_EOL;
    
    if (!$product->image_url) {
        echo "  ⚠ No image URL" . PHP_EOL . PHP_EOL;
        continue;
    }
    
    echo "  Original URL: {$product->image_url}" . PHP_EOL;
    
    // Generate local filename
    $extension = pathinfo(parse_url($product->image_url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'png';
    $filename = 'oppo-' . $product->id . '-' . \Illuminate\Support\Str::slug($product->name) . '.' . $extension;
    $localPath = $imageDir . '/' . $filename;
    $publicPath = '/images/products/' . $filename;
    
    // Download image
    try {
        $context = stream_context_create([
            'http' => [
                'header' => [
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Accept: image/webp,image/apng,image/*,*/*;q=0.8'
                ],
                'timeout' => 30
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);
        
        $imageData = @file_get_contents($product->image_url, false, $context);
        
        if ($imageData === false) {
            echo "  ✗ Failed to download image" . PHP_EOL;
            
            // Try alternative method with cURL
            if (function_exists('curl_init')) {
                echo "  → Trying with cURL..." . PHP_EOL;
                
                $ch = curl_init($product->image_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                
                $imageData = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode !== 200 || $imageData === false) {
                    echo "  ✗ cURL also failed (HTTP {$httpCode})" . PHP_EOL . PHP_EOL;
                    continue;
                }
                
                echo "  ✓ Downloaded with cURL" . PHP_EOL;
            } else {
                echo "  ✗ cURL not available" . PHP_EOL . PHP_EOL;
                continue;
            }
        } else {
            echo "  ✓ Downloaded successfully" . PHP_EOL;
        }
        
        // Save to local file
        File::put($localPath, $imageData);
        echo "  ✓ Saved to: {$filename}" . PHP_EOL;
        
        // Update database
        $product->image_url = $publicPath;
        $product->save();
        echo "  ✓ Updated database" . PHP_EOL;
        
        // Get file size
        $size = File::size($localPath);
        echo "  📦 File size: " . round($size / 1024, 2) . " KB" . PHP_EOL;
        
    } catch (\Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . PHP_EOL;
    }
    
    echo PHP_EOL;
}

echo "✅ Download complete!" . PHP_EOL;
echo "Images saved to: {$imageDir}" . PHP_EOL;
