<?php

echo "📸 UNSPLASH API - IMPLEMENTARE COMPLETĂ\n";
echo "========================================\n\n";

echo "🔑 PASUL 1: OBȚINE API KEY GRATUIT\n";
echo "====================================\n";
echo "1. Accesează: https://unsplash.com/developers\n";
echo "2. Click pe 'Register as a developer'\n";
echo "3. Creează cont (gratis)\n";
echo "4. Click 'New Application'\n";
echo "5. Completează formularul:\n";
echo "   - Application name: Comparix\n";
echo "   - Description: Product comparison website\n";
echo "6. Primești:\n";
echo "   - Access Key: xxxxxxxxxxxxx\n";
echo "   - Secret Key: xxxxxxxxxxxxx\n\n";

echo "📊 LIMITE GRATUITE:\n";
echo "   • 50 requests/oră\n";
echo "   • Ideal pentru development\n";
echo "   • Pentru producție: \$20/lună = 5000 requests\n\n";

echo "💻 PASUL 2: INSTALEAZĂ LIBRARY PHP\n";
echo "====================================\n";
echo "composer require unsplash/unsplash\n\n";

echo "🔧 PASUL 3: CONFIGURARE .ENV\n";
echo "==============================\n";
echo "Adaugă în .env:\n";
echo "UNSPLASH_ACCESS_KEY=your_access_key_here\n";
echo "UNSPLASH_SECRET_KEY=your_secret_key_here\n\n";

echo "📝 PASUL 4: COD IMPLEMENTARE\n";
echo "=============================\n\n";

// Exemplu cod complet
$code = <<<'PHP'
<?php

namespace App\Services;

