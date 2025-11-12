<?php
// Script pentru completarea automată a imaginilor lipsă folosind Unsplash
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\Http;

$unsplashAccessKey = getenv('UNSPLASH_ACCESS_KEY') ?: 'INSERATI_CHEIA_UNSPLASH_AICI';

$products = Product::whereDoesntHave('media', function ($q) {
    $q->where('collection_name', 'gallery');
})->get();

$downloaded = 0;
echo "🔎 Caut imagini Unsplash pentru {$products->count()} produse fără poză...\n\n";

foreach ($products as $product) {
    $query = $product->name;
    $url = "https://api.unsplash.com/search/photos?query=" . urlencode($query) . "&client_id={$unsplashAccessKey}&per_page=1";
    $response = @file_get_contents($url);
    if ($response === false) {
        echo "❌ {$product->name}: Eroare la interogare Unsplash\n";
        continue;
    }
    $data = json_decode($response, true);
    if (!empty($data['results'][0]['urls']['regular'])) {
        $imgUrl = $data['results'][0]['urls']['regular'];
        try {
            $product->addMediaFromUrl($imgUrl)->toMediaCollection('gallery');
            echo "✅ {$product->name}\n";
            $downloaded++;
        } catch (Exception $e) {
            echo "❌ {$product->name}: {$e->getMessage()}\n";
        }
    } else {
        echo "❌ {$product->name}: Nicio imagine găsită pe Unsplash\n";
    }
}
echo "\n✅ Completate automat: {$downloaded} imagini din Unsplash\n";
