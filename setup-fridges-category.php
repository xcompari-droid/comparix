<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Category;
use App\Models\ProductType;

echo "=== CREARE CATEGORIE ELECTROCASNICE ===\n\n";

// Create Electrocasnice category
$category = Category::firstOrCreate(
    ['slug' => 'electrocasnice'],
    [
        'name' => 'Electrocasnice',
        'description' => 'Electrocasnice mari și mici pentru casă',
        'icon' => '🏠'
    ]
);

echo "✓ Categorie: {$category->name} (ID: {$category->id})\n";

// Create Frigider product type
$productType = ProductType::firstOrCreate(
    [
        'category_id' => $category->id,
        'name' => 'Frigider'
    ],
    [
        'slug' => 'frigider',
        'description' => 'Aparate frigorifice și combine frigorifice'
    ]
);

echo "✓ ProductType: {$productType->name} (ID: {$productType->id})\n\n";

echo "Gata! Acum rulează: php artisan import:altex-fridges\n";
