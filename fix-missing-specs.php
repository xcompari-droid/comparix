<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Importers\VersusEarbudImporter;
use App\Services\Importers\AltexFridgeImporter;
use Illuminate\Support\Facades\DB;

echo "🔧 CORECTARE SPECS LIPSĂ\n";
echo "==========================\n\n";

// Step 1: Curățăm specs goale pentru căști
echo "1️⃣ Ștergem spec_values goale pentru Căști wireless...\n";
$deletedEarbuds = DB::table('spec_values')
    ->join('products', 'spec_values.product_id', '=', 'products.id')
    ->join('product_types', 'products.product_type_id', '=', 'product_types.id')
    ->where('product_types.slug', 'casti-wireless')
    ->whereNull('spec_values.value_string')
    ->whereNull('spec_values.value_number')
    ->where(function($q) {
        $q->whereNull('spec_values.value_bool')
          ->orWhere('spec_values.value_bool', false);
    })
    ->delete();
echo "   ✓ Șterse: {$deletedEarbuds} înregistrări\n\n";

// Step 2: Curățăm specs goale pentru frigidere
echo "2️⃣ Ștergem spec_values goale pentru Frigidere...\n";
$deletedFridges = DB::table('spec_values')
    ->join('products', 'spec_values.product_id', '=', 'products.id')
    ->join('product_types', 'products.product_type_id', '=', 'product_types.id')
    ->where('product_types.slug', 'frigider')
    ->whereNull('spec_values.value_string')
    ->whereNull('spec_values.value_number')
    ->where(function($q) {
        $q->whereNull('spec_values.value_bool')
          ->orWhere('spec_values.value_bool', false);
    })
    ->delete();
echo "   ✓ Șterse: {$deletedFridges} înregistrări\n\n";

// Step 3: Re-importăm căștile wireless
echo "3️⃣ Re-importăm specificațiile pentru Căști wireless...\n";
echo "   (Acest proces poate dura câteva minute...)\n";
try {
    $earbudImporter = new VersusEarbudImporter();
    $earbudImporter->import(33);
    echo "   ✅ Import finalizat cu succes!\n\n";
} catch (\Exception $e) {
    echo "   ❌ EROARE: {$e->getMessage()}\n\n";
}

// Step 4: Re-importăm frigiderele
echo "4️⃣ Re-importăm specificațiile pentru Frigidere...\n";
try {
    $fridgeImporter = new AltexFridgeImporter();
    $fridgeImporter->import(10);
    echo "   ✅ Import finalizat cu succes!\n\n";
} catch (\Exception $e) {
    echo "   ❌ EROARE: {$e->getMessage()}\n\n";
}

// Step 5: Verificăm rezultatele
echo "5️⃣ VERIFICARE FINALĂ\n";
echo "=====================\n\n";

$categories = [
    'Căști wireless' => 'casti-wireless',
    'Frigider' => 'frigider',
];

foreach ($categories as $name => $slug) {
    echo "📁 {$name}\n";
    
    // Get sample product
    $product = DB::table('products')
        ->join('product_types', 'products.product_type_id', '=', 'product_types.id')
        ->where('product_types.slug', $slug)
        ->select('products.id', 'products.name')
        ->first();
    
    if (!$product) {
        echo "   ⚠️  Niciun produs găsit!\n\n";
        continue;
    }
    
    echo "   Exemplu: {$product->name} (ID: {$product->id})\n";
    
    // Count total specs
    $totalSpecs = DB::table('spec_values')
        ->where('product_id', $product->id)
        ->count();
    
    // Count specs with values
    $validSpecs = DB::table('spec_values')
        ->where('product_id', $product->id)
        ->where(function($q) {
            $q->whereNotNull('value_string')
              ->orWhereNotNull('value_number')
              ->orWhere('value_bool', true);
        })
        ->count();
    
    // Count NULL specs
    $nullSpecs = DB::table('spec_values')
        ->where('product_id', $product->id)
        ->whereNull('value_string')
        ->whereNull('value_number')
        ->where(function($q) {
            $q->whereNull('value_bool')->orWhere('value_bool', false);
        })
        ->count();
    
    $percentage = $totalSpecs > 0 ? round(($validSpecs / $totalSpecs) * 100, 1) : 0;
    
    echo "   Total specs: {$totalSpecs}\n";
    echo "   ✅ Cu valori: {$validSpecs} ({$percentage}%)\n";
    echo "   ❌ NULL/FALSE: {$nullSpecs}\n";
    
    if ($nullSpecs > 0) {
        echo "   ⚠️  ÎNCĂ EXISTĂ PROBLEME!\n";
    } else {
        echo "   🎉 PERFECT! Toate specs au valori!\n";
    }
    
    echo "\n";
}

echo "✅ PROCESARE COMPLETĂ!\n";
echo "🔍 Testează site-ul pentru a verifica afișarea specs:\n";
echo "   • http://localhost:8080/categorii/casti-wireless\n";
echo "   • http://localhost:8080/categorii/frigider\n";
