<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Google Custom Search API credentials from .env
$apiKey = env('GOOGLE_API_KEY');
$searchEngineId = env('GOOGLE_SEARCH_ENGINE_ID');

if (!$apiKey || !$searchEngineId) {
    die("❌ GOOGLE_API_KEY sau GOOGLE_SEARCH_ENGINE_ID nu sunt setate în .env\n");
}

function fetchImageFromGoogle($productName, $apiKey, $searchEngineId) {
    $query = urlencode($productName . ' official product image');
    $url = "https://www.googleapis.com/customsearch/v1?key={$apiKey}&cx={$searchEngineId}&q={$query}&searchType=image&num=1&imgSize=large&imgType=photo";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        echo "  ⚠️  HTTP Error $httpCode\n";
        return null;
    }
    
    $data = json_decode($response, true);
    
    if (isset($data['items'][0]['link'])) {
        return $data['items'][0]['link'];
    }
    
    return null;
}

echo "🔄 Actualizare imagini telefoane cu Google Custom Search API...\n\n";

$phones = App\Models\Product::where('product_type_id', 1)
    ->where('image_url', 'LIKE', '%ui-avatars.com%')
    ->get();

echo "📱 Găsite {$phones->count()} telefoane fără imagini reale\n\n";

$updated = 0;
$failed = 0;

foreach ($phones as $phone) {
    echo "🔍 {$phone->name}...\n";
    
    $imageUrl = fetchImageFromGoogle($phone->name, $apiKey, $searchEngineId);
    
    if ($imageUrl) {
        $phone->image_url = $imageUrl;
        $phone->save();
        $updated++;
        echo "  ✅ Imagine actualizată: {$imageUrl}\n";
    } else {
        $failed++;
        echo "  ❌ Nu s-a găsit imagine\n";
    }
    
    // Rate limiting - 100 requests/day, so we can do ~4 requests/hour safely
    sleep(1);
    
    echo "\n";
}

echo "\n✓ Proces finalizat!\n";
echo "  - Actualizate: $updated\n";
echo "  - Eșuate: $failed\n";
