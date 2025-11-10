<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Find the "Frigider" product type
$productTypeId = DB::table('product_types')
    ->where('name', 'Frigider')
    ->value('id');

if (!$productTypeId) {
    echo "Product type 'Frigider' not found!\n";
    exit(1);
}

echo "Found product type ID: {$productTypeId}\n";

// Get all fridge products
$products = DB::table('products')
    ->where('product_type_id', $productTypeId)
    ->get(['id', 'name']);

echo "Found " . count($products) . " fridges\n";

// Delete all related data
foreach ($products as $product) {
    echo "Deleting {$product->name}...\n";
    
    // Delete spec values
    DB::table('spec_values')->where('product_id', $product->id)->delete();
    
    // Delete offers
    DB::table('offers')->where('product_id', $product->id)->delete();
    
    // Delete the product itself
    DB::table('products')->where('id', $product->id)->delete();
}

echo "✓ All fridges deleted!\n";
