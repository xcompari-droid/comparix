<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;

echo "OPPO Products count: " . Product::where('brand', 'OPPO')->count() . PHP_EOL;
echo "Total Products count: " . Product::count() . PHP_EOL;
echo PHP_EOL;

$products = Product::where('brand', 'OPPO')
    ->with('specValues.specKey', 'offers')
    ->get();

foreach ($products as $product) {
    echo "Product: {$product->name}" . PHP_EOL;
    echo "Brand: {$product->brand}" . PHP_EOL;
    echo "Specs: {$product->specValues->count()}" . PHP_EOL;
    echo "Offers: {$product->offers->count()}" . PHP_EOL;
    
    if ($product->offers->count() > 0) {
        $offer = $product->offers->first();
        echo "Price: {$offer->price} {$offer->currency}" . PHP_EOL;
    }
    
    echo PHP_EOL . "Specifications:" . PHP_EOL;
    foreach ($product->specValues->take(8) as $spec) {
        echo "  - {$spec->specKey->name}: {$spec->value}" . PHP_EOL;
    }
    
    echo PHP_EOL . str_repeat('-', 60) . PHP_EOL . PHP_EOL;
}
