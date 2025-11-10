<?php

namespace App\Services\Importers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\SpecKey;
use App\Models\SpecValue;
use App\Models\Offer;
use App\Services\PuppeteerScraper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VersusWatchImporter
{
    protected $category;
    protected $productType;
    protected $specKeys = [];
    protected $baseUrl = 'https://versus.com';

    public function __construct()
    {
        // Create or get the Smartwatch category
        $this->category = Category::firstOrCreate(
            ['name' => 'Smartwatch-uri'],
            [
                'slug' => 'smartwatch-uri',
                'description' => 'Ceasuri inteligente de la toate mărcile',
                'icon' => '⌚',
                'is_active' => true,
            ]
        );

        // Create or get the Smartwatch product type
        $this->productType = ProductType::firstOrCreate(
            ['name' => 'Smartwatch'],
            [
                'slug' => 'smartwatch',
                'category_id' => $this->category->id,
            ]
        );

        // Initialize specification keys
        $this->initializeSpecKeys();
    }

    protected function initializeSpecKeys()
    {
        $specs = [
            // Design
            'weight' => ['name' => 'Greutate', 'unit' => 'g'],
            'thickness' => ['name' => 'Grosime', 'unit' => 'mm'],
            'water_resistance' => ['name' => 'Rezistență la apă', 'unit' => 'ATM'],
            'case_size' => ['name' => 'Dimensiune carcasă', 'unit' => 'mm'],
            'strap_width' => ['name' => 'Lățime curea', 'unit' => 'mm'],
            
            // Display
            'screen_size' => ['name' => 'Diagonală ecran', 'unit' => 'inch'],
            'resolution_width' => ['name' => 'Rezoluție lățime', 'unit' => 'px'],
            'resolution_height' => ['name' => 'Rezoluție înălțime', 'unit' => 'px'],
            'pixel_density' => ['name' => 'Densitate pixeli', 'unit' => 'ppi'],
            'display_type' => ['name' => 'Tip display', 'unit' => ''],
            'always_on_display' => ['name' => 'Always-on display', 'unit' => ''],
            'touchscreen' => ['name' => 'Touchscreen', 'unit' => ''],
            'brightness' => ['name' => 'Luminozitate', 'unit' => 'nits'],
            
            // Performance
            'ram' => ['name' => 'RAM', 'unit' => 'GB'],
            'internal_storage' => ['name' => 'Stocare internă', 'unit' => 'GB'],
            'chipset' => ['name' => 'Chipset', 'unit' => ''],
            'cpu_cores' => ['name' => 'Nuclee CPU', 'unit' => ''],
            
            // Battery
            'battery_capacity' => ['name' => 'Capacitate baterie', 'unit' => 'mAh'],
            'battery_life' => ['name' => 'Autonomie baterie', 'unit' => 'zile'],
            'charging_speed' => ['name' => 'Viteză încărcare', 'unit' => 'W'],
            'wireless_charging' => ['name' => 'Încărcare wireless', 'unit' => ''],
            
            // Sensors
            'heart_rate_monitor' => ['name' => 'Monitor ritm cardiac', 'unit' => ''],
            'blood_oxygen' => ['name' => 'Oxigen din sânge (SpO2)', 'unit' => ''],
            'ecg' => ['name' => 'ECG', 'unit' => ''],
            'blood_pressure' => ['name' => 'Presiune sanguină', 'unit' => ''],
            'body_temperature' => ['name' => 'Temperatură corp', 'unit' => ''],
            'accelerometer' => ['name' => 'Accelerometru', 'unit' => ''],
            'gyroscope' => ['name' => 'Giroscop', 'unit' => ''],
            'barometer' => ['name' => 'Barometru', 'unit' => ''],
            'compass' => ['name' => 'Busolă', 'unit' => ''],
            'gps' => ['name' => 'GPS', 'unit' => ''],
            'glonass' => ['name' => 'GLONASS', 'unit' => ''],
            
            // Features
            'sleep_tracking' => ['name' => 'Monitorizare somn', 'unit' => ''],
            'stress_tracking' => ['name' => 'Monitorizare stres', 'unit' => ''],
            'menstrual_cycle' => ['name' => 'Ciclu menstrual', 'unit' => ''],
            'vo2_max' => ['name' => 'VO2 Max', 'unit' => ''],
            'workout_modes' => ['name' => 'Moduri antrenament', 'unit' => ''],
            'swimming_tracking' => ['name' => 'Tracking înot', 'unit' => ''],
            'music_storage' => ['name' => 'Stocare muzică', 'unit' => 'GB'],
            'music_playback' => ['name' => 'Redare muzică', 'unit' => ''],
            'voice_assistant' => ['name' => 'Asistent vocal', 'unit' => ''],
            'nfc' => ['name' => 'NFC', 'unit' => ''],
            'mobile_payments' => ['name' => 'Plăți mobile', 'unit' => ''],
            
            // Connectivity
            'bluetooth_version' => ['name' => 'Versiune Bluetooth', 'unit' => ''],
            'wifi' => ['name' => 'Wi-Fi', 'unit' => ''],
            'lte' => ['name' => 'LTE/4G', 'unit' => ''],
            'esim' => ['name' => 'eSIM', 'unit' => ''],
            
            // OS
            'os' => ['name' => 'Sistem operare', 'unit' => ''],
            'os_version' => ['name' => 'Versiune OS', 'unit' => ''],
            'compatible_os' => ['name' => 'Compatibil cu', 'unit' => ''],
            
            // Other
            'release_date' => ['name' => 'Dată lansare', 'unit' => ''],
            'price' => ['name' => 'Preț', 'unit' => 'RON'],
        ];

        foreach ($specs as $slug => $data) {
            $fullSlug = $this->productType->id . '_' . $slug;
            $this->specKeys[$slug] = SpecKey::firstOrCreate(
                ['slug' => $fullSlug],
                [
                    'name' => $data['name'],
                    'unit' => $data['unit'],
                    'product_type_id' => $this->productType->id,
                ]
            );
        }
    }

    public function import($limit = 100)
    {
        try {
            Log::info("Starting smartwatch import from Versus.com");
            
            // Scrape watch list from versus.com
            $watches = $this->scrapeWatchList($limit);
            
            Log::info("Found " . count($watches) . " smartwatches to import");
            
            $imported = 0;
            foreach ($watches as $watchData) {
                try {
                    $this->importWatch($watchData);
                    $imported++;
                    echo "✓ Imported: {$watchData['name']}\n";
                    sleep(2); // Be respectful to the server
                } catch (\Exception $e) {
                    echo "✗ Error importing {$watchData['name']}: {$e->getMessage()}\n";
                    Log::error("Error importing watch {$watchData['name']}: " . $e->getMessage());
                }
            }
            
            Log::info("Smartwatch import completed. Imported {$imported} watches");
            
        } catch (\Exception $e) {
            Log::error("Error in smartwatch import: " . $e->getMessage());
            throw $e;
        }
    }

    protected function scrapeWatchList($limit)
    {
        $watches = [];
        $url = 'https://versus.com/en/smartwatch';
        
        try {
            echo "Fetching smartwatch list from {$url}...\n";
            
            $response = Http::timeout(30)
                ->withoutVerifying()
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                ])
                ->get($url);
            
            if (!$response->successful()) {
                throw new \Exception("Failed to fetch watch list: " . $response->status());
            }
            
            $html = $response->body();
            
            // Extract watch links using regex
            preg_match_all('#href="(?:https://versus\.com)?/en/([a-z0-9-]+)"[^>]*>([^<]*(?:<[^>]*>)*)*([A-Z][^\n]*?)(?:\s|<)#i', $html, $matches, PREG_SET_ORDER);
            
            $seenSlugs = [];
            
            foreach ($matches as $match) {
                $slug = $match[1];
                
                // Skip non-watch pages
                $excludePatterns = ['smartwatch', 'categories', 'news', 'glossary', 'about-us', 'privacy-policy', 'terms-and-conditions', 'suggest-product', 'partnerships', 'vs'];
                $isExcluded = false;
                foreach ($excludePatterns as $pattern) {
                    if ($slug === $pattern || strpos($slug, $pattern) === 0) {
                        $isExcluded = true;
                        break;
                    }
                }
                
                // Must contain watch brand patterns
                $watchBrands = ['apple-watch', 'samsung-galaxy-watch', 'samsung-gear', 'fitbit', 'garmin', 'huawei-watch', 'amazfit', 'xiaomi-watch', 'honor-watch', 'oppo-watch', 'realme-watch', 'fossil', 'ticwatch', 'suunto', 'polar', 'withings', 'google-pixel-watch', 'nothing-cmf-watch', 'oneplus-watch', 'xiaomi-mi-watch', 'redmi-watch'];
                $hasWatchBrand = false;
                foreach ($watchBrands as $brand) {
                    if (strpos($slug, $brand) !== false) {
                        $hasWatchBrand = true;
                        break;
                    }
                }
                
                if ($isExcluded || !$hasWatchBrand || isset($seenSlugs[$slug])) {
                    continue;
                }
                
                $seenSlugs[$slug] = true;
                
                // Generate name from slug
                $name = ucwords(str_replace('-', ' ', $slug));
                
                $watches[] = [
                    'name' => $name,
                    'slug' => $slug,
                    'url' => $this->baseUrl . '/en/' . $slug
                ];
                
                if (count($watches) >= $limit) {
                    break;
                }
            }
            
            if (count($watches) === 0) {
                throw new \Exception("No watches found on page");
            }
            
            echo "✓ Found " . count($watches) . " smartwatches\n";
            return $watches;
            
        } catch (\Exception $e) {
            Log::error("Error scraping watch list: " . $e->getMessage());
            echo "✗ Error scraping watch list, using fallback list\n";
            return $this->getFallbackWatchList($limit);
        }
    }

    protected function getFallbackWatchList($limit)
    {
        // Hardcoded smartwatches with COMPLETE specifications
        $smartwatches = [
            // Apple Watch
            [
                'brand' => 'Apple',
                'model' => 'Watch Series 10',
                'name' => 'Apple Watch Series 10',
                'slug' => 'apple-watch-series-10',
                'price' => 2499.99,
                'image_url' => 'https://m.media-amazon.com/images/I/71M0sABTNxL._AC_SL1500_.jpg',
                'specs' => [
                    'screen_size' => '1.96',
                    'resolution_width' => '496',
                    'resolution_height' => '502',
                    'display_type' => 'OLED',
                    'always_on_display' => 'Da',
                    'touchscreen' => 'Da',
                    'brightness' => '2000',
                    'weight' => '42',
                    'thickness' => '9.7',
                    'water_resistance' => '5',
                    'battery_capacity' => '308',
                    'battery_life' => '1.5',
                    'wireless_charging' => 'Da',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'ecg' => 'Da',
                    'gps' => 'Da',
                    'sleep_tracking' => 'Da',
                    'workout_modes' => '100',
                ],
            ],
            [
                'brand' => 'Apple',
                'model' => 'Watch Ultra 2',
                'name' => 'Apple Watch Ultra 2',
                'slug' => 'apple-watch-ultra-2',
                'price' => 4499.99,
                'image_url' => 'https://m.media-amazon.com/images/I/81S0fHXEURL._AC_SL1500_.jpg',
                'specs' => [
                    'screen_size' => '1.92',
                    'resolution_width' => '410',
                    'resolution_height' => '502',
                    'display_type' => 'OLED',
                    'always_on_display' => 'Da',
                    'brightness' => '3000',
                    'weight' => '61',
                    'thickness' => '14.4',
                    'water_resistance' => '10',
                    'battery_capacity' => '542',
                    'battery_life' => '3',
                    'wireless_charging' => 'Da',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'ecg' => 'Da',
                    'gps' => 'Da',
                    'glonass' => 'Da',
                    'sleep_tracking' => 'Da',
                    'workout_modes' => '100',
                    'swimming_tracking' => 'Da',
                ],
            ],
            [
                'brand' => 'Samsung',
                'model' => 'Galaxy Watch 7',
                'name' => 'Samsung Galaxy Watch 7',
                'slug' => 'samsung-galaxy-watch-7',
                'price' => 1799.99,
                'image_url' => 'https://images.samsung.com/is/image/samsung/p6pim/ro/sm-l310nzgaeue/gallery/ro-galaxy-watch7-sm-l310-sm-l310nzgaeue-543021944',
                'specs' => [
                    'screen_size' => '1.5',
                    'resolution_width' => '480',
                    'resolution_height' => '480',
                    'display_type' => 'AMOLED',
                    'always_on_display' => 'Da',
                    'brightness' => '2000',
                    'weight' => '33',
                    'thickness' => '9.7',
                    'water_resistance' => '5',
                    'ram' => '2',
                    'internal_storage' => '32',
                    'battery_capacity' => '425',
                    'battery_life' => '2',
                    'wireless_charging' => 'Da',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'ecg' => 'Da',
                    'gps' => 'Da',
                    'sleep_tracking' => 'Da',
                    'workout_modes' => '120',
                ],
            ],
            [
                'brand' => 'Samsung',
                'model' => 'Galaxy Watch 6 Classic',
                'name' => 'Samsung Galaxy Watch 6 Classic',
                'slug' => 'samsung-galaxy-watch-6-classic',
                'price' => 1999.99,
                'image_url' => 'https://images.samsung.com/is/image/samsung/p6pim/ro/sm-r960nzaaeue/gallery/ro-galaxy-watch6-r960-sm-r960nzaaeue-537099961',
                'specs' => [
                    'screen_size' => '1.5',
                    'resolution_width' => '480',
                    'resolution_height' => '480',
                    'display_type' => 'AMOLED',
                    'always_on_display' => 'Da',
                    'brightness' => '2000',
                    'weight' => '59',
                    'thickness' => '10.9',
                    'water_resistance' => '5',
                    'ram' => '2',
                    'internal_storage' => '16',
                    'battery_capacity' => '425',
                    'battery_life' => '2',
                    'wireless_charging' => 'Da',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'ecg' => 'Da',
                    'gps' => 'Da',
                    'sleep_tracking' => 'Da',
                    'stress_tracking' => 'Da',
                    'workout_modes' => '95',
                ],
            ],
            [
                'brand' => 'Google',
                'model' => 'Pixel Watch 3',
                'name' => 'Google Pixel Watch 3',
                'slug' => 'google-pixel-watch-3',
                'price' => 2199.99,
                'image_url' => 'https://lh3.googleusercontent.com/jnVVT0e5y7LvKtNLxvRVGFxp82XH8EUYHCb1q3h2d8k',
                'specs' => [
                    'screen_size' => '1.4',
                    'resolution_width' => '456',
                    'resolution_height' => '456',
                    'display_type' => 'AMOLED',
                    'always_on_display' => 'Da',
                    'brightness' => '2000',
                    'weight' => '37',
                    'thickness' => '12.3',
                    'water_resistance' => '5',
                    'ram' => '2',
                    'internal_storage' => '32',
                    'battery_capacity' => '307',
                    'battery_life' => '1.5',
                    'wireless_charging' => 'Da',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'ecg' => 'Da',
                    'gps' => 'Da',
                    'sleep_tracking' => 'Da',
                    'workout_modes' => '40',
                ],
            ],
            [
                'brand' => 'Garmin',
                'model' => 'Fenix 8',
                'name' => 'Garmin Fenix 8',
                'slug' => 'garmin-fenix-8',
                'price' => 4999.99,
                'image_url' => 'https://static.garmin.com/en/products/010-02738-11/g/cf-lg.jpg',
                'specs' => [
                    'screen_size' => '1.4',
                    'resolution_width' => '454',
                    'resolution_height' => '454',
                    'display_type' => 'AMOLED',
                    'always_on_display' => 'Da',
                    'weight' => '82',
                    'thickness' => '14.5',
                    'water_resistance' => '10',
                    'internal_storage' => '32',
                    'battery_capacity' => '1800',
                    'battery_life' => '29',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'gps' => 'Da',
                    'glonass' => 'Da',
                    'barometer' => 'Da',
                    'compass' => 'Da',
                    'sleep_tracking' => 'Da',
                    'vo2_max' => 'Da',
                    'workout_modes' => '150',
                ],
            ],
            [
                'brand' => 'Garmin',
                'model' => 'Venu 3',
                'name' => 'Garmin Venu 3',
                'slug' => 'garmin-venu-3',
                'price' => 2799.99,
                'image_url' => 'https://static.garmin.com/en/products/010-02784-10/g/cf-lg.jpg',
                'specs' => [
                    'screen_size' => '1.4',
                    'resolution_width' => '454',
                    'resolution_height' => '454',
                    'display_type' => 'AMOLED',
                    'always_on_display' => 'Da',
                    'weight' => '47',
                    'thickness' => '12',
                    'water_resistance' => '5',
                    'internal_storage' => '8',
                    'battery_capacity' => '450',
                    'battery_life' => '14',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'gps' => 'Da',
                    'sleep_tracking' => 'Da',
                    'stress_tracking' => 'Da',
                    'vo2_max' => 'Da',
                    'workout_modes' => '30',
                    'music_storage' => '8',
                ],
            ],
            [
                'brand' => 'Fitbit',
                'model' => 'Sense 2',
                'name' => 'Fitbit Sense 2',
                'slug' => 'fitbit-sense-2',
                'price' => 1599.99,
                'image_url' => 'https://www.fitbit.com/global/content/dam/fitbit/global/pdp/devices/sense-2/hero-static/blue-mist/sense2-blue-mist-device.png',
                'specs' => [
                    'screen_size' => '1.58',
                    'resolution_width' => '336',
                    'resolution_height' => '336',
                    'display_type' => 'AMOLED',
                    'always_on_display' => 'Da',
                    'weight' => '37',
                    'thickness' => '12.3',
                    'water_resistance' => '5',
                    'battery_life' => '6',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'ecg' => 'Da',
                    'gps' => 'Da',
                    'sleep_tracking' => 'Da',
                    'stress_tracking' => 'Da',
                    'menstrual_cycle' => 'Da',
                    'workout_modes' => '40',
                ],
            ],
            [
                'brand' => 'Huawei',
                'model' => 'Watch GT 5 Pro',
                'name' => 'Huawei Watch GT 5 Pro',
                'slug' => 'huawei-watch-gt-5-pro',
                'price' => 1899.99,
                'image_url' => 'https://consumer.huawei.com/content/dam/huawei-cbg-site/common/mkt/pdp/wearables/watch-gt-5-pro/img/pc/huawei-watch-gt-5-pro-kv.png',
                'specs' => [
                    'screen_size' => '1.43',
                    'resolution_width' => '466',
                    'resolution_height' => '466',
                    'display_type' => 'AMOLED',
                    'always_on_display' => 'Da',
                    'weight' => '53',
                    'thickness' => '11',
                    'water_resistance' => '5',
                    'internal_storage' => '32',
                    'battery_capacity' => '530',
                    'battery_life' => '14',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'gps' => 'Da',
                    'sleep_tracking' => 'Da',
                    'stress_tracking' => 'Da',
                    'workout_modes' => '100',
                ],
            ],
            [
                'brand' => 'Amazfit',
                'model' => 'GTR 4',
                'name' => 'Amazfit GTR 4',
                'slug' => 'amazfit-gtr-4',
                'price' => 999.99,
                'image_url' => 'https://s3.amazonaws.com/amazfit-web/uploads/2022/09/GTR4-Black-1.png',
                'specs' => [
                    'screen_size' => '1.43',
                    'resolution_width' => '466',
                    'resolution_height' => '466',
                    'display_type' => 'AMOLED',
                    'always_on_display' => 'Da',
                    'weight' => '48',
                    'thickness' => '10.6',
                    'water_resistance' => '5',
                    'internal_storage' => '2',
                    'battery_capacity' => '475',
                    'battery_life' => '14',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'gps' => 'Da',
                    'sleep_tracking' => 'Da',
                    'workout_modes' => '150',
                ],
            ],
            [
                'brand' => 'Apple',
                'model' => 'Watch Series 9',
                'name' => 'Apple Watch Series 9',
                'slug' => 'apple-watch-series-9',
                'price' => 2199.99,
                'image_url' => 'https://m.media-amazon.com/images/I/71h2OSurzJL._AC_SL1500_.jpg',
                'specs' => [
                    'screen_size' => '1.9',
                    'resolution_width' => '484',
                    'resolution_height' => '396',
                    'display_type' => 'OLED',
                    'always_on_display' => 'Da',
                    'brightness' => '2000',
                    'weight' => '38',
                    'thickness' => '10.7',
                    'water_resistance' => '5',
                    'battery_capacity' => '295',
                    'battery_life' => '1.5',
                    'wireless_charging' => 'Da',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'ecg' => 'Da',
                    'gps' => 'Da',
                    'sleep_tracking' => 'Da',
                    'workout_modes' => '100',
                ],
            ],
            [
                'brand' => 'Samsung',
                'model' => 'Galaxy Watch 6',
                'name' => 'Samsung Galaxy Watch 6',
                'slug' => 'samsung-galaxy-watch-6',
                'price' => 1599.99,
                'image_url' => 'https://images.samsung.com/is/image/samsung/p6pim/ro/sm-r950nzaaeue/gallery/ro-galaxy-watch6-r950-sm-r950nzaaeue-537099944',
                'specs' => [
                    'screen_size' => '1.5',
                    'resolution_width' => '480',
                    'resolution_height' => '480',
                    'display_type' => 'AMOLED',
                    'always_on_display' => 'Da',
                    'weight' => '33',
                    'thickness' => '9',
                    'water_resistance' => '5',
                    'ram' => '2',
                    'internal_storage' => '16',
                    'battery_capacity' => '425',
                    'battery_life' => '2',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'ecg' => 'Da',
                    'gps' => 'Da',
                    'sleep_tracking' => 'Da',
                    'workout_modes' => '95',
                ],
            ],
            [
                'brand' => 'Garmin',
                'model' => 'Forerunner 965',
                'name' => 'Garmin Forerunner 965',
                'slug' => 'garmin-forerunner-965',
                'price' => 3299.99,
                'image_url' => 'https://static.garmin.com/en/products/010-02809-00/g/cf-lg.jpg',
                'specs' => [
                    'screen_size' => '1.4',
                    'resolution_width' => '454',
                    'resolution_height' => '454',
                    'display_type' => 'AMOLED',
                    'always_on_display' => 'Da',
                    'weight' => '53',
                    'thickness' => '13.2',
                    'water_resistance' => '5',
                    'internal_storage' => '32',
                    'battery_capacity' => '450',
                    'battery_life' => '23',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'gps' => 'Da',
                    'glonass' => 'Da',
                    'sleep_tracking' => 'Da',
                    'vo2_max' => 'Da',
                    'workout_modes' => '140',
                ],
            ],
            [
                'brand' => 'Fitbit',
                'model' => 'Versa 4',
                'name' => 'Fitbit Versa 4',
                'slug' => 'fitbit-versa-4',
                'price' => 1299.99,
                'image_url' => 'https://www.fitbit.com/global/content/dam/fitbit/global/pdp/devices/versa-4/hero-static/black/versa4-black-device.png',
                'specs' => [
                    'screen_size' => '1.58',
                    'resolution_width' => '336',
                    'resolution_height' => '336',
                    'display_type' => 'AMOLED',
                    'always_on_display' => 'Da',
                    'weight' => '37',
                    'thickness' => '12',
                    'water_resistance' => '5',
                    'battery_life' => '6',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'gps' => 'Da',
                    'sleep_tracking' => 'Da',
                    'stress_tracking' => 'Da',
                    'workout_modes' => '40',
                ],
            ],
            [
                'brand' => 'Huawei',
                'model' => 'Watch GT 4',
                'name' => 'Huawei Watch GT 4',
                'slug' => 'huawei-watch-gt-4',
                'price' => 1599.99,
                'image_url' => 'https://consumer.huawei.com/content/dam/huawei-cbg-site/common/mkt/pdp/wearables/watch-gt-4/img/pc/huawei-watch-gt-4-kv.png',
                'specs' => [
                    'screen_size' => '1.43',
                    'resolution_width' => '466',
                    'resolution_height' => '466',
                    'display_type' => 'AMOLED',
                    'always_on_display' => 'Da',
                    'weight' => '48',
                    'thickness' => '10.5',
                    'water_resistance' => '5',
                    'internal_storage' => '32',
                    'battery_capacity' => '530',
                    'battery_life' => '14',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'gps' => 'Da',
                    'sleep_tracking' => 'Da',
                    'workout_modes' => '100',
                ],
            ],
            [
                'brand' => 'Amazfit',
                'model' => 'T-Rex 3',
                'name' => 'Amazfit T-Rex 3',
                'slug' => 'amazfit-t-rex-3',
                'price' => 1199.99,
                'image_url' => 'https://s3.amazonaws.com/amazfit-web/uploads/2024/05/TRex3-Black-1.png',
                'specs' => [
                    'screen_size' => '1.5',
                    'resolution_width' => '480',
                    'resolution_height' => '480',
                    'display_type' => 'AMOLED',
                    'always_on_display' => 'Da',
                    'weight' => '68',
                    'thickness' => '13.5',
                    'water_resistance' => '10',
                    'internal_storage' => '2',
                    'battery_capacity' => '700',
                    'battery_life' => '27',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'gps' => 'Da',
                    'glonass' => 'Da',
                    'sleep_tracking' => 'Da',
                    'workout_modes' => '170',
                ],
            ],
            [
                'brand' => 'OnePlus',
                'model' => 'Watch 2',
                'name' => 'OnePlus Watch 2',
                'slug' => 'oneplus-watch-2',
                'price' => 1799.99,
                'image_url' => 'https://image01.oneplus.net/ebp/202402/19/1-m00-5b-63-cpgm7mxs0vkadfcxaahm-_w0cq0816.png',
                'specs' => [
                    'screen_size' => '1.43',
                    'resolution_width' => '466',
                    'resolution_height' => '466',
                    'display_type' => 'AMOLED',
                    'always_on_display' => 'Da',
                    'weight' => '49',
                    'thickness' => '12.1',
                    'water_resistance' => '5',
                    'ram' => '2',
                    'internal_storage' => '32',
                    'battery_capacity' => '500',
                    'battery_life' => '4',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'gps' => 'Da',
                    'sleep_tracking' => 'Da',
                    'workout_modes' => '100',
                ],
            ],
            [
                'brand' => 'Xiaomi',
                'model' => 'Watch 2 Pro',
                'name' => 'Xiaomi Watch 2 Pro',
                'slug' => 'xiaomi-watch-2-pro',
                'price' => 1499.99,
                'image_url' => 'https://i02.appmifile.com/mi-com-product/fly-birds/xiaomi-watch-2-pro/pc/gallery-1.png',
                'specs' => [
                    'screen_size' => '1.43',
                    'resolution_width' => '466',
                    'resolution_height' => '466',
                    'display_type' => 'AMOLED',
                    'always_on_display' => 'Da',
                    'weight' => '50',
                    'thickness' => '12.2',
                    'water_resistance' => '5',
                    'ram' => '2',
                    'internal_storage' => '32',
                    'battery_capacity' => '495',
                    'battery_life' => '5',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'gps' => 'Da',
                    'sleep_tracking' => 'Da',
                    'workout_modes' => '150',
                ],
            ],
            [
                'brand' => 'Honor',
                'model' => 'Watch 4 Pro',
                'name' => 'Honor Watch 4 Pro',
                'slug' => 'honor-watch-4-pro',
                'price' => 1699.99,
                'image_url' => 'https://www.hihonor.com/content/dam/honor/common/smartwear/honor-watch-4-pro/navigation/honor-watch-4-pro-black.png',
                'specs' => [
                    'screen_size' => '1.5',
                    'resolution_width' => '466',
                    'resolution_height' => '466',
                    'display_type' => 'AMOLED',
                    'always_on_display' => 'Da',
                    'weight' => '53',
                    'thickness' => '11.5',
                    'water_resistance' => '5',
                    'battery_capacity' => '530',
                    'battery_life' => '14',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'gps' => 'Da',
                    'sleep_tracking' => 'Da',
                    'workout_modes' => '100',
                ],
            ],
            [
                'brand' => 'Fossil',
                'model' => 'Gen 6',
                'name' => 'Fossil Gen 6',
                'slug' => 'fossil-gen-6',
                'price' => 1399.99,
                'image_url' => 'https://fossil.scene7.com/is/image/FossilPartners/FTW4059_main',
                'specs' => [
                    'screen_size' => '1.28',
                    'resolution_width' => '416',
                    'resolution_height' => '416',
                    'display_type' => 'AMOLED',
                    'always_on_display' => 'Da',
                    'weight' => '45',
                    'thickness' => '12',
                    'water_resistance' => '3',
                    'ram' => '1',
                    'internal_storage' => '8',
                    'battery_life' => '1',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'gps' => 'Da',
                    'sleep_tracking' => 'Da',
                    'workout_modes' => '20',
                ],
            ],
            [
                'brand' => 'TicWatch',
                'model' => 'Pro 5',
                'name' => 'TicWatch Pro 5',
                'slug' => 'ticwatch-pro-5',
                'price' => 1899.99,
                'image_url' => 'https://www.mobvoi.com/us/products/ticwatchpro5/images/ticwatch-pro-5-obsidian.png',
                'specs' => [
                    'screen_size' => '1.43',
                    'resolution_width' => '466',
                    'resolution_height' => '466',
                    'display_type' => 'AMOLED',
                    'always_on_display' => 'Da',
                    'weight' => '44',
                    'thickness' => '11.8',
                    'water_resistance' => '5',
                    'ram' => '2',
                    'internal_storage' => '32',
                    'battery_capacity' => '628',
                    'battery_life' => '3',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'gps' => 'Da',
                    'sleep_tracking' => 'Da',
                    'workout_modes' => '100',
                ],
            ],
            [
                'brand' => 'Garmin',
                'model' => 'Epix Pro',
                'name' => 'Garmin Epix Pro',
                'slug' => 'garmin-epix-pro',
                'price' => 4799.99,
                'image_url' => 'https://static.garmin.com/en/products/010-02803-00/g/cf-lg.jpg',
                'specs' => [
                    'screen_size' => '1.4',
                    'resolution_width' => '454',
                    'resolution_height' => '454',
                    'display_type' => 'AMOLED',
                    'always_on_display' => 'Da',
                    'weight' => '70',
                    'thickness' => '14.5',
                    'water_resistance' => '10',
                    'internal_storage' => '32',
                    'battery_capacity' => '1800',
                    'battery_life' => '31',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'gps' => 'Da',
                    'glonass' => 'Da',
                    'barometer' => 'Da',
                    'compass' => 'Da',
                    'sleep_tracking' => 'Da',
                    'vo2_max' => 'Da',
                    'workout_modes' => '150',
                ],
            ],
            [
                'brand' => 'Huawei',
                'model' => 'Watch Fit 3',
                'name' => 'Huawei Watch Fit 3',
                'slug' => 'huawei-watch-fit-3',
                'price' => 799.99,
                'image_url' => 'https://consumer.huawei.com/content/dam/huawei-cbg-site/common/mkt/pdp/wearables/watch-fit-3/img/pc/huawei-watch-fit-3-kv.png',
                'specs' => [
                    'screen_size' => '1.82',
                    'resolution_width' => '480',
                    'resolution_height' => '408',
                    'display_type' => 'AMOLED',
                    'always_on_display' => 'Da',
                    'weight' => '26',
                    'thickness' => '9.9',
                    'water_resistance' => '5',
                    'battery_capacity' => '400',
                    'battery_life' => '10',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'gps' => 'Da',
                    'sleep_tracking' => 'Da',
                    'workout_modes' => '100',
                ],
            ],
            [
                'brand' => 'Amazfit',
                'model' => 'Falcon',
                'name' => 'Amazfit Falcon',
                'slug' => 'amazfit-falcon',
                'price' => 2199.99,
                'image_url' => 'https://s3.amazonaws.com/amazfit-web/uploads/2022/09/Falcon-Desert-1.png',
                'specs' => [
                    'screen_size' => '1.28',
                    'resolution_width' => '416',
                    'resolution_height' => '416',
                    'display_type' => 'AMOLED',
                    'always_on_display' => 'Da',
                    'weight' => '64',
                    'thickness' => '13.6',
                    'water_resistance' => '20',
                    'internal_storage' => '4',
                    'battery_capacity' => '500',
                    'battery_life' => '14',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'gps' => 'Da',
                    'glonass' => 'Da',
                    'barometer' => 'Da',
                    'compass' => 'Da',
                    'sleep_tracking' => 'Da',
                    'workout_modes' => '150',
                ],
            ],
            [
                'brand' => 'Google',
                'model' => 'Pixel Watch 2',
                'name' => 'Google Pixel Watch 2',
                'slug' => 'google-pixel-watch-2',
                'price' => 1899.99,
                'image_url' => 'https://lh3.googleusercontent.com/Bh_1i9xByR7Ss-NTQ_cQfWPr4X8f7FqyCBK0uHxsqkj',
                'specs' => [
                    'screen_size' => '1.2',
                    'resolution_width' => '384',
                    'resolution_height' => '384',
                    'display_type' => 'AMOLED',
                    'always_on_display' => 'Da',
                    'weight' => '31',
                    'thickness' => '12.3',
                    'water_resistance' => '5',
                    'ram' => '2',
                    'internal_storage' => '32',
                    'battery_capacity' => '306',
                    'battery_life' => '1.5',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'ecg' => 'Da',
                    'gps' => 'Da',
                    'sleep_tracking' => 'Da',
                    'workout_modes' => '40',
                ],
            ],
            [
                'brand' => 'Samsung',
                'model' => 'Galaxy Watch 5 Pro',
                'name' => 'Samsung Galaxy Watch 5 Pro',
                'slug' => 'samsung-galaxy-watch-5-pro',
                'price' => 2299.99,
                'image_url' => 'https://images.samsung.com/is/image/samsung/p6pim/ro/sm-r920nzkaeue/gallery/ro-galaxy-watch5-r920-sm-r920nzkaeue-533660513',
                'specs' => [
                    'screen_size' => '1.4',
                    'resolution_width' => '450',
                    'resolution_height' => '450',
                    'display_type' => 'AMOLED',
                    'always_on_display' => 'Da',
                    'weight' => '46',
                    'thickness' => '10.5',
                    'water_resistance' => '5',
                    'ram' => '1.5',
                    'internal_storage' => '16',
                    'battery_capacity' => '590',
                    'battery_life' => '3',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'ecg' => 'Da',
                    'gps' => 'Da',
                    'sleep_tracking' => 'Da',
                    'workout_modes' => '90',
                ],
            ],
            [
                'brand' => 'Garmin',
                'model' => 'Fenix 7',
                'name' => 'Garmin Fenix 7',
                'slug' => 'garmin-fenix-7',
                'price' => 3999.99,
                'image_url' => 'https://static.garmin.com/en/products/010-02540-00/g/cf-lg.jpg',
                'specs' => [
                    'screen_size' => '1.3',
                    'resolution_width' => '260',
                    'resolution_height' => '260',
                    'display_type' => 'MIP',
                    'always_on_display' => 'Da',
                    'weight' => '79',
                    'thickness' => '14.5',
                    'water_resistance' => '10',
                    'internal_storage' => '16',
                    'battery_capacity' => '1300',
                    'battery_life' => '18',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'gps' => 'Da',
                    'glonass' => 'Da',
                    'barometer' => 'Da',
                    'compass' => 'Da',
                    'sleep_tracking' => 'Da',
                    'vo2_max' => 'Da',
                    'workout_modes' => '140',
                ],
            ],
            [
                'brand' => 'Apple',
                'model' => 'Watch SE 2024',
                'name' => 'Apple Watch SE 2024',
                'slug' => 'apple-watch-se-2024',
                'price' => 1599.99,
                'image_url' => 'https://m.media-amazon.com/images/I/71cSV-RTBZL._AC_SL1500_.jpg',
                'specs' => [
                    'screen_size' => '1.78',
                    'resolution_width' => '448',
                    'resolution_height' => '368',
                    'display_type' => 'OLED',
                    'always_on_display' => 'Nu',
                    'brightness' => '1000',
                    'weight' => '33',
                    'thickness' => '10.7',
                    'water_resistance' => '5',
                    'battery_capacity' => '296',
                    'battery_life' => '1.5',
                    'wireless_charging' => 'Da',
                    'heart_rate_monitor' => 'Da',
                    'gps' => 'Da',
                    'sleep_tracking' => 'Da',
                    'workout_modes' => '100',
                ],
            ],
            [
                'brand' => 'Samsung',
                'model' => 'Galaxy Watch 7 Ultra',
                'name' => 'Samsung Galaxy Watch 7 Ultra',
                'slug' => 'samsung-galaxy-watch-7-ultra',
                'price' => 3499.99,
                'image_url' => 'https://images.samsung.com/is/image/samsung/p6pim/ro/sm-l705fzaaeue/gallery/ro-galaxy-watch-ultra-sm-l705-sm-l705fzaaeue-543022016',
                'specs' => [
                    'screen_size' => '1.5',
                    'resolution_width' => '480',
                    'resolution_height' => '480',
                    'display_type' => 'AMOLED',
                    'always_on_display' => 'Da',
                    'brightness' => '3000',
                    'weight' => '60',
                    'thickness' => '12.1',
                    'water_resistance' => '10',
                    'ram' => '2',
                    'internal_storage' => '32',
                    'battery_capacity' => '590',
                    'battery_life' => '4',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'ecg' => 'Da',
                    'gps' => 'Da',
                    'glonass' => 'Da',
                    'sleep_tracking' => 'Da',
                    'workout_modes' => '120',
                ],
            ],
            [
                'brand' => 'Apple',
                'model' => 'Watch Ultra',
                'name' => 'Apple Watch Ultra',
                'slug' => 'apple-watch-ultra',
                'price' => 3999.99,
                'image_url' => 'https://m.media-amazon.com/images/I/81Rb8bTPrFL._AC_SL1500_.jpg',
                'specs' => [
                    'screen_size' => '1.92',
                    'resolution_width' => '410',
                    'resolution_height' => '502',
                    'display_type' => 'OLED',
                    'always_on_display' => 'Da',
                    'brightness' => '2000',
                    'weight' => '61',
                    'thickness' => '14.4',
                    'water_resistance' => '10',
                    'battery_capacity' => '542',
                    'battery_life' => '2',
                    'wireless_charging' => 'Da',
                    'heart_rate_monitor' => 'Da',
                    'blood_oxygen' => 'Da',
                    'ecg' => 'Da',
                    'gps' => 'Da',
                    'glonass' => 'Da',
                    'sleep_tracking' => 'Da',
                    'workout_modes' => '100',
                ],
            ],
        ];

        return array_slice($smartwatches, 0, $limit);
    }

    protected function importWatch($watchData)
    {
        // Extract brand from name
        $brand = $watchData['brand'] ?? explode(' ', $watchData['name'])[0];
        
        echo "  Processing: {$watchData['name']}\n";
        
        // Use hardcoded specs if available, otherwise scrape
        if (isset($watchData['specs'])) {
            $specs = $watchData['specs'];
            $specs['image_url'] = $watchData['image_url'] ?? $this->generatePlaceholderImage($watchData['name']);
            $specs['price'] = $watchData['price'] ?? null;
            $specs['description'] = "Smartwatch {$watchData['name']}";
            echo "    Using hardcoded specs (" . count($specs) . " specs)\n";
        } else {
            // Fallback to scraping if no hardcoded data
            $specs = $this->scrapeWatchSpecs($watchData['url']);
        }
        
        // Disable Scout indexing during import
        Product::withoutSyncingToSearch(function () use ($watchData, $brand, $specs) {
            // Create or update product
            $product = Product::updateOrCreate(
                [
                    'product_type_id' => $this->productType->id,
                    'brand' => $brand,
                    'name' => $watchData['name'],
                ],
                [
                    'category_id' => $this->category->id,
                    'model' => $watchData['model'] ?? $watchData['slug'],
                    'mpn' => $watchData['slug'],
                    'short_desc' => $specs['description'] ?? "Smartwatch {$watchData['name']}",
                    'image_url' => $specs['image_url'] ?? $this->generatePlaceholderImage($watchData['name']),
                    'score' => $specs['score'] ?? 75,
                ]
            );

            // Save all specifications
            $this->saveWatchSpecifications($product, $specs);

            // Create offer
            $this->createOffer($product, $specs['price'] ?? null);

            return $product;
        });
        
        echo "    ✓ Imported successfully\n";
    }

    protected function scrapeWatchSpecs($url)
    {
        try {
            echo "  Fetching specs from {$url}...\n";
            
            // Use Puppeteer for dynamic content
            $scraper = new PuppeteerScraper();
            $html = $scraper->scrape($url);
            
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadHTML($html);
            libxml_clear_errors();
            
            $xpath = new \DOMXPath($dom);
            
            $specs = [];
            
            // Extract REAL product image (not placeholder)
            $imageNodes = $xpath->query('//img[contains(@src, "versus.com") and contains(@src, "/product/")]');
            if ($imageNodes->length > 0) {
                $imageSrc = $imageNodes->item(0)->getAttribute('src');
                // Ensure it's a full URL
                if (strpos($imageSrc, 'http') !== 0) {
                    $imageSrc = 'https://versus.com' . $imageSrc;
                }
                $specs['image_url'] = $imageSrc;
                echo "  ✓ Found real product image\n";
            } else {
                // Try og:image meta tag
                $ogImageNodes = $xpath->query('//meta[@property="og:image"]/@content');
                if ($ogImageNodes->length > 0) {
                    $specs['image_url'] = $ogImageNodes->item(0)->nodeValue;
                    echo "  ✓ Found OG image\n";
                }
            }
            
            // Extract ALL specifications from data attributes
            $specNodes = $xpath->query('//*[@data-spec-name and @data-spec-value]');
            echo "  Found {$specNodes->length} spec nodes with data attributes\n";
            
            foreach ($specNodes as $node) {
                $specName = $node->getAttribute('data-spec-name');
                $specValue = $node->getAttribute('data-spec-value');
                
                if (empty($specName) || empty($specValue)) {
                    continue;
                }
                
                $normalizedKey = $this->normalizeSpecKey($specName);
                $specs[$normalizedKey] = $specValue;
            }
            
            // Extract specs from structured data (li elements with class "specs__item")
            $specItems = $xpath->query('//li[contains(@class, "specs__item")]');
            echo "  Found {$specItems->length} spec items\n";
            
            foreach ($specItems as $item) {
                $labelNode = $xpath->query('.//span[contains(@class, "specs__label")]', $item);
                $valueNode = $xpath->query('.//span[contains(@class, "specs__value")]', $item);
                
                if ($labelNode->length > 0 && $valueNode->length > 0) {
                    $label = trim($labelNode->item(0)->textContent);
                    $value = trim($valueNode->item(0)->textContent);
                    
                    if (!empty($label) && !empty($value)) {
                        $normalizedKey = $this->normalizeSpecKey($label);
                        $specs[$normalizedKey] = $value;
                    }
                }
            }
            
            // Extract specs from table rows
            $tableRows = $xpath->query('//table[@class="specs-table"]//tr | //div[contains(@class, "specs-table")]//tr');
            foreach ($tableRows as $row) {
                $cells = $xpath->query('.//td', $row);
                if ($cells->length >= 2) {
                    $label = trim($cells->item(0)->textContent);
                    $value = trim($cells->item(1)->textContent);
                    
                    if (!empty($label) && !empty($value)) {
                        $normalizedKey = $this->normalizeSpecKey($label);
                        $specs[$normalizedKey] = $value;
                    }
                }
            }
            
            // Extract description
            $descNodes = $xpath->query('//meta[@name="description"]/@content');
            if ($descNodes->length > 0) {
                $specs['description'] = $descNodes->item(0)->nodeValue;
            }
            
            // Extract score
            $scoreNodes = $xpath->query('//*[@data-score]');
            if ($scoreNodes->length > 0) {
                $specs['score'] = (int)$scoreNodes->item(0)->getAttribute('data-score');
            }
            
            // Extract price if available
            $priceNodes = $xpath->query('//span[contains(@class, "price")]');
            if ($priceNodes->length > 0) {
                $priceText = trim($priceNodes->item(0)->textContent);
                if (preg_match('/[\d,\.]+/', $priceText, $matches)) {
                    $specs['price'] = (float)str_replace(',', '', $matches[0]);
                }
            }
            
            echo "  ✓ Extracted " . count($specs) . " specifications\n";
            
            return $specs;
            
        } catch (\Exception $e) {
            Log::error("Error scraping watch specs from {$url}: " . $e->getMessage());
            echo "  ✗ Error: " . $e->getMessage() . "\n";
            return [];
        }
    }

    protected function normalizeSpecKey($label)
    {
        $label = strtolower(trim($label));
        
        // Comprehensive mapping for ALL Versus.com spec labels
        $mapping = [
            // Display
            'screen size' => 'screen_size',
            'display size' => 'screen_size',
            'screen diagonal' => 'screen_size',
            'resolution' => 'resolution_width',
            'screen resolution' => 'resolution_width',
            'pixel density' => 'pixel_density',
            'ppi' => 'pixel_density',
            'display type' => 'display_type',
            'screen type' => 'display_type',
            'amoled' => 'display_type',
            'oled' => 'display_type',
            'always on display' => 'always_on_display',
            'always-on display' => 'always_on_display',
            'aod' => 'always_on_display',
            'touchscreen' => 'touchscreen',
            'touch screen' => 'touchscreen',
            'brightness' => 'brightness',
            'max brightness' => 'brightness',
            'nits' => 'brightness',
            
            // Design & Build
            'weight' => 'weight',
            'thickness' => 'thickness',
            'height' => 'thickness',
            'water resistance' => 'water_resistance',
            'waterproof' => 'water_resistance',
            'ip rating' => 'water_resistance',
            'case size' => 'case_size',
            'case diameter' => 'case_size',
            'watch size' => 'case_size',
            'strap width' => 'strap_width',
            'band width' => 'strap_width',
            
            // Performance
            'ram' => 'ram',
            'memory' => 'ram',
            'storage' => 'internal_storage',
            'internal storage' => 'internal_storage',
            'chipset' => 'chipset',
            'processor' => 'chipset',
            'soc' => 'chipset',
            'cpu' => 'chipset',
            'cpu cores' => 'cpu_cores',
            'cores' => 'cpu_cores',
            
            // Battery
            'battery' => 'battery_capacity',
            'battery capacity' => 'battery_capacity',
            'mah' => 'battery_capacity',
            'battery life' => 'battery_life',
            'battery duration' => 'battery_life',
            'autonomy' => 'battery_life',
            'charging' => 'charging_speed',
            'charging speed' => 'charging_speed',
            'fast charging' => 'charging_speed',
            'wireless charging' => 'wireless_charging',
            
            // Health Sensors
            'heart rate' => 'heart_rate_monitor',
            'heart rate monitor' => 'heart_rate_monitor',
            'hrm' => 'heart_rate_monitor',
            'spo2' => 'blood_oxygen',
            'blood oxygen' => 'blood_oxygen',
            'oxygen saturation' => 'blood_oxygen',
            'ecg' => 'ecg',
            'electrocardiogram' => 'ecg',
            'blood pressure' => 'blood_pressure',
            'bp monitor' => 'blood_pressure',
            'body temperature' => 'body_temperature',
            'temperature sensor' => 'body_temperature',
            'skin temperature' => 'body_temperature',
            
            // Motion Sensors
            'accelerometer' => 'accelerometer',
            'gyroscope' => 'gyroscope',
            'gyro' => 'gyroscope',
            'barometer' => 'barometer',
            'altimeter' => 'barometer',
            'compass' => 'compass',
            'magnetometer' => 'compass',
            
            // GPS & Location
            'gps' => 'gps',
            'glonass' => 'glonass',
            'galileo' => 'gps',
            'beidou' => 'gps',
            
            // Health Features
            'sleep tracking' => 'sleep_tracking',
            'sleep monitor' => 'sleep_tracking',
            'stress tracking' => 'stress_tracking',
            'stress monitor' => 'stress_tracking',
            'menstrual cycle' => 'menstrual_cycle',
            'period tracking' => 'menstrual_cycle',
            'vo2 max' => 'vo2_max',
            'vo2max' => 'vo2_max',
            'workout modes' => 'workout_modes',
            'sports modes' => 'workout_modes',
            'exercise modes' => 'workout_modes',
            'swimming' => 'swimming_tracking',
            'swim tracking' => 'swimming_tracking',
            
            // Audio & Music
            'music storage' => 'music_storage',
            'music playback' => 'music_playback',
            'music player' => 'music_playback',
            'speaker' => 'music_playback',
            'microphone' => 'voice_assistant',
            
            // Smart Features
            'voice assistant' => 'voice_assistant',
            'siri' => 'voice_assistant',
            'google assistant' => 'voice_assistant',
            'alexa' => 'voice_assistant',
            'nfc' => 'nfc',
            'mobile payments' => 'mobile_payments',
            'contactless payments' => 'mobile_payments',
            'apple pay' => 'mobile_payments',
            'google pay' => 'mobile_payments',
            'samsung pay' => 'mobile_payments',
            
            // Connectivity
            'bluetooth' => 'bluetooth_version',
            'bluetooth version' => 'bluetooth_version',
            'bt' => 'bluetooth_version',
            'wifi' => 'wifi',
            'wi-fi' => 'wifi',
            'lte' => 'lte',
            '4g' => 'lte',
            'cellular' => 'lte',
            'esim' => 'esim',
            'e-sim' => 'esim',
            
            // OS & Compatibility
            'os' => 'os',
            'operating system' => 'os',
            'watchos' => 'os',
            'wear os' => 'os',
            'tizen' => 'os',
            'os version' => 'os_version',
            'compatible' => 'compatible_os',
            'compatibility' => 'compatible_os',
            'works with' => 'compatible_os',
            
            // Other
            'release date' => 'release_date',
            'launch date' => 'release_date',
            'price' => 'price',
            'msrp' => 'price',
        ];
        
        // Find best match
        foreach ($mapping as $key => $value) {
            if (strpos($label, $key) !== false) {
                return $value;
            }
        }
        
        // Fallback: create slug from label
        return str_replace([' ', '-', '/', '(', ')', '.', ','], '_', $label);
    }

    protected function saveWatchSpecifications($product, $specs)
    {
        foreach ($this->specKeys as $specKey => $specKeyModel) {
            $value = $specs[$specKey] ?? null;
            
            if ($value === null || $value === '') {
                continue;
            }
            
            // Determine value type and column
            $valueData = [
                'value_string' => null,
                'value_number' => null,
                'value_bool' => null,
            ];
            
            if (is_bool($value) || in_array(strtolower($value), ['yes', 'no', 'da', 'nu', 'true', 'false'])) {
                $valueData['value_bool'] = in_array(strtolower($value), ['yes', 'da', 'true', '1', true]);
            } elseif (is_numeric($value)) {
                $valueData['value_number'] = (float)$value;
            } elseif (preg_match('/^([\d\.]+)\s*(.*)$/', $value, $matches)) {
                // Extract numeric value with unit (e.g., "1.9 inch", "500 mAh")
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
                    'spec_key_id' => $specKeyModel->id,
                ],
                $valueData
            );
        }
    }

    protected function createOffer($product, $price = null)
    {
        Offer::updateOrCreate(
            [
                'product_id' => $product->id,
                'merchant' => 'Versus.com',
            ],
            [
                'price' => $price ?? 1999.00,
                'currency' => 'RON',
                'url' => 'https://versus.com/en/' . Str::slug($product->name),
                'url_affiliate' => 'https://versus.com/en/' . Str::slug($product->name),
                'in_stock' => true,
            ]
        );
    }

    protected function generatePlaceholderImage($watchName)
    {
        return "https://ui-avatars.com/api/?name=" . urlencode($watchName) . "&size=400&background=random";
    }
}
