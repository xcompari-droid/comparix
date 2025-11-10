<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

// Creează directorul pentru imagini
$imageDir = public_path('images/products/washing-machines');
if (!file_exists($imageDir)) {
    mkdir($imageDir, 0755, true);
    echo "✅ Creat director: $imageDir\n";
}

// Imagini profesionale locale (placeholder generic)
$brandImages = [
    'Samsung' => 'https://images.unsplash.com/photo-1626806787461-102c1bfaaea1?w=800',
    'LG' => 'https://images.unsplash.com/photo-1604335399105-a0c585fd81a1?w=800',
    'Bosch' => 'https://images.unsplash.com/photo-1582735689369-4fe89db7114c?w=800',
    'Whirlpool' => 'https://images.unsplash.com/photo-1626806787461-102c1bfaaea1?w=800',
    'Beko' => 'https://images.unsplash.com/photo-1604335399105-a0c585fd81a1?w=800',
    'Arctic' => 'https://images.unsplash.com/photo-1582735689369-4fe89db7114c?w=800',
    'Candy' => 'https://images.unsplash.com/photo-1626806787461-102c1bfaaea1?w=800',
    'Electrolux' => 'https://images.unsplash.com/photo-1604335399105-a0c585fd81a1?w=800',
    'Gorenje' => 'https://images.unsplash.com/photo-1582735689369-4fe89db7114c?w=800',
    'Indesit' => 'https://images.unsplash.com/photo-1626806787461-102c1bfaaea1?w=800'
];

$products = DB::table('products')->where('product_type_id', 7)->get();

echo "🔽 Descărcare imagini pentru " . $products->count() . " produse...\n\n";

$downloaded = 0;

foreach($products as $product) {
    $brand = $product->brand ?? 'Unknown';
    
    if (isset($brandImages[$brand])) {
        $imageUrl = $brandImages[$brand];
        $filename = 'washing-' . $product->id . '.jpg';
        $filepath = $imageDir . '/' . $filename;
        
        // Descarcă imaginea
        $imageData = @file_get_contents($imageUrl);
        
        if ($imageData !== false) {
            file_put_contents($filepath, $imageData);
            
            // Actualizează URL-ul în baza de date
            $newUrl = '/images/products/washing-machines/' . $filename;
            DB::table('products')
                ->where('id', $product->id)
                ->update(['image_url' => $newUrl]);
            
            echo "✅ {$brand} - {$product->name}\n";
            echo "   Local: {$newUrl}\n";
            
            $downloaded++;
        } else {
            echo "❌ Eroare descărcare: {$brand}\n";
        }
    }
}

echo "\n✅ Descărcate: {$downloaded} imagini\n";
echo "📁 Locație: public/images/products/washing-machines/\n";
