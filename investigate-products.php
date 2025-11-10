<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\Category;

echo "🔍 Investigating products...\n\n";

// Check all products
$products = Product::all();
echo "Total products: " . $products->count() . "\n\n";

// Group by brand
$byBrand = $products->groupBy('brand');
foreach ($byBrand as $brand => $items) {
    echo "{$brand}: {$items->count()} produse\n";
}

echo "\n";

// Check categories
$categories = Category::all();
echo "Categories:\n";
foreach ($categories as $cat) {
    echo "  - {$cat->name} (slug: {$cat->slug}, id: {$cat->id})\n";
}

echo "\n";

// Check a sample city product
$city = Product::where('brand', 'România')->first();
if ($city) {
    echo "Sample city product:\n";
    echo "  Name: {$city->name}\n";
    echo "  Brand: {$city->brand}\n";
    echo "  Category ID: {$city->category_id}\n";
    echo "  Product Type ID: {$city->product_type_id}\n";
    echo "  Image: {$city->image_url}\n";
    
    if ($city->category_id) {
        $cat = Category::find($city->category_id);
        echo "  Category: " . ($cat ? $cat->name : 'NOT FOUND') . "\n";
    }
}
