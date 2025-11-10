<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\Importers\LGWashingRosterFetcher;

echo "=== TESTING LG WASHING MACHINE ROSTER FETCHER ===\n\n";

try {
    $fetcher = new LGWashingRosterFetcher();
    
    echo "Fetching 5 products for testing...\n\n";
    $products = $fetcher->fetch(5);
    
    echo "\n=== RESULTS ===\n\n";
    
    foreach ($products as $i => $product) {
        echo ($i + 1) . ". " . $product['name'] . "\n";
        echo "   Model: " . ($product['model'] ?? 'N/A') . "\n";
        echo "   URL: " . $product['url'] . "\n";
        echo "   Image: " . (isset($product['image']) && $product['image'] ? '✓' : '✗') . "\n";
        echo "\n";
    }
    
    echo "✓ Test completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
