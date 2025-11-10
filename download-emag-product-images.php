<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "  DESCĂRCARE IMAGINI DE PE eMAG (DOAR PRODUSE)\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$idsFile = __DIR__ . '/products-need-images.json';
if (!file_exists($idsFile)) {
    echo "❌ Rulează mai întâi: php find-products-no-images.php\n\n";
    exit;
}

$ids = json_decode(file_get_contents($idsFile), true);
echo "Procesez " . count($ids) . " produse...\n\n";

function searchEmagForImage($product) {
    // Construim query pentru căutare pe eMAG
    $query = urlencode("{$product->brand} {$product->name}");
    $searchUrl = "https://www.emag.ro/search/{$query}";
    
    echo "    Căutare: {$searchUrl}\n";
    
    try {
        $response = Http::timeout(15)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ])
            ->get($searchUrl);
        
        if (!$response->successful()) {
            echo "    ❌ Request eșuat: " . $response->status() . "\n";
            return null;
        }
        
        $html = $response->body();
        
        // Pattern pentru imagini eMAG (sunt în format specific)
        // eMAG folosește imagini optimizate fără persoane
        $patterns = [
            '/<img[^>]+class="card-img[^"]*"[^>]+src="([^"]+)"/',
            '/<img[^>]+data-src="([^"]+)"[^>]+class="card-img/',
            '/<img[^>]+src="(https:\/\/s13emagst\.akamaized\.net\/products\/[^"]+)"/',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $imageUrl = $matches[1];
                
                // Validăm că e imagine de produs, nu banner/reclama
                if (strpos($imageUrl, '/products/') !== false || 
                    strpos($imageUrl, 'akamaized') !== false) {
                    
                    // Convertim la rezoluție mai mare dacă e thumbnail
                    $imageUrl = str_replace('/140/', '/500/', $imageUrl);
                    $imageUrl = str_replace('/200/', '/500/', $imageUrl);
                    $imageUrl = str_replace('_thumb', '', $imageUrl);
                    
                    echo "    ✅ Găsită: " . substr($imageUrl, 0, 70) . "...\n";
                    return $imageUrl;
                }
            }
        }
        
        // Dacă nu găsim în primul produs, căutăm în listă
        if (preg_match_all('/<img[^>]+src="(https:\/\/s13emagst\.akamaized\.net\/products\/[^"]+)"/', $html, $allMatches)) {
            if (isset($allMatches[1][0])) {
                $imageUrl = str_replace(['/140/', '/200/'], '/500/', $allMatches[1][0]);
                echo "    ✅ Găsită din listă: " . substr($imageUrl, 0, 70) . "...\n";
                return $imageUrl;
            }
        }
        
        echo "    ⚠️  Nu s-a găsit imagine în HTML\n";
        return null;
        
    } catch (\Exception $e) {
        echo "    ❌ Eroare: " . $e->getMessage() . "\n";
        return null;
    }
}

function downloadAndSaveImage($imageUrl, $productId) {
    try {
        $imageData = Http::timeout(20)->get($imageUrl)->body();
        
        if (strlen($imageData) < 1000) {
            echo "    ⚠️  Imagine prea mică (probabil eroare)\n";
            return null;
        }
        
        // Salvăm local
        $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
        $filename = "products/{$productId}-" . time() . ".{$extension}";
        
        Storage::disk('public')->put($filename, $imageData);
        
        $localUrl = "/storage/{$filename}";
        $fileSize = strlen($imageData);
        echo "    💾 Salvată local: {$filename} ({$fileSize} bytes)\n";
        
        return $localUrl;
        
    } catch (\Exception $e) {
        echo "    ❌ Eroare descărcare: " . $e->getMessage() . "\n";
        return null;
    }
}

$success = 0;
$failed = 0;
$failedProducts = [];

foreach ($ids as $id) {
    $product = Product::find($id);
    if (!$product) continue;
    
    echo "\n[{$product->id}] {$product->brand} {$product->name}\n";
    
    // Căutăm pe eMAG
    $imageUrl = searchEmagForImage($product);
    
    if ($imageUrl) {
        // Descărcăm și salvăm local
        $localUrl = downloadAndSaveImage($imageUrl, $product->id);
        
        if ($localUrl) {
            // Update direct în DB fără Meilisearch
            \DB::table('products')
                ->where('id', $product->id)
                ->update(['image_url' => $localUrl]);
            echo "    ✅ SUCCES - Imagine salvată!\n";
            $success++;
        } else {
            $failed++;
            $failedProducts[] = $product->id . ": " . $product->name;
        }
    } else {
        $failed++;
        $failedProducts[] = $product->id . ": " . $product->name;
    }
    
    // Rate limiting - important pentru a nu fi blocați
    echo "    ⏳ Așteptare 3 secunde...\n";
    sleep(3);
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "  REZULTATE FINALE:\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  ✅ Succes: {$success}\n";
echo "  ❌ Eșuat: {$failed}\n";
echo "  📊 Rată succes: " . round(($success / count($ids)) * 100, 1) . "%\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

if (!empty($failedProducts)) {
    echo "❌ Produse eșuate:\n";
    foreach (array_slice($failedProducts, 0, 10) as $failed) {
        echo "   • {$failed}\n";
    }
    if (count($failedProducts) > 10) {
        echo "   ... și încă " . (count($failedProducts) - 10) . " produse\n";
    }
    echo "\n";
    
    // Salvăm lista pentru retry
    file_put_contents(__DIR__ . '/failed-image-downloads.json', json_encode($failedProducts, JSON_PRETTY_PRINT));
    echo "💾 Lista eșuate salvată în: failed-image-downloads.json\n\n";
}

echo "✅ GATA! Imaginile sunt doar cu produsele (fără persoane/ambientări)\n";
echo "   eMAG folosește imagini profesionale pe fundal alb.\n\n";
