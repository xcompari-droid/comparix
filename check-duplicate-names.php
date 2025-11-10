<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

echo "=== Checking for specs with same NAME but different key_id ===\n\n";

$products = [416, 417];

foreach ($products as $productId) {
    $product = Product::with('specValues.specKey')->find($productId);
    echo "\n--- Product {$productId}: {$product->name} ---\n";
    
    // Grupează după nume (nu după key_id)
    $byName = $product->specValues->groupBy(fn($s) => $s->specKey->name);
    
    $duplicates = 0;
    
    foreach ($byName as $name => $specs) {
        if ($specs->count() > 1) {
            echo "  DUPLICATE NAME: '{$name}' appears {$specs->count()} times\n";
            
            foreach ($specs as $spec) {
                $value = 'NULL';
                if ($spec->value_bool !== null) $value = $spec->value_bool ? 'Da (bool)' : 'Nu (bool)';
                elseif ($spec->value_string) $value = $spec->value_string . ' (string)';
                elseif ($spec->value_number !== null) $value = $spec->value_number . ' (number)';
                
                echo "    ID {$spec->id}, key_id {$spec->spec_key_id}: {$value}\n";
            }
            
            $duplicates++;
        }
    }
    
    if ($duplicates === 0) {
        echo "  No duplicate spec names found\n";
    } else {
        echo "\n  Total: {$duplicates} specs with duplicate names\n";
    }
}
