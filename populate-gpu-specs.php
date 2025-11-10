<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\SpecKey;
use App\Models\SpecValue;
use App\Models\ProductType;

echo "=== POPULARE SPECIFICAȚII GPU (Date Manuale) ===\n\n";

$productType = ProductType::where('name', 'Placă video')->first();

if (!$productType) {
    die("ProductType 'Placă video' not found!\n");
}

// Get spec keys
$specKeys = SpecKey::where('product_type_id', $productType->id)->get()->keyBy('key');

// Define GPU specifications (from public data)
$gpuSpecs = [
    'RTX 5090' => [
        'gpu_clock' => 2015,
        'boost_clock' => 2407,
        'memory_size' => 32,
        'memory_type' => 'GDDR7',
        'memory_bus' => 512,
        'memory_bandwidth' => 1792,
        'cuda_cores' => 21760,
        'tensor_cores' => 680,
        'rt_cores' => 170,
        'tdp' => 575,
        'power_connector' => '16-pin (12VHPWR)',
        'recommended_psu' => 1000,
        'directx_version' => '12 Ultimate',
        'ray_tracing' => 'yes',
        'dlss' => 'DLSS 4',
    ],
    'RTX 4090' => [
        'gpu_clock' => 2235,
        'boost_clock' => 2520,
        'memory_size' => 24,
        'memory_type' => 'GDDR6X',
        'memory_bus' => 384,
        'memory_bandwidth' => 1008,
        'cuda_cores' => 16384,
        'tensor_cores' => 512,
        'rt_cores' => 128,
        'tdp' => 450,
        'power_connector' => '16-pin (12VHPWR)',
        'recommended_psu' => 850,
        'directx_version' => '12 Ultimate',
        'ray_tracing' => 'yes',
        'dlss' => 'DLSS 3',
    ],
    'RTX 4080' => [
        'gpu_clock' => 2205,
        'boost_clock' => 2505,
        'memory_size' => 16,
        'memory_type' => 'GDDR6X',
        'memory_bus' => 256,
        'memory_bandwidth' => 736,
        'cuda_cores' => 9728,
        'tensor_cores' => 304,
        'rt_cores' => 76,
        'tdp' => 320,
        'power_connector' => '16-pin (12VHPWR)',
        'recommended_psu' => 750,
        'directx_version' => '12 Ultimate',
        'ray_tracing' => 'yes',
        'dlss' => 'DLSS 3',
    ],
];

// Apply specs to all GPUs
$gpus = Product::where('product_type_id', $productType->id)->get();

$updated = 0;
foreach ($gpus as $gpu) {
    // Detect GPU model from name
    $specs = null;
    if (stripos($gpu->name, 'RTX 5090') !== false) {
        $specs = $gpuSpecs['RTX 5090'];
    } elseif (stripos($gpu->name, 'RTX 4090') !== false) {
        $specs = $gpuSpecs['RTX 4090'];
    } elseif (stripos($gpu->name, 'RTX 4080') !== false) {
        $specs = $gpuSpecs['RTX 4080'];
    }
    
    if ($specs) {
        foreach ($specs as $key => $value) {
            if (!isset($specKeys[$key])) {
                continue;
            }
            
            // Detect value type
            $valueType = 'string';
            $detectedValue = $value;
            
            if (strtolower($value) === 'yes' || strtolower($value) === 'true') {
                $detectedValue = 1;
                $valueType = 'bool';
            } elseif (strtolower($value) === 'no' || strtolower($value) === 'false') {
                $detectedValue = 0;
                $valueType = 'bool';
            } elseif (is_numeric($value)) {
                $detectedValue = floatval($value);
                $valueType = 'number';
            }
            
            SpecValue::updateOrCreate(
                [
                    'product_id' => $gpu->id,
                    'spec_key_id' => $specKeys[$key]->id,
                ],
                [
                    'value' => $detectedValue,
                    'value_type' => $valueType,
                ]
            );
        }
        
        $updated++;
        $specCount = SpecValue::where('product_id', $gpu->id)->count();
        echo "✓ Updated: {$gpu->name} ({$specCount} specs)\n";
    } else {
        echo "✗ Skipped: {$gpu->name} (no matching model)\n";
    }
}

echo "\n✓ Total GPU-uri actualizate: {$updated} / " . $gpus->count() . "\n";
