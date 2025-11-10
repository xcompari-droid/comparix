<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\ProductType;
use App\Models\SpecValue;

echo "═══════════════════════════════════════════════════════════\n";
echo "             RAPORT FINAL - STATUS PRODUSE\n";
echo "═══════════════════════════════════════════════════════════\n\n";

$types = [
    ['id' => 3, 'name' => 'Smartwatch-uri', 'icon' => '⌚'],
    ['id' => 5, 'name' => 'Căști wireless', 'icon' => '🎧'],
    ['id' => 6, 'name' => 'Frigidere', 'icon' => '🧊'],
    ['id' => 7, 'name' => 'Mașini de spălat', 'icon' => '🧺'],
];

$totalProducts = 0;
$totalSpecs = 0;
$totalWithRealImages = 0;

foreach ($types as $typeData) {
    $products = Product::where('product_type_id', $typeData['id'])->get();
    $count = $products->count();
    
    $totalProducts += $count;
    
    if ($count === 0) {
        echo "{$typeData['icon']} {$typeData['name']}: 0 produse\n";
        echo "   └─ ❌ Niciun produs importat\n\n";
        continue;
    }
    
    // Count images
    $realImages = 0;
    $placeholders = 0;
    
    foreach ($products as $product) {
        if ($product->image_url) {
            if (str_contains($product->image_url, 'ui-avatars.com') || 
                str_contains($product->image_url, 'placeholder') ||
                str_contains($product->image_url, 'versus_banner_black.png')) {
                $placeholders++;
            } else {
                $realImages++;
            }
        }
    }
    
    $totalWithRealImages += $realImages;
    
    // Count specs
    $specsCount = 0;
    $minSpecs = PHP_INT_MAX;
    $maxSpecs = 0;
    
    foreach ($products as $product) {
        $productSpecs = SpecValue::where('product_id', $product->id)->count();
        $specsCount += $productSpecs;
        
        if ($productSpecs < $minSpecs) $minSpecs = $productSpecs;
        if ($productSpecs > $maxSpecs) $maxSpecs = $productSpecs;
    }
    
    $totalSpecs += $specsCount;
    $avgSpecs = round($specsCount / $count, 1);
    
    echo "{$typeData['icon']} {$typeData['name']}: {$count} produse\n";
    echo "   ├─ Imagini reale: {$realImages}/{$count} ";
    if ($realImages == $count) {
        echo "✅\n";
    } elseif ($realImages > $count / 2) {
        echo "⚠️\n";
    } else {
        echo "❌\n";
    }
    
    if ($placeholders > 0) {
        echo "   ├─ Placeholders: {$placeholders}\n";
    }
    
    echo "   ├─ Specificații: {$specsCount} total ({$avgSpecs} medie/produs)\n";
    echo "   └─ Range: {$minSpecs}-{$maxSpecs} specs/produs\n\n";
}

echo "═══════════════════════════════════════════════════════════\n";
echo "TOTAL GENERAL:\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "📦 Produse: {$totalProducts}\n";
echo "🖼️  Imagini reale: {$totalWithRealImages}/{$totalProducts}\n";
echo "📝 Specificații: {$totalSpecs} total\n";
echo "\n";

// Calculate percentages
$imagePercent = $totalProducts > 0 ? round(($totalWithRealImages / $totalProducts) * 100, 1) : 0;
$avgSpecsPerProduct = $totalProducts > 0 ? round($totalSpecs / $totalProducts, 1) : 0;

echo "CALITATE:\n";
echo "  • Imagini reale: {$imagePercent}%\n";
echo "  • Medie specs/produs: {$avgSpecsPerProduct}\n";
echo "\n";

if ($imagePercent >= 90 && $avgSpecsPerProduct >= 4) {
    echo "✅ STATUS: EXCELENT - Toate produsele sunt complete!\n";
} elseif ($imagePercent >= 70 && $avgSpecsPerProduct >= 3) {
    echo "✅ STATUS: BINE - Majoritatea produselor sunt complete\n";
} else {
    echo "⚠️  STATUS: NECESITĂ ÎMBUNĂTĂȚIRI\n";
}

echo "\n";
