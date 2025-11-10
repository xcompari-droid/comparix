<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

echo "🎨 Creating professional gradient images for remaining products...\n\n";

$imageDir = __DIR__ . '/public/images/products';

// Products needing professional gradient images
$products = [
    'Huawei Pura 70 Ultra' => ['brand' => 'Huawei', 'color1' => '#C8102E', 'color2' => '#8B0000'],
    'Huawei Pura 70 Pro' => ['brand' => 'Huawei', 'color1' => '#DC143C', 'color2' => '#A52A2A'],
    'Huawei Pura 70' => ['brand' => 'Huawei', 'color1' => '#FF6B6B', 'color2' => '#C41E3A'],
    'OPPO A3 Pro 5G' => ['brand' => 'OPPO', 'color1' => '#06B6D4', 'color2' => '#0891B2'],
    'OPPO Reno 12 5G' => ['brand' => 'OPPO', 'color1' => '#14B8A6', 'color2' => '#0D9488'],
    'OPPO Reno 12 Pro 5G' => ['brand' => 'OPPO', 'color1' => '#06B6D4', 'color2' => '#0284C7'],
];

foreach ($products as $productName => $config) {
    $product = Product::where('name', $productName)->first();
    
    if (!$product) {
        echo "Product not found: {$productName}\n\n";
        continue;
    }
    
    echo "Creating image for: {$productName}\n";
    
    // Create professional SVG with gradient
    $modelName = str_replace($config['brand'] . ' ', '', $productName);
    $shortName = str_replace(['5G', ' Pro', ' Ultra'], ['', ' PRO', ' ULTRA'], $modelName);
    
    $svg = <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg width="800" height="800" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="bgGradient" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:{$config['color1']};stop-opacity:1" />
      <stop offset="100%" style="stop-color:{$config['color2']};stop-opacity:1" />
    </linearGradient>
    <filter id="shadow">
      <feDropShadow dx="0" dy="10" stdDeviation="20" flood-opacity="0.3"/>
    </filter>
    <linearGradient id="phoneGradient" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:#ffffff;stop-opacity:0.9" />
      <stop offset="100%" style="stop-color:#f0f0f0;stop-opacity:0.9" />
    </linearGradient>
  </defs>
  
  <!-- Background -->
  <rect width="800" height="800" fill="url(#bgGradient)"/>
  
  <!-- Phone device mockup -->
  <g transform="translate(400, 400)" filter="url(#shadow)">
    <!-- Phone body -->
    <rect x="-120" y="-220" width="240" height="440" rx="30" fill="url(#phoneGradient)" stroke="#d0d0d0" stroke-width="2"/>
    
    <!-- Screen -->
    <rect x="-110" y="-200" width="220" height="390" rx="25" fill="#000000" opacity="0.95"/>
    
    <!-- Screen content (gradient) -->
    <rect x="-105" y="-195" width="210" height="380" rx="22" fill="url(#bgGradient)" opacity="0.3"/>
    
    <!-- Notch/Camera -->
    <ellipse cx="0" cy="-180" rx="30" ry="8" fill="#1a1a1a"/>
    
    <!-- Camera lenses -->
    <g transform="translate(-105, 120)">
      <circle cx="0" cy="0" r="25" fill="#2a2a2a" stroke="#404040" stroke-width="2"/>
      <circle cx="0" cy="0" r="15" fill="#1a1a1a"/>
      <circle cx="-3" cy="-3" r="5" fill="#4a4a4a"/>
      
      <circle cx="50" cy="0" r="20" fill="#2a2a2a" stroke="#404040" stroke-width="2"/>
      <circle cx="50" cy="0" r="12" fill="#1a1a1a"/>
      
      <circle cx="25" cy="40" r="18" fill="#2a2a2a" stroke="#404040" stroke-width="2"/>
      <circle cx="25" cy="40" r="10" fill="#1a1a1a"/>
    </g>
    
    <!-- Power button -->
    <rect x="120" y="-80" width="6" height="50" rx="3" fill="#c0c0c0"/>
    
    <!-- Volume buttons -->
    <rect x="120" y="-150" width="6" height="35" rx="3" fill="#c0c0c0"/>
    <rect x="120" y="-110" width="6" height="35" rx="3" fill="#c0c0c0"/>
  </g>
  
  <!-- Brand name -->
  <text x="400" y="680" font-family="Arial, sans-serif" font-size="48" font-weight="bold" fill="#ffffff" text-anchor="middle" opacity="0.95">
    {$config['brand']}
  </text>
  
  <!-- Model name -->
  <text x="400" y="730" font-family="Arial, sans-serif" font-size="36" font-weight="300" fill="#ffffff" text-anchor="middle" opacity="0.9">
    {$shortName}
  </text>
</svg>
SVG;
    
    $fileName = preg_replace('/[^a-z0-9-_]/i', '-', strtolower($productName)) . '.svg';
    $localPath = $imageDir . '/' . $fileName;
    $publicUrl = '/images/products/' . $fileName;
    
    file_put_contents($localPath, $svg);
    
    // Update database
    Product::withoutSyncingToSearch(function() use ($product, $publicUrl) {
        $product->update(['image_url' => $publicUrl]);
    });
    
    $size = round(strlen($svg) / 1024, 2);
    echo "  ✓ Created professional SVG ({$size} KB) → {$publicUrl}\n\n";
}

echo "✅ Done! Created professional gradient images for all 6 products.\n";
