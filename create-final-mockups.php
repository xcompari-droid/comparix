<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

echo "🎨 Creating professional mockups for remaining products...\n\n";

$imageDir = __DIR__ . '/public/images/products';

$products = [
    'iPhone 15 Plus' => [
        'color1' => '#2C2C2E',
        'color2' => '#48484A',
        'brand_color' => '#147CE5',
        'model_text' => '15 Plus',
    ],
    'Samsung Galaxy S24 Ultra' => [
        'color1' => '#1A1A1A',
        'color2' => '#2D2D2D',
        'brand_color' => '#1428A0',
        'model_text' => 'S24 Ultra',
    ],
    'OPPO Reno 12 Pro 5G' => [
        'color1' => '#00B9AE',
        'color2' => '#00D9C8',
        'brand_color' => '#00B050',
        'model_text' => 'Reno 12 Pro',
    ],
    'OPPO Reno 12 5G' => [
        'color1' => '#0AA89E',
        'color2' => '#00C4B8',
        'brand_color' => '#00A388',
        'model_text' => 'Reno 12',
    ],
];

foreach ($products as $productName => $colors) {
    echo "Creating mockup for: {$productName}\n";
    
    $brand = explode(' ', $productName)[0];
    $modelText = $colors['model_text'];
    
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" width="1000" height="1000">
  <defs>
    <linearGradient id="bg-{$brand}" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:{$colors['color1']};stop-opacity:1" />
      <stop offset="100%" style="stop-color:{$colors['color2']};stop-opacity:1" />
    </linearGradient>
    <linearGradient id="phone-{$brand}" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" style="stop-color:#E8E8E8;stop-opacity:1" />
      <stop offset="100%" style="stop-color:#CACACA;stop-opacity:1" />
    </linearGradient>
    <radialGradient id="screen-{$brand}" cx="50%" cy="50%" r="50%">
      <stop offset="0%" style="stop-color:#1C1C1E;stop-opacity:1" />
      <stop offset="100%" style="stop-color:#000000;stop-opacity:1" />
    </radialGradient>
    <filter id="shadow">
      <feGaussianBlur in="SourceAlpha" stdDeviation="15"/>
      <feOffset dx="0" dy="20" result="offsetblur"/>
      <feComponentTransfer>
        <feFuncA type="linear" slope="0.3"/>
      </feComponentTransfer>
      <feMerge>
        <feMergeNode/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>
  
  <!-- Background -->
  <rect width="1000" height="1000" fill="url(#bg-{$brand})"/>
  
  <!-- Phone with shadow -->
  <g filter="url(#shadow)">
    <!-- Phone body -->
    <rect x="360" y="220" width="280" height="560" rx="35" fill="url(#phone-{$brand})" stroke="#999" stroke-width="2"/>
    
    <!-- Screen -->
    <rect x="375" y="245" width="250" height="510" rx="25" fill="url(#screen-{$brand})"/>
    
    <!-- Screen reflection -->
    <ellipse cx="500" cy="350" rx="80" ry="120" fill="white" opacity="0.05"/>
    
    <!-- Dynamic Island / Notch -->
SVG;

    if ($brand === 'Apple') {
        $svg .= <<<SVG

    <rect x="460" y="260" width="80" height="25" rx="12" fill="black"/>
SVG;
    } elseif ($brand === 'Samsung') {
        $svg .= <<<SVG

    <circle cx="500" cy="265" r="6" fill="black"/>
SVG;
    }

    $svg .= <<<SVG

    
    <!-- Camera module -->
    <rect x="380" y="240" width="55" height="80" rx="20" fill="#2C2C2E" opacity="0.8"/>
    
    <!-- Camera lenses -->
    <circle cx="407" cy="265" r="12" fill="#1A1A1A" stroke="#444" stroke-width="2"/>
    <circle cx="407" cy="265" r="7" fill="#0A0A0A"/>
    <circle cx="407" cy="265" r="3" fill="#147CE5" opacity="0.3"/>
    
    <circle cx="407" cy="295" r="12" fill="#1A1A1A" stroke="#444" stroke-width="2"/>
    <circle cx="407" cy="295" r="7" fill="#0A0A0A"/>
    
    <circle cx="420" cy="280" r="8" fill="#1A1A1A" stroke="#444" stroke-width="1.5"/>
    <circle cx="420" cy="280" r="5" fill="#0A0A0A"/>
    
    <!-- Flash -->
    <circle cx="392" cy="280" r="5" fill="#FFE4B5" opacity="0.6"/>
    
    <!-- Power button -->
    <rect x="642" y="350" width="4" height="60" rx="2" fill="#999"/>
    
    <!-- Volume buttons -->
    <rect x="358" y="320" width="4" height="35" rx="2" fill="#999"/>
    <rect x="358" y="365" width="4" height="35" rx="2" fill="#999"/>
  </g>
  
  <!-- Brand name -->
  <text x="500" y="150" font-family="Arial, sans-serif" font-size="56" font-weight="bold" 
        fill="{$colors['brand_color']}" text-anchor="middle">{$brand}</text>
  
  <!-- Model name -->
  <text x="500" y="200" font-family="Arial, sans-serif" font-size="38" font-weight="300" 
        fill="white" text-anchor="middle" opacity="0.9">{$modelText}</text>
  
  <!-- 5G Badge -->
  <circle cx="880" cy="120" r="45" fill="white" opacity="0.15"/>
  <text x="880" y="130" font-family="Arial, sans-serif" font-size="28" font-weight="bold" 
        fill="white" text-anchor="middle">5G</text>
</svg>
SVG;

    $fileName = preg_replace('/[^a-z0-9-_]/i', '-', strtolower($productName)) . '.svg';
    $localPath = $imageDir . '/' . $fileName;
    $publicUrl = '/images/products/' . $fileName;
    
    file_put_contents($localPath, $svg);
    
    $product = Product::where('name', $productName)->first();
    if ($product) {
        Product::withoutSyncingToSearch(function() use ($product, $publicUrl) {
            $product->update(['image_url' => $publicUrl]);
        });
        echo "  ✓ Created mockup → {$publicUrl}\n";
    } else {
        echo "  ⚠ Product not found in database\n";
    }
    
    echo "\n";
}

echo "════════════════════════════════════════════\n";
echo "✅ Professional mockups created!\n";
echo "════════════════════════════════════════════\n\n";

echo "📊 Final status:\n\n";
passthru('php check-image-status.php');
