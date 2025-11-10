<?php

// Test direct Google Custom Search API

$apiKey = 'AIzaSyCws9hT674choqd_mFkf9NMZPjrwO6Cn2s';
$searchEngineId = 'f598acf67b291425e';
$query = 'Samsung RB38A7B6AS9/EF';

$url = 'https://www.googleapis.com/customsearch/v1?' . http_build_query([
    'key' => $apiKey,
    'cx' => $searchEngineId,
    'q' => $query,
    'searchType' => 'image',
    'num' => 3
]);

echo "🔗 URL Apel API:\n";
echo $url . "\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📡 HTTP Code: $httpCode\n\n";

$data = json_decode($response, true);

echo "📄 Răspuns complet JSON:\n";
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
echo "\n\n";

if (isset($data['error'])) {
    echo "❌ EROARE:\n";
    echo "   Mesaj: " . $data['error']['message'] . "\n";
    echo "   Cod: " . $data['error']['code'] . "\n";
    
    if (isset($data['error']['errors'])) {
        foreach ($data['error']['errors'] as $err) {
            echo "   Detalii: " . ($err['message'] ?? 'N/A') . "\n";
            echo "   Motiv: " . ($err['reason'] ?? 'N/A') . "\n";
        }
    }
} elseif (isset($data['items']) && count($data['items']) > 0) {
    echo "✅ SUCCESS - Găsit " . count($data['items']) . " imagini!\n";
    foreach ($data['items'] as $i => $item) {
        echo "\n📷 Imagine " . ($i + 1) . ":\n";
        echo "   URL: " . $item['link'] . "\n";
        echo "   Title: " . ($item['title'] ?? 'N/A') . "\n";
    }
} else {
    echo "⚠️  Răspuns valid dar fără imagini (items array gol sau lipsă)\n";
    echo "   Verifică că Search Engine are:\n";
    echo "   - 'Search the entire web' = ON\n";
    echo "   - 'Image search' = ENABLED\n";
}
