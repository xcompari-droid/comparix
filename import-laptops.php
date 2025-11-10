<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\SpecKey;
use App\Models\SpecValue;
use Illuminate\Support\Str;

echo "💻 Import laptopuri Versus.com...\n\n";

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
    ['name' => 'Apple MacBook Pro 16" (2023) M3 Max', 'brand' => 'Apple', 'price' => 21999.99, 'url' => 'https://versus.com/en/apple-macbook-pro-16-2023-m3-max', 'specs' => ['Dimensiune ecran' => '16 inch', 'Rezoluție' => '3456 x 2234 px', 'Procesor' => 'Apple M3 Max', 'Nuclee CPU' => '16', 'Frecvență CPU' => '4.05 GHz', 'RAM' => '36 GB', 'Stocare' => '1 TB SSD', 'Placă video' => 'Apple M3 Max GPU', 'Greutate' => '2.14 kg', 'Grosime' => '16.8 mm', 'Autonomie baterie' => '22 ore', 'Capacitate baterie' => '100 Wh', 'Rată de reîmprospătare' => '120 Hz', 'Luminozitate' => '1600 nits', 'Tip display' => 'Mini LED', 'Sistem de operare' => 'macOS', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.3', 'Porturi USB-C' => '3', 'Port HDMI' => 'Da', 'Jack audio' => 'Da']],
    ['name' => 'Dell XPS 15 9530', 'brand' => 'Dell', 'price' => 12999.99, 'url' => 'https://versus.com/en/dell-xps-15-9530', 'specs' => ['Dimensiune ecran' => '15.6 inch', 'Rezoluție' => '3840 x 2400 px', 'Procesor' => 'Intel Core i9-13900H', 'Nuclee CPU' => '14', 'Frecvență CPU' => '5.4 GHz', 'RAM' => '32 GB', 'Stocare' => '1 TB SSD', 'Placă video' => 'NVIDIA GeForce RTX 4070', 'VRAM' => '8 GB', 'Greutate' => '1.86 kg', 'Grosime' => '18 mm', 'Autonomie baterie' => '13 ore', 'Capacitate baterie' => '86 Wh', 'Rată de reîmprospătare' => '60 Hz', 'Luminozitate' => '500 nits', 'Tip display' => 'OLED', 'Touchscreen' => 'Da', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.2', 'Porturi USB-C' => '2', 'Port USB-A' => '1', 'Jack audio' => 'Da', 'Cititor carduri SD' => 'Da']],
    ['name' => 'Lenovo ThinkPad X1 Carbon Gen 11', 'brand' => 'Lenovo', 'price' => 9999.99, 'url' => 'https://versus.com/en/lenovo-thinkpad-x1-carbon-gen-11', 'specs' => ['Dimensiune ecran' => '14 inch', 'Rezoluție' => '2880 x 1800 px', 'Procesor' => 'Intel Core i7-1365U', 'Nuclee CPU' => '10', 'Frecvență CPU' => '5.2 GHz', 'RAM' => '32 GB', 'Stocare' => '1 TB SSD', 'Placă video' => 'Intel Iris Xe', 'Greutate' => '1.12 kg', 'Grosime' => '15.36 mm', 'Autonomie baterie' => '19 ore', 'Capacitate baterie' => '57 Wh', 'Rată de reîmprospătare' => '60 Hz', 'Luminozitate' => '400 nits', 'Tip display' => 'IPS', 'Sistem de operare' => 'Windows 11 Pro', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.2', 'Porturi USB-C' => '2', 'Port HDMI' => 'Da', 'Jack audio' => 'Da', 'Cititor amprente' => 'Da', 'Camera IR' => 'Da']],
    ['name' => 'ASUS ROG Zephyrus G16', 'brand' => 'ASUS', 'price' => 13499.99, 'url' => 'https://versus.com/en/asus-rog-zephyrus-g16', 'specs' => ['Dimensiune ecran' => '16 inch', 'Rezoluție' => '2560 x 1600 px', 'Procesor' => 'Intel Core i9-13900H', 'Nuclee CPU' => '14', 'Frecvență CPU' => '5.4 GHz', 'RAM' => '32 GB', 'Stocare' => '1 TB SSD', 'Placă video' => 'NVIDIA GeForce RTX 4080', 'VRAM' => '12 GB', 'Greutate' => '1.95 kg', 'Grosime' => '19.9 mm', 'Autonomie baterie' => '10 ore', 'Capacitate baterie' => '90 Wh', 'Rată de reîmprospătare' => '240 Hz', 'Luminozitate' => '500 nits', 'Tip display' => 'IPS', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.3', 'Porturi USB-C' => '2', 'Port USB-A' => '2', 'Port HDMI' => 'Da', 'Jack audio' => 'Da', 'RGB Lighting' => 'Da']],
    ['name' => 'HP Spectre x360 14', 'brand' => 'HP', 'price' => 10999.99, 'url' => 'https://versus.com/en/hp-spectre-x360-14', 'specs' => ['Dimensiune ecran' => '13.5 inch', 'Rezoluție' => '3000 x 2000 px', 'Procesor' => 'Intel Core i7-1355U', 'Nuclee CPU' => '10', 'Frecvență CPU' => '5.0 GHz', 'RAM' => '16 GB', 'Stocare' => '512 GB SSD', 'Placă video' => 'Intel Iris Xe', 'Greutate' => '1.39 kg', 'Grosime' => '17 mm', 'Autonomie baterie' => '16 ore', 'Capacitate baterie' => '66 Wh', 'Rată de reîmprospătare' => '60 Hz', 'Luminozitate' => '400 nits', 'Tip display' => 'OLED', 'Touchscreen' => 'Da', 'Convertibil 2-în-1' => 'Da', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.3', 'Porturi USB-C' => '2', 'Port USB-A' => '1', 'Jack audio' => 'Da', 'Cititor amprente' => 'Da']],
    ['name' => 'Microsoft Surface Laptop Studio 2', 'brand' => 'Microsoft', 'price' => 14999.99, 'url' => 'https://versus.com/en/microsoft-surface-laptop-studio-2', 'specs' => ['Dimensiune ecran' => '14.4 inch', 'Rezoluție' => '2400 x 1600 px', 'Procesor' => 'Intel Core i7-13700H', 'Nuclee CPU' => '14', 'Frecvență CPU' => '5.0 GHz', 'RAM' => '32 GB', 'Stocare' => '1 TB SSD', 'Placă video' => 'NVIDIA GeForce RTX 4060', 'VRAM' => '8 GB', 'Greutate' => '2.0 kg', 'Grosime' => '18.94 mm', 'Autonomie baterie' => '18 ore', 'Capacitate baterie' => '58 Wh', 'Rată de reîmprospătare' => '120 Hz', 'Luminozitate' => '450 nits', 'Tip display' => 'IPS', 'Touchscreen' => 'Da', 'Suport stylus' => 'Da', 'Sistem de operare' => 'Windows 11 Pro', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.3', 'Porturi USB-C' => '2', 'Jack audio' => 'Da']],
    ['name' => 'Razer Blade 15 Advanced', 'brand' => 'Razer', 'price' => 15999.99, 'url' => 'https://versus.com/en/razer-blade-15-advanced', 'specs' => ['Dimensiune ecran' => '15.6 inch', 'Rezoluție' => '2560 x 1440 px', 'Procesor' => 'Intel Core i9-13950HX', 'Nuclee CPU' => '24', 'Frecvență CPU' => '5.5 GHz', 'RAM' => '32 GB', 'Stocare' => '1 TB SSD', 'Placă video' => 'NVIDIA GeForce RTX 4080', 'VRAM' => '12 GB', 'Greutate' => '2.01 kg', 'Grosime' => '16.99 mm', 'Autonomie baterie' => '6 ore', 'Capacitate baterie' => '80 Wh', 'Rată de reîmprospătare' => '240 Hz', 'Luminozitate' => '400 nits', 'Tip display' => 'IPS', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.3', 'Porturi USB-C' => '2', 'Port USB-A' => '3', 'Port HDMI' => 'Da', 'Jack audio' => 'Da', 'RGB Lighting' => 'Da']],
    ['name' => 'LG Gram 17', 'brand' => 'LG', 'price' => 8999.99, 'url' => 'https://versus.com/en/lg-gram-17', 'specs' => ['Dimensiune ecran' => '17 inch', 'Rezoluție' => '2560 x 1600 px', 'Procesor' => 'Intel Core i7-1360P', 'Nuclee CPU' => '12', 'Frecvență CPU' => '5.0 GHz', 'RAM' => '16 GB', 'Stocare' => '512 GB SSD', 'Placă video' => 'Intel Iris Xe', 'Greutate' => '1.35 kg', 'Grosime' => '17.78 mm', 'Autonomie baterie' => '20 ore', 'Capacitate baterie' => '80 Wh', 'Rată de reîmprospătare' => '60 Hz', 'Luminozitate' => '350 nits', 'Tip display' => 'IPS', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.1', 'Porturi USB-C' => '2', 'Port USB-A' => '2', 'Port HDMI' => 'Da', 'Jack audio' => 'Da', 'Cititor carduri SD' => 'Da']],
    ['name' => 'Acer Predator Helios 16', 'brand' => 'Acer', 'price' => 11999.99, 'url' => 'https://versus.com/en/acer-predator-helios-16', 'specs' => ['Dimensiune ecran' => '16 inch', 'Rezoluție' => '2560 x 1600 px', 'Procesor' => 'Intel Core i9-13900HX', 'Nuclee CPU' => '24', 'Frecvență CPU' => '5.4 GHz', 'RAM' => '32 GB', 'Stocare' => '1 TB SSD', 'Placă video' => 'NVIDIA GeForce RTX 4070', 'VRAM' => '8 GB', 'Greutate' => '2.6 kg', 'Grosime' => '26.9 mm', 'Autonomie baterie' => '8 ore', 'Capacitate baterie' => '90 Wh', 'Rată de reîmprospătare' => '165 Hz', 'Luminozitate' => '500 nits', 'Tip display' => 'IPS', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.2', 'Porturi USB-C' => '1', 'Port USB-A' => '3', 'Port HDMI' => 'Da', 'Port Ethernet' => 'Da', 'Jack audio' => 'Da']],
    ['name' => 'MSI Creator Z17', 'brand' => 'MSI', 'price' => 16999.99, 'url' => 'https://versus.com/en/msi-creator-z17', 'specs' => ['Dimensiune ecran' => '17 inch', 'Rezoluție' => '3840 x 2160 px', 'Procesor' => 'Intel Core i9-12900H', 'Nuclee CPU' => '14', 'Frecvență CPU' => '5.0 GHz', 'RAM' => '64 GB', 'Stocare' => '2 TB SSD', 'Placă video' => 'NVIDIA GeForce RTX 3080 Ti', 'VRAM' => '16 GB', 'Greutate' => '2.5 kg', 'Grosime' => '20.5 mm', 'Autonomie baterie' => '8 ore', 'Capacitate baterie' => '90 Wh', 'Rată de reîmprospătare' => '120 Hz', 'Luminozitate' => '600 nits', 'Tip display' => 'Mini LED', 'Touchscreen' => 'Da', 'Sistem de operare' => 'Windows 11 Pro', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.2', 'Porturi USB-C' => '2', 'Port USB-A' => '2', 'Port HDMI' => 'Da', 'Cititor carduri SD' => 'Da', 'Jack audio' => 'Da']],
    ['name' => 'Gigabyte Aero 16 OLED', 'brand' => 'Gigabyte', 'price' => 13999.99, 'url' => 'https://versus.com/en/gigabyte-aero-16-oled', 'specs' => ['Dimensiune ecran' => '16 inch', 'Rezoluție' => '3840 x 2400 px', 'Procesor' => 'Intel Core i9-13900H', 'Nuclee CPU' => '14', 'Frecvență CPU' => '5.4 GHz', 'RAM' => '32 GB', 'Stocare' => '1 TB SSD', 'Placă video' => 'NVIDIA GeForce RTX 4070', 'VRAM' => '8 GB', 'Greutate' => '2.1 kg', 'Grosime' => '20 mm', 'Autonomie baterie' => '10 ore', 'Capacitate baterie' => '99 Wh', 'Rată de reîmprospătare' => '60 Hz', 'Luminozitate' => '400 nits', 'Tip display' => 'OLED', 'Sistem de operare' => 'Windows 11 Pro', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.2', 'Porturi USB-C' => '2', 'Port USB-A' => '3', 'Port HDMI' => 'Da', 'Jack audio' => 'Da', 'Cititor carduri SD' => 'Da']],
    ['name' => 'Samsung Galaxy Book3 Ultra', 'brand' => 'Samsung', 'price' => 12499.99, 'url' => 'https://versus.com/en/samsung-galaxy-book3-ultra', 'specs' => ['Dimensiune ecran' => '16 inch', 'Rezoluție' => '2880 x 1800 px', 'Procesor' => 'Intel Core i9-13900H', 'Nuclee CPU' => '14', 'Frecvență CPU' => '5.4 GHz', 'RAM' => '32 GB', 'Stocare' => '1 TB SSD', 'Placă video' => 'NVIDIA GeForce RTX 4070', 'VRAM' => '8 GB', 'Greutate' => '1.81 kg', 'Grosime' => '16.5 mm', 'Autonomie baterie' => '12 ore', 'Capacitate baterie' => '76 Wh', 'Rată de reîmprospătare' => '120 Hz', 'Luminozitate' => '400 nits', 'Tip display' => 'AMOLED', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.1', 'Porturi USB-C' => '2', 'Port USB-A' => '1', 'Port HDMI' => 'Da', 'Jack audio' => 'Da']],
    ['name' => 'Alienware m18 R1', 'brand' => 'Dell', 'price' => 18999.99, 'url' => 'https://versus.com/en/alienware-m18-r1', 'specs' => ['Dimensiune ecran' => '18 inch', 'Rezoluție' => '2560 x 1600 px', 'Procesor' => 'Intel Core i9-13980HX', 'Nuclee CPU' => '24', 'Frecvență CPU' => '5.6 GHz', 'RAM' => '64 GB', 'Stocare' => '2 TB SSD', 'Placă video' => 'NVIDIA GeForce RTX 4090', 'VRAM' => '16 GB', 'Greutate' => '3.97 kg', 'Grosime' => '26.9 mm', 'Autonomie baterie' => '5 ore', 'Capacitate baterie' => '97 Wh', 'Rată de reîmprospătare' => '165 Hz', 'Luminozitate' => '300 nits', 'Tip display' => 'IPS', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.3', 'Porturi USB-C' => '1', 'Port USB-A' => '3', 'Port HDMI' => 'Da', 'Port Ethernet' => 'Da', 'Jack audio' => 'Da', 'RGB Lighting' => 'Da']],
    ['name' => 'Framework Laptop 16', 'brand' => 'Framework', 'price' => 10999.99, 'url' => 'https://versus.com/en/framework-laptop-16', 'specs' => ['Dimensiune ecran' => '16 inch', 'Rezoluție' => '2560 x 1600 px', 'Procesor' => 'AMD Ryzen 9 7940HS', 'Nuclee CPU' => '8', 'Frecvență CPU' => '5.2 GHz', 'RAM' => '32 GB', 'Stocare' => '1 TB SSD', 'Placă video' => 'AMD Radeon RX 7700S', 'VRAM' => '8 GB', 'Greutate' => '2.1 kg', 'Grosime' => '18.8 mm', 'Autonomie baterie' => '10 ore', 'Capacitate baterie' => '85 Wh', 'Rată de reîmprospătare' => '165 Hz', 'Luminozitate' => '500 nits', 'Tip display' => 'IPS', 'Modular' => 'Da', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.2', 'Reparabilitate' => '10/10']],
    ['name' => 'ASUS Zenbook S 13 OLED', 'brand' => 'ASUS', 'price' => 7999.99, 'url' => 'https://versus.com/en/asus-zenbook-s-13-oled', 'specs' => ['Dimensiune ecran' => '13.3 inch', 'Rezoluție' => '2880 x 1800 px', 'Procesor' => 'AMD Ryzen 7 7735U', 'Nuclee CPU' => '8', 'Frecvență CPU' => '4.75 GHz', 'RAM' => '16 GB', 'Stocare' => '512 GB SSD', 'Placă video' => 'AMD Radeon', 'Greutate' => '1.0 kg', 'Grosime' => '14.9 mm', 'Autonomie baterie' => '19 ore', 'Capacitate baterie' => '67 Wh', 'Rată de reîmprospătare' => '60 Hz', 'Luminozitate' => '550 nits', 'Tip display' => 'OLED', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.2', 'Porturi USB-C' => '2', 'Port USB-A' => '1', 'Jack audio' => 'Da']],
    ['name' => 'HP Omen 16', 'brand' => 'HP', 'price' => 9999.99, 'url' => 'https://versus.com/en/hp-omen-16', 'specs' => ['Dimensiune ecran' => '16.1 inch', 'Rezoluție' => '2560 x 1440 px', 'Procesor' => 'Intel Core i7-13700HX', 'Nuclee CPU' => '16', 'Frecvență CPU' => '5.0 GHz', 'RAM' => '32 GB', 'Stocare' => '1 TB SSD', 'Placă video' => 'NVIDIA GeForce RTX 4070', 'VRAM' => '8 GB', 'Greutate' => '2.44 kg', 'Grosime' => '23.5 mm', 'Autonomie baterie' => '7 ore', 'Capacitate baterie' => '83 Wh', 'Rată de reîmprospătare' => '165 Hz', 'Luminozitate' => '300 nits', 'Tip display' => 'IPS', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.3', 'Porturi USB-C' => '1', 'Port USB-A' => '3', 'Port HDMI' => 'Da', 'Port Ethernet' => 'Da', 'Jack audio' => 'Da']],
    ['name' => 'Lenovo Legion Pro 7i Gen 8', 'brand' => 'Lenovo', 'price' => 14999.99, 'url' => 'https://versus.com/en/lenovo-legion-pro-7i-gen-8', 'specs' => ['Dimensiune ecran' => '16 inch', 'Rezoluție' => '2560 x 1600 px', 'Procesor' => 'Intel Core i9-13900HX', 'Nuclee CPU' => '24', 'Frecvență CPU' => '5.4 GHz', 'RAM' => '32 GB', 'Stocare' => '1 TB SSD', 'Placă video' => 'NVIDIA GeForce RTX 4080', 'VRAM' => '12 GB', 'Greutate' => '2.8 kg', 'Grosime' => '26 mm', 'Autonomie baterie' => '6 ore', 'Capacitate baterie' => '99.9 Wh', 'Rată de reîmprospătare' => '240 Hz', 'Luminozitate' => '500 nits', 'Tip display' => 'IPS', 'G-Sync' => 'Da', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.1', 'Porturi USB-C' => '2', 'Port USB-A' => '3', 'Port HDMI' => 'Da', 'Port Ethernet' => 'Da', 'Jack audio' => 'Da']],
    ['name' => 'Acer Swift 14', 'brand' => 'Acer', 'price' => 5999.99, 'url' => 'https://versus.com/en/acer-swift-14', 'specs' => ['Dimensiune ecran' => '14 inch', 'Rezoluție' => '2880 x 1800 px', 'Procesor' => 'Intel Core i7-13700H', 'Nuclee CPU' => '14', 'Frecvență CPU' => '5.0 GHz', 'RAM' => '16 GB', 'Stocare' => '512 GB SSD', 'Placă video' => 'Intel Iris Xe', 'Greutate' => '1.25 kg', 'Grosime' => '14.9 mm', 'Autonomie baterie' => '15 ore', 'Capacitate baterie' => '76 Wh', 'Rată de reîmprospătare' => '90 Hz', 'Luminozitate' => '400 nits', 'Tip display' => 'OLED', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.2', 'Porturi USB-C' => '2', 'Port USB-A' => '2', 'Port HDMI' => 'Da', 'Jack audio' => 'Da']],
    ['name' => 'MSI Stealth 16 Studio', 'brand' => 'MSI', 'price' => 12999.99, 'url' => 'https://versus.com/en/msi-stealth-16-studio', 'specs' => ['Dimensiune ecran' => '16 inch', 'Rezoluție' => '3840 x 2160 px', 'Procesor' => 'Intel Core i9-13900H', 'Nuclee CPU' => '14', 'Frecvență CPU' => '5.4 GHz', 'RAM' => '64 GB', 'Stocare' => '2 TB SSD', 'Placă video' => 'NVIDIA GeForce RTX 4070', 'VRAM' => '8 GB', 'Greutate' => '1.99 kg', 'Grosime' => '19.95 mm', 'Autonomie baterie' => '9 ore', 'Capacitate baterie' => '99.9 Wh', 'Rată de reîmprospătare' => '120 Hz', 'Luminozitate' => '600 nits', 'Tip display' => 'Mini LED', 'Sistem de operare' => 'Windows 11 Pro', 'Wi-Fi' => 'Wi-Fi 6E', 'Bluetooth' => '5.3', 'Porturi USB-C' => '2', 'Port USB-A' => '2', 'Port HDMI' => 'Da', 'Cititor carduri SD' => 'Da', 'Jack audio' => 'Da']],
    ['name' => 'Huawei MateBook X Pro', 'brand' => 'Huawei', 'price' => 8499.99, 'url' => 'https://versus.com/en/huawei-matebook-x-pro', 'specs' => ['Dimensiune ecran' => '14.2 inch', 'Rezoluție' => '3120 x 2080 px', 'Procesor' => 'Intel Core i7-1360P', 'Nuclee CPU' => '12', 'Frecvență CPU' => '5.0 GHz', 'RAM' => '16 GB', 'Stocare' => '1 TB SSD', 'Placă video' => 'Intel Iris Xe', 'Greutate' => '1.38 kg', 'Grosime' => '15.5 mm', 'Autonomie baterie' => '14 ore', 'Capacitate baterie' => '60 Wh', 'Rată de reîmprospătare' => '90 Hz', 'Luminozitate' => '500 nits', 'Tip display' => 'OLED', 'Touchscreen' => 'Da', 'Sistem de operare' => 'Windows 11', 'Wi-Fi' => 'Wi-Fi 6', 'Bluetooth' => '5.1', 'Porturi USB-C' => '2', 'Jack audio' => 'Da']],
];

echo "Total laptopuri: " . count($laptops) . "\n\n";

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
    
    // Ștergem specificațiile vechi
    SpecValue::where('product_id', $product->id)->delete();
    
    // Adăugăm specificațiile noi
    foreach ($laptop['specs'] as $specName => $specValue) {
        addSpec($product, $specName, $specValue, $specKeyCache);
    }
    
    $imported++;
    echo "✓ {$laptop['name']}\n";
}

echo "\n✅ Import finalizat: $imported laptopuri!\n";
echo "📊 Specificații unice: " . count($specKeyCache) . "\n";
