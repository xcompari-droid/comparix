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

// Color schemes for different products
$colors = [
    ['bg' => '#0891b2', 'text' => '#ffffff'], // cyan
    ['bg' => '#10b981', 'text' => '#ffffff'], // emerald
    ['bg' => '#8b5cf6', 'text' => '#ffffff'], // violet
    ['bg' => '#f59e0b', 'text' => '#ffffff'], // amber
    ['bg' => '#ef4444', 'text' => '#ffffff'], // red
];

$colorIndex = 0;

foreach ($products as $product) {
    echo "Processing: {$product->name}" . PHP_EOL;
    
    // Generate local filename
    $filename = 'oppo-' . $product->id . '-' . \Illuminate\Support\Str::slug($product->name) . '.svg';
    $localPath = $imageDir . '/' . $filename;
    $publicPath = '/images/products/' . $filename;
    
    // Get color scheme
    $color = $colors[$colorIndex % count($colors)];
    $colorIndex++;
    
    // Create SVG with product info
    $brandName = htmlspecialchars($product->brand);
    $modelName = htmlspecialchars($product->name);
    
    // Extract model name without brand
    $shortName = str_replace(['OPPO ', 'Samsung ', 'Galaxy '], '', $modelName);
    
    $svg = <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg width="400" height="400" xmlns="http://www.w3.org/2000/svg">
    <!-- Background -->
    <defs>
        <linearGradient id="grad{$product->id}" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:{$color['bg']};stop-opacity:1" />
            <stop offset="100%" style="stop-color:{$color['bg']};stop-opacity:0.7" />
        </linearGradient>
    </defs>
    
    <rect width="400" height="400" fill="url(#grad{$product->id})"/>
    
    <!-- Phone Icon -->
    <g transform="translate(150, 80)">
        <rect x="0" y="0" width="100" height="180" rx="15" fill="{$color['text']}" opacity="0.9"/>
        <rect x="10" y="15" width="80" height="140" rx="5" fill="{$color['bg']}" opacity="0.3"/>
        <circle cx="50" cy="165" r="8" fill="{$color['bg']}" opacity="0.3"/>
    </g>
    
    <!-- Brand -->
    <text x="200" y="290" font-family="Arial, sans-serif" font-size="32" font-weight="bold" 
          fill="{$color['text']}" text-anchor="middle">{$brandName}</text>
    
    <!-- Model -->
    <text x="200" y="325" font-family="Arial, sans-serif" font-size="18" 
          fill="{$color['text']}" text-anchor="middle" opacity="0.9">{$shortName}</text>
    
    <!-- Price indicator if available -->
SVG;

    // Add price if available
    if ($product->offers->count() > 0) {
        $price = number_format($product->offers->first()->price, 0);
        $svg .= <<<SVG
    <text x="200" y="360" font-family="Arial, sans-serif" font-size="24" font-weight="bold"
          fill="{$color['text']}" text-anchor="middle">{$price} RON</text>
SVG;
    }
    
    $svg .= "\n</svg>";
    
    // Save SVG
    File::put($localPath, $svg);
    echo "  ✓ Created SVG: {$filename}" . PHP_EOL;
    
    // Update database without Scout syncing
    Product::withoutSyncingToSearch(function () use ($product, $publicPath) {
        $product->image_url = $publicPath;
        $product->save();
    });
    echo "  ✓ Updated database with local path" . PHP_EOL;
    
    echo PHP_EOL;
}

echo "✅ Image generation complete!" . PHP_EOL;
echo "Images saved to: {$imageDir}" . PHP_EOL;
echo PHP_EOL;
echo "📱 Generated SVG placeholders for all products with gradient backgrounds" . PHP_EOL;
