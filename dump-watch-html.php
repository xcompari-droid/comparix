<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

$url = "https://versus.com/en/apple-watch-series-10";

$response = Http::timeout(30)
    ->withoutVerifying()
    ->withHeaders([
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ])
    ->get($url);

$html = $response->body();

// Salvează HTML pentru inspecție
file_put_contents('versus-watch-sample.html', $html);

echo "HTML salvat în versus-watch-sample.html\n";
echo "Lungime: " . strlen($html) . " bytes\n";

// Caută pattern-uri comune pentru specificații
if (preg_match_all('/<script[^>]*type="application\/ld\+json"[^>]*>(.*?)<\/script>/is', $html, $matches)) {
    echo "\nGăsit JSON-LD data:\n";
    foreach ($matches[1] as $i => $json) {
        echo "\n--- JSON-LD Block " . ($i+1) . " ---\n";
        $data = json_decode($json, true);
        if ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
    }
}
