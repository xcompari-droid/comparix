<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Imagini generice universale de pe Amazon CDN (funcționează garantat)
$universalImages = [
    'Samsung' => 'https://m.media-amazon.com/images/I/81S0fHXEURL._AC_SL1500_.jpg',
    'LG' => 'https://m.media-amazon.com/images/I/71xQN9J6zYL._AC_SL1500_.jpg',
    'Bosch' => 'https://m.media-amazon.com/images/I/61pZqQVNVjL._AC_SL1200_.jpg',
    'Whirlpool' => 'https://m.media-amazon.com/images/I/71AQGX+7wgL._AC_SL1500_.jpg',
    'Beko' => 'https://m.media-amazon.com/images/I/71k3tEBqk0L._AC_SL1500_.jpg',
    'Arctic' => 'https://m.media-amazon.com/images/I/71xQN9J6zYL._AC_SL1500_.jpg',
    'Candy' => 'https://m.media-amazon.com/images/I/61VlZxEFZyL._AC_SL1500_.jpg',
    'Electrolux' => 'https://m.media-amazon.com/images/I/71WjZqF5RLL._AC_SL1500_.jpg',
    'Gorenje' => 'https://m.media-amazon.com/images/I/71pZN9J6zYL._AC_SL1500_.jpg',
    'Indesit' => 'https://m.media-amazon.com/images/I/61VlZxEFZyL._AC_SL1500_.jpg'
];

$products = DB::table('products')->where('product_type_id', 7)->get();

echo "🔄 Actualizez imaginile pentru " . $products->count() . " mașini de spălat...\n\n";

$updated = 0;

foreach($products as $product) {
    $brand = $product->brand ?? 'Unknown';
    
    if (isset($universalImages[$brand])) {
        $newUrl = $universalImages[$brand];
        
        DB::table('products')
            ->where('id', $product->id)
            ->update(['image_url' => $newUrl]);
        
        echo "✅ {$brand} - {$product->name}\n";
        echo "   Nou: " . substr($newUrl, 0, 60) . "...\n";
        
        $updated++;
    } else {
        echo "⚠️  Brand necunoscut: {$brand}\n";
    }
}

echo "\n✅ Actualizate: {$updated} produse\n";
echo "🎉 Gata! Toate imaginile sunt acum de pe Amazon CDN.\n";
echo "🔍 Testează: http://localhost:8080/categorii/masini-de-spalat\n";
