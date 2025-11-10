<?php

echo "🎯 GHID SIMPLU: Descărcare Manuală Imagini\n\n";
echo "════════════════════════════════════════════\n\n";

echo "📋 PAȘI:\n\n";

echo "1️⃣  Deschide acest folder în Windows Explorer:\n";
echo "    C:\\Users\\calin\\Documents\\comparix\\public\\images\\products\n\n";

echo "2️⃣  Pentru fiecare produs de mai jos:\n";
echo "    - Deschide link-ul în browser\n";
echo "    - Găsește produsul\n";
echo "    - Click DREAPTA pe imaginea principală\n";
echo "    - Selectează 'Save image as...'\n";
echo "    - Salvează cu NUMELE EXACT specificat\n\n";

echo "════════════════════════════════════════════\n\n";

$products = [
    [
        'name' => 'Samsung Galaxy S24 Ultra',
        'filename' => 'samsung-galaxy-s24-ultra.jpg',
        'search' => 'https://www.google.com/search?q=samsung+galaxy+s24+ultra+official+image&tbm=isch',
        'emag' => 'https://www.emag.ro/telefon-mobil-samsung-galaxy-s24-ultra-dual-sim-256gb-12gb-ram-5g-titanium-black-sm-s928bzkgeue/pd/D8PM21MBM/',
    ],
    [
        'name' => 'OPPO Reno 12 Pro 5G',
        'filename' => 'oppo-reno-12-pro-5g.jpg',
        'search' => 'https://www.google.com/search?q=oppo+reno+12+pro+official+image&tbm=isch',
        'emag' => 'https://www.emag.ro/search/oppo%20reno%2012%20pro',
    ],
    [
        'name' => 'OPPO Reno 12 5G',
        'filename' => 'oppo-reno-12-5g.jpg',
        'search' => 'https://www.google.com/search?q=oppo+reno+12+official+image&tbm=isch',
        'emag' => 'https://www.emag.ro/search/oppo%20reno%2012',
    ],
    [
        'name' => 'OPPO A3 Pro 5G',
        'filename' => 'oppo-a3-pro-5g.jpg',
        'search' => 'https://www.google.com/search?q=oppo+a3+pro+5g+official+image&tbm=isch',
        'emag' => 'https://www.emag.ro/search/oppo%20a3%20pro',
    ],
    [
        'name' => 'Huawei Pura 70 Ultra',
        'filename' => 'huawei-pura-70-ultra.jpg',
        'search' => 'https://www.google.com/search?q=huawei+pura+70+ultra+official+image&tbm=isch',
        'emag' => 'https://www.emag.ro/search/huawei%20pura%2070%20ultra',
    ],
    [
        'name' => 'Huawei Pura 70 Pro',
        'filename' => 'huawei-pura-70-pro.jpg',
        'search' => 'https://www.google.com/search?q=huawei+pura+70+pro+official+image&tbm=isch',
        'emag' => 'https://www.emag.ro/search/huawei%20pura%2070%20pro',
    ],
    [
        'name' => 'Huawei Pura 70',
        'filename' => 'huawei-pura-70.jpg',
        'search' => 'https://www.google.com/search?q=huawei+pura+70+official+image&tbm=isch',
        'emag' => 'https://www.emag.ro/search/huawei%20pura%2070',
    ],
];

foreach ($products as $i => $product) {
    $num = $i + 1;
    echo "──────────────────────────────────────────\n";
    echo "PRODUS #{$num}: {$product['name']}\n";
    echo "──────────────────────────────────────────\n";
    echo "Salvează ca: {$product['filename']}\n\n";
    echo "Opțiune 1 - Google Images (RECOMANDAT):\n";
    echo "{$product['search']}\n\n";
    echo "Opțiune 2 - eMAG:\n";
    echo "{$product['emag']}\n\n";
}

echo "════════════════════════════════════════════\n\n";
echo "3️⃣  După ce ai descărcat TOATE imaginile, rulează:\n";
echo "    php update-manual-images.php\n\n";

echo "4️⃣  Verifică rezultatul:\n";
echo "    php check-image-status.php\n\n";

echo "════════════════════════════════════════════\n\n";
echo "💡 SFAT:\n";
echo "   - Caută imagini mari (minim 500x500px)\n";
echo "   - Preferă PNG sau JPG de calitate\n";
echo "   - Evită imagini cu watermark\n\n";
