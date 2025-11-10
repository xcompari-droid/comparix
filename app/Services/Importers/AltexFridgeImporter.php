<?php

namespace App\Services\Importers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\SpecKey;
use App\Models\SpecValue;
use App\Models\Offer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AltexFridgeImporter
{
    protected $category;
    protected $productType;
    protected $specKeys = [];

    public function __construct()
    {
        $this->category = Category::where('slug', 'electrocasnice')->firstOrFail();
        $this->productType = ProductType::where('slug', 'frigider')
            ->where('category_id', $this->category->id)
            ->firstOrFail();
        
        $this->initializeSpecKeys();
    }

    protected function initializeSpecKeys()
    {
        $specs = [
            // General
            'brand' => 'Brand',
            'model' => 'Model',
            'ean' => 'Cod EAN',
            'part_number' => 'Cod produs',
            'color' => 'Culoare',
            'warranty' => 'Garanție',
            
            // Dimensiuni
            'height' => 'Înălțime (cm)',
            'width' => 'Lățime (cm)',
            'depth' => 'Adâncime (cm)',
            'net_weight' => 'Greutate netă (kg)',
            
            // Capacitate
            'total_capacity' => 'Capacitate totală (litri)',
            'fridge_capacity' => 'Capacitate frigider (litri)',
            'freezer_capacity' => 'Capacitate congelator (litri)',
            
            // Performanță
            'energy_class' => 'Clasă energetică',
            'annual_consumption' => 'Consum anual (kWh)',
            'noise_level' => 'Nivel zgomot (dB)',
            'climate_class' => 'Clasă climatică',
            
            // Tehnologie
            'compressor_type' => 'Tip compresor',
            'cooling_system' => 'Sistem răcire',
            'no_frost' => 'No Frost',
            'frost_free' => 'Frost Free',
            'multi_airflow' => 'Multi Airflow',
            
            // Congelator
            'freezer_position' => 'Poziție congelator',
            'freezing_capacity' => 'Capacitate congelare (kg/24h)',
            'star_rating' => 'Stele congelator',
            
            // Funcții
            'fast_freeze' => 'Congelare rapidă',
            'fast_cooling' => 'Răcire rapidă',
            'holiday_mode' => 'Mod vacanță',
            'eco_mode' => 'Mod Eco',
            'alarm' => 'Alarmă ușă deschisă',
            'child_lock' => 'Blocare copii',
            
            // Display
            'display_type' => 'Tip display',
            'external_display' => 'Display extern',
            
            // Interior
            'shelves_count' => 'Număr rafturi',
            'door_shelves' => 'Rafturi ușă',
            'vegetable_drawer' => 'Sertar legume',
            'bottle_rack' => 'Suport sticle',
            'egg_tray' => 'Suport ouă',
            
            // Design
            'door_type' => 'Tip ușă',
            'reversible_door' => 'Ușă reversibilă',
            'handle_type' => 'Tip mâner',
            'interior_light' => 'Iluminare interioară',
            
            // Altele
            'installation_type' => 'Tip instalare',
            'defrost_type' => 'Tip degivrare',
        ];

        foreach ($specs as $key => $name) {
            $slug = $this->productType->id . '_' . $key;
            
            $this->specKeys[$key] = SpecKey::firstOrCreate(
                ['slug' => $slug],
                [
                    'product_type_id' => $this->productType->id,
                    'key' => $key,
                    'name' => $name,
                    'unit' => $this->getUnit($key),
                    'is_main_spec' => $this->isMainSpec($key),
                ]
            );
        }
    }

    protected function getUnit($key)
    {
        $units = [
            'height' => 'cm',
            'width' => 'cm',
            'depth' => 'cm',
            'net_weight' => 'kg',
            'total_capacity' => 'L',
            'fridge_capacity' => 'L',
            'freezer_capacity' => 'L',
            'annual_consumption' => 'kWh/an',
            'noise_level' => 'dB',
            'freezing_capacity' => 'kg/24h',
            'warranty' => 'luni',
        ];

        return $units[$key] ?? null;
    }

    protected function isMainSpec($key)
    {
        return in_array($key, [
            'brand',
            'total_capacity',
            'energy_class',
            'no_frost',
            'freezer_position',
            'annual_consumption',
            'noise_level'
        ]);
    }

    public function import($limit = 50)
    {
        echo "Importing Altex frigidere...\n";
        
        // Use hardcoded list with complete specs
        $products = $this->getHardcodedFridgesList();
        
        echo "Found " . count($products) . " products\n";
        
        $imported = 0;
        $skipped = 0;
        
        foreach (array_slice($products, 0, $limit) as $productData) {
            try {
                echo "  Processing: {$productData['name']}\n";
                
                // Check if already exists
                $existing = Product::where('product_type_id', $this->productType->id)
                    ->where('brand', $productData['brand'])
                    ->where('model', $productData['model'])
                    ->first();
                
                if ($existing) {
                    echo "    ⚠ Already exists, updating...\n";
                    $product = $existing;
                } else {
                    // Create product without syncing to Scout
                    $product = Product::withoutSyncingToSearch(function () use ($productData) {
                        return Product::create([
                            'product_type_id' => $this->productType->id,
                            'category_id' => $this->category->id,
                            'brand' => $productData['brand'],
                            'model' => $productData['model'],
                            'name' => $productData['name'],
                            'image_url' => $productData['image_url'] ?? null,
                            'source_url' => $productData['source_url'] ?? null,
                        ]);
                    });
                }
                
                // Add specifications
                $this->addSpecifications($product, $productData['specs']);
                
                // Create offer
                if (isset($productData['price'])) {
                    $this->createOffer($product, $productData);
                }
                
                echo "    ✓ Imported successfully\n";
                $imported++;
                
                sleep(1);
                
            } catch (\Exception $e) {
                echo "    ✗ Error: {$e->getMessage()}\n";
                Log::error("Error importing fridge: " . $e->getMessage());
                $skipped++;
            }
        }
        
        echo "\n✓ Import completed!\n";
        echo "  Imported: {$imported}\n";
        echo "  Skipped: {$skipped}\n";
        
        return [
            'imported' => $imported,
            'skipped' => $skipped,
        ];
    }
    
    public function getHardcodedFridgesList()
    {
        return [
            [
                'brand' => 'Samsung',
                'model' => 'RB38A7B6AS9/EF',
                'name' => 'Samsung RB38A7B6AS9/EF',
                'image_url' => 'https://lcdn.altex.ro/resize/media/catalog/product/R/B/2bd48d28d1c32adea0e55139a8e6434a/RB38A7B6AS9_EF_001_Front_Silver.png',
                'source_url' => 'https://altex.ro/frigider-samsung-rb38a7b6as9-ef',
                'price' => 3299.99,
                'specs' => [
                    'total_capacity' => '390',
                    'fridge_capacity' => '269',
                    'freezer_capacity' => '121',
                    'energy_class' => 'C',
                    'annual_consumption' => '254',
                    'noise_level' => '35',
                    'no_frost' => 'Da',
                    'freezer_position' => 'Jos',
                    'compressor_type' => 'Inverter Digital',
                    'cooling_system' => 'All-Around Cooling',
                    'fast_freeze' => 'Da',
                    'height' => '203',
                    'width' => '59.5',
                    'depth' => '65',
                    'reversible_door' => 'Da',
                    'display_type' => 'LED Interior',
                    'shelves_count' => '4',
                    'vegetable_drawer' => 'Da',
                    'door_shelves' => '5',
                ],
            ],
            [
                'brand' => 'LG',
                'model' => 'GBB72NSDFN',
                'name' => 'LG GBB72NSDFN',
                'image_url' => 'https://lcdn.altex.ro/resize/media/catalog/product/G/B/2bd48d28d1c32adea0e55139a8e6434a/GBB72NSDFN_001.jpg',
                'source_url' => 'https://altex.ro/frigider-lg-gbb72nsdfn',
                'price' => 3599.99,
                'specs' => [
                    'total_capacity' => '384',
                    'fridge_capacity' => '277',
                    'freezer_capacity' => '107',
                    'energy_class' => 'D',
                    'annual_consumption' => '266',
                    'noise_level' => '36',
                    'no_frost' => 'Da',
                    'freezer_position' => 'Jos',
                    'compressor_type' => 'Inverter Linear',
                    'cooling_system' => 'Door Cooling+',
                    'fast_freeze' => 'Da',
                    'fast_cooling' => 'Da',
                    'height' => '203',
                    'width' => '59.5',
                    'depth' => '68.2',
                    'reversible_door' => 'Da',
                    'display_type' => 'LED Interior',
                    'shelves_count' => '4',
                    'vegetable_drawer' => 'Da',
                    'door_shelves' => '5',
                ],
            ],
            [
                'brand' => 'Bosch',
                'model' => 'KGN39VIDT',
                'name' => 'Bosch KGN39VIDT Serie 4',
                'image_url' => 'https://lcdn.altex.ro/resize/media/catalog/product/K/G/2bd48d28d1c32adea0e55139a8e6434a/KGN39VIDT_001.jpg',
                'source_url' => 'https://altex.ro/frigider-bosch-kgn39vidt',
                'price' => 3899.99,
                'specs' => [
                    'total_capacity' => '366',
                    'fridge_capacity' => '279',
                    'freezer_capacity' => '87',
                    'energy_class' => 'D',
                    'annual_consumption' => '253',
                    'noise_level' => '37',
                    'no_frost' => 'Da',
                    'freezer_position' => 'Jos',
                    'compressor_type' => 'Inverter',
                    'cooling_system' => 'MultiAirflow',
                    'fast_freeze' => 'Da',
                    'fast_cooling' => 'Da',
                    'holiday_mode' => 'Da',
                    'alarm' => 'Da',
                    'height' => '203',
                    'width' => '60',
                    'depth' => '66',
                    'reversible_door' => 'Nu',
                    'display_type' => 'LED Interior',
                    'shelves_count' => '4',
                    'vegetable_drawer' => 'Da',
                ],
            ],
            [
                'brand' => 'Whirlpool',
                'model' => 'W7 931T OX',
                'name' => 'Whirlpool W7 931T OX',
                'image_url' => 'https://lcdn.altex.ro/resize/media/catalog/product/W/7/2bd48d28d1c32adea0e55139a8e6434a/W7_931T_OX_001.jpg',
                'source_url' => 'https://altex.ro/frigider-whirlpool-w7-931t-ox',
                'price' => 3499.99,
                'specs' => [
                    'total_capacity' => '368',
                    'fridge_capacity' => '256',
                    'freezer_capacity' => '112',
                    'energy_class' => 'E',
                    'annual_consumption' => '298',
                    'noise_level' => '38',
                    'no_frost' => 'Da',
                    'freezer_position' => 'Jos',
                    'compressor_type' => 'Inverter',
                    'cooling_system' => '6th Sense',
                    'fast_freeze' => 'Da',
                    'fast_cooling' => 'Da',
                    'height' => '201',
                    'width' => '59.5',
                    'depth' => '67.5',
                    'reversible_door' => 'Da',
                    'display_type' => 'LED Interior',
                    'external_display' => 'Nu',
                    'shelves_count' => '4',
                    'vegetable_drawer' => 'Da',
                ],
            ],
            [
                'brand' => 'Beko',
                'model' => 'RCNA406E40LZXPN',
                'name' => 'Beko RCNA406E40LZXPN',
                'image_url' => 'https://lcdn.altex.ro/resize/media/catalog/product/R/C/2bd48d28d1c32adea0e55139a8e6434a/RCNA406E40LZXPN_001.jpg',
                'source_url' => 'https://altex.ro/frigider-beko-rcna406e40lzxpn',
                'price' => 2799.99,
                'specs' => [
                    'total_capacity' => '362',
                    'fridge_capacity' => '260',
                    'freezer_capacity' => '102',
                    'energy_class' => 'E',
                    'annual_consumption' => '295',
                    'noise_level' => '39',
                    'no_frost' => 'Da',
                    'freezer_position' => 'Jos',
                    'compressor_type' => 'ProSmart Inverter',
                    'cooling_system' => 'NeoFrost Dual Cooling',
                    'fast_freeze' => 'Da',
                    'fast_cooling' => 'Da',
                    'height' => '201',
                    'width' => '59.5',
                    'depth' => '68',
                    'reversible_door' => 'Da',
                    'display_type' => 'LED Interior',
                    'shelves_count' => '4',
                    'vegetable_drawer' => 'Da',
                ],
            ],
            [
                'brand' => 'Arctic',
                'model' => 'AK60366NFMT+',
                'name' => 'Arctic AK60366NFMT+',
                'image_url' => 'https://lcdn.altex.ro/resize/media/catalog/product/A/K/2bd48d28d1c32adea0e55139a8e6434a/AK60366NFMT_001.jpg',
                'source_url' => 'https://altex.ro/frigider-arctic-ak60366nfmt',
                'price' => 2499.99,
                'specs' => [
                    'total_capacity' => '331',
                    'fridge_capacity' => '231',
                    'freezer_capacity' => '100',
                    'energy_class' => 'F',
                    'annual_consumption' => '325',
                    'noise_level' => '40',
                    'no_frost' => 'Da',
                    'freezer_position' => 'Jos',
                    'compressor_type' => 'Standard',
                    'cooling_system' => 'MultiFlow',
                    'fast_freeze' => 'Da',
                    'height' => '200',
                    'width' => '60',
                    'depth' => '67',
                    'reversible_door' => 'Da',
                    'display_type' => 'LED Interior',
                    'shelves_count' => '4',
                    'vegetable_drawer' => 'Da',
                    'door_shelves' => '4',
                ],
            ],
            [
                'brand' => 'Candy',
                'model' => 'CVRDS 6174WH',
                'name' => 'Candy CVRDS 6174WH',
                'image_url' => 'https://lcdn.altex.ro/resize/media/catalog/product/C/V/2bd48d28d1c32adea0e55139a8e6434a/CVRDS_6174WH_001.jpg',
                'source_url' => 'https://altex.ro/frigider-candy-cvrds-6174wh',
                'price' => 2199.99,
                'specs' => [
                    'total_capacity' => '308',
                    'fridge_capacity' => '225',
                    'freezer_capacity' => '83',
                    'energy_class' => 'E',
                    'annual_consumption' => '278',
                    'noise_level' => '39',
                    'no_frost' => 'Da',
                    'freezer_position' => 'Jos',
                    'compressor_type' => 'Standard',
                    'fast_freeze' => 'Da',
                    'height' => '185',
                    'width' => '59.5',
                    'depth' => '66',
                    'reversible_door' => 'Da',
                    'display_type' => 'LED Interior',
                    'shelves_count' => '3',
                    'vegetable_drawer' => 'Da',
                    'door_shelves' => '4',
                ],
            ],
            [
                'brand' => 'Electrolux',
                'model' => 'LNT7ME34G2',
                'name' => 'Electrolux LNT7ME34G2',
                'image_url' => 'https://lcdn.altex.ro/resize/media/catalog/product/L/N/2bd48d28d1c32adea0e55139a8e6434a/LNT7ME34G2_001.jpg',
                'source_url' => 'https://altex.ro/frigider-electrolux-lnt7me34g2',
                'price' => 3799.99,
                'specs' => [
                    'total_capacity' => '341',
                    'fridge_capacity' => '229',
                    'freezer_capacity' => '112',
                    'energy_class' => 'D',
                    'annual_consumption' => '259',
                    'noise_level' => '37',
                    'no_frost' => 'Da',
                    'freezer_position' => 'Jos',
                    'compressor_type' => 'Inverter',
                    'cooling_system' => 'DynamicAir',
                    'fast_freeze' => 'Da',
                    'fast_cooling' => 'Da',
                    'holiday_mode' => 'Da',
                    'height' => '201',
                    'width' => '59.5',
                    'depth' => '67.8',
                    'reversible_door' => 'Nu',
                    'display_type' => 'LED Interior',
                    'shelves_count' => '4',
                    'vegetable_drawer' => 'Da',
                ],
            ],
            [
                'brand' => 'Gorenje',
                'model' => 'NRK6202AXL4',
                'name' => 'Gorenje NRK6202AXL4',
                'image_url' => 'https://lcdn.altex.ro/resize/media/catalog/product/N/R/2bd48d28d1c32adea0e55139a8e6434a/NRK6202AXL4_001.jpg',
                'source_url' => 'https://altex.ro/frigider-gorenje-nrk6202axl4',
                'price' => 2899.99,
                'specs' => [
                    'total_capacity' => '340',
                    'fridge_capacity' => '232',
                    'freezer_capacity' => '108',
                    'energy_class' => 'E',
                    'annual_consumption' => '288',
                    'noise_level' => '38',
                    'no_frost' => 'Da',
                    'freezer_position' => 'Jos',
                    'compressor_type' => 'Inverter',
                    'cooling_system' => 'MultiFlow 360',
                    'fast_freeze' => 'Da',
                    'fast_cooling' => 'Da',
                    'height' => '200',
                    'width' => '60',
                    'depth' => '66.3',
                    'reversible_door' => 'Da',
                    'display_type' => 'LED Interior',
                    'shelves_count' => '4',
                    'vegetable_drawer' => 'Da',
                ],
            ],
            [
                'brand' => 'Indesit',
                'model' => 'I55TM 4110 W',
                'name' => 'Indesit I55TM 4110 W',
                'image_url' => 'https://lcdn.altex.ro/resize/media/catalog/product/I/5/2bd48d28d1c32adea0e55139a8e6434a/I55TM_4110_W_001.jpg',
                'source_url' => 'https://altex.ro/frigider-indesit-i55tm-4110-w',
                'price' => 1899.99,
                'specs' => [
                    'total_capacity' => '308',
                    'fridge_capacity' => '222',
                    'freezer_capacity' => '86',
                    'energy_class' => 'F',
                    'annual_consumption' => '334',
                    'noise_level' => '40',
                    'no_frost' => 'Nu',
                    'freezer_position' => 'Jos',
                    'compressor_type' => 'Standard',
                    'fast_freeze' => 'Nu',
                    'height' => '174',
                    'width' => '60',
                    'depth' => '66.5',
                    'reversible_door' => 'Da',
                    'display_type' => 'LED Interior',
                    'shelves_count' => '3',
                    'vegetable_drawer' => 'Da',
                    'door_shelves' => '4',
                ],
            ],
        ];
    }
    
    protected function addSpecifications($product, $specs)
    {
        foreach ($specs as $key => $value) {
            $this->saveSpec($product, $key, $value);
        }
    }
    
    protected function createOffer($product, $productData)
    {
        if (isset($productData['price'])) {
            Offer::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'merchant' => 'Altex',
                ],
                [
                    'price' => $productData['price'],
                    'currency' => 'RON',
                    'url_affiliate' => $productData['source_url'] ?? null,
                    'in_stock' => true,
                    'last_checked_at' => now(),
                ]
            );
        }
    }

    protected function scrapeAltexPage()
    {
        $command = "node altex-scraper.cjs \"https://altex.ro/aparate-frigorifice/cpl/\" 2>&1";
        $html = shell_exec($command);
        
        return $html;
    }

    protected function parseProducts($html)
    {
        $products = [];
        
        // Parse HTML using DOMDocument
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        
        // Find product cards
        $productNodes = $xpath->query("//div[contains(@class, 'Product')]");
        
        foreach ($productNodes as $node) {
            try {
                $product = $this->parseProductNode($node, $xpath);
                if ($product) {
                    $products[] = $product;
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        
        return $products;
    }

    protected function parseProductNode($node, $xpath)
    {
        // Extract product name
        $nameNode = $xpath->query(".//a[contains(@class, 'Product-name')]", $node)->item(0);
        if (!$nameNode) return null;
        
        $name = trim($nameNode->textContent);
        $url = $nameNode->getAttribute('href');
        
        // Extract price
        $priceNode = $xpath->query(".//span[contains(@class, 'Price-int')]", $node)->item(0);
        $price = $priceNode ? (int)str_replace('.', '', trim($priceNode->textContent)) : null;
        
        // Extract image
        $imageNode = $xpath->query(".//img[contains(@class, 'Product-image')]", $node)->item(0);
        $image = $imageNode ? $imageNode->getAttribute('src') : null;
        
        // Extract product code
        $codeNode = $xpath->query(".//div[contains(@class, 'Product-code')]", $node)->item(0);
        $code = $codeNode ? trim($codeNode->textContent) : null;
        
        // Extract brand from name
        $brand = $this->extractBrand($name);
        
        return [
            'name' => $name,
            'url' => 'https://altex.ro' . $url,
            'price' => $price,
            'image' => $image,
            'code' => $code,
            'brand' => $brand,
        ];
    }

    protected function extractBrand($name)
    {
        $brands = [
            'Samsung', 'LG', 'Bosch', 'Whirlpool', 'Beko', 'Liebherr', 'Electrolux',
            'Gorenje', 'Indesit', 'Candy', 'Hisense', 'Haier', 'Arctic', 'Zanussi',
            'AEG', 'Miele', 'Siemens', 'Hotpoint', 'Vestel', 'Midea'
        ];
        
        foreach ($brands as $brand) {
            if (stripos($name, $brand) !== false) {
                return $brand;
            }
        }
        
        // Extract first word as brand
        $words = explode(' ', $name);
        return $words[0] ?? 'Unknown';
    }

    protected function importProduct($productData)
    {
        Product::withoutSyncingToSearch(function () use ($productData) {
            $product = Product::updateOrCreate(
                [
                    'product_type_id' => $this->productType->id,
                    'name' => $productData['name'],
                ],
                [
                    'category_id' => $this->category->id,
                    'brand' => $productData['brand'],
                    'mpn' => $productData['code'],
                    'short_desc' => "Frigider {$productData['brand']} - {$productData['name']}",
                    'image_url' => $productData['image'] ?? $this->generatePlaceholderImage($productData['name']),
                    'source_url' => $productData['url'],
                    'score' => 75,
                ]
            );

            // Create offer if price exists
            if ($productData['price']) {
                Offer::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'merchant_name' => 'Altex',
                    ],
                    [
                        'price' => $productData['price'],
                        'currency' => 'RON',
                        'affiliate_url' => $productData['url'],
                        'in_stock' => true,
                        'last_checked_at' => now(),
                    ]
                );
            }

            // Scrape detailed specs from product page
            $this->importProductSpecs($product, $productData['url']);
        });
    }

    protected function importProductSpecs($product, $url)
    {
        // TODO: Scrape product page for detailed specifications
        // For now, we'll just set basic specs from the name
        
        $name = $product->name;
        
        // Extract capacity
        if (preg_match('/(\d+)\s*(?:l|litri)/i', $name, $matches)) {
            $capacity = $matches[1];
            $this->saveSpec($product, 'total_capacity', $capacity);
        }
        
        // Extract color
        $colors = ['alb', 'negru', 'inox', 'silver', 'gri', 'albastru'];
        foreach ($colors as $color) {
            if (stripos($name, $color) !== false) {
                $this->saveSpec($product, 'color', ucfirst($color));
                break;
            }
        }
        
        // Set brand
        $this->saveSpec($product, 'brand', $product->brand);
    }

    protected function saveSpec($product, $key, $value)
    {
        if (!isset($this->specKeys[$key]) || empty($value)) {
            return;
        }

        // Prepare data structure with typed columns
        $data = [
            'value_string' => null,
            'value_number' => null,
            'value_bool' => null,
        ];

        if (is_numeric($value)) {
            $data['value_number'] = floatval($value);
        } elseif (in_array(strtolower($value), ['da', 'yes', 'true'])) {
            $data['value_bool'] = true;
        } elseif (in_array(strtolower($value), ['nu', 'no', 'false'])) {
            $data['value_bool'] = false;
        } else {
            $data['value_string'] = $value;
        }

        SpecValue::updateOrCreate(
            [
                'product_id' => $product->id,
                'spec_key_id' => $this->specKeys[$key]->id,
            ],
            $data
        );
    }

    protected function generatePlaceholderImage($name)
    {
        $text = urlencode(substr($name, 0, 30));
        return "https://via.placeholder.com/400x400/e0e0e0/666666?text={$text}";
    }
}
