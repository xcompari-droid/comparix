<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// DummyImage.com - placeholder-uri profesionale cu aspect de produs
// Folosim culori și text relevant pentru fiecare brand
$brandImages = [
    'Samsung' => 'https://dummyimage.com/600x400/1428a0/ffffff&text=Samsung+Washing+Machine',
    'LG' => 'https://dummyimage.com/600x400/a50034/ffffff&text=LG+Washing+Machine',
    'Bosch' => 'https://dummyimage.com/600x400/ea0016/ffffff&text=Bosch+Serie+6',
    'Whirlpool' => 'https://dummyimage.com/600x400/da291c/ffffff&text=Whirlpool+Washing+Machine',
    'Beko' => 'https://dummyimage.com/600x400/0066b3/ffffff&text=Beko+Washing+Machine',
    'Arctic' => 'https://dummyimage.com/600x400/0072ce/ffffff&text=Arctic+Washing+Machine',
    'Candy' => 'https://dummyimage.com/600x400/e31937/ffffff&text=Candy+Washing+Machine',
    'Electrolux' => 'https://dummyimage.com/600x400/2d2926/ffffff&text=Electrolux+Washing+Machine',
    'Gorenje' => 'https://dummyimage.com/600x400/e30613/ffffff&text=Gorenje+Washing+Machine',
    'Indesit' => 'https://dummyimage.com/600x400/ffb600/000000&text=Indesit+Washing+Machine'
];

$products = DB::table('products')->where('product_type_id', 7)->get();

echo "🎨 Actualizare cu imagini placeholder branded...\n\n";

$updated = 0;

foreach($products as $product) {
    $brand = $product->brand ?? 'Unknown';
    
    if (isset($brandImages[$brand])) {
        $newUrl = $brandImages[$brand];
        
        DB::table('products')
            ->where('id', $product->id)
            ->update(['image_url' => $newUrl]);
        
        echo "✅ {$brand} - {$product->name}\n";
        
        $updated++;
    }
}

echo "\n✅ Actualizate: {$updated} produse\n";
echo "🎉 Placeholder-uri branded cu text și culori specifice!\n";
echo "🔍 Testează: http://localhost:8080/categorii/masini-de-spalat\n";
