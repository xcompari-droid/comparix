<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\Category;
use App\Models\SpecValue;

echo "=== VERIFICARE SMARTWATCH-URI ===\n\n";

$watchCategory = Category::where('name', 'Smartwatch-uri')->first();
if (!$watchCategory) {
    echo "❌ Categoria Smartwatch-uri nu există!\n";
    exit;
}

$watches = Product::whereHas('productType', function($q) use ($watchCategory) {
    $q->where('category_id', $watchCategory->id);
})->get();

echo "Total smartwatch-uri: {$watches->count()}\n\n";

// Verifică fiecare smartwatch
foreach ($watches as $watch) {
    echo "⌚ {$watch->brand} {$watch->name} (ID: {$watch->id})\n";
    echo "  Slug: {$watch->slug}\n";
    echo "  Imagine: " . ($watch->image_url ?: 'LIPSĂ') . "\n";
    
    // Verifică specificațiile
    $specs = SpecValue::where('product_id', $watch->id)->get();
    echo "  Specificații: {$specs->count()}\n";
    
    if ($specs->count() > 0) {
        echo "  Primele 5 specificații:\n";
        foreach ($specs->take(5) as $spec) {
            $value = $spec->value_string ?? $spec->value_number ?? ($spec->value_bool ? 'Da' : 'Nu');
            echo "    - {$spec->specKey->name}: {$value}\n";
        }
    } else {
        echo "  ⚠️  FĂRĂ SPECIFICAȚII!\n";
    }
    
    // Verifică ofertele
    $offers = $watch->offers()->count();
    echo "  Oferte: {$offers}\n";
    
    echo "\n";
}
