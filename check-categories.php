<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;

echo "📊 Categories in database:\n\n";

$categories = Category::all();
foreach ($categories as $cat) {
    echo "  - {$cat->name}\n";
    echo "    Slug: {$cat->slug}\n";
    echo "    URL: /categorii/{$cat->slug}\n";
    echo "    Products: " . \App\Models\Product::where('category_id', $cat->id)->count() . "\n\n";
}
