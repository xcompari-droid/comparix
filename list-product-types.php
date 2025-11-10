<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ProductType;

$types = ProductType::all();
foreach ($types as $t) {
    echo "{$t->id}: {$t->slug} ({$t->name})\n";
}
