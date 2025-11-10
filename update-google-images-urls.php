<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

echo "🔄 Actualizare URL-uri imagini Google → Local\n\n";

$updated = 0;
$notFound = 0;

// Găsesc toate produsele cu imagini Google
$products = Product::whereNotNull('image_url')
    ->where(function($q) {
        $q->where('image_url', 'like', 'http%')
          ->orWhere('image_url', 'like', '/images/%');
    })
    ->get();

echo "📦 Produse de verificat: " . $products->count() . "\n\n";

foreach ($products as $product) {
    // Verifică dacă există imagine Google locală (în storage shared)
    $basePath = "/home/forge/comparix.ro/storage/app/public/products/";
    
    // Caută ambele extensii
    $jpgFile = $basePath . "google-{$product->id}-*.jpg";
    $pngFile = $basePath . "google-{$product->id}-*.png";
    
    $jpgFiles = glob($jpgFile);
    $pngFiles = glob($pngFile);
    $files = array_merge($jpgFiles, $pngFiles);
    
    if (!empty($files)) {
        $filename = basename($files[0]);
        $newUrl = "/storage/products/{$filename}";
        
        if ($product->image_url !== $newUrl) {
            echo "✅ [{$product->id}] {$product->name}\n";
            echo "   OLD: {$product->image_url}\n";
            echo "   NEW: {$newUrl}\n\n";
            
            $product->image_url = $newUrl;
            $product->save();
            $updated++;
        }
    } else {
        $notFound++;
    }
}

echo "\n════════════════════════════════════════\n";
echo "✅ Actualizate: {$updated}\n";
echo "⚠️  Fără imagine locală: {$notFound}\n";
echo "════════════════════════════════════════\n";
