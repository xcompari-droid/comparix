<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;
use App\Models\Product;

echo "📊 Comparix Statistics\n";
echo "══════════════════════════════════════════\n\n";

$categories = Category::all();

foreach ($categories as $category) {
    $count = Product::where('category_id', $category->id)->count();
    echo "  📁 {$category->name}: {$count} produse\n";
    
    if ($category->slug === 'orase') {
        $cities = Product::where('category_id', $category->id)->get();
        foreach ($cities as $city) {
            echo "     🏙️  {$city->name}\n";
        }
    }
}

echo "\n══════════════════════════════════════════\n";
echo "Total: " . Product::count() . " produse\n";
echo "══════════════════════════════════════════\n";
