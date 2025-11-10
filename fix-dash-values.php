<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SpecValue;

echo "=== Searching for specs with '-' value ===\n\n";

// Caută toate SpecValue care au value_string = '-'
$dashSpecs = SpecValue::with(['specKey', 'product'])
    ->where('value_string', '-')
    ->orWhere('value_string', '—')
    ->orWhere('value_string', '–')
    ->get();

echo "Found {$dashSpecs->count()} specs with '-' value\n\n";

if ($dashSpecs->count() > 0) {
    // Grupează după spec key name
    $byKey = $dashSpecs->groupBy(fn($s) => $s->specKey->name);
    
    foreach ($byKey as $keyName => $specs) {
        echo "Spec: '{$keyName}' - {$specs->count()} products\n";
        
        // Afișează primele 5 produse ca exemplu
        $examples = $specs->take(5);
        foreach ($examples as $spec) {
            echo "  Product {$spec->product_id}: {$spec->product->name}\n";
        }
        if ($specs->count() > 5) {
            echo "  ... and " . ($specs->count() - 5) . " more\n";
        }
        echo "\n";
    }
    
    echo "=== Analysis ===\n";
    echo "Spec keys with '-' values:\n";
    foreach ($byKey as $keyName => $specs) {
        echo "  {$keyName}: {$specs->count()} products\n";
    }
    
    // Determină ce ar trebui să fie fiecare
    echo "\n=== Determining correct values ===\n";
    
    $booleanKeys = [
        'Wi-Fi', 'Bluetooth', 'NFC', 'GPS', 'Direct Drive',
        'Motor inverter', 'Tehnologie AI', 'Control aplicație',
        'Program rapid', 'Program eco', 'Funcție abur', 'Program alergii',
        'Blocare copii', 'AquaStop', 'Display digital', 'Pornire întârziată',
        'Auto-curățare', 'Smart Diagnosis', 'TurboWash', 'Steam',
        '5G', '4G', 'Dual SIM', 'Face ID', 'Touch ID', 'Wireless charging',
        'Water resistant', 'Fast charging', 'Stylus support',
        'HDR', 'Smart TV', 'Voice control', 'Gaming mode',
        'USB-C', 'HDMI', 'Ethernet', 'Headphone jack'
    ];
    
    $fixed = 0;
    
    foreach ($dashSpecs as $spec) {
        $keyName = $spec->specKey->name;
        
        // Verifică dacă este un boolean key
        $isBoolean = false;
        foreach ($booleanKeys as $boolKey) {
            if (stripos($keyName, $boolKey) !== false) {
                $isBoolean = true;
                break;
            }
        }
        
        if ($isBoolean) {
            // Convertește la boolean false (Nu)
            echo "Converting '{$keyName}' for product {$spec->product_id}: '-' -> Nu\n";
            $spec->value_string = null;
            $spec->value_bool = false;
            $spec->save();
            $fixed++;
        } else {
            // Lasă ca NULL sau șterge
            echo "Deleting non-boolean '{$keyName}' for product {$spec->product_id}: '-' (not useful)\n";
            $spec->delete();
            $fixed++;
        }
    }
    
    echo "\n=== Summary ===\n";
    echo "Fixed/deleted {$fixed} specs with '-' value\n";
} else {
    echo "No specs with '-' value found!\n";
}

// Verificare finală
echo "\n=== Final check ===\n";
$remaining = SpecValue::where('value_string', '-')
    ->orWhere('value_string', '—')
    ->orWhere('value_string', '–')
    ->count();
echo "Remaining specs with '-': {$remaining}\n";
