<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$p1 = App\Models\Product::with('specValues.specKey')->find(416);
$p2 = App\Models\Product::with('specValues.specKey')->find(417);

echo "Product 416: {$p1->name}\n";
echo "Brand: {$p1->brand}\n";
echo "EAN: {$p1->ean}\n\n";

echo "Product 417: {$p2->name}\n";
echo "Brand: {$p2->brand}\n";
echo "EAN: {$p2->ean}\n\n";

echo "=== Specs cu valoare FALSE pentru produsul 416 ===\n";
foreach ($p1->specValues as $sv) {
    if ($sv->value_bool === false) {
        echo "  - {$sv->specKey->name}: Nu\n";
    }
}

echo "\n=== Specs cu valoare FALSE pentru produsul 417 ===\n";
foreach ($p2->specValues as $sv) {
    if ($sv->value_bool === false) {
        echo "  - {$sv->specKey->name}: Nu\n";
    }
}

echo "\n=== Total specs pentru 416: " . $p1->specValues->count() . " ===\n";
echo "=== Total specs pentru 417: " . $p2->specValues->count() . " ===\n";
