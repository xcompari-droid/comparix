<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\ProductType;

echo "=== ANALIZA COMPLETA PRODUSE ===\n\n";

$types = ProductType::whereIn('id', [3, 5, 6, 7])->get();

foreach ($types as $type) {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "CATEGORIE: {$type->name} (ID: {$type->id})\n";
    echo str_repeat("=", 60) . "\n\n";
    
    $products = Product::where('product_type_id', $type->id)->get();
    
    echo "Total produse: {$products->count()}\n\n";
    
    // Check images
    $realImages = 0;
    $placeholders = 0;
    $noImages = 0;
    
    foreach ($products as $product) {
        if (!$product->image_url) {
            $noImages++;
        } elseif (str_contains($product->image_url, 'ui-avatars.com') || 
                  str_contains($product->image_url, 'placeholder') ||
                  str_contains($product->image_url, 'avatar')) {
            $placeholders++;
        } else {
            $realImages++;
        }
    }
    
    echo "IMAGINI:\n";
    echo "  - Imagini reale: {$realImages}\n";
    echo "  - Placeholders: {$placeholders}\n";
    echo "  - Fără imagine: {$noImages}\n\n";
    
    // Check specifications
    $totalSpecs = 0;
    $minSpecs = PHP_INT_MAX;
    $maxSpecs = 0;
    $productsWithoutSpecs = 0;
    
    foreach ($products as $product) {
        $specCount = $product->specValues->count();
        $totalSpecs += $specCount;
        
        if ($specCount === 0) {
            $productsWithoutSpecs++;
        }
        
        if ($specCount < $minSpecs) {
            $minSpecs = $specCount;
        }
        if ($specCount > $maxSpecs) {
            $maxSpecs = $specCount;
        }
    }
    
    $avgSpecs = $products->count() > 0 ? round($totalSpecs / $products->count(), 1) : 0;
    
    echo "SPECIFICAȚII:\n";
    echo "  - Medie specificații/produs: {$avgSpecs}\n";
    echo "  - Minim specificații: {$minSpecs}\n";
    echo "  - Maxim specificații: {$maxSpecs}\n";
    echo "  - Produse fără specificații: {$productsWithoutSpecs}\n\n";
    
    // Sample products
    echo "EXEMPLE PRODUSE:\n";
    $samples = $products->take(3);
    foreach ($samples as $product) {
        $imageType = 'NONE';
        if ($product->image_url) {
            if (str_contains($product->image_url, 'ui-avatars.com')) {
                $imageType = 'PLACEHOLDER';
            } elseif (str_contains($product->image_url, 'http')) {
                $imageType = 'REAL URL';
            }
        }
        
        echo "  • {$product->name}\n";
        echo "    Imagine: {$imageType}\n";
        echo "    Specificații: {$product->specValues->count()}\n";
        if ($product->image_url && $imageType === 'REAL URL') {
            echo "    URL: " . substr($product->image_url, 0, 80) . "...\n";
        }
        echo "\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "REZUMAT PROBLEME\n";
echo str_repeat("=", 60) . "\n\n";

// Check earbuds
$earbuds = Product::where('product_type_id', 5)->get();
$earbudsPlaceholder = $earbuds->filter(function($p) {
    return $p->image_url && str_contains($p->image_url, 'ui-avatars.com');
})->count();

echo "❌ Căști wireless: {$earbudsPlaceholder}/{$earbuds->count()} au placeholder\n";

// Check fridges
$fridges = Product::where('product_type_id', 6)->get();
$fridgesReal = $fridges->filter(function($p) {
    return $p->image_url && str_contains($p->image_url, 'lcdn.altex.ro');
})->count();

echo "⚠️  Frigidere: {$fridgesReal}/{$fridges->count()} au URLs Altex (necesită verificare afișare)\n";

// Check washing machines
$washing = Product::where('product_type_id', 7)->count();
echo "❌ Mașini de spălat: 0 produse importate\n";

echo "\n";
