<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;

$products = Product::where('product_type_id', 7)->get(['id', 'name', 'brand', 'image_url']);

echo "🔍 Total mașini de spălat: " . $products->count() . "\n\n";

$brandCount = [];
foreach($products as $p) {
    $brand = $p->brand ?? 'Unknown';
    $brandCount[$brand] = ($brandCount[$brand] ?? 0) + 1;
}

echo "📊 Produse per brand:\n";
foreach($brandCount as $brand => $count) {
    echo "  • $brand: $count produse\n";
}

echo "\n🖼️ Primele 5 produse:\n";
foreach($products->take(5) as $p) {
    echo "\n" . $p->brand . " - " . $p->name . "\n";
    echo "  ID: " . $p->id . "\n";
    echo "  URL actual: " . substr($p->image_url, 0, 80) . "...\n";
}
