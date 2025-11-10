<?php

// Quick search on eMAG to find real product URLs
$searches = [
    'Samsung Galaxy S24 Ultra' => 'https://www.emag.ro/search/samsung%20galaxy%20s24%20ultra',
    'Samsung Galaxy S23 FE' => 'https://www.emag.ro/search/samsung%20galaxy%20s23%20fe',
    'Samsung Galaxy A55 5G' => 'https://www.emag.ro/search/samsung%20galaxy%20a55',
    'Samsung Galaxy A35 5G' => 'https://www.emag.ro/search/samsung%20galaxy%20a35',
    'Samsung Galaxy Z Fold5' => 'https://www.emag.ro/search/samsung%20galaxy%20z%20fold5',
    'OPPO Find X7 Ultra' => 'https://www.emag.ro/search/oppo%20find%20x7%20ultra',
    'OPPO Reno 12 Pro 5G' => 'https://www.emag.ro/search/oppo%20reno%2012%20pro',
    'OPPO Reno 12 5G' => 'https://www.emag.ro/search/oppo%20reno%2012',
    'OPPO A3 Pro 5G' => 'https://www.emag.ro/search/oppo%20a3%20pro',
    'OPPO A79 5G' => 'https://www.emag.ro/search/oppo%20a79',
    'Huawei Pura 70 Ultra' => 'https://www.emag.ro/search/huawei%20pura%2070%20ultra',
    'Huawei Pura 70 Pro' => 'https://www.emag.ro/search/huawei%20pura%2070%20pro',
    'Huawei Pura 70' => 'https://www.emag.ro/search/huawei%20pura%2070',
    'Huawei Mate 60 Pro' => 'https://www.emag.ro/search/huawei%20mate%2060%20pro',
    'Huawei Mate X5' => 'https://www.emag.ro/search/huawei%20mate%20x5',
    'Huawei Nova 12 SE' => 'https://www.emag.ro/search/huawei%20nova%2012%20se',
    'Huawei Nova 12i' => 'https://www.emag.ro/search/huawei%20nova%2012i',
    'Huawei P60 Pro' => 'https://www.emag.ro/search/huawei%20p60%20pro',
];

echo "📋 eMAG Search URLs:\n\n";
echo "Visit these URLs to find real product images:\n\n";

foreach ($searches as $product => $url) {
    echo "{$product}:\n{$url}\n\n";
}

echo "\n💡 Instructions:\n";
echo "1. Visit each URL in browser\n";
echo "2. Click on first product\n";
echo "3. Right-click main product image → Copy image address\n";
echo "4. Update download-emag-images.php with real URLs\n";
