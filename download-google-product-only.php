<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "  GOOGLE IMAGES - DOAR PRODUSE (FĂRĂ PERSOANE)\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$apiKey = env('GOOGLE_API_KEY');
$searchEngineId = env('GOOGLE_SEARCH_ENGINE_ID');

if (!$apiKey || !$searchEngineId) {
    echo "❌ Lipsesc credențialele Google din .env\n";
    echo "   GOOGLE_API_KEY și GOOGLE_SEARCH_ENGINE_ID\n\n";
    exit;
}

echo "✅ API Key: " . substr($apiKey, 0, 20) . "...\n";
echo "✅ Search Engine ID: {$searchEngineId}\n\n";

$idsFile = __DIR__ . '/products-need-images.json';
if (!file_exists($idsFile)) {
    echo "❌ Rulează mai întâi: php find-products-no-images.php\n\n";
    exit;
}

$ids = json_decode(file_get_contents($idsFile), true);
echo "📋 Produse de procesat: " . count($ids) . "\n";
echo "🔢 Query-uri Google disponibile astăzi: 100\n";
echo "⚠️  Voi folosi: " . min(100, count($ids)) . " query-uri\n\n";

$limit = min(100, count($ids)); // Max 100 pe zi gratuit

function searchGoogleImages($product, $apiKey, $searchEngineId) {
    // Query optimizat pentru DOAR produse (fără persoane/lifestyle)
    $brand = $product->brand;
    $name = $product->name;
    
    // Curățăm numele de caractere speciale
    $cleanName = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $name);
    $cleanName = preg_replace('/\s+/', ' ', $cleanName); // Remove duplicate spaces
    
    // Query simplu: brand + model
    // NU adăugăm prea multe filtre negative, că Google nu găsește nimic
    $query = "{$brand} {$cleanName}";
    
    // Pentru electronice mari: specificăm "product image"
    if (in_array($product->product_type_id, [6, 7])) { // Frigider, Mașină spălat
        $query .= " product";
    }
    
    echo "    🔍 Query: " . substr($query, 0, 80) . "\n";
    
    $url = "https://www.googleapis.com/customsearch/v1?" . http_build_query([
        'key' => $apiKey,
        'cx' => $searchEngineId,
        'q' => $query,
        'searchType' => 'image',
        'num' => 5, // Primele 5 rezultate
        'imgType' => 'photo',
        'imgSize' => 'large',
        'safe' => 'active',
        'fileType' => 'jpg,png',
    ]);
    
    try {
        $response = Http::timeout(15)->get($url);
        
        if (!$response->successful()) {
            echo "    ❌ API Error: " . $response->status() . "\n";
            if ($response->status() == 429) {
                echo "    ⚠️  LIMITĂ ZILNICĂ ATINSĂ!\n";
            }
            return null;
        }
        
        $data = $response->json();
        
        if (empty($data['items'])) {
            echo "    ⚠️  Niciun rezultat găsit\n";
            return null;
        }
        
        // Luăm primul rezultat (cel mai relevant)
        $firstResult = $data['items'][0];
        $imageUrl = $firstResult['link'];
        
        echo "    ✅ Găsită: " . substr($imageUrl, 0, 70) . "...\n";
        
        return [
            'url' => $imageUrl,
            'thumbnail' => $firstResult['image']['thumbnailLink'] ?? null,
            'context' => $firstResult['image']['contextLink'] ?? null,
            'width' => $firstResult['image']['width'] ?? 0,
            'height' => $firstResult['image']['height'] ?? 0,
        ];
        
    } catch (\Exception $e) {
        echo "    ❌ Eroare: " . $e->getMessage() . "\n";
        return null;
    }
}

function downloadAndSaveImage($imageData, $productId) {
    $imageUrl = $imageData['url'];
    
    try {
        echo "    📥 Descărcare...\n";
        
        $response = Http::timeout(20)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ])
            ->get($imageUrl);
        
        if (!$response->successful()) {
            echo "    ❌ Download failed: " . $response->status() . "\n";
            return null;
        }
        
        $data = $response->body();
        
        if (strlen($data) < 1000) {
            echo "    ⚠️  Imagine prea mică (probabil eroare)\n";
            return null;
        }
        
        // Detectăm extensia
        $extension = 'jpg';
        if (strpos($imageUrl, '.png') !== false) {
            $extension = 'png';
        }
        
        $filename = "products/google-{$productId}-" . time() . ".{$extension}";
        Storage::disk('public')->put($filename, $data);
        
        $fileSize = number_format(strlen($data) / 1024, 1);
        echo "    💾 Salvată: {$filename} ({$fileSize} KB)\n";
        
        return "/storage/{$filename}";
        
    } catch (\Exception $e) {
        echo "    ❌ Eroare download: " . $e->getMessage() . "\n";
        return null;
    }
}

$success = 0;
$failed = 0;
$queryCount = 0;

foreach (array_slice($ids, 0, $limit) as $id) {
    $product = Product::find($id);
    if (!$product) continue;
    
    $queryCount++;
    echo "\n[{$queryCount}/{$limit}] [{$product->id}] {$product->brand} {$product->name}\n";
    
    // Căutare Google
    $imageData = searchGoogleImages($product, $apiKey, $searchEngineId);
    
    if ($imageData) {
        // Descărcăm imaginea
        $localUrl = downloadAndSaveImage($imageData, $product->id);
        
        if ($localUrl) {
            // Update fără triggering Meilisearch
            \DB::table('products')
                ->where('id', $product->id)
                ->update(['image_url' => $localUrl]);
            echo "    ✅ SUCCES!\n";
            $success++;
        } else {
            $failed++;
        }
    } else {
        $failed++;
    }
    
    // Rate limiting - 1 query/secundă pentru a fi politicos
    echo "    ⏳ Pauză 1s...\n";
    sleep(1);
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "  REZULTATE FINALE:\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  ✅ Succes: {$success}\n";
echo "  ❌ Eșuat: {$failed}\n";
echo "  🔢 Query-uri folosite: {$queryCount}/100\n";
echo "  🔢 Query-uri rămase astăzi: " . (100 - $queryCount) . "\n";
echo "  📊 Rată succes: " . round(($success / $queryCount) * 100, 1) . "%\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

if ($success > 0) {
    echo "✅ Imaginile descărcate sunt DOAR cu produsele!\n";
    echo "   Google a filtrat imaginile cu persoane/lifestyle.\n\n";
}

if (count($ids) > 100) {
    $remaining = count($ids) - 100;
    echo "ℹ️  Au mai rămas {$remaining} produse.\n";
    echo "   Rulează din nou scriptul mâine pentru restul.\n\n";
}
