<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\SpecKey;
use App\Models\SpecValue;
use Illuminate\Support\Str;

echo "💻 Import laptopuri 41-50 (ULTIMELE)...\n\n";

$specKeyCache = [];

function getOrCreateSpecKey($name, &$cache) {
    if (isset($cache[$name])) {
        return $cache[$name];
    }
    
    $specKey = SpecKey::where('name', $name)
        ->where('product_type_id', 9)
        ->first();
        
    if (!$specKey) {
        $specKey = SpecKey::create([
            'name' => $name,
            'slug' => Str::slug($name),
            'product_type_id' => 9,
        ]);
    }
    
    $cache[$name] = $specKey;
    return $specKey;
}

function addSpec($product, $specName, $specValue, &$cache) {
    $specKey = getOrCreateSpecKey($specName, $cache);
    
    if (is_numeric($specValue)) {
        SpecValue::create([
            'product_id' => $product->id,
            'spec_key_id' => $specKey->id,
            'value_number' => (float) $specValue,
        ]);
    } elseif (strtolower($specValue) === 'da' || strtolower($specValue) === 'nu') {
        SpecValue::create([
            'product_id' => $product->id,
            'spec_key_id' => $specKey->id,
            'value_bool' => strtolower($specValue) === 'da',
        ]);
    } else {
        SpecValue::create([
            'product_id' => $product->id,
            'spec_key_id' => $specKey->id,
            'value_string' => $specValue,
        ]);
    }
}

