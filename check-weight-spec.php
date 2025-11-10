<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$specs = App\Models\SpecKey::where('name', 'LIKE', '%weight%')
    ->orWhere('slug', 'LIKE', '%weight%')
    ->get(['id', 'name', 'slug', 'product_type_id']);

echo "=== SPEC KEYS CU 'WEIGHT' ===\n";
foreach ($specs as $spec) {
    echo "ID: {$spec->id}, Name: {$spec->name}, Slug: {$spec->slug}, Type ID: {$spec->product_type_id}\n";
}
