<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST TOATE SPEC KEYS ===\n\n";

$products = App\Models\Product::whereIn('id', [3, 6])
    ->with(['specValues.specKey'])
    ->get();

echo "Product 1 ({$products[0]->name}): {$products[0]->specValues->count()} specs\n";
echo "Product 2 ({$products[1]->name}): {$products[1]->specValues->count()} specs\n\n";

// Logica din view
$allSpecKeys = collect();
foreach($products as $product) {
    $allSpecKeys = $allSpecKeys->merge($product->specValues->pluck('specKey'));
}
$allSpecKeys = $allSpecKeys->unique('id');

echo "Total unique spec keys: {$allSpecKeys->count()}\n\n";

echo "=== SPEC KEYS GĂSITE ===\n";
foreach($allSpecKeys as $specKey) {
    echo "- {$specKey->name} (ID: {$specKey->id})\n";
}
