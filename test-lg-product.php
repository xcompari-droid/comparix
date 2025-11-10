<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;

echo "=== TEST LG F4WV710P2E ===\n\n";

$product = Product::where('name', 'LIKE', '%LG F4WV710P2E%')
    ->orWhere('mpn', 'F4WV710P2E')
    ->with(['specValues.specKey', 'offers'])
    ->first();

if (!$product) {
    echo "❌ Produsul LG F4WV710P2E NU a fost găsit!\n";
    
    // Caută orice LG
    $anyLG = Product::where('brand', 'LG')
        ->where('name', 'LIKE', '%F4WV710P2E%')
        ->first();
    
    if ($anyLG) {
        echo "\nGăsit un LG similar: {$anyLG->name} (ID: {$anyLG->id})\n";
        $product = Product::with(['specValues.specKey', 'offers'])->find($anyLG->id);
    } else {
        echo "\nCaut orice mașină LG...\n";
        $product = Product::where('brand', 'LG')
            ->whereHas('productType', function($q) {
                $q->where('name', 'LIKE', '%mașin%');
            })
            ->with(['specValues.specKey', 'offers'])
            ->first();
        
        if ($product) {
            echo "Găsit: {$product->name} (ID: {$product->id})\n";
        } else {
            die("❌ Nicio mașină LG în baza de date!\n");
        }
    }
}

echo "\n📦 PRODUS: {$product->name}\n";
echo "ID: {$product->id}\n";
echo "Brand: {$product->brand}\n";
echo "MPN: {$product->mpn}\n\n";

echo "🖼️ IMAGINE:\n";
echo "URL: " . ($product->image_url ?: 'NULL') . "\n";
echo "Lungime URL: " . strlen($product->image_url ?: '') . " caractere\n\n";

echo "📋 SPECIFICAȚII ({$product->specValues->count()}):\n";

if ($product->specValues->isEmpty()) {
    echo "❌ NU ARE SPECIFICAȚII!\n";
} else {
    foreach ($product->specValues as $spec) {
        $key = $spec->specKey ? $spec->specKey->name : 'UNKNOWN KEY';
        $value = $spec->value_string ?? $spec->value_number ?? ($spec->value_bool ? 'Da' : 'Nu');
        echo "  • {$key}: {$value}\n";
    }
}

echo "\n💰 OFERTE ({$product->offers->count()}):\n";
if ($product->offers->isEmpty()) {
    echo "❌ NU ARE OFERTE!\n";
} else {
    foreach ($product->offers as $offer) {
        echo "  • {$offer->merchant}: {$offer->price} RON\n";
    }
}

echo "\n📊 ATRIBUTE RAW:\n";
$attrs = $product->getAttributes();
foreach (['image_url', 'short_desc', 'long_desc', 'source_url', 'score'] as $field) {
    $value = $attrs[$field] ?? 'NULL';
    if (strlen($value) > 100) {
        $value = substr($value, 0, 100) . '... (' . strlen($value) . ' chars)';
    }
    echo "  {$field}: {$value}\n";
}
