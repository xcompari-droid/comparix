<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\ProductType;
use App\Models\Category;
use App\Models\SpecKey;
use App\Models\SpecValue;
use App\Models\Offer;

echo "=== IMPORT MANUAL FRIGIDERE ALTEX ===\n\n";

$category = Category::where('slug', 'electrocasnice')->first();
$productType = ProductType::where('slug', 'frigider')->first();

if (!$category || !$productType) {
    die("Categorie sau ProductType nu există!\n");
}

// Popular refrigerators from Altex
$fridges = [
    [
        'name' => 'Samsung RB38A7B6AS9/EF',
        'brand' => 'Samsung',
        'capacity' => 385,
        'energy_class' => 'A+++',
        'no_frost' => true,
        'freezer_position' => 'Jos',
        'price' => 3199,
        'image' => 'https://lcdn.altex.ro/resize/media/catalog/product/R/B/2bd48d28d1c32adea0e55139a8e6434a/RB38A7B6AS9_EF_001_Front_Silver.png',
    ],
    [
        'name' => 'LG GBB92STAXP',
        'brand' => 'LG',
        'capacity' => 384,
        'energy_class' => 'A++',
        'no_frost' => true,
        'freezer_position' => 'Jos',
        'price' => 2899,
        'image' => 'https://lcdn.altex.ro/resize/media/catalog/product/G/B/2bd48d28d1c32adea0e55139a8e6434a/GBB92STAXP_001_Front-Closed_InoxLook.png',
    ],
    [
        'name' => 'Bosch KGN39VLDA Serie 4',
        'brand' => 'Bosch',
        'capacity' => 366,
        'energy_class' => 'A+++',
        'no_frost' => true,
        'freezer_position' => 'Jos',
        'price' => 3499,
        'image' => 'https://lcdn.altex.ro/resize/media/catalog/product/K/G/2bd48d28d1c32adea0e55139a8e6434a/KGN39VLDA_001.png',
    ],
    [
        'name' => 'Whirlpool W7 821O OX',
        'brand' => 'Whirlpool',
        'capacity' => 338,
        'energy_class' => 'A++',
        'no_frost' => true,
        'freezer_position' => 'Jos',
        'price' => 2599,
        'image' => 'https://lcdn.altex.ro/resize/media/catalog/product/W/7/2bd48d28d1c32adea0e55139a8e6434a/W7_821O_OX_001.png',
    ],
    [
        'name' => 'Beko RCNA406E40ZXBRN',
        'brand' => 'Beko',
        'capacity' => 406,
        'energy_class' => 'A++',
        'no_frost' => true,
        'freezer_position' => 'Jos',
        'price' => 2199,
        'image' => 'https://lcdn.altex.ro/resize/media/catalog/product/R/C/2bd48d28d1c32adea0e55139a8e6434a/RCNA406E40ZXBRN_001.png',
    ],
    [
        'name' => 'Arctic AK60366MT+',
        'brand' => 'Arctic',
        'capacity' => 362,
        'energy_class' => 'A++',
        'no_frost' => true,
        'freezer_position' => 'Jos',
        'price' => 1899,
        'image' => 'https://lcdn.altex.ro/resize/media/catalog/product/A/K/2bd48d28d1c32adea0e55139a8e6434a/AK60366MT_001.png',
    ],
    [
        'name' => 'Liebherr CNef 4845',
        'brand' => 'Liebherr',
        'capacity' => 442,
        'energy_class' => 'A+++',
        'no_frost' => true,
        'freezer_position' => 'Jos',
        'price' => 5999,
        'image' => 'https://lcdn.altex.ro/resize/media/catalog/product/C/N/2bd48d28d1c32adea0e55139a8e6434a/CNef_4845_001.png',
    ],
    [
        'name' => 'Gorenje NRK6192AXL4',
        'brand' => 'Gorenje',
        'capacity' => 307,
        'energy_class' => 'A++',
        'no_frost' => true,
        'freezer_position' => 'Jos',
        'price' => 2499,
        'image' => 'https://lcdn.altex.ro/resize/media/catalog/product/N/R/2bd48d28d1c32adea0e55139a8e6434a/NRK6192AXL4_001.png',
    ],
    [
        'name' => 'Electrolux LNT7ME34X2',
        'brand' => 'Electrolux',
        'capacity' => 337,
        'energy_class' => 'A++',
        'no_frost' => true,
        'freezer_position' => 'Jos',
        'price' => 3299,
        'image' => 'https://lcdn.altex.ro/resize/media/catalog/product/L/N/2bd48d28d1c32adea0e55139a8e6434a/LNT7ME34X2_001.png',
    ],
    [
        'name' => 'Candy CCTOS 502XH',
        'brand' => 'Candy',
        'capacity' => 250,
        'energy_class' => 'A+',
        'no_frost' => false,
        'freezer_position' => 'Sus',
        'price' => 1499,
        'image' => 'https://lcdn.altex.ro/resize/media/catalog/product/C/C/2bd48d28d1c32adea0e55139a8e6434a/CCTOS_502XH_001.png',
    ],
];

// Initialize spec keys
$specKeys = [];
$specs = [
    'brand' => 'Brand',
    'total_capacity' => 'Capacitate totală (litri)',
    'energy_class' => 'Clasă energetică',
    'no_frost' => 'No Frost',
    'freezer_position' => 'Poziție congelator',
];

foreach ($specs as $key => $name) {
    $slug = $productType->id . '_' . $key;
    $specKeys[$key] = SpecKey::firstOrCreate(
        ['slug' => $slug],
        [
            'product_type_id' => $productType->id,
            'key' => $key,
            'name' => $name,
            'unit' => ($key == 'total_capacity' ? 'L' : null),
            'is_main_spec' => true,
        ]
    );
}

foreach ($fridges as $fridgeData) {
    Product::withoutSyncingToSearch(function () use ($fridgeData, $category, $productType, $specKeys) {
        $product = Product::updateOrCreate(
            [
                'product_type_id' => $productType->id,
                'name' => $fridgeData['name'],
            ],
            [
                'category_id' => $category->id,
                'brand' => $fridgeData['brand'],
                'short_desc' => "Frigider {$fridgeData['brand']} cu capacitate {$fridgeData['capacity']}L",
                'image_url' => $fridgeData['image'],
                'source_url' => 'https://altex.ro/aparate-frigorifice/cpl/',
                'score' => 80,
            ]
        );

        // Save specs
        foreach (['brand', 'total_capacity', 'energy_class', 'no_frost', 'freezer_position'] as $key) {
            if (isset($fridgeData[$key]) && isset($specKeys[$key])) {
                $value = $fridgeData[$key];
                $valueType = 'string';
                
                if (is_numeric($value)) {
                    $valueType = 'number';
                } elseif (is_bool($value)) {
                    $valueType = 'bool';
                    $value = $value ? 1 : 0;
                }
                
                SpecValue::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'spec_key_id' => $specKeys[$key]->id,
                    ],
                    [
                        'value' => $value,
                        'value_type' => $valueType,
                    ]
                );
            }
        }

        // Create offer
        Offer::updateOrCreate(
            [
                'product_id' => $product->id,
                'merchant' => 'Altex',
            ],
            [
                'price' => $fridgeData['price'],
                'currency' => 'RON',
                'url_affiliate' => 'https://altex.ro/aparate-frigorifice/cpl/',
                'in_stock' => true,
                'last_checked_at' => now(),
            ]
        );
    });
    
    echo "✓ {$fridgeData['name']}\n";
}

echo "\n✓ Importate " . count($fridges) . " frigidere!\n";
