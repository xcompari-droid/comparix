<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;

echo "\n🔍 TESTARE DATE PENTRU VERSUS COMPARE\n";
echo "=====================================\n\n";

// Găsește 3 GPU-uri
$gpus = Product::where('product_type_id', 3)
    ->with('specValues.specKey')
    ->limit(3)
    ->get();

echo "📊 GPU-uri găsite: " . $gpus->count() . "\n\n";

foreach ($gpus as $gpu) {
    echo "🎮 {$gpu->name} ({$gpu->brand})\n";
    echo "   ID: {$gpu->id}\n";
    echo "   Imagine: " . ($gpu->image_url ? '✅' : '❌') . "\n";
    
    $specs = $gpu->specValues;
    echo "   Specificații: " . $specs->count() . "\n";
    
    // Afișează primele 5 specs
    echo "   Primele specs:\n";
    foreach ($specs->take(5) as $spec) {
        $value = $spec->value_number ?? $spec->value_string ?? $spec->value_bool;
        echo "     • {$spec->specKey->name}: {$value}\n";
    }
    
    echo "\n";
}

// Test URL-uri
$ids = $gpus->pluck('id')->implode(',');
echo "📎 URL-uri pentru testare:\n";
echo "   Demo: http://localhost:8080/compare/demo\n";
echo "   Real: http://localhost:8080/compare/versus?ids={$ids}\n\n";

// Test pentru laptop
$laptops = Product::where('product_type_id', 9)->limit(3)->get();
if ($laptops->count() >= 2) {
    $laptopIds = $laptops->pluck('id')->implode(',');
    echo "💻 Laptopuri: http://localhost:8080/compare/versus?ids={$laptopIds}\n";
}

// Test pentru smartphone
$phones = Product::where('product_type_id', 1)->limit(3)->get();
if ($phones->count() >= 2) {
    $phoneIds = $phones->pluck('id')->implode(',');
    echo "📱 Smartphone-uri: http://localhost:8080/compare/versus?ids={$phoneIds}\n";
}

// Test pentru frigider
$fridges = Product::where('product_type_id', 5)->limit(3)->get();
if ($fridges->count() >= 2) {
    $fridgeIds = $fridges->pluck('id')->implode(',');
    echo "❄️  Frigidere: http://localhost:8080/compare/versus?ids={$fridgeIds}\n";
}

echo "\n✅ Testare completă!\n";
echo "🌐 Deschide browser-ul la URL-urile de mai sus\n\n";
