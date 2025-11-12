<?php
// Script pentru completare automată imagini reale la produse fără poză
// Surse: site oficial, Amazon, fallback Unsplash

require __DIR__.'/vendor/autoload.php';

use App\Models\Product;
use Illuminate\Support\Str;
use GuzzleHttp\Client;

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$client = new Client(['timeout' => 20]);

$products = Product::whereNull('image_url')->orWhere('image_url', '')->get();
echo "Total produse fără imagine: ".$products->count()."\n\n";

foreach ($products as $product) {
    $name = $product->name;
    $brand = $product->brand;
    $query = urlencode($brand.' '.$name.' official product image');
    $img = null;

    // 1. Caută pe Amazon (scraping rapid, doar thumbnail)
    try {
        $amazonUrl = "https://www.amazon.com/s?k=".urlencode($brand.' '.$name);
        $res = $client->get($amazonUrl, [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept-Language' => 'en-US,en;q=0.9',
            ]
        ]);
        if ($res->getStatusCode() === 200) {
            $html = (string)$res->getBody();
            if (preg_match('/src="(https:\/\/m\.media-amazon\.com\/images\/[^"]+\.(jpg|jpeg|png))"/', $html, $m)) {
                $img = $m[1];
            }
        }
    } catch (\Exception $e) {}

    // 2. Fallback Unsplash
    if (!$img) {
        try {
            $unsplashKey = getenv('UNSPLASH_ACCESS_KEY') ?: 'demo';
            $unsplash = $client->get("https://api.unsplash.com/search/photos?query=$query&client_id=$unsplashKey");
            $data = json_decode($unsplash->getBody(), true);
            if (!empty($data['results'][0]['urls']['regular'])) {
                $img = $data['results'][0]['urls']['regular'];
            }
        } catch (\Exception $e) {}
    }

    // 3. Setează imaginea dacă a fost găsită
    if ($img) {
        $product->image_url = $img;
        $product->save();
        echo "[OK] $brand $name => $img\n";
    } else {
        echo "[FAIL] $brand $name\n";
    }
}

echo "\nFinalizat!\n";
