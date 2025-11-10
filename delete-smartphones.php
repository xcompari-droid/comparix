<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\ProductType;
use App\Models\Category;
use App\Models\SpecValue;
use App\Models\SpecKey;
use Illuminate\Support\Facades\DB;

echo "=== ȘTERGERE CATEGORIE SMARTPHONE ===\n\n";

DB::transaction(function () {
    $productType = ProductType::where('name', 'Smartphone')->first();
    
    if (!$productType) {
        echo "ProductType 'Smartphone' nu există!\n";
        return;
    }
    
    $products = Product::where('product_type_id', $productType->id)->get();
    echo "Găsite {$products->count()} telefoane\n";
    
    // Delete all spec values
    $specValuesDeleted = 0;
    foreach ($products as $product) {
        $count = SpecValue::where('product_id', $product->id)->delete();
        $specValuesDeleted += $count;
    }
    echo "Șterse {$specValuesDeleted} specificații\n";
    
    // Delete all products
    $productsDeleted = Product::where('product_type_id', $productType->id)->delete();
    echo "Șterse {$productsDeleted} produse\n";
    
    // Delete spec keys
    $specKeysDeleted = SpecKey::where('product_type_id', $productType->id)->delete();
    echo "Șterse {$specKeysDeleted} chei specificații\n";
    
    // Delete product type
    $productType->delete();
    echo "Șters ProductType 'Smartphone'\n";
    
    // Check if category "Electrocasnice & IT" has other product types
    $category = Category::find($productType->category_id);
    if ($category) {
        $remainingTypes = ProductType::where('category_id', $category->id)->count();
        if ($remainingTypes == 0) {
            $category->delete();
            echo "Ștersă categoria '{$category->name}' (fără alte produse)\n";
        } else {
            echo "Categoria '{$category->name}' păstrată ({$remainingTypes} alte tipuri)\n";
        }
    }
});

echo "\n✓ Finalizat!\n";
