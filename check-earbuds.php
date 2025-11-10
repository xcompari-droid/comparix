<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\Category;

echo "=== STATISTICI COMPLETE PRODUSE ===\n\n";

// Get all categories with product counts
$categories = Category::withCount('productTypes')->get();

$totalProducts = Product::count();
echo "📊 Total produse: {$totalProducts}\n\n";

echo "=== Produse pe Categorii ===\n";
foreach ($categories as $category) {
    $productCount = Product::whereHas('productType', function($q) use ($category) {
        $q->where('category_id', $category->id);
    })->count();
    
    $icon = $category->icon ?? '📦';
    echo "{$icon} {$category->name}: {$productCount} produse\n";
}

echo "\n=== Breakdown per brand (Earbuds) ===\n";
$earbuds = Product::whereHas('productType', function($q) {
    $q->where('name', 'Căști wireless');
})->select('brand', \DB::raw('count(*) as count'))
  ->groupBy('brand')
  ->orderBy('count', 'desc')
  ->get();

foreach ($earbuds as $earbud) {
    echo "  {$earbud->brand}: {$earbud->count} căști\n";
}

echo "\n=== Status imagini (Earbuds) ===\n";
$earbudsWithImages = Product::whereHas('productType', function($q) {
    $q->where('name', 'Căști wireless');
})->where('image_url', 'NOT LIKE', '%ui-avatars%')->count();

$earbudsTotal = Product::whereHas('productType', function($q) {
    $q->where('name', 'Căști wireless');
})->count();

$percentage = $earbudsTotal > 0 ? round(($earbudsWithImages / $earbudsTotal) * 100, 1) : 0;
echo "  Cu imagini reale: {$earbudsWithImages} / {$earbudsTotal} ({$percentage}%)\n";

echo "\n=== Sample Earbuds (primele 5) ===\n";
$sampleEarbuds = Product::whereHas('productType', function($q) {
    $q->where('name', 'Căști wireless');
})->limit(5)->get();

foreach ($sampleEarbuds as $earbud) {
    $hasImage = !str_contains($earbud->image_url, 'ui-avatars');
    $imageIcon = $hasImage ? '✓' : '✗';
    echo "  [{$imageIcon}] {$earbud->name} ({$earbud->brand})\n";
}
