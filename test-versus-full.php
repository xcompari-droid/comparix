<?php

// Test direct dacă VersusDemo poate fi randată
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🧪 TESTARE VERSUS COMPARE\n";
echo "========================\n\n";

try {
    // Test controller
    $controller = new App\Http\Controllers\VersusCompareController();
    $result = $controller->demo();
    
    echo "✅ Controller funcționează\n";
    echo "Tip: " . get_class($result) . "\n";
    
    if (method_exists($result, 'getData')) {
        $data = $result->getData();
        echo "\n📊 Date:\n";
        echo "  Items: " . count($data['items'] ?? []) . "\n";
        echo "  Metrics: " . count($data['metricDefinitions'] ?? []) . "\n\n";
        
        // Verifică structura datelor
        if (!empty($data['items'])) {
            $item = $data['items'][0];
            echo "📝 Primul item:\n";
            echo "  Name: " . ($item['name'] ?? 'LIPSĂ') . "\n";
            echo "  Color: " . ($item['color'] ?? 'LIPSĂ') . "\n";
            echo "  Metrics count: " . count($item['metrics'] ?? []) . "\n";
            
            if (!empty($item['metrics'])) {
                echo "\n  Primele 3 metrics:\n";
                $count = 0;
                foreach ($item['metrics'] as $key => $value) {
                    echo "    - $key: $value\n";
                    if (++$count >= 3) break;
                }
            }
        }
        
        echo "\n📐 Metric Definitions:\n";
        if (!empty($data['metricDefinitions'])) {
            foreach ($data['metricDefinitions'] as $idx => $metric) {
                echo "  " . ($idx + 1) . ". " . ($metric['label'] ?? 'N/A');
                echo " (" . ($metric['key'] ?? 'N/A') . ")";
                echo " - direction: " . ($metric['direction'] ?? 'N/A') . "\n";
                if ($idx >= 4) {
                    echo "  ... și " . (count($data['metricDefinitions']) - 5) . " mai mult\n";
                    break;
                }
            }
        }
    }
    
    // Verifică dacă VersusDemo.vue există în manifest
    echo "\n\n🔍 VERIFICARE MANIFEST\n";
    echo "========================\n";
    
    $manifestPath = __DIR__ . '/public/build/manifest.json';
    if (file_exists($manifestPath)) {
        $manifest = json_decode(file_get_contents($manifestPath), true);
        
        $versusKey = 'resources/js/Pages/Compare/VersusDemo.vue';
        if (isset($manifest[$versusKey])) {
            echo "✅ VersusDemo.vue în manifest:\n";
            echo "  File: " . $manifest[$versusKey]['file'] . "\n";
            
            $assetPath = __DIR__ . '/public/build/' . $manifest[$versusKey]['file'];
            if (file_exists($assetPath)) {
                $size = filesize($assetPath);
                echo "  Size: " . number_format($size / 1024, 2) . " KB\n";
                echo "  ✅ Asset file EXISTS\n";
            } else {
                echo "  ❌ Asset file MISSING: " . $assetPath . "\n";
            }
        } else {
            echo "❌ VersusDemo.vue NU este în manifest!\n";
            echo "Chei disponibile:\n";
            foreach (array_keys($manifest) as $key) {
                if (strpos($key, 'Compare') !== false || strpos($key, 'Versus') !== false) {
                    echo "  - $key\n";
                }
            }
        }
    } else {
        echo "❌ Manifest lipsește: $manifestPath\n";
    }
    
    echo "\n✅ TOTUL OK - Pagina ar trebui să funcționeze!\n";
    
} catch (Exception $e) {
    echo "❌ EROARE:\n";
    echo "Mesaj: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
