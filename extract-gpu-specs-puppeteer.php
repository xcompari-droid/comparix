<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\SpecKey;
use App\Models\SpecValue;
use App\Models\ProductType;

echo "=== EXTRAGERE SPECIFICAȚII GPU CU PUPPETEER ===\n\n";

$productType = ProductType::where('name', 'Placă video')->first();

if (!$productType) {
    die("ProductType 'Placă video' not found!\n");
}

// Get spec keys
$specKeys = SpecKey::where('product_type_id', $productType->id)->get()->keyBy('key');

// Get GPUs without specs
$gpus = Product::where('product_type_id', $productType->id)
    ->whereNotNull('source_url')
    ->get()
    ->filter(function($gpu) {
        return SpecValue::where('product_id', $gpu->id)->count() === 0;
    })
    ->take(5); // Process 5 at a time to avoid timeout

echo "GPU-uri de procesat: " . $gpus->count() . "\n\n";

foreach ($gpus as $gpu) {
    echo "Procesare: {$gpu->name}...\n";
    
    $url = $gpu->source_url;
    if (!$url) {
        echo "  ✗ Fără URL\n\n";
        continue;
    }
    
    // Run Puppeteer scraper
    $command = "node scraper.cjs " . escapeshellarg($url) . " 2>&1";
    $html = shell_exec($command);
    
    if (!$html) {
        echo "  ✗ Eroare la scraping\n\n";
        continue;
    }
    
    // Parse HTML and extract specs
    $specs = extractGPUSpecs($html);
    
    if (empty($specs)) {
        echo "  ✗ Nicio specificație găsită\n\n";
        continue;
    }
    
    // Save specs
    $saved = 0;
    foreach ($specs as $key => $value) {
        if (!isset($specKeys[$key]) || empty($value)) {
            continue;
        }
        
        // Detect value type
        $valueType = 'string';
        $detectedValue = $value;
        
        if (is_numeric($value)) {
            $detectedValue = floatval($value);
            $valueType = 'number';
        } elseif (strtolower($value) === 'yes' || strtolower($value) === 'true') {
            $detectedValue = 1;
            $valueType = 'bool';
        } elseif (strtolower($value) === 'no' || strtolower($value) === 'false') {
            $detectedValue = 0;
            $valueType = 'bool';
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
        
        $saved++;
    }
    
    echo "  ✓ Salvate {$saved} specificații\n\n";
    
    // Rate limiting
    sleep(3);
}

echo "✓ Proces finalizat!\n";

function extractGPUSpecs($html) {
    $specs = [];
    
    // Extract GPU clock
    if (preg_match('/GPU clock speed.*?(\d+)\s*MHz/', $html, $matches)) {
        $specs['gpu_clock'] = $matches[1];
    }
    
    // Extract boost clock
    if (preg_match('/GPU turbo.*?(\d+)\s*MHz/', $html, $matches)) {
        $specs['boost_clock'] = $matches[1];
    }
    
    // Extract memory size
    if (preg_match('/VRAM.*?(\d+)GB/', $html, $matches)) {
        $specs['memory_size'] = $matches[1];
    }
    
    // Extract memory type
    if (preg_match('/GDDR version.*?(GDDR\d+)/', $html, $matches)) {
        $specs['memory_type'] = $matches[1];
    }
    
    // Extract memory bus
    if (preg_match('/memory bus width.*?(\d+)-bit/', $html, $matches)) {
        $specs['memory_bus'] = $matches[1];
    }
    
    // Extract memory bandwidth
    if (preg_match('/maximum memory bandwidth.*?(\d+)\s*GB\/s/', $html, $matches)) {
        $specs['memory_bandwidth'] = $matches[1];
    }
    
    // Extract CUDA cores (shading units)
    if (preg_match('/shading units.*?(\d+)/', $html, $matches)) {
        $specs['cuda_cores'] = $matches[1];
    }
    
    // Extract TMUs
    if (preg_match('/texture mapping units.*?(\d+)/', $html, $matches)) {
        $specs['tmus'] = $matches[1];
    }
    
    // Extract ROPs
    if (preg_match('/render output units.*?(\d+)/', $html, $matches)) {
        $specs['rops'] = $matches[1];
    }
    
    // Extract TDP
    if (preg_match('/Thermal Design Power.*?(\d+)W/', $html, $matches)) {
        $specs['tdp'] = $matches[1];
    }
    
    // Extract architecture
    if (preg_match('/GPU architecture.*?<p>([^<]+)</', $html, $matches)) {
        $specs['architecture'] = trim($matches[1]);
    }
    
    // Extract process size
    if (preg_match('/semiconductor size.*?(\d+)\s*nm/', $html, $matches)) {
        $specs['process_size'] = $matches[1];
    }
    
    // Extract transistors
    if (preg_match('/number of transistors.*?(\d+)\s*million/', $html, $matches)) {
        $specs['transistor_count'] = $matches[1];
    }
    
    // Extract DirectX version
    if (preg_match('/DirectX version.*?(\d+(?:\.\d+)?)/', $html, $matches)) {
        $specs['directx_version'] = $matches[1];
    }
    
    // Extract ray tracing support
    if (preg_match('/supports ray tracing/i', $html)) {
        $specs['ray_tracing'] = 'yes';
    }
    
    // Extract DLSS
    if (preg_match('/DLSS\s*(\d+(?:\.\d+)?)/', $html, $matches)) {
        $specs['dlss'] = 'DLSS ' . $matches[1];
    }
    
    return $specs;
}
