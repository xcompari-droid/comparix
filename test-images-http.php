<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\ProductType;

// Test pentru fiecare categorie
$categories = [
    'masina-de-spalat' => 'Mașini de spălat',
    'smartwatch' => 'Smartwatch-uri',
    'casti-wireless' => 'Căști wireless',
    'frigider' => 'Frigidere'
];

foreach ($categories as $slug => $name) {
    $type = ProductType::where('slug', $slug)->first();
    if (!$type) continue;
    
    $product = Product::where('product_type_id', $type->id)->first();
    
    echo "\n{$name}:\n";
    echo "  Produs: {$product->name}\n";
    echo "  Image URL: {$product->image_url}\n";
    
    // Verifică dacă URL-ul e valid
    $headers = @get_headers($product->image_url);
    if ($headers && strpos($headers[0], '200') !== false) {
        echo "  Status: ✅ OK (imagine accesibilă)\n";
    } else {
        echo "  Status: ❌ FAIL (imagine nu se încarcă)\n";
    }
}
