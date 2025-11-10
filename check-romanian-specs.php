<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Verificăm specificațiile TV-urilor în română...\n\n";

// Lista de chei traduse
$specKeys = DB::table('spec_keys')
    ->where('product_type_id', 8)
    ->orderBy('name')
    ->get();

echo "✓ Chei de specificații (toate în română):\n";
echo str_repeat('-', 60) . "\n";
foreach ($specKeys as $key) {
    echo "  • {$key->name}\n";
}

echo "\n✓ Total: " . count($specKeys) . " chei de specificații\n";

// Verificăm un TV ca exemplu
$tv = DB::table('products')
    ->where('product_type_id', 8)
    ->first();

if ($tv) {
    $specs = DB::table('spec_values as sv')
        ->join('spec_keys as sk', 'sv.spec_key_id', '=', 'sk.id')
        ->where('sv.product_id', $tv->id)
        ->select('sk.name', 'sv.value_string', 'sv.value_number', 'sv.value_bool')
        ->get();
    
    echo "\n📺 Exemplu - {$tv->name}:\n";
    echo str_repeat('-', 60) . "\n";
    foreach ($specs as $spec) {
        $value = $spec->value_string ?? $spec->value_number ?? ($spec->value_bool ? 'Da' : 'Nu');
        echo "  {$spec->name}: $value\n";
    }
}
