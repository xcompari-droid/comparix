<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Importers\AltexFridgeImporter;
use Illuminate\Support\Facades\DB;

echo "🔧 RE-IMPORT FRIGIDERE CU DEBUG\n";
echo "================================\n\n";

$importer = new AltexFridgeImporter();

// Get first fridge from hardcoded list
$products = $importer->getHardcodedFridgesList();
$firstFridge = $products[0];

echo "Testing cu primul frigider:\n";
echo "  Brand: {$firstFridge['brand']}\n";
echo "  Model: {$firstFridge['model']}\n";
echo "  Specs count: " . count($firstFridge['specs']) . "\n\n";

// Get the product
$product = DB::table('products')
    ->where('name', $firstFridge['name'])
    ->first();

if (!$product) {
    echo "❌ Product not found!\n";
    exit(1);
}

echo "Product ID: {$product->id}\n";
echo "Product Type ID: {$product->product_type_id}\n\n";

// Manually call addSpecifications with reflection
$reflection = new ReflectionClass($importer);
$method = $reflection->getMethod('addSpecifications');
$method->setAccessible(true);

// Get the Product model
$productModel = \App\Models\Product::find($product->id);

echo "Apelăm addSpecifications cu " . count($firstFridge['specs']) . " specs...\n";

try {
    $method->invoke($importer, $productModel, $firstFridge['specs']);
    echo "✅ addSpecifications executat cu succes!\n\n";
} catch (\Exception $e) {
    echo "❌ EROARE: {$e->getMessage()}\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
    exit(1);
}

// Check if specs were saved
$specsCount = DB::table('spec_values')
    ->where('product_id', $product->id)
    ->count();

echo "Specs salvate: {$specsCount}\n\n";

if ($specsCount > 0) {
    echo "✅ SUCCESS! Primele 5 specs:\n";
    $specs = DB::table('spec_values')
        ->join('spec_keys', 'spec_values.spec_key_id', '=', 'spec_keys.id')
        ->where('spec_values.product_id', $product->id)
        ->select('spec_keys.name', 'spec_values.value_string', 'spec_values.value_number', 'spec_values.value_bool')
        ->limit(5)
        ->get();
    
    foreach ($specs as $spec) {
        $value = $spec->value_string ?? $spec->value_number ?? ($spec->value_bool ? 'Da' : 'Nu');
        echo "  • {$spec->name}: {$value}\n";
    }
} else {
    echo "❌ Niciun spec nu a fost salvat!\n";
    
    // Debug spec_keys
    $keysCount = DB::table('spec_keys')
        ->where('product_type_id', $product->product_type_id)
        ->count();
    
    echo "\nSpec keys disponibile: {$keysCount}\n";
    
    // Try to manually save one spec
    echo "\nÎncerc să salvez manual un spec...\n";
    
    $specKeySlug = $product->product_type_id . '_total_capacity';
    $specKey = DB::table('spec_keys')
        ->where('slug', $specKeySlug)
        ->first();
    
    if ($specKey) {
        echo "  Spec key găsit: {$specKey->name} (ID: {$specKey->id})\n";
        
        $inserted = DB::table('spec_values')->insert([
            'product_id' => $product->id,
            'spec_key_id' => $specKey->id,
            'value_number' => 390,
            'value_string' => null,
            'value_bool' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        if ($inserted) {
            echo "  ✅ Spec salvat cu succes!\n";
        } else {
            echo "  ❌ Eroare la salvare!\n";
        }
    } else {
        echo "  ❌ Spec key nu a fost găsit pentru slug: {$specKeySlug}\n";
    }
}
