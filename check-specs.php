<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Products: " . \App\Models\Product::count() . PHP_EOL;
echo "SpecKeys: " . \App\Models\SpecKey::count() . PHP_EOL;
echo "SpecValues: " . \App\Models\SpecValue::count() . PHP_EOL;

if (\App\Models\Product::count() > 0) {
    $product = \App\Models\Product::first();
    echo PHP_EOL . "Specifications for: " . $product->name . PHP_EOL;
    echo str_repeat('-', 50) . PHP_EOL;
    
    foreach($product->specValues as $sv) {
        $value = $sv->value_string ?? $sv->value_number ?? ($sv->value_bool ? 'Da' : 'Nu');
        echo sprintf("%-25s : %s\n", $sv->specKey->name, $value);
    }
}
