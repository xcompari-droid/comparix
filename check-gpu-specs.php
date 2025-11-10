<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\SpecValue;

echo "=== VERIFICARE SPECIFICAȚII GPU ===\n\n";

$gpus = Product::whereHas('productType', function($q) {
    $q->where('name', 'Placă video');
})->get();

echo "Total GPU-uri: " . $gpus->count() . "\n\n";

foreach ($gpus->take(10) as $gpu) {
    $specCount = SpecValue::where('product_id', $gpu->id)->count();
    $hasImage = !str_contains($gpu->image_url, 'ui-avatars');
    $imageIcon = $hasImage ? '✓' : '✗';
    
    echo "[{$imageIcon}] {$gpu->name}\n";
    echo "    Brand: {$gpu->brand}\n";
    echo "    Specificații: {$specCount}\n";
    echo "    Image: " . ($hasImage ? 'Real' : 'Placeholder') . "\n\n";
}

$totalSpecs = SpecValue::whereIn('product_id', $gpus->pluck('id'))->count();
echo "Total specificații salvate pentru toate GPU-urile: {$totalSpecs}\n";
