<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Category;
use App\Models\Product;

$category = Category::where('name', 'Smartwatch-uri')->first();

if (!$category) {
    echo "❌ Categoria Smartwatch-uri nu există\n";
    exit(1);
}

$count = Product::whereHas('productType', function($q) use ($category) {
    $q->where('category_id', $category->id);
})->count();

echo "✅ Total smartwatch-uri importate: $count\n";

$watches = Product::whereHas('productType', function($q) use ($category) {
    $q->where('category_id', $category->id);
})->latest()->take(10)->get();

echo "\nUltimele 10 smartwatch-uri importate:\n";
foreach ($watches as $watch) {
    echo "  - $watch->brand $watch->name\n";
}