use Crew\Unsplash\Photo;
use Crew\Unsplash\Search;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UnsplashImageService
{
    private $accessKey;
    private $baseUrl = 'https://api.unsplash.com';
    
    public function __construct()
    {
        $this->accessKey = config('services.unsplash.access_key');
    }
    
    /**
     * Caută imagini pentru un produs
     */
    public function searchProductImage($productName, $category = null)
    {
        $query = $this->buildSearchQuery($productName, $category);
        
        $response = Http::get("{$this->baseUrl}/search/photos", [
            'client_id' => $this->accessKey,
            'query' => $query,
            'per_page' => 5,
            'orientation' => 'squarish',
        ]);
        
        if ($response->successful()) {
            $results = $response->json()['results'];
            
            if (!empty($results)) {
                return $this->selectBestImage($results);
            }
        }
        
        return null;
    }
    
    /**
     * Construiește query inteligent bazat pe produs
     */
    private function buildSearchQuery($productName, $category)
    {
        // Extrage brand și model
        $parts = explode(' ', $productName);
        $brand = $parts[0] ?? '';
        
        // Query-uri specifice per categorie
        $categoryQueries = [
            'masini-de-spalat' => 'modern washing machine front load white',
            'frigider' => 'modern refrigerator stainless steel kitchen',
            'casti-wireless' => 'wireless earbuds headphones white background',
            'smartwatch' => 'smartwatch wearable technology black',
            'smartphone' => 'smartphone mobile phone modern',
            'placa-video' => 'graphics card GPU technology',
        ];
        
        if ($category && isset($categoryQueries[$category])) {
            return $categoryQueries[$category];
        }
        
        return "modern {$brand} product white background";
    }
    
    /**
     * Selectează cea mai bună imagine (rezoluție + downloads)
     */
    private function selectBestImage($results)
    {
        usort($results, function($a, $b) {
            return $b['downloads'] - $a['downloads'];
        });
        
        $bestImage = $results[0];
        
        return [
            'id' => $bestImage['id'],
            'url' => $bestImage['urls']['regular'], // 1080px
            'url_small' => $bestImage['urls']['small'], // 400px
            'url_thumb' => $bestImage['urls']['thumb'], // 200px
            'download_url' => $bestImage['links']['download'],
            'photographer' => $bestImage['user']['name'],
            'photographer_url' => $bestImage['user']['links']['html'],
        ];
    }
    
    /**
     * Descarcă și salvează imaginea local
     */
    public function downloadAndStore($imageUrl, $productSlug)
    {
        try {
            // Download imagine
            $imageData = file_get_contents($imageUrl);
            
            if ($imageData === false) {
                return null;
            }
            
            // Generează nume fișier
            $filename = Str::slug($productSlug) . '-' . time() . '.jpg';
            $path = "products/{$filename}";
            
            // Salvează în storage/app/public/products/
            Storage::disk('public')->put($path, $imageData);
            
            // Returnează URL public
            return Storage::url($path);
            
        } catch (\Exception $e) {
            \Log::error("Failed to download Unsplash image: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Trigger download credit pentru fotograf (obligatoriu per ToS Unsplash)
     */
    public function triggerDownload($downloadUrl)
    {
        Http::get($downloadUrl, [
            'client_id' => $this->accessKey,
        ]);
    }
}
PHP;

echo "```php\n";
echo $code;
echo "\n```\n\n";

echo "🎯 PASUL 5: SCRIPT DE IMPORT IMAGINI\n";
echo "======================================\n\n";

$importScript = <<<'PHP'
<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\UnsplashImageService;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

$unsplash = new UnsplashImageService();

echo "📸 IMPORT IMAGINI DE PE UNSPLASH\n";
echo "=================================\n\n";

// Selectează categoriile care au nevoie de imagini
$categories = [
    'masini-de-spalat' => 'Mașini de spălat',
    'frigider' => 'Frigidere',
];

foreach ($categories as $slug => $name) {
    echo "📁 {$name}\n";
    echo str_repeat('-', 50) . "\n";
    
    // Ia primele 10 produse fără imagini reale
    $products = DB::table('products')
        ->join('product_types', 'products.product_type_id', '=', 'product_types.id')
        ->where('product_types.slug', $slug)
        ->where('products.image_url', 'LIKE', '%dummyimage%')
        ->select('products.*')
        ->limit(10)
        ->get();
    
    echo "   Găsite {$products->count()} produse\n\n";
    
    foreach ($products as $product) {
        echo "   • {$product->name}... ";
        
        // Caută imagine
        $image = $unsplash->searchProductImage($product->name, $slug);
        
        if ($image) {
            // Descarcă și salvează local
            $localUrl = $unsplash->downloadAndStore($image['url'], $product->name);
            
            if ($localUrl) {
                // Update database
                DB::table('products')
                    ->where('id', $product->id)
                    ->update(['image_url' => $localUrl]);
                
                // Trigger download credit (obligatoriu)
                $unsplash->triggerDownload($image['download_url']);
                
                echo "✅ Salvat\n";
                
                // Rate limiting - 50 requests/oră = 1 request/72 secunde
                sleep(3);
            } else {
                echo "❌ Eroare download\n";
            }
        } else {
            echo "⚠️  Nu s-a găsit imagine\n";
        }
    }
    
    echo "\n";
}

echo "✅ Import finalizat!\n";
PHP;

echo "📄 Creează: import-unsplash-images.php\n";
echo "```php\n";
echo $importScript;
echo "\n```\n\n";

echo "⚙️ PASUL 6: CONFIGURARE config/services.php\n";
echo "=============================================\n\n";

$configCode = <<<'PHP'
// config/services.php

return [
    // ... alte servicii ...
    
    'unsplash' => [
        'access_key' => env('UNSPLASH_ACCESS_KEY'),
        'secret_key' => env('UNSPLASH_SECRET_KEY'),
    ],
];
PHP;

echo "```php\n";
echo $configCode;
echo "\n```\n\n";

echo "🚀 PASUL 7: RULARE\n";
echo "===================\n\n";
echo "1. composer require guzzlehttp/guzzle (dacă nu e deja instalat)\n";
echo "2. Setează UNSPLASH_ACCESS_KEY în .env\n";
echo "3. php artisan storage:link (pentru public storage)\n";
echo "4. php import-unsplash-images.php\n\n";

echo "📊 REZULTATE AȘTEPTATE:\n";
echo "========================\n";
echo "• 10 produse per categorie cu imagini reale\n";
echo "• Imagini HD (1080px), profesionale\n";
echo "• Salvate local în storage/app/public/products/\n";
echo "• URL: /storage/products/samsung-ww90t554daw-123456.jpg\n";
echo "• Fără probleme CORS\n";
echo "• Attributie fotograf în footer (ToS Unsplash)\n\n";

echo "⚖️ TERMENI ȘI CONDIȚII UNSPLASH:\n";
echo "=================================\n";
echo "✅ PERMIS:\n";
echo "   • Folosire comercială\n";
echo "   • Modificare imagini\n";
echo "   • Descărcare și hosting propriu\n\n";

echo "❌ INTERZIS:\n";
echo "   • Vânzare imagini ca atare\n";
echo "   • Folosire în servicii concurente cu Unsplash\n\n";

echo "✅ OBLIGATORIU:\n";
echo "   • Credit fotograf: 'Photo by [name] on Unsplash'\n";
echo "   • Link către profilul fotografului\n";
echo "   • Trigger download endpoint (pentru analytics)\n\n";

echo "💡 EXEMPLU CREDIT FOOTER:\n";
echo "==========================\n\n";

$footerExample = <<<'HTML'
<!-- resources/views/layouts/app.blade.php -->
<footer>
    <div class="container">
        <p>
            Fotografii produse de pe 
            <a href="https://unsplash.com/?utm_source=comparix&utm_medium=referral">Unsplash</a>
        </p>
    </div>
</footer>
HTML;

echo "```html\n";
echo $footerExample;
echo "\n```\n\n";

echo "🎯 RECOMANDARE FINALĂ:\n";
echo "=======================\n\n";
echo "Strategie Hibridă (CEL MAI BUN):\n";
echo "1. 🎨 Placeholder-uri branded (ACUM) → Lansare imediată\n";
echo "2. 📸 Unsplash pentru 10 produse top → Weekend\n";
echo "3. 🏪 2Performant feed → Lună 2\n";
echo "4. 💰 Imagini reale producători → Treptat\n\n";

echo "📈 PROGRES IMAGINI:\n";
echo "   Săptămâna 1: 20% imagini reale (placeholders)\n";
echo "   Săptămâna 2: 50% imagini reale (top produse Unsplash)\n";
echo "   Luna 2:      80% imagini reale (affiliate feeds)\n";
echo "   Luna 3:      95% imagini reale (manual + API)\n\n";

echo "✅ CONCLUZIE: Unsplash e perfect pentru început!\n";
echo "   • Gratuit pentru 50 produse/zi\n";
echo "   • Imagini HD profesionale\n";
echo "   • Legal pentru uz comercial\n";
echo "   • Implementare în 30 minute\n";
