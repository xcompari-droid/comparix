<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "  DESCĂRCARE IMAGINI DE PE ALTEX (DOAR PRODUSE)\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$idsFile = __DIR__ . '/products-need-images.json';
if (!file_exists($idsFile)) {
    echo "❌ Rulează mai întâi: php find-products-no-images.php\n\n";
    exit;
}

$ids = json_decode(file_get_contents($idsFile), true);

// Verificăm care încă nu au imagini (nu au fost găsite de Google/eMAG)
$remainingProducts = Product::whereIn('id', $ids)
    ->where(function($q) {
        $q->whereNull('image_url')
          ->orWhere('image_url', 'LIKE', '%placeholder%')
          ->orWhere('image_url', 'LIKE', '%picsum%');
    })
    ->get();

echo "Produse rămase fără imagini: " . $remainingProducts->count() . "\n\n";

if ($remainingProducts->isEmpty()) {
    echo "✅ Toate produsele au imagini!\n\n";
    exit;
}

function searchAltexForImage($product) {
    $query = urlencode("{$product->brand} {$product->name}");
    $searchUrl = "https://altex.ro/cauta/?q={$query}";
    
    echo "    🔍 Căutare: {$searchUrl}\n";
    
    try {
        $response = Http::timeout(15)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ])
            ->get($searchUrl);
        
        if (!$response->successful()) {
            return null;
        }
        
        $html = $response->body();
        
        // Altex folosește imagini CDN
        $patterns = [
            '/<img[^>]+src="(https:\/\/[^"]*altex[^"]*\/image[^"]+)"/',
            '/<img[^>]+data-src="(https:\/\/[^"]*altex[^"]+)"/',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $imageUrl = $matches[1];
                
                // Convertim la rezoluție mare
                $imageUrl = preg_replace('/\/w_\d+/', '/w_800', $imageUrl);
                $imageUrl = preg_replace('/\/h_\d+/', '/h_800', $imageUrl);
                
                echo "    ✅ Găsită: " . substr($imageUrl, 0, 70) . "...\n";
                return $imageUrl;
            }
        }
        
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
            return null;
        }
        
        $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
        $filename = "products/altex-{$productId}-" . time() . ".{$extension}";
        
        Storage::disk('public')->put($filename, $imageData);
        
        $fileSize = number_format(strlen($imageData) / 1024, 1);
        echo "    💾 Salvată: {$filename} ({$fileSize} KB)\n";
        
        return "/storage/{$filename}";
        
    } catch (\Exception $e) {
        return null;
    }
}

$success = 0;
$failed = 0;

foreach ($remainingProducts as $product) {
    echo "\n[{$product->id}] {$product->brand} {$product->name}\n";
    
    $imageUrl = searchAltexForImage($product);
    
    if ($imageUrl) {
        $localUrl = downloadAndSaveImage($imageUrl, $product->id);
        
        if ($localUrl) {
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
    
    echo "    ⏳ Pauză 3s...\n";
    sleep(3);
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "  REZULTATE:\n";
echo "  ✅ Succes: {$success}\n";
echo "  ❌ Eșuat: {$failed}\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
