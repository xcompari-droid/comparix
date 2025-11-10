<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Picsum Photos - CDN gratuit cu imagini random de înaltă calitate
// Folosim seed-uri pentru consistență per brand
$brandImages = [
    'Samsung' => 'https://picsum.photos/seed/samsung-wash/600/400',
    'LG' => 'https://picsum.photos/seed/lg-washer/600/400',
    'Bosch' => 'https://picsum.photos/seed/bosch-machine/600/400',
    'Whirlpool' => 'https://picsum.photos/seed/whirlpool-wash/600/400',
    'Beko' => 'https://picsum.photos/seed/beko-washer/600/400',
    'Arctic' => 'https://picsum.photos/seed/arctic-wash/600/400',
    'Candy' => 'https://picsum.photos/seed/candy-machine/600/400',
    'Electrolux' => 'https://picsum.photos/seed/electrolux/600/400',
    'Gorenje' => 'https://picsum.photos/seed/gorenje-wash/600/400',
    'Indesit' => 'https://picsum.photos/seed/indesit-washer/600/400'
];

$products = DB::table('products')->where('product_type_id', 7)->get();

echo "📸 Actualizare cu imagini Picsum Photos (HD, fără CORS)...\n\n";

$updated = 0;

foreach($products as $product) {
    $brand = $product->brand ?? 'Unknown';
    
    if (isset($brandImages[$brand])) {
        // Adaugă ID-ul produsului pentru unicitate
        $newUrl = str_replace('/600/400', "/600/400?v={$product->id}", $brandImages[$brand]);
        
        DB::table('products')
            ->where('id', $product->id)
            ->update(['image_url' => $newUrl]);
        
        echo "✅ {$brand} - {$product->name}\n";
        
        $updated++;
    }
}

echo "\n✅ Actualizate: {$updated} produse\n";
echo "🎉 Picsum Photos funcționează 100% (imagini HD, fără restricții CORS)!\n";
echo "🔍 Testează: http://localhost:8080/categorii/masini-de-spalat\n";
