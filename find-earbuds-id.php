<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProductType;
use App\Models\Product;

echo "\n=== TOATE PRODUCT TYPES ===\n\n";

$types = ProductType::orderBy('id')->get();

foreach ($types as $type) {
    $count = Product::where('product_type_id', $type->id)->count();
    echo "ID {$type->id}: {$type->name} (slug: {$type->slug}) - {$count} products\n";
}

echo "\n=== CĂȘTI WIRELESS ===\n\n";
$earbuds = ProductType::where('slug', 'casti-wireless')->first();
if ($earbuds) {
    echo "✅ Found!\n";
    echo "ID: {$earbuds->id}\n";
    echo "Name: {$earbuds->name}\n";
    echo "Slug: {$earbuds->slug}\n";
    echo "Products: " . Product::where('product_type_id', $earbuds->id)->count() . "\n";
    
    echo "\nSample products:\n";
    $samples = Product::where('product_type_id', $earbuds->id)->limit(3)->get();
    foreach ($samples as $product) {
        echo "- {$product->brand} {$product->name}\n";
    }
} else {
    echo "❌ Căști wireless product type not found!\n";
}
