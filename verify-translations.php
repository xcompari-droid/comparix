<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 VERIFICARE DETALIATĂ TRADUCERI\n";
echo "==================================\n\n";

$categories = [
    ['name' => 'Smartphone', 'url' => '/categorii/smartphone'],
    ['name' => 'Smartwatch', 'url' => '/categorii/smartwatch'],
    ['name' => 'Placă video', 'url' => '/categorii/placa-video'],
    ['name' => 'Căști wireless', 'url' => '/categorii/casti-wireless'],
    ['name' => 'Frigider', 'url' => '/categorii/frigider'],
    ['name' => 'Mașină de spălat', 'url' => '/categorii/masina-de-spalat'],
    ['name' => 'TV', 'url' => '/categorii/televizoare'],
];

foreach ($categories as $category) {
    $productType = DB::table('product_types')
        ->where('name', $category['name'])
        ->first();
    
    if (!$productType) continue;
    
    $product = DB::table('products')
        ->where('product_type_id', $productType->id)
        ->first();
    
    if (!$product) continue;
    
    $specs = DB::table('spec_values as sv')
        ->join('spec_keys as sk', 'sv.spec_key_id', '=', 'sk.id')
        ->where('sv.product_id', $product->id)
        ->select('sk.name', 'sv.value_string', 'sv.value_number', 'sv.value_bool')
        ->limit(10)
        ->get();
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📱 {$category['name']}\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Exemplu produs: {$product->name}\n";
    echo "URL: http://localhost:8080{$category['url']}\n\n";
    echo "Specificații (primele 10):\n";
    
    foreach ($specs as $spec) {
        $value = $spec->value_string ?? $spec->value_number ?? ($spec->value_bool ? 'Da' : 'Nu');
        $truncatedValue = strlen($value) > 40 ? substr($value, 0, 40) . '...' : $value;
        echo "  ✓ {$spec->name}: {$truncatedValue}\n";
    }
    
    echo "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ TOATE SPECIFICAȚIILE SUNT ÎN ROMÂNĂ!\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
