<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Importers\AltexFridgeImporter;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

echo "🔧 RE-IMPORT COMPLET FRIGIDERE\n";
echo "===============================\n\n";

$importer = new AltexFridgeImporter();
$products = $importer->getHardcodedFridgesList();

echo "Găsite " . count($products) . " frigidere în lista hardcoded\n\n";

$updated = 0;

foreach ($products as $productData) {
    echo "📦 {$productData['name']}\n";
    
    // Get product
    $product = Product::where('name', $productData['name'])
        ->where('product_type_id', 6) // frigider product_type_id
        ->first();
    
    if (!$product) {
        echo "   ⚠️  Produs nu există, îl creăm...\n";
        $product = Product::withoutSyncingToSearch(function() use ($productData) {
            return Product::create([
                'product_type_id' => 6,
                'category_id' => 1, // electrocasnice
                'brand' => $productData['brand'],
                'model' => $productData['model'] ?? null,
                'name' => $productData['name'],
                'image_url' => $productData['image_url'] ?? null,
                'source_url' => $productData['source_url'] ?? null,
            ]);
        });
    }
    
    echo "   Product ID: {$product->id}\n";
    
    // Delete old specs
    $deleted = DB::table('spec_values')
        ->where('product_id', $product->id)
        ->delete();
    echo "   Șterse {$deleted} specs vechi\n";
    
    // Add specs using reflection
    $reflection = new ReflectionClass($importer);
    $method = $reflection->getMethod('addSpecifications');
    $method->setAccessible(true);
    
    try {
        $method->invoke($importer, $product, $productData['specs']);
        
        $specsCount = DB::table('spec_values')
            ->where('product_id', $product->id)
            ->count();
        
        echo "   ✅ Salvate {$specsCount} specs\n";
        $updated++;
        
    } catch (\Exception $e) {
        echo "   ❌ EROARE: {$e->getMessage()}\n";
    }
    
    echo "\n";
}

echo "✅ PROCESARE COMPLETĂ!\n";
echo "   Actualizate: {$updated}/{count($products)} frigidere\n\n";

// Verificare finală
echo "🔍 VERIFICARE FINALĂ\n";
echo "====================\n\n";

$sample = Product::where('name', 'Samsung RB38A7B6AS9/EF')->first();

if ($sample) {
    $specsCount = DB::table('spec_values')
        ->where('product_id', $sample->id)
        ->count();
    
    echo "Samsung RB38A7B6AS9/EF: {$specsCount} specs\n";
    
    if ($specsCount > 0) {
        $specs = DB::table('spec_values')
            ->join('spec_keys', 'spec_values.spec_key_id', '=', 'spec_keys.id')
            ->where('spec_values.product_id', $sample->id)
            ->select('spec_keys.name', 'spec_values.value_string', 'spec_values.value_number', 'spec_values.value_bool')
            ->limit(5)
            ->get();
        
        echo "\nPrimele 5 specs:\n";
        foreach ($specs as $spec) {
            $value = $spec->value_string ?? $spec->value_number ?? ($spec->value_bool ? 'Da' : 'Nu');
            echo "  • {$spec->name}: {$value}\n";
        }
    }
}

echo "\n🎉 GATA! Testează la: http://localhost:8080/categorii/frigider\n";
