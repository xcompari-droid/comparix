<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\ProductType;
use App\Models\SpecValue;

echo "=== ȘTERGERE MAȘINI DE SPĂLAT ===\n\n";

$type = ProductType::where('slug', 'masina-de-spalat')->first();

if (!$type) {
    die("❌ Nu am găsit category masini-de-spalat\n");
}

$products = Product::where('product_type_id', $type->id)->get();

echo "Găsite: {$products->count()} mașini de spălat\n\n";

foreach ($products as $product) {
    echo "Șterg: {$product->name}...";
    
    // Șterge specs
    SpecValue::where('product_id', $product->id)->delete();
    
    // Șterge offers
    \App\Models\Offer::where('product_id', $product->id)->delete();
    
    // Șterge produsul
    $product->delete();
    
    echo " ✅\n";
}

echo "\n✅ GATA! Șters tot.\n";
