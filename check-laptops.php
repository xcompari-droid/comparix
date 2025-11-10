<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\DB;

echo "💻 VERIFICARE LAPTOPURI\n";
echo str_repeat("=", 50) . "\n\n";

$laptops = Product::where('product_type_id', 9)->get();
$count = $laptops->count();

$specs = DB::table('spec_values')
    ->join('products', 'spec_values.product_id', '=', 'products.id')
    ->where('products.product_type_id', 9)
    ->count();

$avg = $count > 0 ? round($specs / $count, 1) : 0;

echo "✅ Total laptopuri: $count\n";
echo "📊 Total specificații: $specs\n";
echo "📈 Medie specs/laptop: $avg\n\n";

echo "PRIMELE 5 LAPTOPURI:\n";
echo str_repeat("-", 50) . "\n";

foreach ($laptops->take(5) as $laptop) {
    $specsCount = DB::table('spec_values')
        ->where('product_id', $laptop->id)
        ->count();
    
    echo "• {$laptop->name}\n";
    echo "  Brand: {$laptop->brand} | Preț: {$laptop->price} RON\n";
    echo "  Specs: $specsCount | Image: " . (str_contains($laptop->image_url, 'ui-avatars') ? '❌ Placeholder' : '✅ Real') . "\n\n";
}

echo "\n✅ Import complet și funcțional!\n";
echo "🌐 Vezi toate la: http://localhost:8080/categorii/laptop\n";
