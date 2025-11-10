<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

function normalizeSpecKey($name)
{
    $map = [
        'CUDA Cores' => 'cuda_cores',
        'Stream Processors' => 'cuda_cores',
        'Memorie Video' => 'memory_gb',
        'Memory' => 'memory_gb',
        'Boost Clock' => 'boost_clock_mhz',
        'TDP' => 'tdp_watts',
        'RAM' => 'ram_gb',
        'Stocare' => 'storage_gb',
        'Baterie' => 'battery_mah',
        'Ecran' => 'screen_inch',
        'Greutate' => 'weight_g',
        'Cameră' => 'camera_mp',
        'Capacitate' => 'capacity_l',
        'Consum energetic' => 'energy_kwh',
        'Zgomot' => 'noise_db',
    ];
    
    return $map[$name] ?? strtolower(str_replace(' ', '_', $name));
}

function extractNumber($string)
{
    if (!is_string($string)) return null;
    if (preg_match('/(\d+(?:\.\d+)?)/', $string, $matches)) {
        return (float)$matches[1];
    }
    return null;
}

$products = App\Models\Product::whereIn('id', [323, 324, 325])
    ->with('specValues.specKey')
    ->get();

echo "📊 METRICI EXTRASE\n\n";

foreach ($products as $product) {
    echo "📦 " . $product->name . ":\n";
    
    $metrics = [];
    foreach ($product->specValues as $specValue) {
        $key = normalizeSpecKey($specValue->specKey->name);
        $value = $specValue->value_number ?? 
                extractNumber($specValue->value_string) ?? 
                $specValue->value_bool;
        
        if ($value !== null) {
            $metrics[$key] = is_numeric($value) ? (float)$value : $value;
        }
    }
    
    foreach ($metrics as $key => $value) {
        echo "  {$key}: {$value}\n";
    }
    
    echo "\n";
}
