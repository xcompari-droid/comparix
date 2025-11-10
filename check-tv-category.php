<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CATEGORII EXISTENTE ===\n";
$types = App\Models\ProductType::all();
foreach ($types as $type) {
    $count = App\Models\Product::where('product_type_id', $type->id)->count();
    echo "ID {$type->id}: {$type->name} ({$count} produse)\n";
}

$tv = App\Models\ProductType::where('name', 'LIKE', '%TV%')->orWhere('name', 'LIKE', '%Televi%')->first();
if ($tv) {
    echo "\n✓ Categoria TV există: ID {$tv->id}\n";
} else {
    echo "\n❌ Nu există categoria TV - trebuie creată\n";
}
