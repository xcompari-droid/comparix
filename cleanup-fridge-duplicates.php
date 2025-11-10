<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\ProductType;

$type = ProductType::where('slug', 'frigider')->first();

// Get all fridges grouped by name
$fridges = Product::where('product_type_id', $type->id)
    ->orderBy('name')
    ->orderBy('id')
    ->get();

$kept = [];
$deleted = 0;

Product::withoutSyncingToSearch(function () use ($fridges, &$kept, &$deleted) {
    foreach ($fridges as $fridge) {
        // Keep the first occurrence of each name
        if (!isset($kept[$fridge->name])) {
            $kept[$fridge->name] = $fridge->id;
            echo "✓ Keeping: {$fridge->name} (ID: {$fridge->id})\n";
        } else {
            // Delete duplicate
            echo "✗ Deleting duplicate: {$fridge->name} (ID: {$fridge->id})\n";
            $fridge->delete();
            $deleted++;
        }
    }
});

echo "\n✅ Cleanup complete!\n";
echo "   Kept: " . count($kept) . " unique fridges\n";
echo "   Deleted: {$deleted} duplicates\n";
