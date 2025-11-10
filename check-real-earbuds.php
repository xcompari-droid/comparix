<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$earbuds = Product::where('product_type_id', 5)
    ->orderBy('brand')
    ->orderBy('name')
    ->get();

echo "\n=== CĂȘTI WIRELESS REALE (ID 5) - STATUS IMAGINI ===\n\n";

$realImages = 0;
$versusImages = 0;
$placeholderImages = 0;

foreach ($earbuds as $product) {
    $hasReal = !str_contains($product->image_url, 'versus-dot-com') 
                && !str_contains($product->image_url, 'dummyimage');
    
    if ($hasReal) {
        echo "✅ {$product->brand} {$product->name}\n";
        echo "   📷 {$product->image_url}\n\n";
        $realImages++;
    } else if (str_contains($product->image_url, 'versus-dot-com')) {
        echo "⚠️  {$product->brand} {$product->name}\n";
        echo "   🔗 Versus.com\n\n";
        $versusImages++;
    } else {
        echo "❌ {$product->brand} {$product->name}\n";
        echo "   🖼️  Placeholder\n\n";
        $placeholderImages++;
    }
}

echo "\n=== STATISTICI ===\n";
echo "Total căști wireless REALE: " . $earbuds->count() . "\n";
echo "✅ Imagini reale Google/Altex: $realImages (" . round($realImages * 100 / $earbuds->count(), 1) . "%)\n";
echo "⚠️  Imagini Versus.com: $versusImages\n";
echo "❌ Placeholders: $placeholderImages\n";
echo "\n🎯 Trebuie să import imagini Google pentru: " . ($versusImages + $placeholderImages) . " căști\n";
echo "💡 Queries estimate: " . ($versusImages + $placeholderImages) . "/100 available\n";
