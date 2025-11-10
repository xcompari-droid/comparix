<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$lg = App\Models\Product::with('specValues.specKey')->find(417);
$wifiSpecs = $lg->specValues->filter(fn($s) => stripos($s->specKey->name, 'wi-fi') !== false || stripos($s->specKey->name, 'wifi') !== false);

echo "LG Wi-Fi specs found: " . $wifiSpecs->count() . "\n\n";
foreach($wifiSpecs as $spec) {
    echo "ID: {$spec->id}, Key: '{$spec->specKey->name}' (key_id: {$spec->spec_key_id}), value_bool: " . ($spec->value_bool === null ? 'NULL' : ($spec->value_bool ? 'true' : 'false')) . "\n";
}

// Șterge cel cu value_bool=false
$toDelete = $wifiSpecs->filter(fn($s) => $s->value_bool === false);
echo "\nDeleting " . $toDelete->count() . " Wi-Fi specs with value_bool=false\n";
foreach($toDelete as $spec) {
    echo "Deleting ID: {$spec->id}\n";
    $spec->delete();
}

// Verificare finală
$lg = App\Models\Product::with('specValues.specKey')->find(417);
$wifiSpecs = $lg->specValues->filter(fn($s) => stripos($s->specKey->name, 'wi-fi') !== false || stripos($s->specKey->name, 'wifi') !== false);
echo "\nFinal count: " . $wifiSpecs->count() . "\n";
foreach($wifiSpecs as $spec) {
    echo "ID: {$spec->id}, Key: '{$spec->specKey->name}', value_bool: " . ($spec->value_bool ? 'true' : 'false') . "\n";
}
