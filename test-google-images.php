<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\GoogleImageService;

echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║          TEST GOOGLE CUSTOM SEARCH API - Imagini Produse      ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Check configuration
$apiKey = config('services.google.api_key');
$searchEngineId = config('services.google.search_engine_id');

if (empty($apiKey) || empty($searchEngineId)) {
    echo "❌ EROARE: Lipsesc credentialele Google!\n\n";
    echo "Adaugă în .env:\n";
    echo "GOOGLE_API_KEY=AIzaSyC...\n";
    echo "GOOGLE_SEARCH_ENGINE_ID=017576662...\n\n";
    echo "Vezi GOOGLE-IMAGES-SETUP.md pentru instrucțiuni complete.\n\n";
    exit(1);
}

echo "✅ API Key găsit: " . substr($apiKey, 0, 20) . "...\n";
echo "✅ Search Engine ID: " . substr($searchEngineId, 0, 20) . "...\n\n";

// Test products
$testProducts = [
    ['name' => 'Samsung RB38A7B6AS9/EF', 'category' => 'frigider'],
    ['name' => 'LG GBB72NSDFN', 'category' => 'frigider'],
    ['name' => 'iPhone 15 Pro Max', 'category' => 'smartphone'],
];

$service = new GoogleImageService();

echo "═══════════════════════════════════════════════════════════════\n";
echo "TEST 1: Căutare Imagini Produse\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$successCount = 0;
$failCount = 0;

foreach ($testProducts as $product) {
    echo "🔍 Căutare: {$product['name']} ({$product['category']})\n";
    
    $result = $service->searchProductImage($product['name'], $product['category']);
    
    if ($result) {
        $successCount++;
        echo "   ✅ Găsit!\n";
        echo "   📷 URL: " . substr($result['url'], 0, 60) . "...\n";
        echo "   📐 Dimensiuni: {$result['width']}x{$result['height']}px\n";
        echo "   ⭐ Scor: {$result['score']}/100\n";
        echo "   📝 Title: " . substr($result['title'], 0, 50) . "...\n";
    } else {
        $failCount++;
        echo "   ❌ Nu s-au găsit imagini\n";
    }
    echo "\n";
    
    // Delay to respect rate limits
    sleep(1);
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "REZULTATE TEST\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "✅ Succese: $successCount / " . count($testProducts) . "\n";
echo "❌ Eșecuri: $failCount / " . count($testProducts) . "\n\n";

if ($successCount > 0) {
    echo "🎉 Google Custom Search API funcționează!\n\n";
    
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "NEXT STEPS\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";
    
    echo "1. Rulează import complet:\n";
    echo "   php import-google-images.php\n\n";
    
    echo "2. Rate Limits:\n";
    echo "   - 100 query/zi GRATUIT\n";
    echo "   - Pentru 284 produse = 3 zile (gratis)\n";
    echo "   - Sau 100/zi dacă vrei rapid\n\n";
    
    echo "3. Cost după primele 100:\n";
    echo "   - \$5 per 1000 queries\n";
    echo "   - 284 produse = ~\$1.42 (dacă depășești limita)\n\n";
} else {
    echo "⚠️  Nu s-au găsit imagini. Verifică:\n";
    echo "   1. API Key-ul este corect?\n";
    echo "   2. Custom Search Engine are 'Image Search' activat?\n";
    echo "   3. 'Search the entire web' este ON?\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n\n";
