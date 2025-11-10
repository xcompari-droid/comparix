<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$smartphones = Product::where('product_type_id', 1)
    ->orderBy('id')
    ->get(['id', 'name', 'brand', 'image_url']);

echo "📱 Total smartphone-uri: " . $smartphones->count() . "\n\n";

$realImages = 0;
$placeholders = 0;
$versusImages = 0;

foreach ($smartphones as $product) {
    if (strpos($product->image_url, 'versus-dot-com.imgix.net') !== false) {
        $versusImages++;
    } elseif (strpos($product->image_url, 'dummyimage.com') !== false) {
        $placeholders++;
    } else {
        $realImages++;
    }
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Imagini reale (Google/Altex): $realImages / " . $smartphones->count() . "\n";
echo "🔵 Imagini Versus.com: $versusImages / " . $smartphones->count() . "\n";
echo "❌ Placeholders: $placeholders / " . $smartphones->count() . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$needImages = $smartphones->count() - $realImages - $versusImages;
echo "📊 Necesită imagini Google: $needImages smartphone-uri\n";
echo "📊 Queries disponibile astăzi: 70\n\n";

if ($needImages > 70) {
    echo "⚠️  Nu avem suficiente queries pentru toate ($needImages > 70)\n";
    echo "💡 Strategie: Importăm primele 70 smartphone-uri\n\n";
} else {
    echo "✅ Avem suficiente queries pentru toate!\n\n";
}

echo "Primele 10 smartphone-uri fără imagini reale:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$count = 0;
foreach ($smartphones as $product) {
    if (strpos($product->image_url, 'versus-dot-com.imgix.net') !== false || 
        strpos($product->image_url, 'dummyimage.com') !== false) {
        $count++;
        if ($count <= 10) {
            echo "$count. {$product->brand} {$product->name}\n";
        }
    }
}

echo "\n=== END ===\n";
