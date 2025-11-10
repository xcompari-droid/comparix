<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Ștergere directă din DB...\n";

DB::table('spec_values')->whereIn('product_id', function($q) {
    $q->select('id')->from('products')->where('product_type_id', 7);
})->delete();

DB::table('offers')->whereIn('product_id', function($q) {
    $q->select('id')->from('products')->where('product_type_id', 7);
})->delete();

$deleted = DB::table('products')->where('product_type_id', 7)->delete();

echo "✅ Șters {$deleted} produse\n";
