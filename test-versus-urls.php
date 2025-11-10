<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "  TEST URLs PENTRU COMPARAȚII VERSUS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Product types
$categories = [
    2 => 'Smartwatch',
    3 => 'GPU',
    5 => 'Laptop',
    6 => 'Frigider',
    7 => 'Mașină de spălat',
    8 => 'Căști Wireless',
];

foreach ($categories as $typeId => $typeName) {
    $products = Product::where('product_type_id', $typeId)
        ->whereNotNull('image_url')
        ->where('image_url', 'NOT LIKE', '%placeholder%')
        ->where('image_url', 'NOT LIKE', '%picsum%')
        ->limit(3)
        ->get();
    
    if ($products->count() < 3) {
        echo "❌ {$typeName}: Nu sunt suficiente produse (doar {$products->count()})\n\n";
        continue;
    }
    
    $ids = $products->pluck('id')->implode(',');
    $names = $products->pluck('name')->take(3)->implode(', ');
    
    echo "✅ {$typeName}\n";
    echo "   Produse: " . $names . "\n";
    echo "   URL: http://localhost:8080/compara?ids={$ids}&style=versus\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "  Deschide URL-urile de mai sus pentru testare!\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
