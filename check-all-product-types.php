<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$productTypes = DB::table('product_types')->get(['id', 'name', 'slug', 'category_id']);

echo "All product types:\n";
foreach ($productTypes as $type) {
    $count = DB::table('products')->where('product_type_id', $type->id)->count();
    echo "  ID: {$type->id} | Name: {$type->name} | Slug: {$type->slug} | Products: {$count}\n";
}