$laptops = [
    ['name' => 'LG Gram Style 16', 'brand' => 'LG', 'price' => 12999.99, 'url' => 'https://versus.com/en/lg-gram-style-16', 'specs' => ['Dimensiune ecran' => '16 inch', 'Rezoluție' => '3200 x 2000 px', 'Procesor' => 'Intel Core i7-1360P', 'Nuclee CPU' => '12', 'Frecvență CPU' => '5.0 GHz', 'RAM' => '16 GB', 'Stocare' => '1 TB SSD', 'Placă video' => 'Intel Iris Xe', 'Greutate' => '1.48 kg', 'Grosime' => '16.8 mm', 'Autonomie baterie' => '18 ore', 'Capacitate baterie' => '80 Wh', 'Rată de reîmprospătare' => '120 Hz', 'Luminozitate' => '400 nits', 'Tip display' => 'OLED', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.1', 'Porturi USB-C' => '2', 'Port USB-A' => '2', 'Port HDMI' => 'Da', 'Jack audio' => 'Da']],
    ['name' => 'Microsoft Surface Laptop 5', 'brand' => 'Microsoft', 'price' => 9999.99, 'url' => 'https://versus.com/en/microsoft-surface-laptop-5', 'specs' => ['Dimensiune ecran' => '13.5 inch', 'Rezoluție' => '2256 x 1504 px', 'Procesor' => 'Intel Core i7-1255U', 'Nuclee CPU' => '10', 'Frecvență CPU' => '4.7 GHz', 'RAM' => '16 GB', 'Stocare' => '512 GB SSD', 'Placă video' => 'Intel Iris Xe', 'Greutate' => '1.27 kg', 'Grosime' => '14.5 mm', 'Autonomie baterie' => '17 ore', 'Capacitate baterie' => '47.4 Wh', 'Rată de reîmprospătare' => '60 Hz', 'Luminozitate' => '350 nits', 'Tip display' => 'IPS', 'Touchscreen' => 'Da', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6', 'Bluetooth' => '5.1', 'Porturi USB-C' => '1', 'Port USB-A' => '1', 'Jack audio' => 'Da']],
    ['name' => 'ASUS ProArt Studiobook 16', 'brand' => 'ASUS', 'price' => 18999.99, 'url' => 'https://versus.com/en/asus-proart-studiobook-16', 'specs' => ['Dimensiune ecran' => '16 inch', 'Rezoluție' => '3840 x 2400 px', 'Procesor' => 'Intel Core i9-13980HX', 'Nuclee CPU' => '24', 'Frecvență CPU' => '5.6 GHz', 'RAM' => '64 GB', 'Stocare' => '2 TB SSD', 'Placă video' => 'NVIDIA RTX 4000 Ada', 'VRAM' => '20 GB', 'Greutate' => '2.4 kg', 'Grosime' => '19.4 mm', 'Autonomie baterie' => '9 ore', 'Capacitate baterie' => '90 Wh', 'Rată de reîmprospătare' => '120 Hz', 'Luminozitate' => '600 nits', 'Tip display' => 'OLED', 'Calibrare culori' => 'Da', 'Sistem de operare' => 'Windows 11 Pro', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.2', 'Porturi USB-C' => '2', 'Port USB-A' => '1', 'Cititor carduri SD' => 'Da', 'Jack audio' => 'Da']],
    ['name' => 'Acer TravelMate P4', 'brand' => 'Acer', 'price' => 5499.99, 'url' => 'https://versus.com/en/acer-travelmate-p4', 'specs' => ['Dimensiune ecran' => '14 inch', 'Rezoluție' => '1920 x 1200 px', 'Procesor' => 'Intel Core i5-1335U', 'Nuclee CPU' => '10', 'Frecvență CPU' => '4.6 GHz', 'RAM' => '16 GB', 'Stocare' => '512 GB SSD', 'Placă video' => 'Intel Iris Xe', 'Greutate' => '1.4 kg', 'Grosime' => '17.95 mm', 'Autonomie baterie' => '17 ore', 'Capacitate baterie' => '56 Wh', 'Rată de reîmprospătare' => '60 Hz', 'Luminozitate' => '300 nits', 'Tip display' => 'IPS', 'Sistem de operare' => 'Windows 11 Pro', 'Wi-Fi' => 'Wi-Fi 6', 'Bluetooth' => '5.1', 'Porturi USB-C' => '2', 'Port USB-A' => '2', 'Port HDMI' => 'Da', 'Port Ethernet' => 'Da', 'Jack audio' => 'Da', 'Certificare militară' => 'Da']],
    ['name' => 'MSI Modern 14', 'brand' => 'MSI', 'price' => 4999.99, 'url' => 'https://versus.com/en/msi-modern-14', 'specs' => ['Dimensiune ecran' => '14 inch', 'Rezoluție' => '1920 x 1080 px', 'Procesor' => 'Intel Core i5-1235U', 'Nuclee CPU' => '10', 'Frecvență CPU' => '4.4 GHz', 'RAM' => '16 GB', 'Stocare' => '512 GB SSD', 'Placă video' => 'Intel Iris Xe', 'Greutate' => '1.4 kg', 'Grosime' => '16.9 mm', 'Autonomie baterie' => '12 ore', 'Capacitate baterie' => '52 Wh', 'Rată de reîmprospătare' => '60 Hz', 'Luminozitate' => '250 nits', 'Tip display' => 'IPS', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6', 'Bluetooth' => '5.1', 'Porturi USB-C' => '2', 'Port USB-A' => '2', 'Port HDMI' => 'Da', 'Jack audio' => 'Da']],
    ['name' => 'Lenovo ThinkPad P1 Gen 6', 'brand' => 'Lenovo', 'price' => 22999.99, 'url' => 'https://versus.com/en/lenovo-thinkpad-p1-gen-6', 'specs' => ['Dimensiune ecran' => '16 inch', 'Rezoluție' => '3840 x 2400 px', 'Procesor' => 'Intel Core i9-13900H', 'Nuclee CPU' => '14', 'Frecvență CPU' => '5.4 GHz', 'RAM' => '64 GB', 'Stocare' => '2 TB SSD', 'Placă video' => 'NVIDIA RTX 5000 Ada', 'VRAM' => '16 GB', 'Greutate' => '1.89 kg', 'Grosime' => '17.2 mm', 'Autonomie baterie' => '11 ore', 'Capacitate baterie' => '90 Wh', 'Rată de reîmprospătare' => '165 Hz', 'Luminozitate' => '600 nits', 'Tip display' => 'IPS', 'Sistem de operare' => 'Windows 11 Pro', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.3', 'Porturi USB-C' => '2', 'Port USB-A' => '2', 'Port HDMI' => 'Da', 'Cititor carduri SD' => 'Da', 'Jack audio' => 'Da']],
    ['name' => 'HP Dragonfly G4', 'brand' => 'HP', 'price' => 14999.99, 'url' => 'https://versus.com/en/hp-dragonfly-g4', 'specs' => ['Dimensiune ecran' => '13.5 inch', 'Rezoluție' => '1920 x 1280 px', 'Procesor' => 'Intel Core i7-1365U', 'Nuclee CPU' => '10', 'Frecvență CPU' => '5.2 GHz', 'RAM' => '32 GB', 'Stocare' => '1 TB SSD', 'Placă video' => 'Intel Iris Xe', 'Greutate' => '0.99 kg', 'Grosime' => '16.4 mm', 'Autonomie baterie' => '20 ore', 'Capacitate baterie' => '68 Wh', 'Rată de reîmprospătare' => '60 Hz', 'Luminozitate' => '1000 nits', 'Tip display' => 'IPS', 'Touchscreen' => 'Da', 'Convertibil 2-în-1' => 'Da', 'Sistem de operare' => 'Windows 11 Pro', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.3', 'Porturi USB-C' => '2', 'Jack audio' => 'Da', '5G' => 'Da']],
    ['name' => 'ASUS Chromebook Flip CX5', 'brand' => 'ASUS', 'price' => 4499.99, 'url' => 'https://versus.com/en/asus-chromebook-flip-cx5', 'specs' => ['Dimensiune ecran' => '15.6 inch', 'Rezoluție' => '1920 x 1080 px', 'Procesor' => 'Intel Core i5-1135G7', 'Nuclee CPU' => '4', 'Frecvență CPU' => '4.2 GHz', 'RAM' => '8 GB', 'Stocare' => '256 GB SSD', 'Placă video' => 'Intel Iris Xe', 'Greutate' => '1.95 kg', 'Grosime' => '18.9 mm', 'Autonomie baterie' => '12 ore', 'Capacitate baterie' => '57 Wh', 'Rată de reîmprospătare' => '60 Hz', 'Luminozitate' => '250 nits', 'Tip display' => 'IPS', 'Touchscreen' => 'Da', 'Convertibil 2-în-1' => 'Da', 'Sistem de operare' => 'Chrome OS', 'Wi-Fi' => 'Wi-Fi 6', 'Bluetooth' => '5.0', 'Porturi USB-C' => '2', 'Port USB-A' => '1', 'Jack audio' => 'Da']],
    ['name' => 'Dell Precision 5680', 'brand' => 'Dell', 'price' => 20999.99, 'url' => 'https://versus.com/en/dell-precision-5680', 'specs' => ['Dimensiune ecran' => '16 inch', 'Rezoluție' => '3840 x 2400 px', 'Procesor' => 'Intel Core i9-13900H', 'Nuclee CPU' => '14', 'Frecvență CPU' => '5.4 GHz', 'RAM' => '64 GB', 'Stocare' => '2 TB SSD', 'Placă video' => 'NVIDIA RTX 5000 Ada', 'VRAM' => '16 GB', 'Greutate' => '2.05 kg', 'Grosime' => '17.4 mm', 'Autonomie baterie' => '10 ore', 'Capacitate baterie' => '95 Wh', 'Rată de reîmprospătare' => '60 Hz', 'Luminozitate' => '500 nits', 'Tip display' => 'OLED', 'Sistem de operare' => 'Windows 11 Pro', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.3', 'Porturi USB-C' => '3', 'Port USB-A' => '1', 'Cititor carduri SD' => 'Da', 'Jack audio' => 'Da']],
    ['name' => 'Samsung Galaxy Book3 Pro 360', 'brand' => 'Samsung', 'price' => 13499.99, 'url' => 'https://versus.com/en/samsung-galaxy-book3-pro-360', 'specs' => ['Dimensiune ecran' => '16 inch', 'Rezoluție' => '2880 x 1800 px', 'Procesor' => 'Intel Core i7-1360P', 'Nuclee CPU' => '12', 'Frecvență CPU' => '5.0 GHz', 'RAM' => '16 GB', 'Stocare' => '512 GB SSD', 'Placă video' => 'Intel Iris Xe', 'Greutate' => '1.66 kg', 'Grosime' => '12.8 mm', 'Autonomie baterie' => '20 ore', 'Capacitate baterie' => '76 Wh', 'Rată de reîmprospătare' => '120 Hz', 'Luminozitate' => '500 nits', 'Tip display' => 'AMOLED', 'Touchscreen' => 'Da', 'Convertibil 2-în-1' => 'Da', 'Suport stylus' => 'Da', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.1', 'Porturi USB-C' => '2', 'Port USB-A' => '1', 'Jack audio' => 'Da']],
];

$imported = 0;

foreach ($laptops as $laptop) {
    $product = Product::updateOrCreate(
        [
            'name' => $laptop['name'],
            'product_type_id' => 9,
        ],
        [
            'brand' => $laptop['brand'],
            'price' => $laptop['price'],
            'image_url' => 'https://ui-avatars.com/api/?name=' . urlencode($laptop['name']) . '&size=400&background=667eea&color=fff',
            'source_url' => $laptop['url'],
        ]
    );
    
    SpecValue::where('product_id', $product->id)->delete();
    
    foreach ($laptop['specs'] as $specName => $specValue) {
        addSpec($product, $specName, $specValue, $specKeyCache);
    }
    
    $imported++;
    echo "✓ {$laptop['name']}\n";
}

echo "\n✅ Import finalizat: $imported laptopuri (41-50)!\n";
echo "📊 Total laptopuri acum: " . Product::where('product_type_id', 9)->count() . "\n";
echo "\n🎉 TOATE CELE 50 DE LAPTOPURI AU FOST IMPORTATE!\n";
