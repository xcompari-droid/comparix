<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;

// Selectează toate produsele cu imagine externă și fără media locală
$products = Product::whereNotNull('image_url')
    ->whereDoesntHave('media', function ($q) {
        $q->where('collection_name', 'gallery');
    })
    ->get();

echo "🔽 Descărcare imagini pentru " . $products->count() . " produse...\n\n";
$downloaded = 0;

foreach ($products as $product) {
    $url = $product->image_url;
    if (!$url) continue;
    // Forțează HTTPS
    $url = preg_replace('#^http://#', 'https://', $url);
    try {
        $product->addMediaFromUrl($url)->toMediaCollection('gallery');
        echo "✅ {$product->name}\n";
        $downloaded++;
    } catch (Exception $e) {
        echo "❌ {$product->name}: {$e->getMessage()}\n";
    }
}

echo "\n✅ Descărcate: {$downloaded} imagini locale\n";
echo "Imaginile sunt servite local prin Spatie Media Library.\n";
