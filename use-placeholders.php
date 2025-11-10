<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Placeholder-uri profesionale cu text și culoare per brand
$brandPlaceholders = [
    'Samsung' => 'https://placehold.co/600x400/1428A0/ffffff/png?text=Samsung+Washing+Machine',
    'LG' => 'https://placehold.co/600x400/A50034/ffffff/png?text=LG+Washing+Machine',
    'Bosch' => 'https://placehold.co/600x400/EA0016/ffffff/png?text=Bosch+Serie+6',
    'Whirlpool' => 'https://placehold.co/600x400/DA291C/ffffff/png?text=Whirlpool',
    'Beko' => 'https://placehold.co/600x400/0066B3/ffffff/png?text=Beko',
    'Arctic' => 'https://placehold.co/600x400/0072CE/ffffff/png?text=Arctic',
    'Candy' => 'https://placehold.co/600x400/E31937/ffffff/png?text=Candy',
    'Electrolux' => 'https://placehold.co/600x400/2D2926/ffffff/png?text=Electrolux',
    'Gorenje' => 'https://placehold.co/600x400/E30613/ffffff/png?text=Gorenje',
    'Indesit' => 'https://placehold.co/600x400/FFB600/000000/png?text=Indesit'
];

$products = DB::table('products')->where('product_type_id', 7)->get();

echo "🎨 Actualizare cu placeholder-uri profesionale...\n\n";

$updated = 0;

foreach($products as $product) {
    $brand = $product->brand ?? 'Unknown';
    
    if (isset($brandPlaceholders[$brand])) {
        $newUrl = $brandPlaceholders[$brand];
        
        DB::table('products')
            ->where('id', $product->id)
            ->update(['image_url' => $newUrl]);
        
        echo "✅ {$brand} - {$product->name}\n";
        
        $updated++;
    }
}

echo "\n✅ Actualizate: {$updated} produse\n";
echo "🎉 Placeholder-urile funcționează INSTANT (fără CORS issues)!\n";
echo "🔍 Testează: http://localhost:8080/categorii/masini-de-spalat\n";
