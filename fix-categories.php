<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\Category;

echo "🔧 Fixing category_id for cities...\n\n";

$orase = Category::where('slug', 'orase')->first();
echo "Orașe category ID: {$orase->id}\n\n";

Product::withoutSyncingToSearch(function() use ($orase) {
    $updated = Product::where('brand', 'România')->update(['category_id' => $orase->id]);
    echo "Updated {$updated} cities with category_id = {$orase->id}\n";
});

// Also fix smartphones
$electronice = Category::where('slug', 'electronice')->first();
if ($electronice) {
    Product::withoutSyncingToSearch(function() use ($electronice) {
        $updated = Product::whereIn('brand', ['Samsung', 'OPPO', 'Huawei', 'Apple'])
            ->update(['category_id' => $electronice->id]);
        echo "Updated {$updated} smartphones with category_id = {$electronice->id}\n";
    });
}

echo "\n✅ Category IDs fixed!\n";
