<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Services\GoogleImageService;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║   IMPORT GOOGLE IMAGES - TEST (10 Produse)              ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

$service = new GoogleImageService();

// Găsește primele 10 produse fără imagini reale (au placeholder sau unsplash)
$products = Product::where(function($query) {
    $query->whereNull('image_url')
          ->orWhere('image_url', 'LIKE', '%unsplash%')
          ->orWhere('image_url', 'LIKE', '%placeholder%');
})
->with('productType')
->limit(10)
->get();

echo "📦 Găsite " . count($products) . " produse pentru import\n\n";

$success = 0;
$failed = 0;
$skipped = 0;

foreach ($products as $index => $product) {
    $num = $index + 1;
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "[$num/10] {$product->name}\n";
    echo "       Categorie: {$product->productType->name}\n";
    echo "       Current: " . ($product->image_url ?? 'NULL') . "\n\n";
    
    try {
        // Caută imagine
        $result = $service->searchProductImage(
            $product->name,
            $product->productType->slug
        );
        
        if ($result) {
            echo "   ✅ Găsită imagine:\n";
            echo "      URL: {$result['url']}\n";
            echo "      Dimensiuni: {$result['width']}x{$result['height']}px\n";
            echo "      Scor: {$result['score']}/100\n";
            echo "      Title: {$result['title']}\n\n";
            
            // Descarcă și salvează
            echo "   ⬇️  Descarc imaginea...\n";
            $savedPath = $service->downloadAndStore($result['url'], $product->slug);
            
            if ($savedPath) {
                // Update database
                $product->image_url = '/storage/' . $savedPath;
                $product->save();
                
                echo "   💾 Salvată: /storage/{$savedPath}\n";
                echo "   ✅ SUCCESS!\n\n";
                $success++;
            } else {
                echo "   ❌ Eroare la descărcare\n\n";
                $failed++;
            }
        } else {
            echo "   ⚠️  Nu s-au găsit imagini pentru acest produs\n\n";
            $skipped++;
        }
        
    } catch (Exception $e) {
        echo "   ❌ EROARE: " . $e->getMessage() . "\n\n";
        $failed++;
    }
    
    // Delay pentru rate limiting (1 query pe secundă)
    if ($num < 10) {
        echo "   ⏱️  Aștept 1 secundă...\n\n";
        sleep(1);
    }
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║                    REZULTATE FINALE                      ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

echo "✅ Succese:  $success / 10\n";
echo "❌ Eșecuri:  $failed / 10\n";
echo "⚠️  Sărite:   $skipped / 10\n\n";

$totalQueries = $success + $failed + $skipped;
echo "📊 Queries folosite: $totalQueries / 100 (limită zilnică gratuită)\n";
echo "📊 Queries rămase astăzi: " . (100 - $totalQueries) . "\n\n";

if ($success > 0) {
    echo "🎉 Import complet! Verifică imaginile pe site:\n";
    echo "   http://localhost:8080/categorii/smartphone\n";
    echo "   http://localhost:8080/categorii/frigider\n\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
