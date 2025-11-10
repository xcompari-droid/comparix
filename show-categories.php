<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "📁 CATEGORII DISPONIBILE:\n";
echo "==========================\n\n";

$types = DB::table('product_types')
    ->select('id', 'name', 'slug')
    ->orderBy('id')
    ->get();

foreach($types as $type) {
    $count = DB::table('products')->where('product_type_id', $type->id)->count();
    echo "{$type->id}. {$type->name} ({$count} produse)\n";
    echo "   URL: http://localhost:8080/categorii/{$type->slug}\n\n";
}
