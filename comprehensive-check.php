<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\SpecValue;

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "  VERIFICARE COMPREHENSIVĂ PRODUSE - TOATE CATEGORIILE\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Product types mapping
$productTypes = [
    2 => 'Smartwatch',
    3 => 'GPU',
    4 => 'Smartphone',
    5 => 'Laptop',
    6 => 'Frigider',
    7 => 'Mașină de spălat',
    8 => 'Căști Wireless',
    9 => 'TV'
];

$totalProducts = 0;
$totalWithImages = 0;
$totalSpecs = 0;
$issuesFound = [];

foreach ($productTypes as $typeId => $typeName) {
    $products = Product::where('product_type_id', $typeId)->get();
    
    if ($products->isEmpty()) {
        continue;
    }
    
    $count = $products->count();
    $withRealImages = $products->filter(fn($p) => $p->image_url && !str_contains($p->image_url, 'placeholder') && !str_contains($p->image_url, 'picsum'))->count();
    $imagePercent = $count > 0 ? round(($withRealImages / $count) * 100, 1) : 0;
    
    $specsCount = 0;
    $productsWithFewSpecs = [];
    $productsWithTranslationIssues = [];
    $productsWithNumberIssues = [];
    
    foreach ($products as $product) {
        $specs = $product->specValues;
        $specCount = $specs->count();
        $specsCount += $specCount;
        
        // Check for products with too few specs (< 10)
        if ($specCount < 10) {
            $productsWithFewSpecs[] = "{$product->name} ({$specCount} specs)";
        }
        
        // Check each spec for issues
        foreach ($specs as $spec) {
            $key = $spec->specKey->name ?? '';
            $value = $spec->value_string ?? $spec->value_number ?? $spec->value_bool;
            
            // Check for untranslated Romanian specs (with diacritics)
            if (preg_match('/[ăâîșțĂÂÎȘȚ]/', $key)) {
                $productsWithTranslationIssues[] = "{$product->name}: '{$key}'";
            }
            
            // Check for number formatting issues
            if (is_string($value) && preg_match('/^\d+[,\.]\d+$/', $value)) {
                // Has comma or dot - might need normalization
                if (strpos($value, ',') !== false) {
                    $productsWithNumberIssues[] = "{$product->name}: '{$key}' = '{$value}' (folosește virgulă)";
                }
            }
        }
    }
    
    $avgSpecs = $count > 0 ? round($specsCount / $count, 1) : 0;
    
    echo "┌─────────────────────────────────────────────────────────────┐\n";
    echo "│ " . str_pad($typeName, 59) . " │\n";
    echo "├─────────────────────────────────────────────────────────────┤\n";
    echo "│ Produse total:        " . str_pad($count, 36) . " │\n";
    echo "│ Cu imagini reale:     " . str_pad("{$withRealImages} ({$imagePercent}%)", 36) . " │\n";
    echo "│ Specs medii/produs:   " . str_pad($avgSpecs, 36) . " │\n";
    echo "└─────────────────────────────────────────────────────────────┘\n";
    
    // Report issues
    if (!empty($productsWithFewSpecs)) {
        echo "  ⚠️  PRODUSE CU PUȚINE SPECS (< 10):\n";
        foreach (array_slice($productsWithFewSpecs, 0, 3) as $issue) {
            echo "      • {$issue}\n";
        }
        if (count($productsWithFewSpecs) > 3) {
            echo "      ... și încă " . (count($productsWithFewSpecs) - 3) . " produse\n";
        }
        $issuesFound[] = "{$typeName}: " . count($productsWithFewSpecs) . " produse cu < 10 specs";
    }
    
    if (!empty($productsWithTranslationIssues)) {
        echo "  ⚠️  SPECS NETRADUSE (cu diacritice):\n";
        $unique = array_unique($productsWithTranslationIssues);
        foreach (array_slice($unique, 0, 3) as $issue) {
            echo "      • {$issue}\n";
        }
        if (count($unique) > 3) {
            echo "      ... și încă " . (count($unique) - 3) . " probleme\n";
        }
        $issuesFound[] = "{$typeName}: " . count($unique) . " specs netraduse";
    }
    
    if (!empty($productsWithNumberIssues)) {
        echo "  ⚠️  PROBLEME FORMATARE NUMERE:\n";
        foreach (array_slice($productsWithNumberIssues, 0, 3) as $issue) {
            echo "      • {$issue}\n";
        }
        if (count($productsWithNumberIssues) > 3) {
            echo "      ... și încă " . (count($productsWithNumberIssues) - 3) . " probleme\n";
        }
        $issuesFound[] = "{$typeName}: " . count($productsWithNumberIssues) . " probleme numere";
    }
    
    if (empty($productsWithFewSpecs) && empty($productsWithTranslationIssues) && empty($productsWithNumberIssues)) {
        echo "  ✅ Nu s-au găsit probleme!\n";
    }
    
    echo "\n";
    
    $totalProducts += $count;
    $totalWithImages += $withRealImages;
    $totalSpecs += $specsCount;
}

// Summary
$totalImagePercent = $totalProducts > 0 ? round(($totalWithImages / $totalProducts) * 100, 1) : 0;
$avgSpecsOverall = $totalProducts > 0 ? round($totalSpecs / $totalProducts, 1) : 0;

echo "═══════════════════════════════════════════════════════════════\n";
echo "  REZUMAT GENERAL\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  Produse total:        {$totalProducts}\n";
echo "  Cu imagini reale:     {$totalWithImages} ({$totalImagePercent}%)\n";
echo "  Specs medii/produs:   {$avgSpecsOverall}\n";
echo "\n";

if (!empty($issuesFound)) {
    echo "  ⚠️  PROBLEME GĂSITE:\n";
    foreach ($issuesFound as $issue) {
        echo "      • {$issue}\n";
    }
} else {
    echo "  ✅ TOATE PRODUSELE SUNT PERFECTE!\n";
}

echo "═══════════════════════════════════════════════════════════════\n\n";

// Recommendation
if ($totalImagePercent < 90) {
    echo "⚠️  RECOMANDARE: Imaginile sunt sub 90%. Descarcă mai multe imagini reale.\n\n";
}

if ($avgSpecsOverall < 12) {
    echo "⚠️  RECOMANDARE: Media specs este sub 12. Adaugă mai multe specificații.\n\n";
}

if ($totalImagePercent >= 94 && $avgSpecsOverall >= 15 && empty($issuesFound)) {
    echo "🎉 PERFECT! Site-ul este gata de lansare!\n\n";
}
