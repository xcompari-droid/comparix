<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\Importers\VersusGPUImporter;
use App\Models\Product;
use App\Models\SpecValue;

echo "=== Testing Enhanced Puppeteer GPU Import ===\n\n";

// Test with RTX 5090 (we know this has many specs)
$testGPU = [
    'name' => 'NVIDIA GeForce RTX 5090',
    'slug' => 'nvidia-geforce-rtx-5090',
    'url' => 'https://versus.com/en/nvidia-geforce-rtx-5090'
];

echo "Testing with: {$testGPU['name']}\n";
echo "URL: {$testGPU['url']}\n\n";

try {
    $importer = new VersusGPUImporter();
    
    // Use reflection to call protected method
    $reflection = new ReflectionClass($importer);
    $method = $reflection->getMethod('scrapeGPUSpecs');
    $method->setAccessible(true);
    
    $specs = $method->invoke($importer, $testGPU['url']);
    
    echo "✓ Scraping completed!\n\n";
    echo "Found " . count($specs) . " specifications:\n";
    echo str_repeat('-', 60) . "\n";
    
    foreach ($specs as $key => $value) {
        $displayValue = is_array($value) ? json_encode($value) : $value;
        if (strlen($displayValue) > 50) {
            $displayValue = substr($displayValue, 0, 50) . '...';
        }
        echo sprintf("%-30s : %s\n", $key, $displayValue);
    }
    
    echo "\n" . str_repeat('=', 60) . "\n";
    
    // Check if product exists and count its specs
    $product = Product::where('name', $testGPU['name'])->first();
    if ($product) {
        $specCount = SpecValue::where('product_id', $product->id)->count();
        echo "\n✓ Product found in database\n";
        echo "  ID: {$product->id}\n";
        echo "  Name: {$product->name}\n";
        echo "  Specifications: {$specCount}\n";
    } else {
        echo "\n⚠ Product not yet in database (run full import to add it)\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
