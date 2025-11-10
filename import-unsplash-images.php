<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\UnsplashImageService;
use Illuminate\Support\Facades\DB;

echo "📸 IMPORT IMAGINI UNSPLASH - SETUP WIZARD\n";
echo "==========================================\n\n";

// Verifică dacă există API key
$apiKey = config('services.unsplash.access_key');

if (!$apiKey) {
    echo "❌ UNSPLASH_ACCESS_KEY nu este configurat!\n\n";
    echo "🔑 PAȘI PENTRU A OBȚINE API KEY:\n";
    echo "================================\n\n";
    echo "1. Mergi pe: https://unsplash.com/developers\n";
    echo "2. Click 'Register as a developer'\n";
    echo "3. Creează cont gratuit\n";
    echo "4. Click 'New Application'\n";
    echo "5. Completează:\n";
    echo "   - Application name: Comparix\n";
    echo "   - Description: Product comparison website\n";
    echo "6. Copiază 'Access Key'\n\n";
    echo "7. Adaugă în .env:\n";
    echo "   UNSPLASH_ACCESS_KEY=your_access_key_here\n\n";
    echo "8. Rulează din nou: php import-unsplash-images.php\n\n";
    exit(1);
}

echo "✅ API Key găsit!\n";
echo "🔍 Caut produse care au nevoie de imagini...\n\n";

$unsplash = new UnsplashImageService();

// Selectează categoriile și numărul de produse
$categories = [
    'masini-de-spalat' => ['name' => 'Mașini de spălat', 'limit' => 10],
    'frigider' => ['name' => 'Frigidere', 'limit' => 10],
    'casti-wireless' => ['name' => 'Căști wireless', 'limit' => 10],
    'smartwatch' => ['name' => 'Smartwatch-uri', 'limit' => 10],
];

$totalUpdated = 0;
$totalFailed = 0;
$startTime = time();

foreach ($categories as $slug => $config) {
    echo "📁 {$config['name']}\n";
    echo str_repeat('=', 60) . "\n";
    
    // Ia produsele cu placeholder-uri
    $products = DB::table('products')
        ->join('product_types', 'products.product_type_id', '=', 'product_types.id')
        ->where('product_types.slug', $slug)
        ->where('products.image_url', 'LIKE', '%dummyimage%')
        ->select('products.*')
        ->limit($config['limit'])
        ->get();
    
    if ($products->isEmpty()) {
        echo "   ⚠️  Niciun produs cu placeholder găsit\n\n";
        continue;
    }
    
    echo "   Găsite {$products->count()} produse\n\n";
    
    foreach ($products as $product) {
        echo "   • {$product->name}... ";
        
        // Caută imagine pe Unsplash
        $image = $unsplash->searchProductImage($product->name, $slug);
        
        if ($image) {
            // Descarcă și salvează local
            $localUrl = $unsplash->downloadAndStore($image['url'], $product->name);
            
            if ($localUrl) {
                // Update database
                DB::table('products')
                    ->where('id', $product->id)
                    ->update([
                        'image_url' => $localUrl,
                        'updated_at' => now(),
                    ]);
                
                // Trigger download credit (obligatoriu per ToS Unsplash)
                if (!empty($image['download_url'])) {
                    $unsplash->triggerDownload($image['download_url']);
                }
                
                echo "✅ Salvat ({$image['photographer']})\n";
                $totalUpdated++;
                
                // Rate limiting - 50 requests/oră = 1 request/72 secunde
                // Pentru siguranță, așteptăm 3 secunde (permite ~1200/oră)
                sleep(3);
            } else {
                echo "❌ Eroare download\n";
                $totalFailed++;
            }
        } else {
            echo "⚠️  Nu s-a găsit imagine\n";
            $totalFailed++;
        }
    }
    
    echo "\n";
}

$duration = time() - $startTime;
$minutes = floor($duration / 60);
$seconds = $duration % 60;

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 RAPORT FINAL\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Imagini actualizate: {$totalUpdated}\n";
echo "❌ Erori: {$totalFailed}\n";
echo "⏱️  Timp total: {$minutes}m {$seconds}s\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

if ($totalUpdated > 0) {
    echo "🎉 Import finalizat cu succes!\n\n";
    echo "📝 IMPORTANT - TERMENI UNSPLASH:\n";
    echo "================================\n";
    echo "Trebuie să adaugi credit fotografilor în footer:\n\n";
    echo "<!-- resources/views/layouts/app.blade.php -->\n";
    echo "<footer>\n";
    echo "    <p>Product images from \n";
    echo "        <a href=\"https://unsplash.com/?utm_source=comparix&utm_medium=referral\">\n";
    echo "            Unsplash\n";
    echo "        </a>\n";
    echo "    </p>\n";
    echo "</footer>\n\n";
    
    echo "🔍 Verifică imaginile:\n";
    echo "   php check-all-images.php\n\n";
} else {
    echo "⚠️  Nicio imagine nu a fost actualizată.\n";
    echo "   Verifică că există produse cu placeholder-uri (dummyimage.com)\n\n";
}
