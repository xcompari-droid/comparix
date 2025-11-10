<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$phones = App\Models\Product::where('product_type_id', 1)->get();

$withReal = 0;
$withPlaceholder = 0;

foreach ($phones as $phone) {
    if (str_contains($phone->image_url, 'ui-avatars.com')) {
        $withPlaceholder++;
    } else {
        $withReal++;
    }
}

echo "=== STATUS IMAGINI TELEFOANE ===\n";
echo "Total telefoane: " . $phones->count() . "\n";
echo "Cu imagini reale: $withReal\n";
echo "Cu placeholder: $withPlaceholder\n";
echo "Procent real: " . round(($withReal / $phones->count()) * 100, 1) . "%\n\n";

echo "=== PRIMELE 10 FĂRĂ IMAGINE ===\n";
$noImage = App\Models\Product::where('product_type_id', 1)
    ->where('image_url', 'LIKE', '%ui-avatars.com%')
    ->limit(10)
    ->get();
    
foreach ($noImage as $phone) {
    echo "- {$phone->name} (ID: {$phone->id})\n";
}
