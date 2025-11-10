<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tvs = App\Models\Product::where('product_type_id', 8)->get();

echo "=== STATUS TELEVIZOARE ===\n";
echo "Total TV-uri: " . $tvs->count() . "\n\n";

// Stats pentru imagini
$withReal = 0;
$withPlaceholder = 0;

foreach ($tvs as $tv) {
    if (str_contains($tv->image_url, 'ui-avatars.com')) {
        $withPlaceholder++;
    } else {
        $withReal++;
    }
}

echo "=== IMAGINI ===\n";
echo "Cu imagini reale: $withReal/" . $tvs->count() . " (" . round(($withReal / $tvs->count()) * 100, 1) . "%)\n";
echo "Cu placeholder: $withPlaceholder\n\n";

// Stats pentru specs
$totalSpecs = 0;
foreach ($tvs as $tv) {
    $totalSpecs += $tv->specValues()->count();
}

$avgSpecs = $totalSpecs / $tvs->count();

echo "=== SPECIFICAȚII ===\n";
echo "Total specs: $totalSpecs\n";
echo "Media specs/TV: " . round($avgSpecs, 1) . "\n\n";

// Source URLs
$withSource = App\Models\Product::where('product_type_id', 8)
    ->whereNotNull('source_url')
    ->where('source_url', '!=', '')
    ->count();

echo "=== SOURCE URLs ===\n";
echo "Cu source_url: $withSource/" . $tvs->count() . " (" . round(($withSource / $tvs->count()) * 100, 1) . "%)\n\n";

// Sample
echo "=== PRIMELE 5 TV-URI ===\n";
foreach ($tvs->take(5) as $tv) {
    $specs = $tv->specValues()->count();
    $hasImage = !str_contains($tv->image_url, 'ui-avatars.com') ? '✅' : '❌';
    echo "- {$tv->name} ($specs specs) $hasImage\n";
}
