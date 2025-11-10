<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Verificăm specificațiile pentru toate categoriile...\n\n";

$productTypes = DB::table('product_types')
    ->orderBy('id')
    ->get();

foreach ($productTypes as $type) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📦 {$type->name} (ID: {$type->id})\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    $specKeys = DB::table('spec_keys')
        ->where('product_type_id', $type->id)
        ->orderBy('name')
        ->get();
    
    if ($specKeys->isEmpty()) {
        echo "   ⚠️  Nicio specificație\n\n";
        continue;
    }
    
    echo "   Total chei: " . count($specKeys) . "\n\n";
    
    $englishSpecs = [];
    $romanianSpecs = [];
    
    foreach ($specKeys as $key) {
        // Detectăm specificații în engleză (conțin doar litere latine și unele cuvinte comune)
        if (preg_match('/^[a-z\s\-\(\)]+$/i', $key->name) && 
            !in_array(strtolower($key->name), ['wi-fi', 'bluetooth', 'usb', 'hdmi', 'smart tv', 'gps', 'nfc', 'sim'])) {
            $englishSpecs[] = $key->name;
        } else {
            $romanianSpecs[] = $key->name;
        }
    }
    
    if (!empty($englishSpecs)) {
        echo "   ⚠️  SPECIFICAȚII ÎN ENGLEZĂ (" . count($englishSpecs) . "):\n";
        foreach ($englishSpecs as $spec) {
            echo "      • $spec\n";
        }
        echo "\n";
    }
    
    if (!empty($romanianSpecs)) {
        echo "   ✓ Specificații în română (" . count($romanianSpecs) . "):\n";
        foreach (array_slice($romanianSpecs, 0, 5) as $spec) {
            echo "      • $spec\n";
        }
        if (count($romanianSpecs) > 5) {
            echo "      ... și " . (count($romanianSpecs) - 5) . " altele\n";
        }
    }
    
    echo "\n";
}

echo "\n✓ Verificare completă!\n";
