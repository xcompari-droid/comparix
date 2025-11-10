<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$category = App\Models\Category::where('name', 'Telefoane')->first();
if (!$category) {
    echo "Categoria 'Telefoane' nu există!\n";
    exit(1);
}

$count = App\Models\Product::where('category_id', $category->id)->count();

echo "\n✅ Total telefoane importate: $count\n\n";

// Afișează primele 10 telefoane
$phones = App\Models\Product::where('category_id', $category->id)
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get(['name', 'brand']);

echo "Ultimele 10 telefoane importate:\n";
foreach ($phones as $phone) {
    echo "  - $phone->brand $phone->name\n";
}
