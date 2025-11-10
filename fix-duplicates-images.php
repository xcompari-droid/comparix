<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\Http;

echo "=== CURĂȚARE DUPLICATE ȘI ACTUALIZARE IMAGINI ===\n\n";

// 1. Șterge duplicatul OPPO Find X7 Ultra (păstrează cel mai vechi)
$oppoFind = Product::where('brand', 'OPPO')
    ->where('name', 'OPPO Find X7 Ultra')
    ->orderBy('id')
    ->get();

if ($oppoFind->count() > 1) {
    echo "🗑️  Găsit duplicat: OPPO Find X7 Ultra\n";
    // Păstrează primul (id cel mai mic), șterge restul
    $toKeep = $oppoFind->first();
    $toDelete = $oppoFind->skip(1);
    
    foreach ($toDelete as $product) {
        echo "  Șterge ID {$product->id}...\n";
        // Șterge specificațiile asociate
        $product->specValues()->delete();
        // Șterge ofertele asociate
        $product->offers()->delete();
        // Șterge produsul
        $product->delete();
    }
    echo "  ✓ Păstrat ID {$toKeep->id}\n\n";
}

// 2. Actualizează imaginile lipsă pentru smartphone-uri
echo "📷 ACTUALIZARE IMAGINI SMARTPHONE-URI\n";

$phonesWithoutImages = Product::whereHas('productType', function($q) {
    $q->whereHas('category', function($q2) {
        $q2->where('name', 'Smartphone-uri');
    });
})->get()->filter(function($phone) {
    return empty($phone->image_url) || 
           strpos($phone->image_url, 'ui-avatars.com') !== false;
});

foreach ($phonesWithoutImages as $phone) {
    echo "  Căutare imagine pentru {$phone->brand} {$phone->name}...\n";
    
    // Încercă să găsească imaginea pe versus.com
    $slug = $phone->slug ?? strtolower(str_replace(' ', '-', $phone->brand . '-' . $phone->name));
    $url = "https://versus.com/en/" . $slug;
    
    try {
        $response = Http::timeout(10)
            ->withoutVerifying()
            ->get($url);
        
        if ($response->successful()) {
            $html = $response->body();
            
            // Extract OG image
            if (preg_match('/<meta property="og:image" content="([^"]+)"/', $html, $matches)) {
                $imageUrl = $matches[1];
                if (!empty($imageUrl) && strpos($imageUrl, 'ui-avatars.com') === false) {
                    $phone->image_url = $imageUrl;
                    $phone->save();
                    echo "    ✓ Actualizat: $imageUrl\n";
                    continue;
                }
            }
        }
    } catch (\Exception $e) {
        // Ignoră erorile
    }
    
    echo "    ✗ Nu s-a găsit imagine\n";
}

// 3. Actualizează imaginile lipsă pentru smartwatch-uri
echo "\n📷 ACTUALIZARE IMAGINI SMARTWATCH-URI\n";

$watchesWithoutImages = Product::whereHas('productType', function($q) {
    $q->whereHas('category', function($q2) {
        $q2->where('name', 'Smartwatch-uri');
    });
})->get()->filter(function($watch) {
    return empty($watch->image_url) || 
           strpos($watch->image_url, 'ui-avatars.com') !== false;
});

foreach ($watchesWithoutImages as $watch) {
    echo "  Căutare imagine pentru {$watch->brand} {$watch->name}...\n";
    
    // Încercă să găsească imaginea pe versus.com
    $slug = $watch->slug ?? strtolower(str_replace(' ', '-', $watch->brand . '-' . $watch->name));
    $url = "https://versus.com/en/" . $slug;
    
    try {
        $response = Http::timeout(10)
            ->withoutVerifying()
            ->get($url);
        
        if ($response->successful()) {
            $html = $response->body();
            
            // Extract OG image
            if (preg_match('/<meta property="og:image" content="([^"]+)"/', $html, $matches)) {
                $imageUrl = $matches[1];
                if (!empty($imageUrl) && strpos($imageUrl, 'ui-avatars.com') === false) {
                    $watch->image_url = $imageUrl;
                    $watch->save();
                    echo "    ✓ Actualizat: $imageUrl\n";
                    sleep(1); // Rate limiting
                    continue;
                }
            }
        }
    } catch (\Exception $e) {
        // Ignoră erorile
    }
    
    echo "    ✗ Nu s-a găsit imagine\n";
    sleep(1); // Rate limiting
}

echo "\n✓ Finalizat!\n";
