<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;

echo "=== CHECKING FRIDGE IMAGES ===\n\n";

$fridges = Product::where('product_type_id', 6)->get();

echo "Total fridges: " . $fridges->count() . "\n\n";

foreach ($fridges as $fridge) {
    echo "Product: {$fridge->name}\n";
    echo "  ID: {$fridge->id}\n";
    echo "  Image URL: " . ($fridge->image_url ?: 'NULL') . "\n";
    echo "  Offers: " . $fridge->offers->count() . "\n";
    
    if ($fridge->offers->count() > 0) {
        $firstOffer = $fridge->offers->first();
        echo "  First offer image: " . ($firstOffer->image_url ?: 'NULL') . "\n";
        echo "  First offer URL: " . ($firstOffer->url ?: 'NULL') . "\n";
    }
    
    echo "\n";
}

echo "\n=== END ===\n";
