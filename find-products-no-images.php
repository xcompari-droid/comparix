<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "  PRODUSE FĂRĂ IMAGINI SAU CU IMAGINI PLACEHOLDER\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Găsim produse fără imagini sau cu placeholder
$productsNoImages = Product::where(function($query) {
    $query->whereNull('image_url')
          ->orWhere('image_url', '')
          ->orWhere('image_url', 'LIKE', '%placeholder%')
          ->orWhere('image_url', 'LIKE', '%picsum%')
          ->orWhere('image_url', 'LIKE', '%dummyimage%')
          ->orWhere('image_url', 'LIKE', '%via.placeholder%');
})->limit(100)->get();

echo "Găsite: " . $productsNoImages->count() . " produse fără imagini\n\n";

if ($productsNoImages->isEmpty()) {
    echo "✅ Toate produsele au imagini reale!\n\n";
    exit;
}

// Grupăm pe categorii
$byType = $productsNoImages->groupBy('product_type_id');

foreach ($byType as $typeId => $products) {
    $typeName = $products->first()->productType->name ?? "Type {$typeId}";
    echo "┌─────────────────────────────────────────────────────────────┐\n";
    echo "│ " . str_pad($typeName, 59) . " │\n";
    echo "└─────────────────────────────────────────────────────────────┘\n";
    
    foreach ($products as $product) {
        $imageStatus = $product->image_url ? "placeholder" : "NULL";
        echo "  [{$product->id}] {$product->brand} {$product->name}\n";
        echo "      Image: {$imageStatus}\n";
    }
    echo "\n";
}

echo "═══════════════════════════════════════════════════════════════\n\n";

// Salvăm IDs pentru următorul script
$ids = $productsNoImages->pluck('id')->toArray();
file_put_contents(__DIR__ . '/products-need-images.json', json_encode($ids, JSON_PRETTY_PRINT));

echo "✅ Salvat în products-need-images.json\n";
echo "   Următorul pas: Rulează download-product-only-images.php\n\n";
