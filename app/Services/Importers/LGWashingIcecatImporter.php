<?php

namespace App\Services\Importers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\SpecKey;
use App\Models\SpecValue;
use App\Models\Offer;
use App\Services\OpenIcecatClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LGWashingIcecatImporter
{
    protected Category $category;
    protected ProductType $productType;
    protected OpenIcecatClient $icecatClient;
    protected LGWashingRosterFetcher $rosterFetcher;
    protected array $specKeys = [];

    public function __construct()
    {
        // Create washing machines category
        $this->category = Category::firstOrCreate(
            ['name' => 'Mașini de spălat'],
            [
                'slug' => 'masini-de-spalat',
                'description' => 'Mașini de spălat rufe de la toate mărcile - comparație specificații, prețuri și review-uri',
                'icon' => '🧺',
                'is_active' => true,
            ]
        );

        // Create washing machine product type
        $this->productType = ProductType::firstOrCreate(
            ['name' => 'Mașină de spălat'],
            [
                'slug' => 'masina-de-spalat',
                'category_id' => $this->category->id,
            ]
        );

        $this->icecatClient = new OpenIcecatClient();
        $this->rosterFetcher = new LGWashingRosterFetcher();
        
        $this->initializeSpecKeys();
    }

    protected function initializeSpecKeys()
    {
        $specs = [
            // Capacity & Loading
            'capacity' => ['name' => 'Capacitate de încărcare', 'unit' => 'kg'],
            'max_load' => ['name' => 'Încărcare maximă', 'unit' => 'kg'],
            'loading_type' => ['name' => 'Tip încărcare', 'unit' => ''],
            
            // Performance
            'spin_speed' => ['name' => 'Viteză centrifugare', 'unit' => 'RPM'],
            'max_spin_speed' => ['name' => 'Viteză maximă centrifugare', 'unit' => 'RPM'],
            'energy_class' => ['name' => 'Clasă energetică', 'unit' => ''],
            'energy_consumption' => ['name' => 'Consum energetic', 'unit' => 'kWh/100 cicluri'],
            'water_consumption' => ['name' => 'Consum apă', 'unit' => 'L/ciclu'],
            'washing_performance' => ['name' => 'Performanță spălare', 'unit' => ''],
            'spin_efficiency' => ['name' => 'Eficiență centrifugare', 'unit' => ''],
            
            // Noise Levels
            'noise_level_washing' => ['name' => 'Nivel zgomot spălare', 'unit' => 'dB'],
            'noise_level_spinning' => ['name' => 'Nivel zgomot centrifugare', 'unit' => 'dB'],
            'noise_class' => ['name' => 'Clasă zgomot', 'unit' => ''],
            
            // Programs & Features
            'programs_count' => ['name' => 'Număr programe', 'unit' => ''],
            'programs' => ['name' => 'Programe disponibile', 'unit' => ''],
            'quick_wash' => ['name' => 'Program rapid', 'unit' => ''],
            'eco_program' => ['name' => 'Program eco', 'unit' => ''],
            'steam_function' => ['name' => 'Funcție abur', 'unit' => ''],
            'allergy_care' => ['name' => 'Program alergii', 'unit' => ''],
            'baby_care' => ['name' => 'Program bebeluși', 'unit' => ''],
            'wool_program' => ['name' => 'Program lână', 'unit' => ''],
            'delicate_program' => ['name' => 'Program delicat', 'unit' => ''],
            'sportswear_program' => ['name' => 'Program îmbrăcăminte sport', 'unit' => ''],
            
            // Technology
            'motor_type' => ['name' => 'Tip motor', 'unit' => ''],
            'inverter_motor' => ['name' => 'Motor inverter', 'unit' => ''],
            'direct_drive' => ['name' => 'Direct Drive', 'unit' => ''],
            'ai_technology' => ['name' => 'Tehnologie AI', 'unit' => ''],
            'smart_features' => ['name' => 'Funcții smart', 'unit' => ''],
            'wifi' => ['name' => 'Wi-Fi', 'unit' => ''],
            'nfc' => ['name' => 'NFC', 'unit' => ''],
            'app_control' => ['name' => 'Control aplicație', 'unit' => ''],
            
            // Drum & Design
            'drum_material' => ['name' => 'Material tambur', 'unit' => ''],
            'drum_volume' => ['name' => 'Volum tambur', 'unit' => 'L'],
            'door_opening_angle' => ['name' => 'Unghi deschidere ușă', 'unit' => '°'],
            'color' => ['name' => 'Culoare', 'unit' => ''],
            'finish' => ['name' => 'Finisaj', 'unit' => ''],
            
            // Dimensions & Weight
            'width' => ['name' => 'Lățime', 'unit' => 'cm'],
            'height' => ['name' => 'Înălțime', 'unit' => 'cm'],
            'depth' => ['name' => 'Adâncime', 'unit' => 'cm'],
            'weight' => ['name' => 'Greutate', 'unit' => 'kg'],
            'net_weight' => ['name' => 'Greutate netă', 'unit' => 'kg'],
            
            // Safety & Protection
            'child_lock' => ['name' => 'Blocare copii', 'unit' => ''],
            'overflow_protection' => ['name' => 'Protecție împotriva revărsării', 'unit' => ''],
            'leak_protection' => ['name' => 'Protecție scurgeri', 'unit' => ''],
            'aquastop' => ['name' => 'AquaStop', 'unit' => ''],
            
            // Display & Control
            'display_type' => ['name' => 'Tip display', 'unit' => ''],
            'led_display' => ['name' => 'Display LED', 'unit' => ''],
            'digital_display' => ['name' => 'Display digital', 'unit' => ''],
            'time_delay' => ['name' => 'Pornire întârziată', 'unit' => 'ore'],
            'time_remaining' => ['name' => 'Timp rămas', 'unit' => ''],
            
            // Additional Features
            'add_clothes_function' => ['name' => 'Funcție adăugare rufe', 'unit' => ''],
            'auto_dosing' => ['name' => 'Dozare automată detergent', 'unit' => ''],
            'foam_control' => ['name' => 'Control spumă', 'unit' => ''],
            'water_temperature_control' => ['name' => 'Control temperatură apă', 'unit' => ''],
            'variable_spin' => ['name' => 'Centrifugare variabilă', 'unit' => ''],
            'rinse_plus' => ['name' => 'Clătire plus', 'unit' => ''],
            
            // Warranty & Compliance
            'warranty' => ['name' => 'Garanție', 'unit' => 'ani'],
            'motor_warranty' => ['name' => 'Garanție motor', 'unit' => 'ani'],
            'energy_label' => ['name' => 'Etichetă energetică', 'unit' => ''],
        ];

        foreach ($specs as $key => $config) {
            $fullSlug = $this->productType->id . '_' . $key;
            $this->specKeys[$key] = SpecKey::firstOrCreate(
                [
                    'product_type_id' => $this->productType->id,
                    'slug' => $fullSlug,
                ],
                [
                    'key' => $key,
                    'name' => $config['name'],
                    'unit' => $config['unit'],
                ]
            );
        }
    }

    public function import(int $limit = 100, bool $includeMedia = true): array
    {
        $stats = [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];

        try {
            echo "=== LG WASHING MACHINES IMPORT ===\n\n";
            
            // Fetch product list from LG Romania
            $products = $this->rosterFetcher->fetch($limit);
            $stats['total'] = count($products);

            if (empty($products)) {
                echo "⚠ No products found\n";
                return $stats;
            }

            foreach ($products as $productData) {
                try {
                    echo "\n  Processing: {$productData['name']}\n";
                    
                    $result = $this->importProduct($productData, $includeMedia);
                    
                    if ($result) {
                        $stats['success']++;
                        echo "  ✓ Imported successfully\n";
                    } else {
                        $stats['skipped']++;
                        echo "  ⚠ Skipped (no Icecat data)\n";
                    }
                    
                    sleep(1); // Rate limiting
                    
                } catch (\Exception $e) {
                    $stats['failed']++;
                    echo "  ✗ Error: " . $e->getMessage() . "\n";
                    Log::error("Failed to import washing machine", [
                        'product' => $productData,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            echo "\n" . str_repeat("=", 60) . "\n";
            echo "IMPORT COMPLETE\n";
            echo "  Total: {$stats['total']}\n";
            echo "  Success: {$stats['success']}\n";
            echo "  Skipped: {$stats['skipped']}\n";
            echo "  Failed: {$stats['failed']}\n";
            echo str_repeat("=", 60) . "\n";

        } catch (\Exception $e) {
            Log::error("LG washing machine import failed: " . $e->getMessage());
            echo "✗ Import failed: " . $e->getMessage() . "\n";
        }

        return $stats;
    }

    protected function importProduct(array $productData, bool $includeMedia): bool
    {
        // Try to find product in Icecat
        $icecatData = null;
        
        // Try by model number first
        if (!empty($productData['model'])) {
            echo "    Searching Icecat by model: {$productData['model']}\n";
            $icecatData = $this->icecatClient->searchByBrandModel('LG', $productData['model']);
        }

        // If not found and we have EAN, try that
        if (!$icecatData && !empty($productData['ean'])) {
            echo "    Searching Icecat by EAN: {$productData['ean']}\n";
            $icecatData = $this->icecatClient->searchByGtin($productData['ean']);
        }

        if (!$icecatData) {
            echo "    ⚠ Not found in Icecat\n";
            return false;
        }

        // Extract specifications from Icecat
        $specs = $this->icecatClient->extractSpecifications($icecatData);
        
        // Extract images
        $images = $includeMedia ? $this->icecatClient->extractImages($icecatData) : [];
        $mainImage = !empty($images) ? $images[0] : ($productData['image'] ?? null);

        // Create or update product
        $product = Product::updateOrCreate(
            [
                'product_type_id' => $this->productType->id,
                'brand' => 'LG',
                'mpn' => $productData['model'] ?? Str::slug($productData['name']),
            ],
            [
                'category_id' => $this->category->id,
                'name' => $productData['name'],
                'slug' => Str::slug($productData['name']),
                'short_desc' => $icecatData['short_description'] ?? "Mașină de spălat {$productData['name']}",
                'image_url' => $mainImage,
                'source_url' => $productData['url'],
                'score' => 75,
            ]
        );

        echo "    ✓ Product created/updated (ID: {$product->id})\n";

        // Save specifications
        $this->saveSpecifications($product, $specs);
        echo "    ✓ Saved " . count($specs) . " specifications\n";

        // Create offer
        $this->createOffer($product, $icecatData);
        echo "    ✓ Offer created\n";

        return true;
    }

    protected function saveSpecifications(Product $product, array $specs): void
    {
        foreach ($specs as $key => $specData) {
            // Try to match with our predefined spec keys
            $specKey = $this->specKeys[$key] ?? null;
            
            if (!$specKey) {
                // Try fuzzy matching
                $specKey = $this->findMatchingSpecKey($specData['name']);
            }

            if (!$specKey) {
                // Create dynamic spec key
                $fullSlug = $this->productType->id . '_' . $key;
                $specKey = SpecKey::firstOrCreate(
                    [
                        'product_type_id' => $this->productType->id,
                        'slug' => $fullSlug,
                    ],
                    [
                        'key' => $key,
                        'name' => $specData['name'],
                        'unit' => $specData['unit'] ?? '',
                    ]
                );
            }

            $value = $specData['value'];
            
            // Determine value type
            $valueData = [
                'value_string' => null,
                'value_number' => null,
                'value_bool' => null,
            ];

            if (is_bool($value) || in_array(strtolower($value), ['yes', 'no', 'da', 'nu', 'true', 'false'])) {
                $valueData['value_bool'] = in_array(strtolower($value), ['yes', 'da', 'true', '1', true], true);
            } elseif (is_numeric($value)) {
                $valueData['value_number'] = (float)$value;
            } elseif (preg_match('/^([\d\.]+)\s*(.*)$/', $value, $matches)) {
                $valueData['value_number'] = (float)$matches[1];
                if (!empty($matches[2])) {
                    $valueData['value_string'] = trim($matches[2]);
                }
            } else {
                $valueData['value_string'] = $value;
            }

            SpecValue::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'spec_key_id' => $specKey->id,
                ],
                $valueData
            );
        }
    }

    protected function findMatchingSpecKey(string $name): ?SpecKey
    {
        $normalized = strtolower(trim($name));
        
        foreach ($this->specKeys as $key => $specKey) {
            $specName = strtolower($specKey->name);
            if (str_contains($specName, $normalized) || str_contains($normalized, $specName)) {
                return $specKey;
            }
        }

        return null;
    }

    protected function createOffer(Product $product, array $icecatData): void
    {
        $price = $icecatData['price'] ?? 2499.00; // Default price

        Offer::updateOrCreate(
            [
                'product_id' => $product->id,
                'seller_name' => 'LG Romania',
            ],
            [
                'url' => $product->source_url,
                'price' => $price,
                'currency' => 'RON',
                'in_stock' => true,
                'merchant' => 'LG Romania',
                'url_affiliate' => $product->source_url,
            ]
        );
    }
}
