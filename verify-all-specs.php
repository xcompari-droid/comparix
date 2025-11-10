<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 VERIFICARE COMPLETĂ SPECIFICAȚII PE TOT SITE-UL\n";
echo "====================================================\n\n";

$categories = DB::table('product_types')
    ->leftJoin('products', 'product_types.id', '=', 'products.product_type_id')
    ->select('product_types.id', 'product_types.name', DB::raw('COUNT(products.id) as product_count'))
    ->groupBy('product_types.id', 'product_types.name')
    ->having('product_count', '>', 0)
    ->orderBy('product_count', 'desc')
    ->get();

$totalIssues = 0;

foreach($categories as $category) {
    echo "📁 {$category->name} ({$category->product_count} produse)\n";
    echo str_repeat('-', 60) . "\n";
    
    // Verificăm un produs sample
    $sampleProduct = DB::table('products')
        ->where('product_type_id', $category->id)
        ->first(['id', 'name']);
    
    if ($sampleProduct) {
        // Numărăm spec_values pentru acest produs
        $specCount = DB::table('spec_values')
            ->where('product_id', $sampleProduct->id)
            ->count();
        
        echo "   Exemplu: {$sampleProduct->name} (ID: {$sampleProduct->id})\n";
        echo "   Total specs în DB: {$specCount}\n";
        
        if ($specCount > 0) {
            // Verificăm câte au valori NULL
            $nullValues = DB::table('spec_values')
                ->where('product_id', $sampleProduct->id)
                ->whereNull('value_string')
                ->whereNull('value_number')
                ->where(function($q) {
                    $q->whereNull('value_bool')->orWhere('value_bool', false);
                })
                ->count();
            
            $validValues = $specCount - $nullValues;
            $percentValid = round(($validValues / $specCount) * 100, 1);
            
            echo "   ✅ Cu valori: {$validValues} ({$percentValid}%)\n";
            echo "   ❌ NULL/FALSE: {$nullValues}\n";
            
            if ($nullValues > 0) {
                echo "   ⚠️  PROBLEMĂ: {$nullValues} specs fără valori!\n";
                $totalIssues++;
                
                // Sample de specs fără valori
                $emptySpecs = DB::table('spec_values')
                    ->join('spec_keys', 'spec_values.spec_key_id', '=', 'spec_keys.id')
                    ->where('spec_values.product_id', $sampleProduct->id)
                    ->whereNull('spec_values.value_string')
                    ->whereNull('spec_values.value_number')
                    ->where(function($q) {
                        $q->whereNull('spec_values.value_bool')->orWhere('spec_values.value_bool', false);
                    })
                    ->select('spec_keys.name')
                    ->limit(5)
                    ->get();
                
                echo "   Exemple specs goale:\n";
                foreach($emptySpecs as $spec) {
                    echo "      • {$spec->name}\n";
                }
            }
        } else {
            echo "   ⚠️  PROBLEMĂ CRITICĂ: Niciun spec în DB!\n";
            $totalIssues++;
        }
        
        // Calculăm media pentru toată categoria
        $avgSpecs = DB::table('products')
            ->where('products.product_type_id', $category->id)
            ->leftJoin('spec_values', 'products.id', '=', 'spec_values.product_id')
            ->selectRaw('COUNT(DISTINCT spec_values.id) / COUNT(DISTINCT products.id) as avg')
            ->value('avg');
        
        echo "   📊 Media specs/produs: " . round($avgSpecs, 1) . "\n";
    }
    
    echo "\n";
}

echo "====================================================\n";
if ($totalIssues > 0) {
    echo "❌ GĂSITE {$totalIssues} CATEGORII CU PROBLEME!\n";
    echo "💡 Recomandare: Verifică importerii pentru aceste categorii\n";
} else {
    echo "✅ Toate categoriile au specificații complete!\n";
}
