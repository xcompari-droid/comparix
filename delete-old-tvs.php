<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

$count = Product::where('product_type_id', 8)->count();
echo "🗑️  Găsite {$count} televizoare vechi...\n";

Product::where('product_type_id', 8)->delete();

echo "✅ Televizoarele vechi au fost șterse.\n";
