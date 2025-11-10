<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🖼️  FIX IMAGINI FRIGIDERE\n";
echo "=========================\n\n";

$brandImages = [
    'Samsung' => 'https://dummyimage.com/600x400/1428a0/ffffff&text=Samsung+Frigider',
    'LG' => 'https://dummyimage.com/600x400/a50034/ffffff&text=LG+Frigider',
    'Bosch' => 'https://dummyimage.com/600x400/ea0016/ffffff&text=Bosch+Serie+4',
    'Whirlpool' => 'https://dummyimage.com/600x400/da291c/ffffff&text=Whirlpool+Frigider',
    'Beko' => 'https://dummyimage.com/600x400/0066b3/ffffff&text=Beko+Frigider',
    'Arctic' => 'https://dummyimage.com/600x400/0072ce/ffffff&text=Arctic+Frigider',
    'Candy' => 'https://dummyimage.com/600x400/e31937/ffffff&text=Candy+Frigider',
    'Electrolux' => 'https://dummyimage.com/600x400/2d2926/ffffff&text=Electrolux+Frigider',
    'Gorenje' => 'https://dummyimage.com/600x400/e30613/ffffff&text=Gorenje+Frigider',
    'Indesit' => 'https://dummyimage.com/600x400/ffb600/000000&text=Indesit+Frigider',
];

$fridges = DB::table('products')
    ->join('product_types', 'products.product_type_id', '=', 'product_types.id')
    ->where('product_types.slug', 'frigider')
    ->select('products.id', 'products.name', 'products.brand', 'products.image_url')
    ->get();

echo "Găsite " . count($fridges) . " frigidere\n\n";

$updated = 0;

foreach ($fridges as $fridge) {
    $brand = $fridge->brand;
    
    if (!isset($brandImages[$brand])) {
        echo "⚠️  {$fridge->name} - Brand necunoscut: {$brand}\n";
        continue;
    }
    
    $newImageUrl = $brandImages[$brand];
    
    DB::table('products')
        ->where('id', $fridge->id)
        ->update(['image_url' => $newImageUrl]);
    
    echo "✅ {$brand} - {$fridge->name}\n";
    echo "   URL: {$newImageUrl}\n\n";
    
    $updated++;
}

echo "✅ Actualizate: {$updated} frigidere\n";
echo "🎉 Placeholder-uri branded cu text și culori specifice!\n";
echo "🔍 Testează: http://localhost:8080/categorii/frigider\n";
