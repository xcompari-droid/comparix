<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

echo "=== Checking for 'Nu' values (string vs boolean) ===\n\n";

$p416 = Product::with('specValues.specKey')->find(416);
$p417 = Product::with('specValues.specKey')->find(417);

foreach ([$p416, $p417] as $product) {
    echo "\n--- Product {$product->id}: {$product->name} ---\n";
    
    foreach ($product->specValues->sortBy(fn($s) => $s->specKey->name) as $spec) {
        $keyName = $spec->specKey->name;
        
        if ($spec->value_bool !== null) {
            $value = $spec->value_bool ? 'Da' : 'Nu';
            echo "{$keyName}: {$value} (BOOLEAN: " . ($spec->value_bool ? 'true' : 'false') . ")\n";
        } elseif ($spec->value_string === 'Nu' || $spec->value_string === 'nu') {
            echo "{$keyName}: {$spec->value_string} (STRING 'Nu')\n";
        } elseif ($spec->value_string === 'Da' || $spec->value_string === 'da') {
            echo "{$keyName}: {$spec->value_string} (STRING 'Da')\n";
        }
    }
}
