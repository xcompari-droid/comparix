<?php

echo "🔍 TESTARE IMAGINI DE PE COMPARI.RO\n";
echo "====================================\n\n";

// Test different product searches on compari.ro
$testProducts = [
    'Samsung WW90T554DAW' => 'https://www.compari.ro/masini-de-spalat-rufe-c4049/samsung/ww90t554daw/',
    'LG F4WV710P2E' => 'https://www.compari.ro/masini-de-spalat-rufe-c4049/lg/f4wv710p2e/',
    'Samsung RB38A7B6AS9' => 'https://www.compari.ro/frigidere-c4047/samsung/rb38a7b6as9/',
];

foreach ($testProducts as $productName => $url) {
    echo "📦 {$productName}\n";
    echo "   URL: {$url}\n";
    
    // Try to fetch the page
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language: ro-RO,ro;q=0.9,en-US;q=0.8,en;q=0.7',
            ],
            'timeout' => 10,
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);
    
    $html = @file_get_contents($url, false, $context);
    
    if ($html === false) {
        echo "   ❌ Eroare la fetch\n\n";
        continue;
    }
    
    echo "   ✅ Pagină încărcată (" . strlen($html) . " bytes)\n";
    
    // Extract images
    preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches);
    
    if (!empty($matches[1])) {
        echo "   Găsite " . count($matches[1]) . " imagini:\n";
        
        $relevantImages = array_filter($matches[1], function($img) {
            return !str_contains($img, 'logo') && 
                   !str_contains($img, 'icon') && 
                   !str_contains($img, 'banner') &&
                   (str_contains($img, 'http') || str_contains($img, '//'));
        });
        
        foreach (array_slice($relevantImages, 0, 3) as $img) {
            echo "      • {$img}\n";
        }
    } else {
        echo "   ⚠️  Nicio imagine găsită\n";
    }
    
    echo "\n";
}

// Test search functionality
echo "🔍 TESTARE CĂUTARE PE COMPARI.RO\n";
echo "==================================\n\n";

$searchTests = [
    'Samsung WW90T554DAW',
    'LG F4WV710P2E',
    'Bosch Serie 6',
];

foreach ($searchTests as $query) {
    echo "Căutare: \"{$query}\"\n";
    
    $searchUrl = 'https://www.compari.ro/cautare/?q=' . urlencode($query);
    echo "   URL: {$searchUrl}\n";
    
    $html = @file_get_contents($searchUrl, false, $context);
    
    if ($html !== false) {
        echo "   ✅ Rezultate (" . strlen($html) . " bytes)\n";
        
        // Try to find product links
        preg_match_all('/<a[^>]+href=["\']([^"\']*masini-de-spalat[^"\']*)["\'][^>]*>/i', $html, $matches);
        
        if (!empty($matches[1])) {
            echo "   Găsite " . count($matches[1]) . " link-uri produse:\n";
            foreach (array_slice(array_unique($matches[1]), 0, 2) as $link) {
                if (!str_contains($link, 'http')) {
                    $link = 'https://www.compari.ro' . $link;
                }
                echo "      • {$link}\n";
            }
        }
    } else {
        echo "   ❌ Eroare la căutare\n";
    }
    
    echo "\n";
}

echo "💡 CONCLUZIE:\n";
echo "- Compari.ro poate fi accesat și poate furniza imagini\n";
echo "- Imaginile sunt hostuite pe CDN-uri externe (posibil aceleași probleme CORS)\n";
echo "- Alternative: păstrăm placeholder-urile branded sau căutăm pe site-uri producătorilor\n";
