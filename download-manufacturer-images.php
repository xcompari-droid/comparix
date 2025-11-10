<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "  DESCĂRCARE IMAGINI DE LA PRODUCĂTORI (FĂRĂ PERSOANE)\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$idsFile = __DIR__ . '/products-need-images.json';
if (!file_exists($idsFile)) {
    echo "❌ Rulează mai întâi: php find-products-no-images.php\n\n";
    exit;
}

$ids = json_decode(file_get_contents($idsFile), true);
echo "Procesez " . count($ids) . " produse...\n\n";

// Mapare URL-uri oficiale producători
$brandUrls = [
    'Samsung' => 'https://images.samsung.com/is/image/samsung/',
    'LG' => 'https://www.lg.com/content/dam/lge/country/product/',
    'Bosch' => 'https://media3.bosch-home.com/Product_Shots/',
    'Whirlpool' => 'https://www.whirlpool.eu/-/media/Digital-Assets/Whirlpool/',
    'Beko' => 'https://www.beko.com/content/dam/beko/',
    'Arctic' => 'https://www.arctic.ro/media/catalog/product/',
    'Candy' => 'https://candy-home.com/media/catalog/product/',
    'Electrolux' => 'https://www.electrolux.ro/globalassets/appliances/',
    'Gorenje' => 'https://www.gorenje.com/img/catalog/products/',
    'Indesit' => 'https://www.indesit.ro/content/dam/indesit/',
];

function downloadProductImage($product, $brandUrls) {
    $brand = $product->brand;
    
    if (!isset($brandUrls[$brand])) {
        echo "    ⚠️  Brand necunoscut: {$brand}\n";
        return null;
    }
    
    // Extragem codul modelului din nume
    $name = $product->name;
    
    // Pattern-uri comune pentru coduri model
    $patterns = [
        '/([A-Z]{2,}\d{3,}[A-Z0-9\-\/]+)/',  // WW90T554DAW, RB38A7B6AS9
        '/(\d{3,}[A-Z0-9\-\/]+)/',           // 554DAW, 7B6AS9
    ];
    
    $modelCode = null;
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $name, $matches)) {
            $modelCode = $matches[1];
            break;
        }
    }
    
    if (!$modelCode) {
        echo "    ⚠️  Nu s-a putut extrage cod model din: {$name}\n";
        return null;
    }
    
    echo "    Cod model: {$modelCode}\n";
    
    // Încercăm variante de URL
    $baseUrl = $brandUrls[$brand];
    $possibleUrls = [
        $baseUrl . strtoupper($modelCode) . '.png',
        $baseUrl . strtolower($modelCode) . '.png',
        $baseUrl . strtoupper($modelCode) . '.jpg',
        $baseUrl . strtolower($modelCode) . '.jpg',
        $baseUrl . str_replace(['/', '-'], ['_', '_'], strtoupper($modelCode)) . '.png',
        $baseUrl . str_replace(['/', '-'], ['_', '_'], strtolower($modelCode)) . '.png',
    ];
    
    foreach ($possibleUrls as $url) {
        try {
            $response = Http::timeout(10)->head($url);
            if ($response->successful()) {
                echo "    ✅ Găsită la: " . substr($url, 0, 60) . "...\n";
                
                // Descărcăm imaginea
                $imageData = Http::timeout(15)->get($url)->body();
                
                // Salvăm local
                $filename = "products/{$product->id}-" . time() . ".png";
                Storage::disk('public')->put($filename, $imageData);
                
                return "/storage/{$filename}";
            }
        } catch (\Exception $e) {
            // Continuăm cu următorul URL
            continue;
        }
    }
    
    echo "    ❌ Nu s-a găsit imagine la niciun URL\n";
    return null;
}

$success = 0;
$failed = 0;

foreach ($ids as $id) {
    $product = Product::find($id);
    if (!$product) continue;
    
    echo "\n[{$product->id}] {$product->brand} {$product->name}\n";
    
    $imageUrl = downloadProductImage($product, $brandUrls);
    
    if ($imageUrl) {
        $product->image_url = $imageUrl;
        $product->save();
        $success++;
    } else {
        $failed++;
    }
    
    usleep(1000000); // 1 secundă între produse
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "  REZULTATE:\n";
echo "  ✅ Succes: {$success}\n";
echo "  ❌ Eșuat: {$failed}\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

if ($failed > 0) {
    echo "ℹ️  Pentru produsele eșuate, poți încerca:\n";
    echo "   1. Căutare manuală pe site-ul producătorului\n";
    echo "   2. Google Custom Search API cu filtru 'product only'\n";
    echo "   3. eMAG/Altex scraping\n\n";
}
