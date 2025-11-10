<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║          RAPORT FINAL - IMAGINI GOOGLE                   ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

$categories = [
    ['id' => 1, 'name' => 'Smartphone', 'total' => 108],
    ['id' => 2, 'name' => 'Smartwatch', 'total' => 30],
    ['id' => 3, 'name' => 'Placă video', 'total' => 30],
    ['id' => 4, 'name' => 'Căști wireless', 'total' => 33],
    ['id' => 6, 'name' => 'Frigider', 'total' => 20],
    ['id' => 7, 'name' => 'Mașină de spălat', 'total' => 53],
];

$totalProducts = 0;
$totalReal = 0;
$totalVersus = 0;
$totalPlaceholder = 0;

foreach ($categories as $cat) {
    $products = Product::where('product_type_id', $cat['id'])->get();
    
    $real = 0;
    $versus = 0;
    $placeholder = 0;
    
    foreach ($products as $product) {
        if (strpos($product->image_url, 'storage/products/') !== false ||
            strpos($product->image_url, 'lcdn.altex.ro') !== false) {
            $real++;
        } elseif (strpos($product->image_url, 'versus-dot-com.imgix.net') !== false) {
            $versus++;
        } else {
            $placeholder++;
        }
    }
    
    $totalProducts += $products->count();
    $totalReal += $real;
    $totalVersus += $versus;
    $totalPlaceholder += $placeholder;
    
    $percent = $products->count() > 0 ? round(($real / $products->count()) * 100, 1) : 0;
    
    echo sprintf("%-20s %3d produse  |  ✅ %2d real  🔵 %2d Versus  ❌ %2d Placeholder  |  %5.1f%%\n", 
        $cat['name'], 
        $products->count(), 
        $real, 
        $versus, 
        $placeholder,
        $percent
    );
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$totalPercent = $totalProducts > 0 ? round(($totalReal / $totalProducts) * 100, 1) : 0;

echo sprintf("%-20s %3d produse  |  ✅ %2d real  🔵 %2d Versus  ❌ %2d Placeholder  |  %5.1f%%\n", 
    "TOTAL", 
    $totalProducts, 
    $totalReal, 
    $totalVersus, 
    $totalPlaceholder,
    $totalPercent
);

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "📊 QUERIES GOOGLE FOLOSITE ASTĂZI:\n";
echo "   • Căști wireless: 30 queries (26 succese)\n";
echo "   • Smartphone-uri: 70 queries (67 succese)\n";
echo "   • TOTAL: 100/100 ✅ (limita zilnică consumată)\n\n";

echo "🎯 OBIECTIV ATINS:\n";
echo "   • Imagini reale: {$totalReal}/{$totalProducts} ({$totalPercent}%)\n";
echo "   • Imagini Versus: {$totalVersus}/{$totalProducts}\n\n";

if ($totalVersus > 0) {
    echo "💡 Pentru mâine:\n";
    echo "   • Mai rămân {$totalVersus} produse cu imagini Versus de înlocuit\n";
    echo "   • Vei avea 100 queries noi disponibile\n\n";
}

echo "=== END ===\n";
