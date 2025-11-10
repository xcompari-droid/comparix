<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$p416 = App\Models\Product::with('specValues.specKey')->find(416);
$p417 = App\Models\Product::with('specValues.specKey')->find(417);

echo "\n=== Product 416 (Samsung): Direct Drive ===\n";
$dd416 = $p416->specValues->filter(fn($s) => stripos($s->specKey->name, 'direct drive') !== false);
echo "Found: " . $dd416->count() . " specs\n";
foreach($dd416 as $spec) {
    echo "ID: {$spec->id}, Key: '{$spec->specKey->name}' (key_id: {$spec->spec_key_id}), value_bool: " . ($spec->value_bool === null ? 'NULL' : ($spec->value_bool ? 'true' : 'false')) . "\n";
}

echo "\n=== Product 417 (LG): Direct Drive ===\n";
$dd417 = $p417->specValues->filter(fn($s) => stripos($s->specKey->name, 'direct drive') !== false);
echo "Found: " . $dd417->count() . " specs\n";
foreach($dd417 as $spec) {
    echo "ID: {$spec->id}, Key: '{$spec->specKey->name}' (key_id: {$spec->spec_key_id}), value_bool: " . ($spec->value_bool === null ? 'NULL' : ($spec->value_bool ? 'true' : 'false')) . "\n";
}

// Verificare și fix
echo "\n=== Fixing Direct Drive ===\n";

// Samsung nu are Direct Drive (folosește motor clasic)
if ($dd416->count() === 0) {
    $ddKey = App\Models\SpecKey::where('name', 'Direct Drive')->first();
    if (!$ddKey) {
        $ddKey = App\Models\SpecKey::create(['name' => 'Direct Drive', 'unit' => null]);
    }
    
    App\Models\SpecValue::create([
        'product_id' => 416,
        'spec_key_id' => $ddKey->id,
        'value_bool' => false,
        'value_string' => null,
        'value_number' => null
    ]);
    echo "Added Direct Drive=Nu for Samsung\n";
}

// LG are Direct Drive - șterge duplicatele false
$toDelete = $dd417->filter(fn($s) => $s->value_bool === false);
echo "Deleting " . $toDelete->count() . " Direct Drive specs with value_bool=false for LG\n";
foreach($toDelete as $spec) {
    echo "Deleting ID: {$spec->id}\n";
    $spec->delete();
}

// Verificare finală
echo "\n=== Final Verification ===\n";
$p416 = App\Models\Product::with('specValues.specKey')->find(416);
$p417 = App\Models\Product::with('specValues.specKey')->find(417);

$dd416 = $p416->specValues->filter(fn($s) => stripos($s->specKey->name, 'direct drive') !== false);
$dd417 = $p417->specValues->filter(fn($s) => stripos($s->specKey->name, 'direct drive') !== false);

echo "Samsung Direct Drive count: " . $dd416->count() . "\n";
foreach($dd416 as $spec) {
    echo "  Value: " . ($spec->value_bool ? 'Da' : 'Nu') . "\n";
}

echo "LG Direct Drive count: " . $dd417->count() . "\n";
foreach($dd417 as $spec) {
    echo "  Value: " . ($spec->value_bool ? 'Da' : 'Nu') . "\n";
}
