<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

echo "=== Checking for duplicate boolean specs ===\n\n";

$products = [416, 417];

foreach ($products as $productId) {
    $product = Product::with('specValues.specKey')->find($productId);
    echo "\n--- Product {$productId}: {$product->name} ---\n";
    
    // Grupează specs după spec_key_id
    $grouped = $product->specValues->groupBy('spec_key_id');
    
    $duplicates = 0;
    $fixed = 0;
    
    foreach ($grouped as $keyId => $specs) {
        if ($specs->count() > 1) {
            $specName = $specs->first()->specKey->name;
            echo "  DUPLICATE: '{$specName}' has {$specs->count()} entries\n";
            
            // Afișează toate valorile
            foreach ($specs as $spec) {
                $value = 'NULL';
                if ($spec->value_bool !== null) $value = $spec->value_bool ? 'Da (bool)' : 'Nu (bool)';
                elseif ($spec->value_string) $value = $spec->value_string . ' (string)';
                elseif ($spec->value_number !== null) $value = $spec->value_number . ' (number)';
                
                echo "    ID {$spec->id}: {$value}\n";
            }
            
            // Logică de curățare:
            // 1. Dacă sunt toate boolean, păstrează un singur adevăr sau ultimul fals
            $boolSpecs = $specs->filter(fn($s) => $s->value_bool !== null);
            
            if ($boolSpecs->count() === $specs->count()) {
                // Toate sunt boolean
                $trueSpecs = $boolSpecs->filter(fn($s) => $s->value_bool === true);
                
                if ($trueSpecs->count() > 0) {
                    // Păstrează primul true, șterge restul
                    $kept = false;
                    foreach ($specs as $spec) {
                        if ($spec->value_bool === true && !$kept) {
                            echo "    KEEPING ID {$spec->id} (Da)\n";
                            $kept = true;
                        } else {
                            echo "    DELETING ID {$spec->id}\n";
                            $spec->delete();
                            $fixed++;
                        }
                    }
                } else {
                    // Toate sunt false, păstrează primul
                    $kept = false;
                    foreach ($specs as $spec) {
                        if (!$kept) {
                            echo "    KEEPING ID {$spec->id} (Nu)\n";
                            $kept = true;
                        } else {
                            echo "    DELETING ID {$spec->id}\n";
                            $spec->delete();
                            $fixed++;
                        }
                    }
                }
            } else {
                // Mix de tipuri, păstrează non-boolean sau primul
                $kept = false;
                foreach ($specs as $spec) {
                    if ($spec->value_string || $spec->value_number !== null) {
                        if (!$kept) {
                            echo "    KEEPING ID {$spec->id} (has string/number value)\n";
                            $kept = true;
                        } else {
                            echo "    DELETING ID {$spec->id}\n";
                            $spec->delete();
                            $fixed++;
                        }
                    }
                }
                
                // Dacă nu am păstrat nimic non-boolean, păstrează primul boolean
                if (!$kept) {
                    $first = true;
                    foreach ($specs as $spec) {
                        if ($first) {
                            echo "    KEEPING ID {$spec->id} (first entry)\n";
                            $first = false;
                        } else {
                            echo "    DELETING ID {$spec->id}\n";
                            $spec->delete();
                            $fixed++;
                        }
                    }
                }
            }
            
            $duplicates++;
        }
    }
    
    echo "\n  Summary: {$duplicates} specs had duplicates, {$fixed} entries deleted\n";
}

echo "\n=== Final verification ===\n";
foreach ($products as $productId) {
    $product = Product::with('specValues.specKey')->find($productId);
    $grouped = $product->specValues->groupBy('spec_key_id');
    $dupes = $grouped->filter(fn($g) => $g->count() > 1)->count();
    echo "Product {$productId}: {$dupes} remaining duplicates\n";
}
