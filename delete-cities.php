<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\Category;

echo "🗑️  Deleting cities...\n\n";

$orase = Category::where('slug', 'orase')->first();
if ($orase) {
    $deleted = Product::where('category_id', $orase->id)->delete();
    echo "Deleted {$deleted} cities\n";
}

echo "✅ Done! Ready to re-import.\n";
