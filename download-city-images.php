<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use GuzzleHttp\Client;

echo "🏙️  Downloading city images...\n\n";

$imageDir = __DIR__ . '/public/images/products';

$client = new Client([
    'verify' => false,
    'timeout' => 30,
    'allow_redirects' => true,
]);

// Romanian city images from Wikipedia and Wikimedia Commons
$cityImages = [
    'București' => [
        'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e3/Bucharest_skyline_2021.jpg/1280px-Bucharest_skyline_2021.jpg',
        'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/15/33/f7/a6/bucharest.jpg',
    ],
    'Cluj-Napoca' => [
        'https://upload.wikimedia.org/wikipedia/commons/thumb/6/69/Cluj-Napoca_2019.jpg/1280px-Cluj-Napoca_2019.jpg',
        'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/0f/91/2e/14/cluj-napoca.jpg',
    ],
    'Timișoara' => [
        'https://upload.wikimedia.org/wikipedia/commons/thumb/1/15/Timisoara_Piata_Victoriei.jpg/1280px-Timisoara_Piata_Victoriei.jpg',
        'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/14/10/2e/5f/timisoara.jpg',
    ],
    'Iași' => [
        'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a7/Palace_of_Culture_Iasi.jpg/1280px-Palace_of_Culture_Iasi.jpg',
        'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/0f/c9/8b/5e/iasi.jpg',
    ],
    'Constanța' => [
        'https://upload.wikimedia.org/wikipedia/commons/thumb/7/79/Constanta_Casino_2021.jpg/1280px-Constanta_Casino_2021.jpg',
        'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/0d/ed/5a/98/constanta.jpg',
    ],
    'Craiova' => [
        'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c8/Craiova_panorama.jpg/1280px-Craiova_panorama.jpg',
        'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/0e/4a/d2/3c/craiova.jpg',
    ],
    'Brașov' => [
        'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6a/Brasov_Council_Square.jpg/1280px-Brasov_Council_Square.jpg',
        'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/0d/2f/0a/5e/brasov.jpg',
    ],
    'Galați' => [
        'https://upload.wikimedia.org/wikipedia/commons/thumb/8/89/Galati_Faleza_Dunarii.jpg/1280px-Galati_Faleza_Dunarii.jpg',
        'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/0c/5b/1e/2a/galati.jpg',
    ],
    'Ploiești' => [
        'https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Ploiesti_Cultural_Palace.jpg/1280px-Ploiesti_Cultural_Palace.jpg',
        'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/0b/8a/3d/1e/ploiesti.jpg',
    ],
    'Brăila' => [
        'https://upload.wikimedia.org/wikipedia/commons/thumb/3/38/Braila_Public_Garden.jpg/1280px-Braila_Public_Garden.jpg',
        'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/0a/7c/2d/4f/braila.jpg',
    ],
];

$downloaded = 0;
$failed = 0;

$cities = Product::where('brand', 'România')->get();

foreach ($cities as $city) {
    echo "Processing: {$city->name}\n";
    
    if (!isset($cityImages[$city->name])) {
        echo "  ⚠ No URLs configured\n\n";
        $failed++;
        continue;
    }
    
    $imageUrls = $cityImages[$city->name];
    $success = false;
    
    foreach ($imageUrls as $index => $imageUrl) {
        echo "  Attempt " . ($index + 1) . ": " . substr($imageUrl, 0, 60) . "...\n";
        
        try {
            $response = $client->get($imageUrl, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept' => 'image/*,*/*;q=0.8',
                ]
            ]);
            
            $imageData = $response->getBody()->getContents();
            $contentType = $response->getHeader('Content-Type')[0] ?? '';
            
            if (strlen($imageData) > 5000 && strpos($contentType, 'image') !== false) {
                $extension = 'jpg';
                if (strpos($contentType, 'png') !== false || strpos($imageUrl, '.png') !== false) {
                    $extension = 'png';
                }
                
                $fileName = 'city-' . preg_replace('/[^a-z0-9-_]/i', '-', strtolower($city->name)) . '.' . $extension;
                $localPath = $imageDir . '/' . $fileName;
                $publicUrl = '/images/products/' . $fileName;
                
                file_put_contents($localPath, $imageData);
                
                Product::withoutSyncingToSearch(function() use ($city, $publicUrl) {
                    $city->update(['image_url' => $publicUrl]);
                });
                
                $size = round(strlen($imageData) / 1024, 2);
                echo "  ✓ Downloaded ({$size} KB) → {$publicUrl}\n";
                $downloaded++;
                $success = true;
                break;
            }
        } catch (\Exception $e) {
            echo "  ✗ Failed: " . $e->getMessage() . "\n";
        }
    }
    
    if (!$success) {
        echo "  ✗ All sources failed\n";
        $failed++;
    }
    
    echo "\n";
    sleep(1);
}

echo "\n════════════════════════════════════════════\n";
echo "✅ City Images Process Complete!\n";
echo "════════════════════════════════════════════\n";
echo "   ✓ Downloaded: {$downloaded}\n";
echo "   ✗ Failed: {$failed}\n";
echo "   🏙️  Total Cities: " . $cities->count() . "\n";
echo "════════════════════════════════════════════\n";
