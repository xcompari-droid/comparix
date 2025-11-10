<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$p416 = App\Models\Product::with('specValues.specKey')->find(416);
$p417 = App\Models\Product::with('specValues.specKey')->find(417);

echo "\n=== Product 416: {$p416->name} ===\n";
$wifi416 = $p416->specValues->first(fn($s) => stripos($s->specKey->name, 'wi-fi') !== false || stripos($s->specKey->name, 'wifi') !== false);
if($wifi416) {
    echo "Wi-Fi key: {$wifi416->specKey->name}\n";
    echo "  value_bool: " . ($wifi416->value_bool === null ? 'NULL' : ($wifi416->value_bool ? 'true' : 'false')) . "\n";
    echo "  value_string: {$wifi416->value_string}\n";
    echo "  value_number: {$wifi416->value_number}\n";
} else {
    echo "Wi-Fi spec NOT FOUND\n";
}

echo "\n=== Product 417: {$p417->name} ===\n";
$wifi417 = $p417->specValues->first(fn($s) => stripos($s->specKey->name, 'wi-fi') !== false || stripos($s->specKey->name, 'wifi') !== false);
if($wifi417) {
    echo "Wi-Fi key: {$wifi417->specKey->name}\n";
    echo "  value_bool: " . ($wifi417->value_bool === null ? 'NULL' : ($wifi417->value_bool ? 'true' : 'false')) . "\n";
    echo "  value_string: {$wifi417->value_string}\n";
    echo "  value_number: {$wifi417->value_number}\n";
} else {
    echo "Wi-Fi spec NOT FOUND\n";
}

echo "\n=== All specs for 416 ===\n";
foreach($p416->specValues as $sv) {
    echo "{$sv->specKey->name}: ";
    if($sv->value_bool !== null) echo ($sv->value_bool ? 'Da' : 'Nu') . ' (bool)';
    elseif($sv->value_string) echo $sv->value_string . ' (string)';
    elseif($sv->value_number !== null) echo $sv->value_number . ' (number)';
    echo "\n";
}

echo "\n=== All specs for 417 ===\n";
foreach($p417->specValues as $sv) {
    echo "{$sv->specKey->name}: ";
    if($sv->value_bool !== null) echo ($sv->value_bool ? 'Da' : 'Nu') . ' (bool)';
    elseif($sv->value_string) echo $sv->value_string . ' (string)';
    elseif($sv->value_number !== null) echo $sv->value_number . ' (number)';
    echo "\n";
}
