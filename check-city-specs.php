<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\SpecValue;

echo "🔍 Checking city specifications...\n\n";

$city = Product::where('brand', 'România')->where('name', 'București')->first();

if ($city) {
    echo "City: {$city->name}\n";
    echo "Short desc: {$city->short_desc}\n\n";
    
    echo "Specifications:\n";
    $specs = SpecValue::where('product_id', $city->id)
        ->with('specKey')
        ->get();
    
    echo "Total specs: " . $specs->count() . "\n\n";
    
    foreach ($specs as $spec) {
        $value = $spec->value_number ?? $spec->value_string ?? 'EMPTY';
        $unit = $spec->specKey->unit ?? '';
        echo "  - {$spec->specKey->name}: {$value} {$unit}\n";
    }
    
    if ($specs->count() === 0) {
        echo "  ⚠️  NO SPECIFICATIONS FOUND!\n";
    }
} else {
    echo "❌ City not found!\n";
}
