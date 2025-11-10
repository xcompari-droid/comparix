<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

$tvs = Product::where('product_type_id', 8)->orderBy('name')->get(['name']);

echo "📺 TV-uri existente (" . $tvs->count() . "):\n\n";

foreach ($tvs as $tv) {
    echo "- {$tv->name}\n";
}
