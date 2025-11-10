<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\Offer;

echo "🔧 Updating city offers...\n\n";

$cities = Product::where('brand', 'România')->get();

foreach ($cities as $city) {
    $offer = Offer::where('product_id', $city->id)->first();
    
    if ($offer) {
        $offer->update([
            'price' => 0,
            'url_affiliate' => "https://ro.wikipedia.org/wiki/{$city->name}",
            'merchant' => 'Wikipedia',
        ]);
        echo "✓ Updated: {$city->name} - Wikipedia link\n";
    }
}

echo "\n✅ All city offers updated!\n";
echo "Cities now link to Wikipedia instead of showing prices.\n";
