<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\SpecKey;
use App\Models\SpecValue;
use Illuminate\Support\Str;

echo "💻 Import laptopuri 31-40...\n\n";

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
    ['name' => 'Dell Latitude 9440', 'brand' => 'Dell', 'price' => 13999.99, 'url' => 'https://versus.com/en/dell-latitude-9440', 'specs' => ['Dimensiune ecran' => '14 inch', 'Rezoluție' => '2560 x 1600 px', 'Procesor' => 'Intel Core i7-1365U', 'Nuclee CPU' => '10', 'Frecvență CPU' => '5.2 GHz', 'RAM' => '32 GB', 'Stocare' => '1 TB SSD', 'Placă video' => 'Intel Iris Xe', 'Greutate' => '1.24 kg', 'Grosime' => '13.91 mm', 'Autonomie baterie' => '21 ore', 'Capacitate baterie' => '60 Wh', 'Rată de reîmprospătare' => '60 Hz', 'Luminozitate' => '500 nits', 'Tip display' => 'IPS', 'Sistem de operare' => 'Windows 11 Pro', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.3', 'Porturi USB-C' => '2', 'Port HDMI' => 'Da', 'Jack audio' => 'Da', 'Cititor amprente' => 'Da', '5G' => 'Da']],
    ['name' => 'ASUS ROG Strix Scar 17', 'brand' => 'ASUS', 'price' => 17999.99, 'url' => 'https://versus.com/en/asus-rog-strix-scar-17', 'specs' => ['Dimensiune ecran' => '17.3 inch', 'Rezoluție' => '2560 x 1440 px', 'Procesor' => 'Intel Core i9-13980HX', 'Nuclee CPU' => '24', 'Frecvență CPU' => '5.6 GHz', 'RAM' => '32 GB', 'Stocare' => '1 TB SSD', 'Placă video' => 'NVIDIA GeForce RTX 4080', 'VRAM' => '12 GB', 'Greutate' => '3.0 kg', 'Grosime' => '28.3 mm', 'Autonomie baterie' => '5 ore', 'Capacitate baterie' => '90 Wh', 'Rată de reîmprospătare' => '240 Hz', 'Luminozitate' => '500 nits', 'Tip display' => 'IPS', 'G-Sync' => 'Da', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.2', 'Porturi USB-C' => '1', 'Port USB-A' => '3', 'Port HDMI' => 'Da', 'Jack audio' => 'Da', 'RGB Lighting' => 'Da']],
    ['name' => 'Acer Chromebook Spin 714', 'brand' => 'Acer', 'price' => 4999.99, 'url' => 'https://versus.com/en/acer-chromebook-spin-714', 'specs' => ['Dimensiune ecran' => '14 inch', 'Rezoluție' => '1920 x 1200 px', 'Procesor' => 'Intel Core i5-1335U', 'Nuclee CPU' => '10', 'Frecvență CPU' => '4.6 GHz', 'RAM' => '8 GB', 'Stocare' => '256 GB SSD', 'Placă video' => 'Intel Iris Xe', 'Greutate' => '1.4 kg', 'Grosime' => '17.9 mm', 'Autonomie baterie' => '13 ore', 'Capacitate baterie' => '56 Wh', 'Rată de reîmprospătare' => '60 Hz', 'Luminozitate' => '300 nits', 'Tip display' => 'IPS', 'Touchscreen' => 'Da', 'Convertibil 2-în-1' => 'Da', 'Sistem de operare' => 'Chrome OS', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.2', 'Porturi USB-C' => '2', 'Port USB-A' => '1', 'Jack audio' => 'Da']],
    ['name' => 'MSI Katana 15', 'brand' => 'MSI', 'price' => 7499.99, 'url' => 'https://versus.com/en/msi-katana-15', 'specs' => ['Dimensiune ecran' => '15.6 inch', 'Rezoluție' => '1920 x 1080 px', 'Procesor' => 'Intel Core i7-13620H', 'Nuclee CPU' => '10', 'Frecvență CPU' => '4.9 GHz', 'RAM' => '16 GB', 'Stocare' => '512 GB SSD', 'Placă video' => 'NVIDIA GeForce RTX 4060', 'VRAM' => '8 GB', 'Greutate' => '2.25 kg', 'Grosime' => '24.9 mm', 'Autonomie baterie' => '7 ore', 'Capacitate baterie' => '53.5 Wh', 'Rată de reîmprospătare' => '144 Hz', 'Luminozitate' => '300 nits', 'Tip display' => 'IPS', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6', 'Bluetooth' => '5.2', 'Porturi USB-C' => '1', 'Port USB-A' => '3', 'Port HDMI' => 'Da', 'Jack audio' => 'Da']],
    ['name' => 'Lenovo ThinkBook 14 Gen 5', 'brand' => 'Lenovo', 'price' => 5999.99, 'url' => 'https://versus.com/en/lenovo-thinkbook-14-gen-5', 'specs' => ['Dimensiune ecran' => '14 inch', 'Rezoluție' => '1920 x 1200 px', 'Procesor' => 'Intel Core i5-1335U', 'Nuclee CPU' => '10', 'Frecvență CPU' => '4.6 GHz', 'RAM' => '16 GB', 'Stocare' => '512 GB SSD', 'Placă video' => 'Intel Iris Xe', 'Greutate' => '1.4 kg', 'Grosime' => '17.9 mm', 'Autonomie baterie' => '15 ore', 'Capacitate baterie' => '56 Wh', 'Rată de reîmprospătare' => '60 Hz', 'Luminozitate' => '300 nits', 'Tip display' => 'IPS', 'Sistem de operare' => 'Windows 11 Pro', 'Wi-Fi' => 'Wi-Fi 6', 'Bluetooth' => '5.1', 'Porturi USB-C' => '2', 'Port USB-A' => '2', 'Port HDMI' => 'Da', 'Jack audio' => 'Da', 'Cititor amprente' => 'Da']],
    ['name' => 'HP ZBook Studio 16 G10', 'brand' => 'HP', 'price' => 19999.99, 'url' => 'https://versus.com/en/hp-zbook-studio-16-g10', 'specs' => ['Dimensiune ecran' => '16 inch', 'Rezoluție' => '3840 x 2400 px', 'Procesor' => 'Intel Core i9-13900H', 'Nuclee CPU' => '14', 'Frecvență CPU' => '5.4 GHz', 'RAM' => '64 GB', 'Stocare' => '2 TB SSD', 'Placă video' => 'NVIDIA RTX 3000 Ada', 'VRAM' => '12 GB', 'Greutate' => '2.05 kg', 'Grosime' => '19.9 mm', 'Autonomie baterie' => '10 ore', 'Capacitate baterie' => '83 Wh', 'Rată de reîmprospătare' => '120 Hz', 'Luminozitate' => '600 nits', 'Tip display' => 'OLED', 'Sistem de operare' => 'Windows 11 Pro', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.3', 'Porturi USB-C' => '2', 'Port USB-A' => '2', 'Port HDMI' => 'Da', 'Cititor carduri SD' => 'Da', 'Jack audio' => 'Da']],
    ['name' => 'ASUS ExpertBook B9', 'brand' => 'ASUS', 'price' => 11999.99, 'url' => 'https://versus.com/en/asus-expertbook-b9', 'specs' => ['Dimensiune ecran' => '14 inch', 'Rezoluție' => '1920 x 1200 px', 'Procesor' => 'Intel Core i7-1355U', 'Nuclee CPU' => '10', 'Frecvență CPU' => '5.0 GHz', 'RAM' => '16 GB', 'Stocare' => '1 TB SSD', 'Placă video' => 'Intel Iris Xe', 'Greutate' => '0.99 kg', 'Grosime' => '14.9 mm', 'Autonomie baterie' => '24 ore', 'Capacitate baterie' => '66 Wh', 'Rată de reîmprospătare' => '60 Hz', 'Luminozitate' => '400 nits', 'Tip display' => 'IPS', 'Sistem de operare' => 'Windows 11 Pro', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.2', 'Porturi USB-C' => '2', 'Port USB-A' => '1', 'Port HDMI' => 'Da', 'Jack audio' => 'Da', 'Certificare militară' => 'Da']],
    ['name' => 'Gigabyte Aorus 15 BSF', 'brand' => 'Gigabyte', 'price' => 14999.99, 'url' => 'https://versus.com/en/gigabyte-aorus-15-bsf', 'specs' => ['Dimensiune ecran' => '15.6 inch', 'Rezoluție' => '2560 x 1440 px', 'Procesor' => 'Intel Core i7-13700H', 'Nuclee CPU' => '14', 'Frecvență CPU' => '5.0 GHz', 'RAM' => '32 GB', 'Stocare' => '1 TB SSD', 'Placă video' => 'NVIDIA GeForce RTX 4070', 'VRAM' => '8 GB', 'Greutate' => '2.25 kg', 'Grosime' => '25 mm', 'Autonomie baterie' => '8 ore', 'Capacitate baterie' => '99 Wh', 'Rată de reîmprospătare' => '240 Hz', 'Luminozitate' => '400 nits', 'Tip display' => 'IPS', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.2', 'Porturi USB-C' => '1', 'Port USB-A' => '3', 'Port HDMI' => 'Da', 'Jack audio' => 'Da', 'RGB Lighting' => 'Da']],
    ['name' => 'Dell Inspiron 16 Plus', 'brand' => 'Dell', 'price' => 8499.99, 'url' => 'https://versus.com/en/dell-inspiron-16-plus', 'specs' => ['Dimensiune ecran' => '16 inch', 'Rezoluție' => '2560 x 1600 px', 'Procesor' => 'Intel Core i7-13700H', 'Nuclee CPU' => '14', 'Frecvență CPU' => '5.0 GHz', 'RAM' => '16 GB', 'Stocare' => '512 GB SSD', 'Placă video' => 'NVIDIA GeForce RTX 4050', 'VRAM' => '6 GB', 'Greutate' => '2.0 kg', 'Grosime' => '18.99 mm', 'Autonomie baterie' => '11 ore', 'Capacitate baterie' => '86 Wh', 'Rată de reîmprospătare' => '60 Hz', 'Luminozitate' => '300 nits', 'Tip display' => 'IPS', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6', 'Bluetooth' => '5.2', 'Porturi USB-C' => '2', 'Port USB-A' => '2', 'Port HDMI' => 'Da', 'Cititor carduri SD' => 'Da', 'Jack audio' => 'Da']],
    ['name' => 'Razer Blade 14 (2024)', 'brand' => 'Razer', 'price' => 16999.99, 'url' => 'https://versus.com/en/razer-blade-14-2024', 'specs' => ['Dimensiune ecran' => '14 inch', 'Rezoluție' => '2560 x 1600 px', 'Procesor' => 'AMD Ryzen 9 8945HS', 'Nuclee CPU' => '8', 'Frecvență CPU' => '5.2 GHz', 'RAM' => '32 GB', 'Stocare' => '1 TB SSD', 'Placă video' => 'NVIDIA GeForce RTX 4070', 'VRAM' => '8 GB', 'Greutate' => '1.84 kg', 'Grosime' => '16.8 mm', 'Autonomie baterie' => '8 ore', 'Capacitate baterie' => '68.1 Wh', 'Rată de reîmprospătare' => '240 Hz', 'Luminozitate' => '500 nits', 'Tip display' => 'IPS', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.3', 'Porturi USB-C' => '2', 'Port USB-A' => '2', 'Port HDMI' => 'Da', 'Jack audio' => 'Da', 'RGB Lighting' => 'Da']],
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

echo "\n✅ Import finalizat: $imported laptopuri (31-40)!\n";
echo "📊 Total laptopuri acum: " . Product::where('product_type_id', 9)->count() . "\n";
