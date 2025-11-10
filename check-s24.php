<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$phone = App\Models\Product::find(1);

echo "=== Samsung Galaxy S24 Ultra ===\n";
echo "Specs count: " . $phone->specValues()->count() . "\n";
echo "Source URL: " . $phone->source_url . "\n\n";

echo "=== Primele 10 specs ===\n";
foreach ($phone->specValues()->with('specKey')->take(10)->get() as $spec) {
    $value = $spec->value_string ?? $spec->value_number ?? ($spec->value_bool ? 'Yes' : 'No');
    echo "- {$spec->specKey->name}: {$value}\n";
}
