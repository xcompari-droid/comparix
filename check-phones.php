<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$category = App\Models\Category::where('slug', 'telefoane')->first();
$count = App\Models\Product::where('category_id', $category->id)->count();

echo "Telefoane importate: {$count}\n";

$phones = App\Models\Product::where('category_id', $category->id)->get();

foreach ($phones as $phone) {
    $specs = $phone->specValues->count();
    echo "  - {$phone->name}: {$specs} specificații\n";
}
