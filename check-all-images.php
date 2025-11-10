<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$categories = DB::table('product_types')
    ->leftJoin('products', 'product_types.id', '=', 'products.product_type_id')
    ->select('product_types.id', 'product_types.name', DB::raw('COUNT(products.id) as product_count'))
    ->groupBy('product_types.id', 'product_types.name')
    ->orderBy('product_count', 'desc')
    ->get();

echo "📊 RAPORT CATEGORII ȘI IMAGINI\n";
echo "================================\n\n";

foreach($categories as $category) {
    if ($category->product_count > 0) {
        echo "📁 {$category->name} ({$category->product_count} produse)\n";
        
        // Sample produs pentru a vedea tipul de URL
        $sampleProduct = DB::table('products')
            ->where('product_type_id', $category->id)
            ->first(['name', 'image_url']);
        
        if ($sampleProduct) {
            echo "   Exemplu: {$sampleProduct->name}\n";
            $url = $sampleProduct->image_url ?? 'NULL';
            $urlShort = strlen($url) > 80 ? substr($url, 0, 80) . '...' : $url;
            echo "   URL: {$urlShort}\n";
            
            // Detectează tipul de URL
            if (str_contains($url, 'placehold.co')) {
                echo "   ✅ Tip: Placeholder (funcționează)\n";
            } elseif (str_contains($url, 'dummyimage.com')) {
                echo "   ✅ Tip: Dummy Image (funcționează)\n";
            } elseif (str_contains($url, 'picsum.photos')) {
                echo "   ⚠️  Tip: Picsum (random)\n";
            } elseif (str_contains($url, 'altex.ro')) {
                echo "   ❌ Tip: Altex CDN (CORS blocked)\n";
            } elseif (str_contains($url, 'versus.com')) {
                echo "   ⚠️  Tip: Versus (posibil CORS)\n";
            } elseif (str_contains($url, '/images/')) {
                echo "   ✅ Tip: Local storage (ideal)\n";
            } else {
                echo "   ❓ Tip: Altul\n";
            }
        }
        echo "\n";
    }
}

echo "💡 RECOMANDARE:\n";
echo "Pentru producție, imaginile trebuie să fie:\n";
echo "1. ✅ Locale în public/images/\n";
echo "2. ✅ Placeholder-uri branded (placehold.co sau dummyimage.com)\n";
echo "3. ❌ NU de pe CDN-uri externe cu restricții CORS\n";
