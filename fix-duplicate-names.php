<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\SpecValue;

echo "=== Fixing duplicate spec names ===\n\n";

$products = [416, 417];

foreach ($products as $productId) {
    $product = Product::with('specValues.specKey')->find($productId);
    echo "\n--- Product {$productId}: {$product->name} ---\n";
    
    $byName = $product->specValues->groupBy(fn($s) => $s->specKey->name);
    
    $fixed = 0;
    
    foreach ($byName as $name => $specs) {
        if ($specs->count() > 1) {
            echo "  Processing: '{$name}' ({$specs->count()} entries)\n";
            
            // Sortează: string/number first, apoi boolean
            $sorted = $specs->sortBy(function($spec) {
                if ($spec->value_string || $spec->value_number !== null) return 0;
                if ($spec->value_bool === true) return 1;
                return 2;
            });
            
            $kept = false;
            foreach ($sorted as $spec) {
                $value = 'NULL';
                if ($spec->value_bool !== null) $value = $spec->value_bool ? 'Da (bool)' : 'Nu (bool)';
                elseif ($spec->value_string) $value = $spec->value_string . ' (string)';
                elseif ($spec->value_number !== null) $value = $spec->value_number . ' (number)';
                
                if (!$kept) {
                    echo "    KEEPING ID {$spec->id}: {$value}\n";
                    $kept = true;
                } else {
                    echo "    DELETING ID {$spec->id}: {$value}\n";
                    SpecValue::destroy($spec->id);
                    $fixed++;
                }
            }
        }
    }
    
    echo "  Deleted {$fixed} duplicate specs\n";
}

echo "\n=== Verification ===\n";
foreach ($products as $productId) {
    $product = Product::with('specValues.specKey')->find($productId);
    $byName = $product->specValues->groupBy(fn($s) => $s->specKey->name);
    $dupes = $byName->filter(fn($g) => $g->count() > 1)->count();
    echo "Product {$productId}: {$dupes} remaining name duplicates\n";
}
