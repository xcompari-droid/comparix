<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\SpecValue;
use App\Models\SpecKey;

echo "🔍 VERIFICARE DETALII FRIGIDER\n";
echo "===============================\n\n";

$fridge = Product::where('name', 'Samsung RB38A7B6AS9/EF')->first();

if (!$fridge) {
    echo "❌ Frigider nu a fost găsit!\n";
    exit(1);
}

echo "Product: {$fridge->name}\n";
echo "ID: {$fridge->id}\n";
echo "Product Type ID: {$fridge->product_type_id}\n\n";

$specs = SpecValue::where('product_id', $fridge->id)->get();

echo "Total specs în spec_values: {$specs->count()}\n\n";

if ($specs->count() > 0) {
    echo "Primele 10 specs:\n";
    foreach ($specs->take(10) as $spec) {
        $key = SpecKey::find($spec->spec_key_id);
        $value = $spec->value_string ?? $spec->value_number ?? ($spec->value_bool ? 'Da' : 'Nu');
        echo "  • {$key->name}: {$value}\n";
    }
} else {
    echo "⚠️  Niciun spec salvat pentru acest produs!\n";
    echo "\nVerificăm spec_keys pentru product_type_id: {$fridge->product_type_id}\n";
    
    $keys = SpecKey::where('product_type_id', $fridge->product_type_id)->get();
    echo "Total spec_keys: {$keys->count()}\n\n";
    
    if ($keys->count() > 0) {
        echo "Primele 5 spec_keys:\n";
        foreach ($keys->take(5) as $key) {
            echo "  • ID: {$key->id} - {$key->name} (slug: {$key->slug})\n";
        }
    }
}
