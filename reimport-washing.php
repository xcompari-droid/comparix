<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Importers\AltexWashingMachineImporter;

$importer = new AltexWashingMachineImporter();

echo "\n=== REIMPORT MAȘINI DE SPĂLAT CU FIX ===\n\n";

$importer->import(30);

echo "\n✅ GATA!\n";
