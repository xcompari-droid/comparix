<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\SpecKey;
use App\Models\SpecValue;
use Illuminate\Support\Str;

echo "💻 Import laptopuri 21-30...\n\n";

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
    ['name' => 'ASUS TUF Gaming A15', 'brand' => 'ASUS', 'price' => 7499.99, 'url' => 'https://versus.com/en/asus-tuf-gaming-a15', 'specs' => ['Dimensiune ecran' => '15.6 inch', 'Rezoluție' => '1920 x 1080 px', 'Procesor' => 'AMD Ryzen 7 7735HS', 'Nuclee CPU' => '8', 'Frecvență CPU' => '4.75 GHz', 'RAM' => '16 GB', 'Stocare' => '512 GB SSD', 'Placă video' => 'NVIDIA GeForce RTX 4050', 'VRAM' => '6 GB', 'Greutate' => '2.2 kg', 'Grosime' => '24.9 mm', 'Autonomie baterie' => '9 ore', 'Capacitate baterie' => '90 Wh', 'Rată de reîmprospătare' => '144 Hz', 'Luminozitate' => '250 nits', 'Tip display' => 'IPS', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6', 'Bluetooth' => '5.2', 'Porturi USB-C' => '1', 'Port USB-A' => '3', 'Port HDMI' => 'Da', 'Jack audio' => 'Da']],
    ['name' => 'HP Envy 13', 'brand' => 'HP', 'price' => 6999.99, 'url' => 'https://versus.com/en/hp-envy-13', 'specs' => ['Dimensiune ecran' => '13.3 inch', 'Rezoluție' => '2560 x 1600 px', 'Procesor' => 'Intel Core i7-1255U', 'Nuclee CPU' => '10', 'Frecvență CPU' => '4.7 GHz', 'RAM' => '16 GB', 'Stocare' => '512 GB SSD', 'Placă video' => 'Intel Iris Xe', 'Greutate' => '1.3 kg', 'Grosime' => '16.9 mm', 'Autonomie baterie' => '14 ore', 'Capacitate baterie' => '51 Wh', 'Rată de reîmprospătare' => '60 Hz', 'Luminozitate' => '400 nits', 'Tip display' => 'IPS', 'Touchscreen' => 'Da', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6', 'Bluetooth' => '5.2', 'Porturi USB-C' => '2', 'Port USB-A' => '1', 'Jack audio' => 'Da', 'Cititor amprente' => 'Da']],
    ['name' => 'Lenovo Yoga 9i Gen 8', 'brand' => 'Lenovo', 'price' => 11999.99, 'url' => 'https://versus.com/en/lenovo-yoga-9i-gen-8', 'specs' => ['Dimensiune ecran' => '14 inch', 'Rezoluție' => '2880 x 1800 px', 'Procesor' => 'Intel Core i7-1360P', 'Nuclee CPU' => '12', 'Frecvență CPU' => '5.0 GHz', 'RAM' => '16 GB', 'Stocare' => '1 TB SSD', 'Placă video' => 'Intel Iris Xe', 'Greutate' => '1.4 kg', 'Grosime' => '15.2 mm', 'Autonomie baterie' => '17 ore', 'Capacitate baterie' => '75 Wh', 'Rată de reîmprospătare' => '60 Hz', 'Luminozitate' => '400 nits', 'Tip display' => 'OLED', 'Touchscreen' => 'Da', 'Convertibil 2-în-1' => 'Da', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.1', 'Porturi USB-C' => '2', 'Jack audio' => 'Da']],
    ['name' => 'Acer Nitro 5', 'brand' => 'Acer', 'price' => 6499.99, 'url' => 'https://versus.com/en/acer-nitro-5', 'specs' => ['Dimensiune ecran' => '15.6 inch', 'Rezoluție' => '1920 x 1080 px', 'Procesor' => 'Intel Core i5-12500H', 'Nuclee CPU' => '12', 'Frecvență CPU' => '4.5 GHz', 'RAM' => '16 GB', 'Stocare' => '512 GB SSD', 'Placă video' => 'NVIDIA GeForce RTX 4050', 'VRAM' => '6 GB', 'Greutate' => '2.5 kg', 'Grosime' => '26.9 mm', 'Autonomie baterie' => '7 ore', 'Capacitate baterie' => '57 Wh', 'Rată de reîmprospătare' => '144 Hz', 'Luminozitate' => '300 nits', 'Tip display' => 'IPS', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6', 'Bluetooth' => '5.1', 'Porturi USB-C' => '1', 'Port USB-A' => '3', 'Port HDMI' => 'Da', 'Port Ethernet' => 'Da', 'Jack audio' => 'Da']],
    ['name' => 'MSI Prestige 14 Evo', 'brand' => 'MSI', 'price' => 8999.99, 'url' => 'https://versus.com/en/msi-prestige-14-evo', 'specs' => ['Dimensiune ecran' => '14 inch', 'Rezoluție' => '1920 x 1080 px', 'Procesor' => 'Intel Core i7-1360P', 'Nuclee CPU' => '12', 'Frecvență CPU' => '5.0 GHz', 'RAM' => '16 GB', 'Stocare' => '512 GB SSD', 'Placă video' => 'Intel Iris Xe', 'Greutate' => '1.29 kg', 'Grosime' => '15.9 mm', 'Autonomie baterie' => '16 ore', 'Capacitate baterie' => '52 Wh', 'Rată de reîmprospătare' => '60 Hz', 'Luminozitate' => '300 nits', 'Tip display' => 'IPS', 'Intel Evo' => 'Da', 'Sistem de operare' => 'Windows 11 Pro', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.2', 'Porturi USB-C' => '2', 'Port USB-A' => '1', 'Jack audio' => 'Da', 'Cititor amprente' => 'Da']],
    ['name' => 'Gigabyte G5', 'brand' => 'Gigabyte', 'price' => 7999.99, 'url' => 'https://versus.com/en/gigabyte-g5', 'specs' => ['Dimensiune ecran' => '15.6 inch', 'Rezoluție' => '1920 x 1080 px', 'Procesor' => 'Intel Core i5-12500H', 'Nuclee CPU' => '12', 'Frecvență CPU' => '4.5 GHz', 'RAM' => '16 GB', 'Stocare' => '512 GB SSD', 'Placă video' => 'NVIDIA GeForce RTX 4060', 'VRAM' => '8 GB', 'Greutate' => '2.08 kg', 'Grosime' => '23 mm', 'Autonomie baterie' => '6 ore', 'Capacitate baterie' => '54 Wh', 'Rată de reîmprospătare' => '144 Hz', 'Luminozitate' => '250 nits', 'Tip display' => 'IPS', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6', 'Bluetooth' => '5.2', 'Porturi USB-C' => '1', 'Port USB-A' => '3', 'Port HDMI' => 'Da', 'Jack audio' => 'Da']],
    ['name' => 'Samsung Galaxy Book4 Pro', 'brand' => 'Samsung', 'price' => 10999.99, 'url' => 'https://versus.com/en/samsung-galaxy-book4-pro', 'specs' => ['Dimensiune ecran' => '14 inch', 'Rezoluție' => '2880 x 1800 px', 'Procesor' => 'Intel Core Ultra 7 155H', 'Nuclee CPU' => '16', 'Frecvență CPU' => '4.8 GHz', 'RAM' => '16 GB', 'Stocare' => '512 GB SSD', 'Placă video' => 'Intel Arc', 'Greutate' => '1.21 kg', 'Grosime' => '11.6 mm', 'Autonomie baterie' => '18 ore', 'Capacitate baterie' => '63 Wh', 'Rată de reîmprospătare' => '120 Hz', 'Luminozitate' => '400 nits', 'Tip display' => 'AMOLED', 'Touchscreen' => 'Da', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.3', 'Porturi USB-C' => '2', 'Port USB-A' => '1', 'Jack audio' => 'Da']],
    ['name' => 'ASUS VivoBook Pro 15', 'brand' => 'ASUS', 'price' => 5999.99, 'url' => 'https://versus.com/en/asus-vivobook-pro-15', 'specs' => ['Dimensiune ecran' => '15.6 inch', 'Rezoluție' => '1920 x 1080 px', 'Procesor' => 'AMD Ryzen 5 5600H', 'Nuclee CPU' => '6', 'Frecvență CPU' => '4.2 GHz', 'RAM' => '16 GB', 'Stocare' => '512 GB SSD', 'Placă video' => 'NVIDIA GeForce GTX 1650', 'VRAM' => '4 GB', 'Greutate' => '1.8 kg', 'Grosime' => '18.9 mm', 'Autonomie baterie' => '9 ore', 'Capacitate baterie' => '63 Wh', 'Rată de reîmprospătare' => '60 Hz', 'Luminozitate' => '300 nits', 'Tip display' => 'IPS', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6', 'Bluetooth' => '5.0', 'Porturi USB-C' => '1', 'Port USB-A' => '3', 'Port HDMI' => 'Da', 'Jack audio' => 'Da']],
    ['name' => 'HP Pavilion Plus 14', 'brand' => 'HP', 'price' => 6499.99, 'url' => 'https://versus.com/en/hp-pavilion-plus-14', 'specs' => ['Dimensiune ecran' => '14 inch', 'Rezoluție' => '2880 x 1800 px', 'Procesor' => 'Intel Core i7-13700H', 'Nuclee CPU' => '14', 'Frecvență CPU' => '5.0 GHz', 'RAM' => '16 GB', 'Stocare' => '512 GB SSD', 'Placă video' => 'Intel Iris Xe', 'Greutate' => '1.44 kg', 'Grosime' => '17.9 mm', 'Autonomie baterie' => '12 ore', 'Capacitate baterie' => '51 Wh', 'Rată de reîmprospătare' => '90 Hz', 'Luminozitate' => '400 nits', 'Tip display' => 'OLED', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.3', 'Porturi USB-C' => '2', 'Port USB-A' => '2', 'Port HDMI' => 'Da', 'Jack audio' => 'Da']],
    ['name' => 'Lenovo IdeaPad Gaming 3', 'brand' => 'Lenovo', 'price' => 5499.99, 'url' => 'https://versus.com/en/lenovo-ideapad-gaming-3', 'specs' => ['Dimensiune ecran' => '15.6 inch', 'Rezoluție' => '1920 x 1080 px', 'Procesor' => 'AMD Ryzen 5 7535HS', 'Nuclee CPU' => '6', 'Frecvență CPU' => '4.55 GHz', 'RAM' => '16 GB', 'Stocare' => '512 GB SSD', 'Placă video' => 'NVIDIA GeForce RTX 3050', 'VRAM' => '4 GB', 'Greutate' => '2.25 kg', 'Grosime' => '24.2 mm', 'Autonomie baterie' => '8 ore', 'Capacitate baterie' => '45 Wh', 'Rată de reîmprospătare' => '120 Hz', 'Luminozitate' => '250 nits', 'Tip display' => 'IPS', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6', 'Bluetooth' => '5.1', 'Porturi USB-C' => '1', 'Port USB-A' => '2', 'Port HDMI' => 'Da', 'Port Ethernet' => 'Da', 'Jack audio' => 'Da']],
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

echo "\n✅ Import finalizat: $imported laptopuri (21-30)!\n";
echo "📊 Total laptopuri acum: " . Product::where('product_type_id', 9)->count() . "\n";
