<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\SpecKey;
use App\Models\SpecValue;

// Găsește spec key pentru Wi-Fi
$wifiKey = SpecKey::where('name', 'Wi-Fi')->first();
if (!$wifiKey) {
    $wifiKey = SpecKey::create(['name' => 'Wi-Fi', 'unit' => null]);
    echo "Created Wi-Fi spec key\n";
}

// Product 416 - Samsung (nu are Wi-Fi)
$samsung = Product::find(416);
$samsungWifi = SpecValue::where('product_id', 416)
    ->where('spec_key_id', $wifiKey->id)
    ->first();

if (!$samsungWifi) {
    SpecValue::create([
        'product_id' => 416,
        'spec_key_id' => $wifiKey->id,
        'value_bool' => false,
        'value_string' => null,
        'value_number' => null
    ]);
    echo "Added Wi-Fi=Nu for Samsung WW90T554DAW/S7\n";
} else {
    echo "Samsung already has Wi-Fi spec\n";
}

// Product 417 - LG (are Wi-Fi duplicat)
$lgWifiSpecs = SpecValue::where('product_id', 417)
    ->where('spec_key_id', $wifiKey->id)
    ->get();

echo "\nLG has " . $lgWifiSpecs->count() . " Wi-Fi specs\n";

if ($lgWifiSpecs->count() > 1) {
    // Păstrează doar cel cu value_bool=true, șterge restul
    $kept = false;
    foreach ($lgWifiSpecs as $spec) {
        if ($spec->value_bool === true && !$kept) {
            echo "Keeping Wi-Fi=Da (ID: {$spec->id})\n";
            $kept = true;
        } else {
            echo "Deleting duplicate Wi-Fi spec (ID: {$spec->id}, value_bool: " . ($spec->value_bool ? 'true' : 'false') . ")\n";
            $spec->delete();
        }
    }
}

// Verificare finală
echo "\n=== Verification ===\n";
$samsung = Product::with('specValues.specKey')->find(416);
$lg = Product::with('specValues.specKey')->find(417);

$samsungWifi = $samsung->specValues->first(fn($s) => $s->specKey->name === 'Wi-Fi');
$lgWifi = $lg->specValues->first(fn($s) => $s->specKey->name === 'Wi-Fi');

echo "Samsung Wi-Fi: " . ($samsungWifi ? ($samsungWifi->value_bool ? 'Da' : 'Nu') : 'NOT FOUND') . "\n";
echo "LG Wi-Fi: " . ($lgWifi ? ($lgWifi->value_bool ? 'Da' : 'Nu') : 'NOT FOUND') . "\n";

$lgWifiCount = $lg->specValues->filter(fn($s) => $s->specKey->name === 'Wi-Fi')->count();
echo "LG Wi-Fi count: {$lgWifiCount}\n";
