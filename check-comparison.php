<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

echo "\n=== COMPARAȚIE PRODUSE ID 3 și 6 ===\n\n";

$product1 = Product::with(['productType', 'specValues.specKey'])->find(3);
$product2 = Product::with(['productType', 'specValues.specKey'])->find(6);

if (!$product1 || !$product2) {
    echo "❌ Unul dintre produse nu există!\n";
    exit(1);
}

echo "📱 Produs 1 (ID 3):\n";
echo "   Nume: {$product1->name}\n";
echo "   Brand: {$product1->brand}\n";
echo "   Tip: {$product1->productType->name}\n";
echo "   Specificații: " . $product1->specValues->count() . "\n\n";

echo "📱 Produs 2 (ID 6):\n";
echo "   Nume: {$product2->name}\n";
echo "   Brand: {$product2->brand}\n";
echo "   Tip: {$product2->productType->name}\n";
echo "   Specificații: " . $product2->specValues->count() . "\n\n";

if ($product1->specValues->count() > 0) {
    echo "✅ Produs 1 - Specificații disponibile:\n";
    foreach ($product1->specValues->take(5) as $spec) {
        $value = $spec->value_string ?? $spec->value_number ?? ($spec->value_bool ? 'Yes' : 'No');
        echo "   • {$spec->specKey->name}: {$value}\n";
    }
    if ($product1->specValues->count() > 5) {
        echo "   ... și încă " . ($product1->specValues->count() - 5) . " specificații\n";
    }
} else {
    echo "❌ Produs 1 - FĂRĂ specificații!\n";
}

echo "\n";

if ($product2->specValues->count() > 0) {
    echo "✅ Produs 2 - Specificații disponibile:\n";
    foreach ($product2->specValues->take(5) as $spec) {
        $value = $spec->value_string ?? $spec->value_number ?? ($spec->value_bool ? 'Yes' : 'No');
        echo "   • {$spec->specKey->name}: {$value}\n";
    }
    if ($product2->specValues->count() > 5) {
        echo "   ... și încă " . ($product2->specValues->count() - 5) . " specificații\n";
    }
} else {
    echo "❌ Produs 2 - FĂRĂ specificații!\n";
}

echo "\n";
