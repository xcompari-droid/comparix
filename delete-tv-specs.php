<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Ștergem specificațiile vechi în engleză pentru TV-uri...\n\n";

// Ștergem spec_values pentru TV-uri (product_type_id = 8)
$deletedValues = DB::table('spec_values')
    ->whereIn('spec_key_id', function($query) {
        $query->select('id')
            ->from('spec_keys')
            ->where('product_type_id', 8);
    })
    ->delete();

echo "✓ Șterse $deletedValues spec_values\n";

// Ștergem spec_keys pentru TV-uri
$deletedKeys = DB::table('spec_keys')
    ->where('product_type_id', 8)
    ->delete();

echo "✓ Șterse $deletedKeys spec_keys\n\n";
echo "Gata! Acum poți rula: php artisan import:versus-tvs --limit=150\n";
