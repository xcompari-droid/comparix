<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Services\GoogleImageService;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║   IMPORT GOOGLE IMAGES - SMARTPHONE                      ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

$service = new GoogleImageService();

// Găsește smartphone-uri cu imagini Versus.com (primele 70)
$smartphones = Product::where('product_type_id', 1)
    ->where(function($query) {
        $query->where('image_url', 'LIKE', '%versus-dot-com.imgix.net%')
              ->orWhere('image_url', 'LIKE', '%dummyimage.com%');
    })
    ->orderBy('id')
    ->limit(70)
    ->get();

echo "📦 Găsite " . count($smartphones) . " smartphone-uri pentru import\n\n";

$success = 0;
$failed = 0;
$skipped = 0;

foreach ($smartphones as $index => $product) {
    $num = $index + 1;
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "[$num/" . count($smartphones) . "] {$product->brand} {$product->name}\n";
    echo "       Current: " . substr($product->image_url, 0, 60) . "...\n\n";
    
    try {
        // Caută imagine
        $result = $service->searchProductImage(
            $product->brand . ' ' . $product->name,
            'smartphone'
        );
        
        if ($result) {
            echo "   ✅ Găsită imagine:\n";
            echo "      URL: " . substr($result['url'], 0, 70) . "...\n";
            echo "      Dimensiuni: {$result['width']}x{$result['height']}px\n";
            echo "      Scor: {$result['score']}/100\n";
            echo "      Title: " . substr($result['title'], 0, 60) . "...\n\n";
            
            // Descarcă și salvează
            echo "   ⬇️  Descarc imaginea...\n";
            $savedPath = $service->downloadAndStore($result['url'], $product->slug);
            
            if ($savedPath) {
                // Update database
                $oldUrl = $product->image_url;
                $product->image_url = '/storage/' . $savedPath;
                $product->save();
                
                echo "   💾 Salvată: /storage/{$savedPath}\n";
                echo "   📝 Actualizat în DB\n";
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
    
    // Delay pentru rate limiting (1 query la 2 secunde pentru siguranță)
    if ($num < count($smartphones)) {
        echo "   ⏱️  Aștept 2 secunde...\n\n";
        sleep(2);
    }
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║                    REZULTATE FINALE                      ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

echo "✅ Succese:  $success / " . count($smartphones) . "\n";
echo "❌ Eșecuri:  $failed / " . count($smartphones) . "\n";
echo "⚠️  Sărite:   $skipped / " . count($smartphones) . "\n\n";

$totalQueries = $success + $failed + $skipped;
echo "📊 Queries folosite: $totalQueries / 100 (limită zilnică gratuită)\n";
echo "📊 Queries rămase astăzi: " . (100 - 30 - $totalQueries) . " (30 folosite pentru căști)\n\n";

$successRate = count($smartphones) > 0 ? round(($success / count($smartphones)) * 100, 1) : 0;
echo "📈 Rata de succes: {$successRate}%\n\n";

if ($success > 0) {
    echo "🎉 Import complet! Verifică imaginile pe site:\n";
    echo "   http://localhost:8080/categorii/smartphone\n\n";
    echo "💡 Pentru a vedea noile imagini:\n";
    echo "   1. Deschide http://localhost:8080/categorii/smartphone\n";
    echo "   2. Compară produsele (ar trebui să aibă imagini reale)\n\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
