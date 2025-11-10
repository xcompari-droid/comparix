<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $controller = new App\Http\Controllers\VersusCompareController();
    $result = $controller->demo();
    
    echo "✅ Controller funcționează!\n";
    echo "Tip rezultat: " . get_class($result) . "\n";
    
    if (method_exists($result, 'getData')) {
        $data = $result->getData();
        echo "\n📊 Date returnate:\n";
        echo "  - Items: " . count($data['items'] ?? []) . "\n";
        echo "  - Metric definitions: " . count($data['metricDefinitions'] ?? []) . "\n";
        
        if (!empty($data['items'])) {
            echo "\n🔍 Primul item:\n";
            $item = $data['items'][0];
            echo "  - Name: " . ($item['name'] ?? 'N/A') . "\n";
            echo "  - Brand: " . ($item['brand'] ?? 'N/A') . "\n";
            echo "  - Metrics: " . count($item['metrics'] ?? []) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ EROARE în controller:\n";
    echo "Mesaj: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
