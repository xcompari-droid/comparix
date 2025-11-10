<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

echo "🧪 TEST UNSPLASH API\n";
echo "====================\n\n";

// Verifică dacă există API key
$apiKey = config('services.unsplash.access_key');

if (!$apiKey) {
    echo "❌ UNSPLASH_ACCESS_KEY nu este configurat în .env\n";
    echo "   Adaugă: UNSPLASH_ACCESS_KEY=your_key_here\n\n";
    exit(1);
}

echo "✅ API Key găsit: " . substr($apiKey, 0, 10) . "...\n\n";

// Test 1: Verifică că API-ul funcționează
echo "🔍 Test 1: Verificare conexiune API...\n";
try {
    $response = Http::get('https://api.unsplash.com/photos/random', [
        'client_id' => $apiKey,
        'query' => 'technology',
    ]);
    
    if ($response->successful()) {
        echo "   ✅ Conexiune OK (Status: {$response->status()})\n";
        $data = $response->json();
        echo "   📸 Imagine test: {$data['urls']['small']}\n";
        echo "   👤 Fotograf: {$data['user']['name']}\n\n";
    } else {
        echo "   ❌ Eroare: Status {$response->status()}\n";
        echo "   Response: " . $response->body() . "\n\n";
        exit(1);
    }
} catch (\Exception $e) {
    echo "   ❌ Excepție: {$e->getMessage()}\n\n";
    exit(1);
}

// Test 2: Caută imagini pentru produse reale
echo "🔍 Test 2: Căutare imagini produse...\n";
$queries = [
    'washing machine' => 'Mașină de spălat',
    'refrigerator' => 'Frigider',
    'wireless earbuds' => 'Căști wireless',
];

foreach ($queries as $query => $label) {
    echo "   • {$label} ({$query})... ";
    
    try {
        $response = Http::get('https://api.unsplash.com/search/photos', [
            'client_id' => $apiKey,
            'query' => $query,
            'per_page' => 3,
        ]);
        
        if ($response->successful()) {
            $results = $response->json()['results'] ?? [];
            echo "✅ " . count($results) . " imagini găsite\n";
            
            if (!empty($results)) {
                $first = $results[0];
                echo "      → {$first['urls']['small']}\n";
            }
        } else {
            echo "❌ Status {$response->status()}\n";
        }
    } catch (\Exception $e) {
        echo "❌ {$e->getMessage()}\n";
    }
    
    sleep(1); // Rate limiting
}

echo "\n";

// Test 3: Verifică storage
echo "🔍 Test 3: Verificare storage public...\n";
$storagePath = storage_path('app/public/products');

if (!file_exists($storagePath)) {
    echo "   ⚠️  Directorul {$storagePath} nu există\n";
    echo "   💡 Rulează: php artisan storage:link\n\n";
} else {
    echo "   ✅ Director storage găsit\n";
    
    // Verifică link simbolic
    $publicLink = public_path('storage');
    if (!file_exists($publicLink)) {
        echo "   ⚠️  Link simbolic lipsește\n";
        echo "   💡 Rulează: php artisan storage:link\n\n";
    } else {
        echo "   ✅ Link simbolic OK\n\n";
    }
}

// Test 4: Verifică rate limit
echo "🔍 Test 4: Verificare rate limit...\n";
try {
    $response = Http::get('https://api.unsplash.com/photos/random', [
        'client_id' => $apiKey,
    ]);
    
    $remaining = $response->header('X-Ratelimit-Remaining');
    $limit = $response->header('X-Ratelimit-Limit');
    
    if ($remaining && $limit) {
        echo "   ✅ Rate limit: {$remaining}/{$limit} requests rămase\n";
        
        if ($remaining < 10) {
            echo "   ⚠️  ATENȚIE: Doar {$remaining} requests rămase!\n";
        }
    } else {
        echo "   ⚠️  Nu s-au putut verifica limitele\n";
    }
} catch (\Exception $e) {
    echo "   ❌ {$e->getMessage()}\n";
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 REZULTATE TEST\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ API funcționează corect\n";
echo "✅ Poți rula: php import-unsplash-images.php\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
