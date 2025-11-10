<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;

$product = Product::find(98);
if ($product) {
    echo "Șterg produsul ID 98: {$product->brand} {$product->name}\n";
    
    // Dezactivează sync Scout
    Product::withoutSyncingToSearch(function () use ($product) {
        $product->specValues()->delete();
        $product->offers()->delete();
        $product->delete();
    });
    
    echo "✓ Șters\n";
} else {
    echo "Produsul nu există\n";
}
