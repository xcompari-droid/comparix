<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\Category;

echo "=== VERIFICARE DUPLICATE ȘI IMAGINI ===\n\n";

// Verificare telefoane (în categoria Smartphone-uri)
$phoneCategory = Category::where('name', 'Smartphone-uri')->first();
if ($phoneCategory) {
    $phones = Product::whereHas('productType', function($q) use ($phoneCategory) {
        $q->where('category_id', $phoneCategory->id);
    })->get();
    
    echo "📱 SMARTPHONE-URI\n";
    echo "Total: " . $phones->count() . "\n";
    
    // Verificare duplicate (același brand și name)
    $duplicates = $phones->groupBy(function($phone) {
        return $phone->brand . '|' . $phone->name;
    })->filter(function($group) {
        return $group->count() > 1;
    });
    
    if ($duplicates->count() > 0) {
        echo "\n⚠️  DUPLICATE GĂSITE:\n";
        foreach ($duplicates as $key => $group) {
            list($brand, $name) = explode('|', $key);
            echo "  - $brand $name: {$group->count()} intrări\n";
            foreach ($group as $phone) {
                echo "    ID: {$phone->id}, Slug: {$phone->slug}\n";
            }
        }
    } else {
        echo "✅ Nu există duplicate\n";
    }
    
    // Verificare imagini
    $withoutImages = $phones->filter(function($phone) {
        return empty($phone->image_url) || 
               strpos($phone->image_url, 'ui-avatars.com') !== false ||
               strpos($phone->image_url, 'placeholder') !== false;
    });
    
    echo "\n📷 IMAGINI:\n";
    echo "Cu imagini reale: " . ($phones->count() - $withoutImages->count()) . "\n";
    echo "Fără imagini / placeholder: " . $withoutImages->count() . "\n";
    
    if ($withoutImages->count() > 0) {
        echo "\nExemple fără imagini:\n";
        foreach ($withoutImages->take(10) as $phone) {
            echo "  - {$phone->brand} {$phone->name}\n";
            echo "    URL: " . ($phone->image_url ?? 'NULL') . "\n";
        }
    }
}

echo "\n" . str_repeat("=", 50) . "\n\n";

// Verificare smartwatch-uri
$watchCategory = Category::where('name', 'Smartwatch-uri')->first();
if ($watchCategory) {
    $watches = Product::whereHas('productType', function($q) use ($watchCategory) {
        $q->where('category_id', $watchCategory->id);
    })->get();
    
    echo "⌚ SMARTWATCH-URI\n";
    echo "Total: " . $watches->count() . "\n";
    
    // Verificare duplicate
    $duplicates = $watches->groupBy(function($watch) {
        return $watch->brand . '|' . $watch->name;
    })->filter(function($group) {
        return $group->count() > 1;
    });
    
    if ($duplicates->count() > 0) {
        echo "\n⚠️  DUPLICATE GĂSITE:\n";
        foreach ($duplicates as $key => $group) {
            list($brand, $name) = explode('|', $key);
            echo "  - $brand $name: {$group->count()} intrări\n";
            foreach ($group as $watch) {
                echo "    ID: {$watch->id}, Slug: {$watch->slug}\n";
            }
        }
    } else {
        echo "✅ Nu există duplicate\n";
    }
    
    // Verificare imagini
    $withoutImages = $watches->filter(function($watch) {
        return empty($watch->image_url) || 
               strpos($watch->image_url, 'ui-avatars.com') !== false ||
               strpos($watch->image_url, 'placeholder') !== false;
    });
    
    echo "\n📷 IMAGINI:\n";
    echo "Cu imagini reale: " . ($watches->count() - $withoutImages->count()) . "\n";
    echo "Fără imagini / placeholder: " . $withoutImages->count() . "\n";
    
    if ($withoutImages->count() > 0) {
        echo "\nExemple fără imagini:\n";
        foreach ($withoutImages->take(10) as $watch) {
            echo "  - {$watch->brand} {$watch->name}\n";
            echo "    URL: " . ($watch->image_url ?? 'NULL') . "\n";
        }
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
