<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== STATUS SMARTPHONE-URI ===\n\n";

$phones = App\Models\Product::where('product_type_id', 1)->get();

echo "Total smartphones: " . $phones->count() . "\n";
$avgSpecs = $phones->avg(function($p) { return $p->specValues->count(); });
echo "Media specs/telefon: " . number_format($avgSpecs, 1) . "\n";

$withSourceUrl = $phones->filter(function($p) { return !empty($p->source_url); })->count();
echo "Cu source_url: $withSourceUrl\n";

$withoutSourceUrl = $phones->filter(function($p) { return empty($p->source_url); })->count();
echo "Fără source_url: $withoutSourceUrl\n\n";

echo "=== PRIMELE 5 FĂRĂ SOURCE_URL ===\n";
$phonesWithoutUrl = $phones->filter(function($p) { return empty($p->source_url); })->take(5);
foreach ($phonesWithoutUrl as $phone) {
    echo "- {$phone->name} (ID: {$phone->id}, Specs: {$phone->specValues->count()})\n";
}
