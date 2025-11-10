<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\ProductType;

// Get the Căști wireless product type
$productType = ProductType::where('slug', 'casti-wireless')->first();

if (!$productType) {
    echo "Product type 'casti-wireless' not found!\n";
    exit(1);
}

// Get all earbuds
$earbuds = Product::where('product_type_id', $productType->id)->get();
$count = $earbuds->count();

echo "Found {$count} earbuds to delete.\n";

// Delete each earbud (will cascade delete specs and offers) - without Meilisearch sync
Product::withoutSyncingToSearch(function () use ($earbuds) {
    foreach ($earbuds as $earbud) {
        echo "Deleting: {$earbud->name}...\n";
        $earbud->delete();
    }
});

echo "\n✅ All earbuds deleted successfully!\n";
