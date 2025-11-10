<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\ProductType;

$type = ProductType::where('slug', 'masina-de-spalat')->first();
$machines = Product::where('product_type_id', $type->id)->orderBy('name')->get();

echo "Mașini de spălat - Verificare imagini:\n\n";

$placeholders = 0;
$real = 0;

foreach ($machines as $machine) {
    $isPlaceholder = strpos($machine->image_url, 'ui-avatars.com') !== false;
    
    if ($isPlaceholder) {
        echo "❌ PLACEHOLDER: {$machine->name}\n";
        echo "   URL: {$machine->image_url}\n\n";
        $placeholders++;
    } else {
        echo "✅ REAL: {$machine->name}\n";
        echo "   URL: {$machine->image_url}\n\n";
        $real++;
    }
}

echo "\n═══════════════════════════════\n";
echo "TOTAL: {$machines->count()} mașini\n";
echo "✅ Imagini reale: {$real}\n";
echo "❌ Placeholders: {$placeholders}\n";
echo "═══════════════════════════════\n";
