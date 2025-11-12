<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

// Dezactivează temporar indexarea Scout/Meilisearch la update
Product::withoutSyncingToSearch(function () {
    echo "\n═══════════════════════════════════════════════════════════════\n";
    echo "  DESCĂRCARE IMAGINI PRODUSE (FĂRĂ PERSOANE/AMBIENTĂRI)\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";

    $idsFile = __DIR__ . '/products-need-images.json';
    if (!file_exists($idsFile)) {
        echo "❌ Nu există products-need-images.json\n";
        echo "   Rulează mai întâi: php find-products-no-images.php\n\n";
        exit;
    }

    $ids = json_decode(file_get_contents($idsFile), true);
    echo "Procesez " . count($ids) . " produse...\n\n";

    $success = 0;
    $failed = 0;
    $skipped = 0;

    foreach ($ids as $id) {
        $product = Product::find($id);
        if (!$product) {
            echo "  ⚠️  Produs #{$id} nu există\n";
            $skipped++;
            continue;
        }
        
        // Construim query pentru căutare imagini DOAR cu produsul
        // Adăugăm termeni care filtrează persoanele
        $searchTerms = "{$product->brand} {$product->name} product white background -person -people -lifestyle -using -model";
        
        // Pentru mașini de spălat și frigidere: căutăm imagini oficiale/stock
        if (in_array($product->product_type_id, [6, 7])) { // Frigider, Mașină spălat
            $searchTerms .= " stock photo isolated";
        }
        
        echo "[{$product->id}] {$product->brand} {$product->name}\n";
        echo "    Query: " . substr($searchTerms, 0, 80) . "...\n";
        
        // Încearcă Unsplash API pentru imagine reală
        $unsplashAccessKey = getenv('UNSPLASH_ACCESS_KEY') ?: 'INSERATI_CHEIA_UNSPLASH_AICI';
        $url = "https://api.unsplash.com/search/photos?query=" . urlencode($searchTerms) . "&client_id={$unsplashAccessKey}&per_page=1";
        $response = @file_get_contents($url);
        $imageUrl = null;
        if ($response !== false) {
            $data = json_decode($response, true);
            if (!empty($data['results'][0]['urls']['regular'])) {
                $imageUrl = $data['results'][0]['urls']['regular'];
            }
        }
        if ($imageUrl) {
            $product->addMediaFromUrl($imageUrl)->toMediaCollection('gallery');
            echo "    ✅ Imagine Unsplash salvată\n\n";
            $success++;
        } else {
            // Fallback: placeholder branded
            $brandSlug = strtolower($product->brand);
            $nameSlug = strtolower(str_replace([' ', '/', '+'], ['-', '-', '-'], $product->name));
            $nameSlug = preg_replace('/[^a-z0-9-]/', '', $nameSlug);
            $imageUrl = "https://dummyimage.com/600x600/ffffff/333333.png&text={$brandSlug}+{$nameSlug}";
            $product->image_url = $imageUrl;
            $product->save();
            echo "    ⚠️  Fallback placeholder salvat\n\n";
            $skipped++;
        }
        // Rate limiting
        usleep(500000); // 0.5 secunde între request-uri
    }

    echo "═══════════════════════════════════════════════════════════════\n";
    echo "  REZULTATE:\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "  ✅ Succes: {$success}\n";
    echo "  ❌ Eșuat: {$failed}\n";
    echo "  ⏭️  Omise: {$skipped}\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";

    if ($success > 0) {
        echo "ℹ️  NOTĂ: Scriptul folosește placeholder-uri pentru demo.\n";
        echo "   Pentru imagini reale, integrează:\n";
        echo "   1. Google Custom Search API\n";
        echo "   2. Unsplash API\n";
        echo "   3. Scraping site-uri producători (Samsung, LG, Bosch, etc.)\n\n";
    }
});
