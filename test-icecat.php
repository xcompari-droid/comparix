<?php

/**
 * Test Icecat API Integration
 * 
 * Before running:
 * 1. Register at https://icecat.biz/en/menu/partners/index.html
 * 2. Add credentials to .env:
 *    ICECAT_USERNAME=your_username
 *    ICECAT_PASSWORD=your_password
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Importers\IcecatImporter;

$icecat = new IcecatImporter();

echo "=== TEST ICECAT API ===\n\n";

// Test 1: Connection test
echo "1. Testing connection...\n";
if ($icecat->testConnection()) {
    echo "   ✓ Connection successful!\n\n";
} else {
    echo "   ✗ Connection failed. Check credentials in .env\n";
    echo "   Register at: https://icecat.biz/en/menu/partners/index.html\n\n";
    exit(1);
}

// Test 2: Get product by EAN (Samsung RB38A7B6AS9/EF)
echo "2. Testing EAN lookup (Samsung fridge)...\n";
$ean = "8806094808919"; // Samsung RB38A7B6AS9/EF
$product = $icecat->getProductByEAN($ean);

if ($product) {
    echo "   ✓ Product found!\n";
    echo "   Name: {$product['name']}\n";
    echo "   Brand: {$product['brand']}\n";
    echo "   MPN: {$product['mpn']}\n";
    echo "   Category: {$product['category']}\n";
    echo "   Image: " . (strlen($product['image_url']) > 60 ? substr($product['image_url'], 0, 60) . '...' : $product['image_url']) . "\n";
    echo "   Specifications: " . count($product['specifications']) . " found\n";
    
    if (count($product['specifications']) > 0) {
        echo "\n   Sample specifications:\n";
        $count = 0;
        foreach ($product['specifications'] as $key => $spec) {
            if ($count++ >= 5) break;
            echo "   - {$spec['name']}: {$spec['value']}\n";
        }
    }
    echo "\n";
} else {
    echo "   ✗ Product not found\n\n";
}

// Test 3: Get refrigerator specs
echo "3. Testing refrigerator data extraction...\n";
$fridgeData = $icecat->getRefrigeratorSpecs($ean, 'Samsung', 'RB38A7B6AS9');

if ($fridgeData) {
    echo "   ✓ Refrigerator data extracted!\n\n";
    echo "   Product Details:\n";
    echo "   - Name: {$fridgeData['name']}\n";
    echo "   - Brand: {$fridgeData['brand']}\n";
    echo "   - MPN: {$fridgeData['mpn']}\n";
    echo "   - EAN: {$fridgeData['ean']}\n";
    
    echo "\n   Key Specifications:\n";
    $specs = $fridgeData['specifications'];
    
    if ($specs['total_capacity']) {
        echo "   - Total Capacity: {$specs['total_capacity']}\n";
    }
    if ($specs['energy_class']) {
        echo "   - Energy Class: {$specs['energy_class']}\n";
    }
    if ($specs['noise_level']) {
        echo "   - Noise Level: {$specs['noise_level']}\n";
    }
    if ($specs['no_frost']) {
        echo "   - No Frost: {$specs['no_frost']}\n";
    }
    if ($specs['freezer_position']) {
        echo "   - Freezer Position: {$specs['freezer_position']}\n";
    }
    if ($specs['width'] || $specs['height'] || $specs['depth']) {
        echo "   - Dimensions: {$specs['width']} x {$specs['height']} x {$specs['depth']}\n";
    }
    
    echo "\n   Image URL: " . (strlen($fridgeData['image_url']) > 60 ? substr($fridgeData['image_url'], 0, 60) . '...' : $fridgeData['image_url']) . "\n";
    echo "   Additional Images: " . count($fridgeData['images']) . "\n";
    
} else {
    echo "   ✗ Failed to extract refrigerator data\n";
}

echo "\n=== TEST COMPLETE ===\n\n";

echo "Next steps:\n";
echo "1. If tests passed: Update import-fridges-manual.php to use Icecat\n";
echo "2. Create Artisan command: php artisan make:command ImportIcecatFridges\n";
echo "3. Expand to other categories: washing machines, dishwashers, etc.\n";
echo "4. Consider caching Icecat responses to reduce API calls\n";
