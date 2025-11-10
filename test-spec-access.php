<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST ACCES SPEC VALUES ===\n\n";

// Testăm Product ID 3
$product = App\Models\Product::with('specValues.specKey')->find(3);

if (!$product) {
    echo "❌ Product ID 3 nu a fost găsit!\n";
    exit(1);
}

echo "📱 Produs: {$product->name}\n";
echo "Număr specValues: " . $product->specValues->count() . "\n\n";

if ($product->specValues->count() > 0) {
    echo "=== TESTARE PRIMELE 3 SPEC VALUES ===\n\n";
    
    foreach ($product->specValues->take(3) as $index => $specValue) {
        echo "Spec " . ($index + 1) . ":\n";
        echo "  spec_key_id: {$specValue->spec_key_id}\n";
        echo "  Cheie: {$specValue->specKey->name}\n";
        
        // Testăm fiecare câmp
        echo "  value_string: " . ($specValue->value_string ?? 'NULL') . "\n";
        echo "  value_number: " . ($specValue->value_number ?? 'NULL') . "\n";
        echo "  value_bool: " . ($specValue->value_bool === null ? 'NULL' : ($specValue->value_bool ? 'TRUE' : 'FALSE')) . "\n";
        echo "  unit (din specKey): " . ($specValue->specKey->unit ?? 'NULL') . "\n";
        
        // Testăm ce s-ar afișa în view
        echo "  >>> Ce ar afișa view-ul: ";
        if ($specValue->value_string) {
            echo $specValue->value_string;
        } elseif ($specValue->value_number !== null) {
            echo number_format($specValue->value_number, 2, ',', '.');
        } elseif ($specValue->value_bool !== null) {
            echo ($specValue->value_bool ? 'Da' : 'Nu');
        } else {
            echo '-';
        }
        
        if ($specValue->specKey->unit) {
            echo " " . $specValue->specKey->unit;
        }
        echo "\n\n";
    }
}

echo "=== TEST RELATIE SPEC_KEY ===\n\n";
$firstSpec = $product->specValues->first();
if ($firstSpec) {
    echo "SpecValue ID: {$firstSpec->id}\n";
    echo "spec_key_id: {$firstSpec->spec_key_id}\n";
    echo "specKey există: " . ($firstSpec->specKey ? 'DA' : 'NU') . "\n";
    if ($firstSpec->specKey) {
        echo "specKey->name: {$firstSpec->specKey->name}\n";
        echo "specKey->unit: " . ($firstSpec->specKey->unit ?? 'NULL') . "\n";
    }
}
