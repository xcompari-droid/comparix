<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ProductType;

echo "Product types in Electrocasnice (category_id=8):\n";
$types = ProductType::where('category_id', 8)->get();

foreach ($types as $type) {
    echo "  - {$type->name} (slug: {$type->slug}, id: {$type->id})\n";
}

echo "\nMașini de spălat (slug=masina-de-spalat):\n";
$washing = ProductType::where('slug', 'masina-de-spalat')->get();
foreach ($washing as $w) {
    echo "  - {$w->name} (slug: {$w->slug}, id: {$w->id}, category_id: {$w->category_id})\n";
}
