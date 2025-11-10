<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "  TOATE SPEC-URILE CU DIACRITICE (NETR ADUSE)\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Get all unique spec keys
$allSpecs = DB::table('spec_keys')
    ->orderBy('name')
    ->pluck('name')
    ->unique()
    ->values();

// Filter those with diacritics
$specsWithDiacritics = $allSpecs->filter(function($spec) {
    return preg_match('/[ăâîșțĂÂÎȘȚ]/', $spec);
});

echo "Total specs cu diacritice: " . $specsWithDiacritics->count() . "\n\n";

// Group by category/type
foreach ($specsWithDiacritics as $spec) {
    $normalized = strtolower(str_replace(' ', '_', $spec));
    $nodiakrit = str_replace(
        ['ă', 'â', 'î', 'ș', 'ț', 'Ă', 'Â', 'Î', 'Ș', 'Ț'],
        ['a', 'a', 'i', 's', 't', 'A', 'A', 'I', 'S', 'T'],
        $spec
    );
    
    echo "'{$spec}' => '" . strtolower(str_replace(' ', '_', $nodiakrit)) . "',\n";
}

echo "\n═══════════════════════════════════════════════════════════════\n";
