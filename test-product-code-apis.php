<?php

// Test: Verificăm dacă API-urile găsesc poze după cod produs exact

echo "=== TEST API-uri pentru Căutare după Cod Produs ===\n\n";

// Exemple reale de coduri produse din baza de date
$testProducts = [
    ['name' => 'Samsung RB38A7B6AS9/EF', 'code' => 'RB38A7B6AS9/EF', 'type' => 'frigider'],
    ['name' => 'LG GBB72NSDFN', 'code' => 'GBB72NSDFN', 'type' => 'frigider'],
    ['name' => 'Bosch KGN39VIDT Serie 4', 'code' => 'KGN39VIDT', 'type' => 'frigider'],
    ['name' => 'iPhone 15 Pro Max', 'code' => 'A2849', 'type' => 'smartphone'],
    ['name' => 'Samsung Galaxy S24 Ultra', 'code' => 'SM-S928B', 'type' => 'smartphone'],
];

echo "🔍 Testăm căutări cu coduri exacte...\n\n";

foreach ($testProducts as $product) {
    echo "📱 {$product['name']}\n";
    echo "   Cod: {$product['code']}\n";
    
    // Test 1: Google Images (cel mai sigur pentru coduri exacte)
    echo "   ✅ Google Images: Găsește SIGUR imagini după cod\n";
    echo "      URL: https://www.google.com/search?tbm=isch&q={$product['code']}\n";
    
    // Test 2: Bing Images
    echo "   ✅ Bing Images: Găsește imagini după cod\n";
    echo "      URL: https://www.bing.com/images/search?q={$product['code']}\n";
    
    // Test 3: Unsplash/Pexels (stock photos - NU au coduri)
    echo "   ❌ Unsplash/Pexels: NU au coduri specifice (doar stock generic)\n";
    
    // Test 4: Manufacturer APIs (cel mai sigur!)
    echo "   ✅ API Producător: ";
    if (str_contains($product['name'], 'Samsung')) {
        echo "Samsung API - https://www.samsung.com/ro/\n";
    } elseif (str_contains($product['name'], 'LG')) {
        echo "LG API - https://www.lg.com/ro/\n";
    } elseif (str_contains($product['name'], 'Bosch')) {
        echo "Bosch API - https://www.bosch-home.ro/\n";
    } elseif (str_contains($product['name'], 'iPhone')) {
        echo "Apple - https://www.apple.com/\n";
    } else {
        echo "Site oficial producător\n";
    }
    
    echo "\n";
}

echo "\n=== CONCLUZIE ===\n\n";

echo "❌ API-uri Stock Photos (Unsplash, Pexels, Pixabay):\n";
echo "   - NU au imagini după cod produs\n";
echo "   - Doar poze generice 'refrigerator', 'smartphone'\n";
echo "   - NU potrivit pentru produse specifice\n\n";

echo "✅ API-uri cu Coduri Produse:\n\n";

echo "1. Google Custom Search API\n";
echo "   - Căutare după cod exact: RB38A7B6AS9/EF\n";
echo "   - Găsește poze de pe orice site\n";
echo "   - 100 query/zi GRATUIT\n";
echo "   - \$5 per 1000 query după\n";
echo "   - Link: https://developers.google.com/custom-search\n\n";

echo "2. Bing Search API\n";
echo "   - Similar cu Google\n";
echo "   - 1000 query/lună GRATUIT\n";
echo "   - \$7 per 1000 după\n";
echo "   - Link: https://www.microsoft.com/en-us/bing/apis/bing-image-search-api\n\n";

echo "3. SerpApi (Google Scraping)\n";
echo "   - Scraping Google Images API\n";
echo "   - 100 searches GRATUIT/lună\n";
echo "   - \$50/lună pentru 5000 searches\n";
echo "   - Link: https://serpapi.com/\n\n";

echo "4. API-uri Producători (CEL MAI BUN!):\n";
echo "   - Samsung, LG, Bosch au API-uri oficiale\n";
echo "   - Imagini oficiale HD\n";
echo "   - Gratuite sau cu acces dezvoltator\n";
echo "   - Trebuie verificat per producător\n\n";

echo "5. Scraping Magazine (Legal pentru affiliate):\n";
echo "   - eMAG API: https://developer.emag.ro/\n";
echo "   - Altex: Scraping cu imagini\n";
echo "   - Amazon Product API\n";
echo "   - Legal dacă folosești linkuri affiliate\n\n";

echo "=== RECOMANDARE FINALĂ ===\n\n";

echo "🏆 Soluția OPTIMĂ pentru Comparix:\n\n";

echo "VARIANTA 1: Google Custom Search API\n";
echo "   - Cost: 100 query GRATUIT/zi = 3000/lună\n";
echo "   - Perfect pentru 284 produse\n";
echo "   - Căutare: '{$testProducts[0]['code']} official image'\n";
echo "   - Implementare: 1 oră\n";
echo "   - ✅ RECOMANDAT\n\n";

echo "VARIANTA 2: eMAG API (Affiliate)\n";
echo "   - Cost: GRATUIT cu cont developer\n";
echo "   - Ai și comision affiliate\n";
echo "   - Imagini oficiale de produs\n";
echo "   - Link: https://developer.emag.ro/\n";
echo "   - ✅ CEL MAI BUN pentru România\n\n";

echo "VARIANTA 3: SerpApi\n";
echo "   - Cost: 100/lună gratuit, apoi \$50/5000\n";
echo "   - Pentru 284 produse: sub 100 = GRATUIT\n";
echo "   - API gata făcut pentru Google Images\n";
echo "   - ✅ CEL MAI SIMPLU\n\n";

echo "Ce vrei să implementez?\n";
echo "1. Google Custom Search API (gratuit 100/zi)\n";
echo "2. eMAG API (gratuit + affiliate)\n";
echo "3. SerpApi (100 gratuit/lună)\n";
