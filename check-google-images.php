<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

$products = Product::where('image_url', 'LIKE', '%google-%')->get();

echo "\n✅ PRODUSE CU IMAGINI GOOGLE DESCĂRCATE ({$products->count()}):\n\n";

foreach ($products as $p) {
    echo "[{$p->id}] {$p->brand} {$p->name}\n";
    echo "  📸 Image: {$p->image_url}\n\n";
}
