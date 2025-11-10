<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\SpecKey;
use App\Models\SpecValue;

// Samsung WW90T554DAW/S7 - specificații reale
$samsung = Product::find(416);
$specs_samsung = [
    'Capacitate de încărcare' => ['value_number' => 9, 'value_string' => '9 kg'],
    'Tip încărcare' => ['value_string' => 'Frontală'],
    'Viteză centrifugare' => ['value_number' => 1400, 'value_string' => '1400 RPM'],
    'Clasă energetică' => ['value_string' => 'A'],
    'Consum energetic' => ['value_number' => 81, 'value_string' => '81 kWh/100 cicluri'],
    'Consum apă' => ['value_number' => 48, 'value_string' => '48 L/ciclu'],
    'Nivel zgomot spălare' => ['value_number' => 73, 'value_string' => '73 dB'],
    'Nivel zgomot centrifugare' => ['value_number' => 74, 'value_string' => '74 dB'],
    'Program rapid' => ['value_bool' => true],
    'Program eco' => ['value_bool' => true],
    'Funcție abur' => ['value_bool' => false],
    'Program alergii' => ['value_bool' => true],
    'Motor inverter' => ['value_bool' => true],
    'Lățime' => ['value_number' => 60, 'value_string' => '60 cm'],
    'Înălțime' => ['value_number' => 85, 'value_string' => '85 cm'],
    'Adâncime' => ['value_number' => 55, 'value_string' => '55 cm'],
    'Blocare copii' => ['value_bool' => true],
    'AquaStop' => ['value_bool' => false],
    'Display digital' => ['value_bool' => true],
    'Pornire întârziată' => ['value_bool' => true],
];

// LG F4WV710P2E - specificații reale
$lg = Product::find(417);
$specs_lg = [
    'Capacitate de încărcare' => ['value_number' => 10.5, 'value_string' => '10.5 kg'],
    'Tip încărcare' => ['value_string' => 'Frontală'],
    'Viteză centrifugare' => ['value_number' => 1400, 'value_string' => '1400 RPM'],
    'Clasă energetică' => ['value_string' => 'A'],
    'Consum energetic' => ['value_number' => 58, 'value_string' => '58 kWh/100 cicluri'],
    'Consum apă' => ['value_number' => 56, 'value_string' => '56 L/ciclu'],
    'Nivel zgomot spălare' => ['value_number' => 51, 'value_string' => '51 dB'],
    'Nivel zgomot centrifugare' => ['value_number' => 73, 'value_string' => '73 dB'],
    'Program rapid' => ['value_bool' => true],
    'Funcție abur' => ['value_bool' => true],
    'Program alergii' => ['value_bool' => true],
    'Motor inverter' => ['value_bool' => true],
    'Direct Drive' => ['value_bool' => true],
    'Tehnologie AI' => ['value_bool' => true],
    'Wi-Fi' => ['value_bool' => true],
    'Control aplicație' => ['value_bool' => true],
    'Lățime' => ['value_number' => 60, 'value_string' => '60 cm'],
    'Înălțime' => ['value_number' => 85, 'value_string' => '85 cm'],
    'Adâncime' => ['value_number' => 65, 'value_string' => '65 cm'],
    'Blocare copii' => ['value_bool' => true],
    'Display digital' => ['value_bool' => true],
];

function updateProductSpecs($product, $specs) {
    echo "Actualizez {$product->name}...\n";
    
    foreach ($specs as $specName => $values) {
        $specKey = SpecKey::where('name', $specName)->first();
        
        if (!$specKey) {
            echo "  ⚠ Spec key nu există: $specName\n";
            continue;
        }
        
        $specValue = SpecValue::where('product_id', $product->id)
            ->where('spec_key_id', $specKey->id)
            ->first();
        
        if (!$specValue) {
            $specValue = new SpecValue();
            $specValue->product_id = $product->id;
            $specValue->spec_key_id = $specKey->id;
        }
        
        // Reset toate valorile
        $specValue->value_string = null;
        $specValue->value_number = null;
        $specValue->value_bool = null;
        
        // Setează valorile noi
        if (isset($values['value_string'])) {
            $specValue->value_string = $values['value_string'];
        }
        if (isset($values['value_number'])) {
            $specValue->value_number = $values['value_number'];
        }
        if (isset($values['value_bool'])) {
            $specValue->value_bool = $values['value_bool'];
        }
        
        $specValue->save();
        echo "  ✓ $specName actualizat\n";
    }
}

updateProductSpecs($samsung, $specs_samsung);
echo "\n";
updateProductSpecs($lg, $specs_lg);

echo "\n✅ Actualizare completă!\n";
