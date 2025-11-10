<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

echo "🎨 Creating professional city mockups...\n\n";

$imageDir = __DIR__ . '/public/images/products';

$cityDesigns = [
    'București' => [
        'icon' => '🏛️',
        'color1' => '#1E3A8A',
        'color2' => '#3B82F6',
        'landmark' => 'M400,300 L420,250 L440,300 L460,240 L480,300 L500,220 L520,300 L540,250 L560,300 L580,260 L600,300', // Parliament skyline
    ],
    'Cluj-Napoca' => [
        'icon' => '⛪',
        'color1' => '#7C2D12',
        'color2' => '#EA580C',
        'landmark' => 'M450,250 L480,200 L510,250 L500,250 L500,300 L460,300 L460,250', // Church spire
    ],
    'Timișoara' => [
        'icon' => '🌹',
        'color1' => '#BE123C',
        'color2' => '#F43F5E',
        'landmark' => 'M420,280 L440,240 L460,280 L480,220 L500,280 L520,230 L540,280 L560,250 L580,280', // City squares
    ],
    'Iași' => [
        'icon' => '🏰',
        'color1' => '#581C87',
        'color2' => '#A855F7',
        'landmark' => 'M430,230 L470,180 L510,230 L520,230 L520,300 L480,300 L480,260 L480,300 L420,300 L420,230', // Palace
    ],
    'Constanța' => [
        'icon' => '🌊',
        'color1' => '#0C4A6E',
        'color2' => '#0EA5E9',
        'landmark' => 'M400,250 Q500,200 600,250 Q500,300 400,250', // Casino waves
    ],
    'Craiova' => [
        'icon' => '🌳',
        'color1' => '#14532D',
        'color2' => '#22C55E',
        'landmark' => 'M450,280 Q470,220 490,280 M480,240 L480,300 M510,280 Q530,230 550,280 M530,250 L530,300', // Park trees
    ],
    'Brașov' => [
        'icon' => '⛰️',
        'color1' => '#44403C',
        'color2' => '#78716C',
        'landmark' => 'M400,280 L450,200 L500,280 L480,280 L480,300 L420,300 L420,280', // Mountain & Black Church
    ],
    'Galați' => [
        'icon' => '⚓',
        'color1' => '#1E40AF',
        'color2' => '#60A5FA',
        'landmark' => 'M480,250 L500,250 L500,280 L495,280 L495,290 L485,290 L485,280 L480,280 M490,240 A5,5 0 1,1 490,250', // Anchor
    ],
    'Ploiești' => [
        'icon' => '🛢️',
        'color1' => '#713F12',
        'color2' => '#F59E0B',
        'landmark' => 'M450,250 L450,300 L480,300 L480,250 M490,250 L490,300 L520,300 L520,250 M530,250 L530,300 L560,300 L560,250', // Oil derricks
    ],
    'Brăila' => [
        'icon' => '🏛️',
        'color1' => '#0F766E',
        'color2' => '#14B8A6',
        'landmark' => 'M430,260 L440,240 L450,260 L450,300 L430,300 M480,260 L490,240 L500,260 L500,300 L480,300 M530,260 L540,240 L550,260 L550,300 L530,300', // Public buildings
    ],
];

foreach ($cityDesigns as $cityName => $design) {
    echo "Creating mockup for: {$cityName}\n";
    
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" width="1000" height="1000">
  <defs>
    <linearGradient id="bg-{$cityName}" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:{$design['color1']};stop-opacity:1" />
      <stop offset="100%" style="stop-color:{$design['color2']};stop-opacity:1" />
    </linearGradient>
    <filter id="glow">
      <feGaussianBlur stdDeviation="4" result="coloredBlur"/>
      <feMerge>
        <feMergeNode in="coloredBlur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>
  
  <!-- Background -->
  <rect width="1000" height="1000" fill="url(#bg-{$cityName})"/>
  
  <!-- Decorative circles -->
  <circle cx="150" cy="150" r="100" fill="white" opacity="0.05"/>
  <circle cx="850" cy="850" r="120" fill="white" opacity="0.05"/>
  <circle cx="850" cy="200" r="80" fill="white" opacity="0.03"/>
  <circle cx="150" cy="850" r="90" fill="white" opacity="0.03"/>
  
  <!-- City icon/emblem (large centered) -->
  <circle cx="500" cy="400" r="180" fill="white" opacity="0.15" filter="url(#glow)"/>
  <circle cx="500" cy="400" r="150" fill="white" opacity="0.2"/>
  
  <!-- Landmark silhouette -->
  <g opacity="0.9" fill="white">
    <path d="{$design['landmark']}" stroke="white" stroke-width="3" fill="none"/>
  </g>
  
  <!-- City name -->
  <text x="500" y="680" font-family="Arial, sans-serif" font-size="72" font-weight="bold" 
        fill="white" text-anchor="middle" filter="url(#glow)">{$cityName}</text>
  
  <!-- Romania label -->
  <text x="500" y="740" font-family="Arial, sans-serif" font-size="32" font-weight="300" 
        fill="white" text-anchor="middle" opacity="0.8">România</text>
  
  <!-- Icon -->
  <text x="500" y="430" font-size="100" text-anchor="middle">{$design['icon']}</text>
  
  <!-- Decorative elements -->
  <circle cx="250" cy="800" r="4" fill="white" opacity="0.6"/>
  <circle cx="280" cy="820" r="3" fill="white" opacity="0.5"/>
  <circle cx="310" cy="790" r="5" fill="white" opacity="0.7"/>
  <circle cx="750" cy="800" r="4" fill="white" opacity="0.6"/>
  <circle cx="720" cy="820" r="3" fill="white" opacity="0.5"/>
  <circle cx="690" cy="790" r="5" fill="white" opacity="0.7"/>
</svg>
SVG;

    $fileName = 'city-' . preg_replace('/[^a-z0-9-_]/i', '-', strtolower($cityName)) . '.svg';
    $localPath = $imageDir . '/' . $fileName;
    $publicUrl = '/images/products/' . $fileName;
    
    file_put_contents($localPath, $svg);
    
    $city = Product::where('brand', 'România')->where('name', $cityName)->first();
    if ($city) {
        Product::withoutSyncingToSearch(function() use ($city, $publicUrl) {
            $city->update(['image_url' => $publicUrl]);
        });
        echo "  ✓ Created mockup → {$publicUrl}\n";
    } else {
        echo "  ⚠ City not found in database\n";
    }
    
    echo "\n";
}

echo "════════════════════════════════════════════\n";
echo "✅ City mockups created successfully!\n";
echo "════════════════════════════════════════════\n\n";

// Count products by category
echo "📊 Product Statistics:\n\n";
$categories = \App\Models\Category::withCount('products')->get();
foreach ($categories as $category) {
    echo "  {$category->name}: {$category->products_count} produse\n";
}

echo "\n";
echo "Total products: " . \App\Models\Product::count() . "\n";
