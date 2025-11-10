<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔧 Creare categorie TV...\n";

// Mai întâi creează categoria în Categories pentru afișare
$category = App\Models\Category::create([
    'name' => 'Televizoare',
    'slug' => 'televizoare',
    'description' => 'Compară televizoare smart, LED, OLED, QLED și alte modele',
    'icon' => '📺',
]);

echo "✅ Categorie Televizoare creată cu ID: {$category->id}\n";

// Apoi creează ProductType legat de categoria creată
$tv = App\Models\ProductType::create([
    'name' => 'TV',
    'slug' => 'tv',
    'category_id' => $category->id,
]);

echo "✅ ProductType TV creat cu ID: {$tv->id}\n";
