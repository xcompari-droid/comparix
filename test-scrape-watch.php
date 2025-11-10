<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

echo "=== TEST SCRAPE SMARTWATCH ===\n\n";

$url = "https://versus.com/en/apple-watch-series-10";
echo "URL: $url\n\n";

try {
    $response = Http::timeout(30)
        ->withoutVerifying()
        ->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ])
        ->get($url);
    
    if (!$response->successful()) {
        throw new \Exception("Failed: " . $response->status());
    }
    
    $html = $response->body();
    
    libxml_use_internal_errors(true);
    $dom = new \DOMDocument();
    $dom->loadHTML($html);
    libxml_clear_errors();
    
    $xpath = new \DOMXPath($dom);
    
    echo "Extragere specificații:\n\n";
    
    // Extract image
    $imageNodes = $xpath->query('//meta[@property="og:image"]/@content');
    if ($imageNodes->length > 0) {
        echo "✓ Imagine găsită: " . substr($imageNodes->item(0)->nodeValue, 0, 80) . "...\n";
    }
    
    // Extract description
    $descNodes = $xpath->query('//meta[@name="description"]/@content');
    if ($descNodes->length > 0) {
        echo "✓ Descriere găsită: " . substr($descNodes->item(0)->nodeValue, 0, 80) . "...\n";
    }
    
    // Extract specifications from data attributes
    echo "\nSpecificații găsite:\n";
    $dataSpecs = $xpath->query('//*[@data-spec-name]');
    echo "Total elemente cu data-spec-name: " . $dataSpecs->length . "\n\n";
    
    $count = 0;
    foreach ($dataSpecs as $spec) {
        $name = $spec->getAttribute('data-spec-name');
        $value = $spec->getAttribute('data-spec-value') ?: trim($spec->textContent);
        if (!empty($name) && !empty($value)) {
            echo "  - $name: $value\n";
            $count++;
            if ($count >= 20) {
                echo "  ... (și alte " . ($dataSpecs->length - 20) . " specificații)\n";
                break;
            }
        }
    }
    
    if ($count === 0) {
        echo "\n⚠️  NICIO SPECIFICAȚIE GĂSITĂ!\n";
        echo "\nÎncerc altă metodă...\n\n";
        
        // Try finding spec tables
        $tables = $xpath->query('//table | //dl | //*[contains(@class, "spec")]');
        echo "Tabele/liste găsite: " . $tables->length . "\n";
        
        // Try finding any divs with specs
        $specDivs = $xpath->query('//*[contains(@class, "specification") or contains(@class, "feature") or contains(@class, "detail")]');
        echo "Div-uri cu spec/feature/detail: " . $specDivs->length . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Eroare: " . $e->getMessage() . "\n";
}
