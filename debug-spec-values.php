<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VERIFICARE SPEC_VALUES ===\n\n";

// Ia primele 10 spec values pentru LG
$specs = DB::table('spec_values')
    ->join('products', 'spec_values.product_id', '=', 'products.id')
    ->join('spec_keys', 'spec_values.spec_key_id', '=', 'spec_keys.id')
    ->where('products.id', 294)
    ->select('spec_keys.name', 'spec_values.value_string', 'spec_values.value_number', 'spec_values.value_bool')
    ->limit(10)
    ->get();

echo "Primele 10 specs pentru LG F4WV710P2E:\n\n";

foreach ($specs as $spec) {
    echo "• {$spec->name}:\n";
    echo "  value_string: " . ($spec->value_string ?: 'NULL') . "\n";
    echo "  value_number: " . ($spec->value_number ?: 'NULL') . "\n";
    echo "  value_bool: " . ($spec->value_bool ? 'TRUE' : 'FALSE') . "\n\n";
}

// Statistici generale
$total = DB::table('spec_values')->count();
$withString = DB::table('spec_values')->whereNotNull('value_string')->count();
$withNumber = DB::table('spec_values')->whereNotNull('value_number')->count();
$withBool = DB::table('spec_values')->where('value_bool', true)->count();

echo "\n📊 STATISTICI GENERALE:\n";
echo "Total spec_values: {$total}\n";
echo "Cu value_string: {$withString} (" . round($withString/$total*100, 1) . "%)\n";
echo "Cu value_number: {$withNumber} (" . round($withNumber/$total*100, 1) . "%)\n";
echo "Cu value_bool=TRUE: {$withBool} (" . round($withBool/$total*100, 1) . "%)\n";
