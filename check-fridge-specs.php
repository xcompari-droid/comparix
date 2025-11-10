<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 VERIFICARE SPECS FRIGIDERE - DIRECT SQL\n";
echo "============================================\n\n";

// Get fridge product
$fridge = DB::table('products')
    ->where('name', 'Samsung RB38A7B6AS9/EF')
    ->first();

if (!$fridge) {
    echo "❌ Frigider nu a fost găsit!\n";
    exit(1);
}

echo "Product: {$fridge->name}\n";
echo "ID: {$fridge->id}\n";
echo "Product Type ID: {$fridge->product_type_id}\n\n";

// Count specs
$specsCount = DB::table('spec_values')
    ->where('product_id', $fridge->id)
    ->count();

echo "Total specs în spec_values: {$specsCount}\n\n";

if ($specsCount > 0) {
    echo "Primele 10 specs:\n";
    $specs = DB::table('spec_values')
        ->join('spec_keys', 'spec_values.spec_key_id', '=', 'spec_keys.id')
        ->where('spec_values.product_id', $fridge->id)
        ->select('spec_keys.name', 'spec_values.value_string', 'spec_values.value_number', 'spec_values.value_bool')
        ->limit(10)
        ->get();
    
    foreach ($specs as $spec) {
        $value = $spec->value_string ?? $spec->value_number ?? ($spec->value_bool ? 'Da' : 'Nu');
        echo "  • {$spec->name}: {$value}\n";
    }
} else {
    echo "⚠️  Niciun spec salvat pentru acest produs!\n\n";
    
    // Check if spec_keys exist
    $keysCount = DB::table('spec_keys')
        ->where('product_type_id', $fridge->product_type_id)
        ->count();
    
    echo "Spec keys pentru product_type {$fridge->product_type_id}: {$keysCount}\n\n";
    
    if ($keysCount > 0) {
        echo "Primele 5 spec_keys:\n";
        $keys = DB::table('spec_keys')
            ->where('product_type_id', $fridge->product_type_id)
            ->limit(5)
            ->get();
        
        foreach ($keys as $key) {
            echo "  • ID: {$key->id} - {$key->name} (slug: {$key->slug})\n";
        }
    } else {
        echo "❌ Niciun spec_key nu există pentru acest product_type!\n";
        echo "   Importerul nu a creat spec_keys corect.\n";
    }
}

echo "\n\n🔍 Verificăm numărul de specs per toate frigiderele:\n";
echo "====================================================\n\n";

$fridges = DB::table('products')
    ->join('product_types', 'products.product_type_id', '=', 'product_types.id')
    ->where('product_types.slug', 'frigider')
    ->select('products.id', 'products.name')
    ->limit(5)
    ->get();

foreach ($fridges as $f) {
    $count = DB::table('spec_values')->where('product_id', $f->id)->count();
    echo "• {$f->name}: {$count} specs\n";
}
