<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔧 Configurare categorie Laptop...\n\n";

// 1. Creare Category pentru Laptop (mai întâi categoria)
$laptopCategory = DB::table('categories')->where('slug', 'laptop')->first();

if (!$laptopCategory) {
    $categoryId = DB::table('categories')->insertGetId([
        'name' => 'Laptop',
        'slug' => 'laptop',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "✓ Category 'Laptop' creată (ID: $categoryId)\n";
    $laptopCategory = DB::table('categories')->find($categoryId);
} else {
    echo "✓ Category 'Laptop' există deja (ID: {$laptopCategory->id})\n";
}

// 2. Creare ProductType pentru Laptop
$laptopType = DB::table('product_types')->where('slug', 'laptop')->first();

if (!$laptopType) {
    $typeId = DB::table('product_types')->insertGetId([
        'name' => 'Laptop',
        'slug' => 'laptop',
        'category_id' => $laptopCategory->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "✓ ProductType 'Laptop' creat (ID: $typeId)\n";
    $laptopType = DB::table('product_types')->find($typeId);
} else {
    echo "✓ ProductType 'Laptop' există deja (ID: {$laptopType->id})\n";
}

echo "\n✅ Configurare completă!\n";
echo "Category ID: {$laptopCategory->id}\n";
echo "ProductType ID: {$laptopType->id}\n";
echo "URL: http://localhost:8080/categorii/laptop\n";
