<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\ProductType;

$type = ProductType::where('slug', 'frigider')->first();
$fridges = Product::where('product_type_id', $type->id)->orderBy('name')->get();

echo "Total fridges: " . $fridges->count() . "\n\n";

// Group by brand to see duplicates
$byBrand = [];
foreach ($fridges as $f) {
    $brand = $f->brand;
    if (!isset($byBrand[$brand])) {
        $byBrand[$brand] = [];
    }
    $byBrand[$brand][] = $f->name;
}

foreach ($byBrand as $brand => $names) {
    echo "{$brand}: " . count($names) . " products\n";
    foreach ($names as $name) {
        echo "  - {$name}\n";
    }
}
