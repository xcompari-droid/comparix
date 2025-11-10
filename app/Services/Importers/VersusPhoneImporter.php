<?php

namespace App\Services\Importers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\SpecKey;
use App\Models\SpecValue;
use App\Models\Offer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VersusPhoneImporter
{
    protected $category;
    protected $productType;
    protected $specKeys = [];
    protected $baseUrl = 'https://versus.com';

    public function __construct()
    {
        $this->category = Category::firstOrCreate(
            ['slug' => 'telefoane'],
            ['name' => 'Telefoane', 'description' => 'Smartphones și telefoane mobile']
        );

        $this->productType = ProductType::firstOrCreate(
            ['slug' => 'smartphone'],
            ['name' => 'Smartphone', 'category_id' => $this->category->id]
        );

        $this->initializeSpecKeys();
    }

    protected function initializeSpecKeys()
    {
        $specs = [
            // Design
            'weight' => ['name' => 'Greutate', 'unit' => 'g'],
            'thickness' => ['name' => 'Grosime', 'unit' => 'mm'],
            'width' => ['name' => 'Lățime', 'unit' => 'mm'],
            'height' => ['name' => 'Înălțime', 'unit' => 'mm'],
            'water_resistance' => ['name' => 'Rezistență la apă', 'unit' => 'IP'],
            
            // Display
            'screen_size' => ['name' => 'Diagonală ecran', 'unit' => 'inch'],
            'resolution_width' => ['name' => 'Rezoluție lățime', 'unit' => 'px'],
            'resolution_height' => ['name' => 'Rezoluție înălțime', 'unit' => 'px'],
            'pixel_density' => ['name' => 'Densitate pixeli', 'unit' => 'ppi'],
            'refresh_rate' => ['name' => 'Rată refresh', 'unit' => 'Hz'],
            'display_type' => ['name' => 'Tip display', 'unit' => ''],
            'touch_sampling_rate' => ['name' => 'Touch sampling rate', 'unit' => 'Hz'],
            'brightness' => ['name' => 'Luminozitate', 'unit' => 'nits'],
            
            // Performance
            'ram' => ['name' => 'RAM', 'unit' => 'GB'],
            'internal_storage' => ['name' => 'Stocare internă', 'unit' => 'GB'],
            'chipset' => ['name' => 'Chipset', 'unit' => ''],
            'gpu' => ['name' => 'GPU', 'unit' => ''],
            'antutu_score' => ['name' => 'AnTuTu Score', 'unit' => 'puncte'],
            'cpu_cores' => ['name' => 'Nuclee CPU', 'unit' => ''],
            'cpu_frequency' => ['name' => 'Frecvență CPU', 'unit' => 'GHz'],
            
            // Camera
            'main_camera_mp' => ['name' => 'Camera principală', 'unit' => 'MP'],
            'main_camera_aperture' => ['name' => 'Apertură camera principală', 'unit' => 'f'],
            'front_camera_mp' => ['name' => 'Camera frontală', 'unit' => 'MP'],
            'video_recording' => ['name' => 'Înregistrare video', 'unit' => ''],
            'optical_image_stabilization' => ['name' => 'OIS', 'unit' => ''],
            'telephoto_lens' => ['name' => 'Teleobiectiv', 'unit' => ''],
            'ultra_wide_lens' => ['name' => 'Ultra wide', 'unit' => ''],
            'macro_lens' => ['name' => 'Macro', 'unit' => ''],
            
            // Battery
            'battery_capacity' => ['name' => 'Capacitate baterie', 'unit' => 'mAh'],
            'charging_speed' => ['name' => 'Viteză încărcare', 'unit' => 'W'],
            'wireless_charging' => ['name' => 'Încărcare wireless', 'unit' => ''],
            'wireless_charging_speed' => ['name' => 'Viteză wireless', 'unit' => 'W'],
            'fast_charging' => ['name' => 'Încărcare rapidă', 'unit' => ''],
            'reverse_charging' => ['name' => 'Încărcare inversă', 'unit' => ''],
            
            // Operating System
            'os_version' => ['name' => 'Versiune OS', 'unit' => ''],
            'android_version' => ['name' => 'Versiune Android', 'unit' => ''],
            
            // Audio
            'headphone_jack' => ['name' => 'Jack 3.5mm', 'unit' => ''],
            'stereo_speakers' => ['name' => 'Boxe stereo', 'unit' => ''],
            'aptx' => ['name' => 'aptX', 'unit' => ''],
            'ldac' => ['name' => 'LDAC', 'unit' => ''],
            
            // Connectivity
            '5g_support' => ['name' => 'Suport 5G', 'unit' => ''],
            'wifi_version' => ['name' => 'Versiune WiFi', 'unit' => ''],
            'bluetooth_version' => ['name' => 'Versiune Bluetooth', 'unit' => ''],
            'nfc' => ['name' => 'NFC', 'unit' => ''],
            'usb_type' => ['name' => 'Tip USB', 'unit' => ''],
            'sim_cards' => ['name' => 'Cartele SIM', 'unit' => ''],
            
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
            Log::info("Starting phone import from Versus.com");
            
            // Scrape phone list from versus.com
            $phones = $this->scrapePhoneList($limit);
            
            Log::info("Found " . count($phones) . " phones to import");
            
            $imported = 0;
            foreach ($phones as $phoneData) {
                try {
                    $this->importPhone($phoneData);
                    $imported++;
                    echo "✓ Imported: {$phoneData['name']}\n";
                    sleep(2); // Be respectful to the server
                } catch (\Exception $e) {
                    echo "✗ Error importing {$phoneData['name']}: {$e->getMessage()}\n";
                    Log::error("Error importing phone {$phoneData['name']}: " . $e->getMessage());
                }
            }
            
            Log::info("Phone import completed. Imported {$imported} phones");
            
        } catch (\Exception $e) {
            Log::error("Error in phone import: " . $e->getMessage());
            throw $e;
        }
    }

    protected function scrapePhoneList($limit)
    {
        $phones = [];
        $url = 'https://versus.com/en/phone';
        
        try {
            echo "Fetching phone list from {$url}...\n";
            
            $response = Http::timeout(30)
                ->withoutVerifying() // Disable SSL verification for development
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                ])
                ->get($url);
            
            if (!$response->successful()) {
                throw new \Exception("Failed to fetch phone list: " . $response->status());
            }
            
            $html = $response->body();
            
            // Extract phone links using regex (more reliable than DOM parsing for this case)
            // Pattern: href="https://versus.com/en/phone-name-here" or href="/en/phone-name-here"
            preg_match_all('#href="(?:https://versus\.com)?/en/([a-z0-9-]+)"[^>]*>([^<]*(?:<[^>]*>)*)*([A-Z][^\n]*?)(?:\s|<)#i', $html, $matches, PREG_SET_ORDER);
            
            $seenSlugs = [];
            
            foreach ($matches as $match) {
                $slug = $match[1];
                
                // Skip non-phone pages
                $excludePatterns = ['phone', 'categories', 'news', 'glossary', 'about-us', 'privacy-policy', 'terms-and-conditions', 'suggest-product', 'partnerships', 'editorial-guidelines', 'impressum', 'vs'];
                $isExcluded = false;
                foreach ($excludePatterns as $pattern) {
                    if ($slug === $pattern || strpos($slug, $pattern) === 0) {
                        $isExcluded = true;
                        break;
                    }
                }
                
                // Must contain at least one brand or phone-like pattern
                $phoneBrands = ['samsung', 'apple', 'iphone', 'xiaomi', 'oppo', 'vivo', 'realme', 'oneplus', 'google', 'pixel', 'motorola', 'honor', 'huawei', 'nokia', 'sony', 'zte', 'nothing', 'asus', 'lenovo', 'lg', 'htc', 'blackberry', 'meizu', 'itel', 'infinix', 'tecno', 'doogee', 'ulefone', 'oukitel', 'blackview', 'cubot', 'umidigi', 'elephone', 'leagoo', 'homtom', 'bluboo', 'vernee', 'letv', 'oneplus', 'tcl', 'lava', 'hmd'];
                $hasPhoneBrand = false;
                foreach ($phoneBrands as $brand) {
                    if (strpos($slug, $brand) !== false) {
                        $hasPhoneBrand = true;
                        break;
                    }
                }
                
                if ($isExcluded || !$hasPhoneBrand || isset($seenSlugs[$slug])) {
                    continue;
                }
                
                $seenSlugs[$slug] = true;
                
                // Generate name from slug
                $name = ucwords(str_replace('-', ' ', $slug));
                
                $phones[] = [
                    'name' => $name,
                    'slug' => $slug,
                    'url' => $this->baseUrl . '/en/' . $slug
                ];
                
                if (count($phones) >= $limit) {
                    break;
                }
            }
            
            if (count($phones) === 0) {
                throw new \Exception("No phones found on page");
            }
            
            echo "✓ Found " . count($phones) . " phones\n";
            return $phones;
            
        } catch (\Exception $e) {
            Log::error("Error scraping phone list: " . $e->getMessage());
            echo "✗ Error scraping phone list, using fallback list\n";
            return $this->getFallbackPhoneList($limit);
        }
    }

    protected function getFallbackPhoneList($limit)
    {
        // Comprehensive fallback list of popular phones
        return array_slice([
            // Samsung Galaxy S Series
            ['name' => 'Samsung Galaxy S24 Ultra', 'slug' => 'samsung-galaxy-s24-ultra', 'url' => 'https://versus.com/en/samsung-galaxy-s24-ultra'],
            ['name' => 'Samsung Galaxy S24 Plus', 'slug' => 'samsung-galaxy-s24-plus', 'url' => 'https://versus.com/en/samsung-galaxy-s24-plus'],
            ['name' => 'Samsung Galaxy S24', 'slug' => 'samsung-galaxy-s24', 'url' => 'https://versus.com/en/samsung-galaxy-s24'],
            ['name' => 'Samsung Galaxy S23 Ultra', 'slug' => 'samsung-galaxy-s23-ultra', 'url' => 'https://versus.com/en/samsung-galaxy-s23-ultra'],
            ['name' => 'Samsung Galaxy S23 Plus', 'slug' => 'samsung-galaxy-s23-plus', 'url' => 'https://versus.com/en/samsung-galaxy-s23-plus'],
            ['name' => 'Samsung Galaxy S23', 'slug' => 'samsung-galaxy-s23', 'url' => 'https://versus.com/en/samsung-galaxy-s23'],
            ['name' => 'Samsung Galaxy S25 FE', 'slug' => 'samsung-galaxy-s25-fe', 'url' => 'https://versus.com/en/samsung-galaxy-s25-fe'],
            
            // Samsung Galaxy A Series
            ['name' => 'Samsung Galaxy A55 5G', 'slug' => 'samsung-galaxy-a55-5g', 'url' => 'https://versus.com/en/samsung-galaxy-a55-5g'],
            ['name' => 'Samsung Galaxy A35 5G', 'slug' => 'samsung-galaxy-a35-5g', 'url' => 'https://versus.com/en/samsung-galaxy-a35-5g'],
            ['name' => 'Samsung Galaxy A25 5G', 'slug' => 'samsung-galaxy-a25-5g', 'url' => 'https://versus.com/en/samsung-galaxy-a25-5g'],
            ['name' => 'Samsung Galaxy A16 5G', 'slug' => 'samsung-galaxy-a16-5g', 'url' => 'https://versus.com/en/samsung-galaxy-a16-5g'],
            ['name' => 'Samsung Galaxy A17 4G', 'slug' => 'samsung-galaxy-a17-4g', 'url' => 'https://versus.com/en/samsung-galaxy-a17-4g'],
            
            // iPhone
            ['name' => 'Apple iPhone 15 Pro Max', 'slug' => 'apple-iphone-15-pro-max', 'url' => 'https://versus.com/en/apple-iphone-15-pro-max'],
            ['name' => 'Apple iPhone 15 Pro', 'slug' => 'apple-iphone-15-pro', 'url' => 'https://versus.com/en/apple-iphone-15-pro'],
            ['name' => 'Apple iPhone 15 Plus', 'slug' => 'apple-iphone-15-plus', 'url' => 'https://versus.com/en/apple-iphone-15-plus'],
            ['name' => 'Apple iPhone 15', 'slug' => 'apple-iphone-15', 'url' => 'https://versus.com/en/apple-iphone-15'],
            ['name' => 'Apple iPhone 16 Pro Max', 'slug' => 'apple-iphone-16-pro-max', 'url' => 'https://versus.com/en/apple-iphone-16-pro-max'],
            ['name' => 'Apple iPhone 16 Pro', 'slug' => 'apple-iphone-16-pro', 'url' => 'https://versus.com/en/apple-iphone-16-pro'],
            ['name' => 'Apple iPhone 16 Plus', 'slug' => 'apple-iphone-16-plus', 'url' => 'https://versus.com/en/apple-iphone-16-plus'],
            ['name' => 'Apple iPhone 16', 'slug' => 'apple-iphone-16', 'url' => 'https://versus.com/en/apple-iphone-16'],
            ['name' => 'Apple iPhone 17 Pro Max', 'slug' => 'apple-iphone-17-pro-max', 'url' => 'https://versus.com/en/apple-iphone-17-pro-max'],
            ['name' => 'Apple iPhone 17 Pro', 'slug' => 'apple-iphone-17-pro', 'url' => 'https://versus.com/en/apple-iphone-17-pro'],
            ['name' => 'Apple iPhone 17', 'slug' => 'apple-iphone-17', 'url' => 'https://versus.com/en/apple-iphone-17'],
            ['name' => 'Apple iPhone Air', 'slug' => 'apple-iphone-air', 'url' => 'https://versus.com/en/apple-iphone-air'],
            
            // Google Pixel
            ['name' => 'Google Pixel 8 Pro', 'slug' => 'google-pixel-8-pro', 'url' => 'https://versus.com/en/google-pixel-8-pro'],
            ['name' => 'Google Pixel 8', 'slug' => 'google-pixel-8', 'url' => 'https://versus.com/en/google-pixel-8'],
            ['name' => 'Google Pixel 8a', 'slug' => 'google-pixel-8a', 'url' => 'https://versus.com/en/google-pixel-8a'],
            ['name' => 'Google Pixel 9 Pro XL', 'slug' => 'google-pixel-9-pro-xl', 'url' => 'https://versus.com/en/google-pixel-9-pro-xl'],
            ['name' => 'Google Pixel 9 Pro', 'slug' => 'google-pixel-9-pro', 'url' => 'https://versus.com/en/google-pixel-9-pro'],
            ['name' => 'Google Pixel 9', 'slug' => 'google-pixel-9', 'url' => 'https://versus.com/en/google-pixel-9'],
            ['name' => 'Google Pixel 10 Pro Fold', 'slug' => 'google-pixel-10-pro-fold', 'url' => 'https://versus.com/en/google-pixel-10-pro-fold'],
            
            // Xiaomi
            ['name' => 'Xiaomi 14 Pro', 'slug' => 'xiaomi-14-pro', 'url' => 'https://versus.com/en/xiaomi-14-pro'],
            ['name' => 'Xiaomi 14', 'slug' => 'xiaomi-14', 'url' => 'https://versus.com/en/xiaomi-14'],
            ['name' => 'Xiaomi 14 Ultra', 'slug' => 'xiaomi-14-ultra', 'url' => 'https://versus.com/en/xiaomi-14-ultra'],
            ['name' => 'Xiaomi 15T Pro', 'slug' => 'xiaomi-15t-pro', 'url' => 'https://versus.com/en/xiaomi-15t-pro'],
            ['name' => 'Xiaomi 15T', 'slug' => 'xiaomi-15t', 'url' => 'https://versus.com/en/xiaomi-15t'],
            ['name' => 'Xiaomi 17 Pro Max', 'slug' => 'xiaomi-17-pro-max', 'url' => 'https://versus.com/en/xiaomi-17-pro-max'],
            ['name' => 'Xiaomi 17 Pro', 'slug' => 'xiaomi-17-pro', 'url' => 'https://versus.com/en/xiaomi-17-pro'],
            ['name' => 'Xiaomi 17', 'slug' => 'xiaomi-17', 'url' => 'https://versus.com/en/xiaomi-17'],
            ['name' => 'Xiaomi Redmi Note 15 Pro Plus 5G', 'slug' => 'xiaomi-redmi-note-15-pro-plus-5g', 'url' => 'https://versus.com/en/xiaomi-redmi-note-15-pro-plus-5g'],
            ['name' => 'Xiaomi Redmi Note 15 Pro 5G', 'slug' => 'xiaomi-redmi-note-15-pro-5g', 'url' => 'https://versus.com/en/xiaomi-redmi-note-15-pro-5g'],
            ['name' => 'Xiaomi Redmi Note 15 5G', 'slug' => 'xiaomi-redmi-note-15-5g', 'url' => 'https://versus.com/en/xiaomi-redmi-note-15-5g'],
            ['name' => 'Xiaomi Redmi Note 14 Pro Plus 5G', 'slug' => 'xiaomi-redmi-note-14-pro-plus-5g', 'url' => 'https://versus.com/en/xiaomi-redmi-note-14-pro-plus-5g'],
            ['name' => 'Xiaomi Redmi Note 14 Pro 5G', 'slug' => 'xiaomi-redmi-note-14-pro-5g', 'url' => 'https://versus.com/en/xiaomi-redmi-note-14-pro-5g'],
            ['name' => 'Xiaomi Poco C85 4G', 'slug' => 'xiaomi-poco-c85-4g', 'url' => 'https://versus.com/en/xiaomi-poco-c85-4g'],
            
            // OnePlus
            ['name' => 'OnePlus 12', 'slug' => 'oneplus-12', 'url' => 'https://versus.com/en/oneplus-12'],
            ['name' => 'OnePlus 12R', 'slug' => 'oneplus-12r', 'url' => 'https://versus.com/en/oneplus-12r'],
            ['name' => 'OnePlus 11', 'slug' => 'oneplus-11', 'url' => 'https://versus.com/en/oneplus-11'],
            ['name' => 'OnePlus 15', 'slug' => 'oneplus-15', 'url' => 'https://versus.com/en/oneplus-15'],
            ['name' => 'OnePlus Ace 6', 'slug' => 'oneplus-ace-6', 'url' => 'https://versus.com/en/oneplus-ace-6'],
            ['name' => 'OnePlus Nord 4', 'slug' => 'oneplus-nord-4', 'url' => 'https://versus.com/en/oneplus-nord-4'],
            
            // Oppo
            ['name' => 'Oppo Find X9 Pro', 'slug' => 'oppo-find-x9-pro', 'url' => 'https://versus.com/en/oppo-find-x9-pro'],
            ['name' => 'Oppo Find X9', 'slug' => 'oppo-find-x9', 'url' => 'https://versus.com/en/oppo-find-x9'],
            ['name' => 'Oppo Find X7 Ultra', 'slug' => 'oppo-find-x7-ultra', 'url' => 'https://versus.com/en/oppo-find-x7-ultra'],
            ['name' => 'Oppo A6 Pro 5G', 'slug' => 'oppo-a6-pro-5g', 'url' => 'https://versus.com/en/oppo-a6-pro-5g'],
            ['name' => 'Oppo A6 Max', 'slug' => 'oppo-a6-max', 'url' => 'https://versus.com/en/oppo-a6-max'],
            ['name' => 'Oppo F31 Pro 5G', 'slug' => 'oppo-f31-pro-5g', 'url' => 'https://versus.com/en/oppo-f31-pro-5g'],
            
            // Vivo
            ['name' => 'Vivo X300 Pro', 'slug' => 'vivo-x300-pro', 'url' => 'https://versus.com/en/vivo-x300-pro'],
            ['name' => 'Vivo X300', 'slug' => 'vivo-x300', 'url' => 'https://versus.com/en/vivo-x300'],
            ['name' => 'Vivo iQOO 15', 'slug' => 'vivo-iqoo-15', 'url' => 'https://versus.com/en/vivo-iqoo-15'],
            ['name' => 'Vivo V60e', 'slug' => 'vivo-v60e', 'url' => 'https://versus.com/en/vivo-v60e'],
            ['name' => 'Vivo T4 Pro', 'slug' => 'vivo-t4-pro', 'url' => 'https://versus.com/en/vivo-t4-pro'],
            
            // Motorola
            ['name' => 'Motorola Moto G57', 'slug' => 'motorola-moto-g57', 'url' => 'https://versus.com/en/motorola-moto-g57'],
            ['name' => 'Motorola Moto G57 Power', 'slug' => 'motorola-moto-g57-power', 'url' => 'https://versus.com/en/motorola-moto-g57-power'],
            ['name' => 'Motorola Edge 70', 'slug' => 'motorola-edge-70', 'url' => 'https://versus.com/en/motorola-edge-70'],
            ['name' => 'Motorola Edge 60 Neo', 'slug' => 'motorola-edge-60-neo', 'url' => 'https://versus.com/en/motorola-edge-60-neo'],
            ['name' => 'Motorola Moto X70 Air', 'slug' => 'motorola-moto-x70-air', 'url' => 'https://versus.com/en/motorola-moto-x70-air'],
            
            // Honor
            ['name' => 'Honor Magic 8 Pro', 'slug' => 'honor-magic-8-pro', 'url' => 'https://versus.com/en/honor-magic-8-pro'],
            ['name' => 'Honor Magic V Flip 2', 'slug' => 'honor-magic-v-flip-2', 'url' => 'https://versus.com/en/honor-magic-v-flip-2'],
            ['name' => 'Honor X9d 5G', 'slug' => 'honor-x9d-5g', 'url' => 'https://versus.com/en/honor-x9d-5g'],
            ['name' => 'Honor 400 Smart 5G', 'slug' => 'honor-400-smart-5g', 'url' => 'https://versus.com/en/honor-400-smart-5g'],
            
            // Realme
            ['name' => 'Realme GT8 Pro (China)', 'slug' => 'realme-gt8-pro-china', 'url' => 'https://versus.com/en/realme-gt8-pro-china'],
            ['name' => 'Realme GT8 (China)', 'slug' => 'realme-gt8-china', 'url' => 'https://versus.com/en/realme-gt8-china'],
            ['name' => 'Realme P4 5G', 'slug' => 'realme-p4-5g', 'url' => 'https://versus.com/en/realme-p4-5g'],
            ['name' => 'Realme 15T 5G', 'slug' => 'realme-15t-5g', 'url' => 'https://versus.com/en/realme-15t-5g'],
            ['name' => 'Realme C85 Pro', 'slug' => 'realme-c85-pro', 'url' => 'https://versus.com/en/realme-c85-pro'],
            
            // Nothing
            ['name' => 'Nothing Phone (3a) Lite', 'slug' => 'nothing-phone-3a-lite', 'url' => 'https://versus.com/en/nothing-phone-3a-lite'],
            ['name' => 'Nothing Phone (2a)', 'slug' => 'nothing-phone-2a', 'url' => 'https://versus.com/en/nothing-phone-2a'],
            ['name' => 'Nothing Phone (2)', 'slug' => 'nothing-phone-2', 'url' => 'https://versus.com/en/nothing-phone-2'],
            
            // ZTE
            ['name' => 'ZTE Nubia Red Magic 11 Pro', 'slug' => 'zte-nubia-red-magic-11-pro', 'url' => 'https://versus.com/en/zte-nubia-red-magic-11-pro'],
            ['name' => 'ZTE Nubia Z80 Ultra', 'slug' => 'zte-nubia-z80-ultra', 'url' => 'https://versus.com/en/zte-nubia-z80-ultra'],
            ['name' => 'ZTE Nubia Air', 'slug' => 'zte-nubia-air', 'url' => 'https://versus.com/en/zte-nubia-air'],
            
            // Sony
            ['name' => 'Sony Xperia 10 VII', 'slug' => 'sony-xperia-10-vii', 'url' => 'https://versus.com/en/sony-xperia-10-vii'],
            ['name' => 'Sony Xperia 1 VI', 'slug' => 'sony-xperia-1-vi', 'url' => 'https://versus.com/en/sony-xperia-1-vi'],
            ['name' => 'Sony Xperia 5 VI', 'slug' => 'sony-xperia-5-vi', 'url' => 'https://versus.com/en/sony-xperia-5-vi'],
            
            // Huawei
            ['name' => 'Huawei Mate 70 Air', 'slug' => 'huawei-mate-70-air', 'url' => 'https://versus.com/en/huawei-mate-70-air'],
            ['name' => 'Huawei Nova Flip S', 'slug' => 'huawei-nova-flip-s', 'url' => 'https://versus.com/en/huawei-nova-flip-s'],
            ['name' => 'Huawei Nova 14i', 'slug' => 'huawei-nova-14i', 'url' => 'https://versus.com/en/huawei-nova-14i'],
            
            // Others
            ['name' => 'Meizu 22', 'slug' => 'meizu-22', 'url' => 'https://versus.com/en/meizu-22'],
            ['name' => 'TCL NxtPaper 60 Ultra', 'slug' => 'tcl-nxtpaper-60-ultra', 'url' => 'https://versus.com/en/tcl-nxtpaper-60-ultra'],
        ], 0, $limit);
    }

    protected function getPhoneList($limit)
    {
        // For now, return a predefined list of popular phones
        // In production, you would scrape the versus.com page
        return [
            [
                'name' => 'Samsung Galaxy S24 Ultra',
                'url' => 'https://versus.com/en/samsung-galaxy-s24-ultra',
                'slug' => 'samsung-galaxy-s24-ultra'
            ],
            [
                'name' => 'iPhone 15 Pro Max',
                'url' => 'https://versus.com/en/apple-iphone-15-pro-max',
                'slug' => 'apple-iphone-15-pro-max'
            ],
            [
                'name' => 'Google Pixel 8 Pro',
                'url' => 'https://versus.com/en/google-pixel-8-pro',
                'slug' => 'google-pixel-8-pro'
            ],
            [
                'name' => 'OnePlus 12',
                'url' => 'https://versus.com/en/oneplus-12',
                'slug' => 'oneplus-12'
            ],
            [
                'name' => 'Xiaomi 14 Pro',
                'url' => 'https://versus.com/en/xiaomi-14-pro',
                'slug' => 'xiaomi-14-pro'
            ],
        ];
    }

    protected function importPhone($phoneData)
    {
        // Scrape detailed specifications from the phone's page
        $specs = $this->scrapePhoneSpecs($phoneData['url']);
        
        // Extract brand from phone name
        $brand = explode(' ', $phoneData['name'])[0];
        
        // Disable Scout indexing during import
        Product::withoutSyncingToSearch(function () use ($phoneData, $brand, $specs) {
            // Create or update product
            $product = Product::updateOrCreate(
                [
                    'product_type_id' => $this->productType->id,
                    'brand' => $brand,
                    'name' => $phoneData['name'],
                ],
                [
                    'category_id' => $this->category->id,
                    'mpn' => $phoneData['slug'],
                    'short_desc' => $specs['description'] ?? "Smartphone {$phoneData['name']}",
                    'image_url' => $specs['image_url'] ?? $this->generatePlaceholderImage($phoneData['name']),
                    'score' => $specs['score'] ?? 75,
                ]
            );

            // Save all specifications
            $this->savePhoneSpecifications($product, $specs);

            // Create offer
            $this->createOffer($product, $specs['price'] ?? null);

            return $product;
        });
    }

    protected function scrapePhoneSpecs($url)
    {
        try {
            echo "  Fetching specs from {$url}...\n";
            
            $response = Http::timeout(30)
                ->withoutVerifying() // Disable SSL verification for development
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                ])
                ->get($url);
            
            if (!$response->successful()) {
                throw new \Exception("Failed to fetch phone page: " . $response->status());
            }
            
            $html = $response->body();
            
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadHTML($html);
            libxml_clear_errors();
            
            $xpath = new \DOMXPath($dom);
            
            $specs = [];
            
            // Extract image
            $imageNodes = $xpath->query('//meta[@property="og:image"]/@content');
            if ($imageNodes->length > 0) {
                $specs['image_url'] = $imageNodes->item(0)->nodeValue;
            }
            
            // Extract description
            $descNodes = $xpath->query('//meta[@name="description"]/@content');
            if ($descNodes->length > 0) {
                $specs['description'] = $descNodes->item(0)->nodeValue;
            }
            
            // Extract specifications from data attributes (versus.com uses these)
            $dataSpecs = $xpath->query('//*[@data-spec-name]');
            foreach ($dataSpecs as $spec) {
                $name = $spec->getAttribute('data-spec-name');
                $value = $spec->getAttribute('data-spec-value') ?: trim($spec->textContent);
                if (!empty($name) && !empty($value)) {
                    $specs[$this->normalizeSpecKey($name)] = $value;
                }
            }
            
            // Extract price
            $priceNodes = $xpath->query('//span[contains(@class, "price")] | //*[@itemprop="price"]/@content');
            if ($priceNodes->length > 0) {
                $priceText = $priceNodes->item(0)->nodeValue;
                if (preg_match('/[\d,\.]+/', $priceText, $matches)) {
                    $specs['price'] = (float)str_replace(',', '', $matches[0]);
                }
            }
            
            // Extract score/rating
            $scoreNodes = $xpath->query('//*[contains(@class, "score")] | //*[@data-score]/@data-score');
            if ($scoreNodes->length > 0) {
                $scoreText = $scoreNodes->item(0)->nodeValue;
                if (preg_match('/[\d\.]+/', $scoreText, $matches)) {
                    $specs['score'] = (float)$matches[0];
                }
            }
            
            return $specs;
            
        } catch (\Exception $e) {
            Log::error("Error scraping phone specs from {$url}: " . $e->getMessage());
            return [];
        }
    }

    protected function normalizeSpecKey($label)
    {
        $label = strtolower(trim($label));
        
        $mapping = [
            'screen size' => 'screen_size',
            'display size' => 'screen_size',
            'diagonal' => 'screen_size',
            'ram' => 'ram',
            'memory' => 'ram',
            'storage' => 'internal_storage',
            'internal storage' => 'internal_storage',
            'battery' => 'battery_capacity',
            'battery capacity' => 'battery_capacity',
            'weight' => 'weight',
            'thickness' => 'thickness',
            'chipset' => 'chipset',
            'processor' => 'chipset',
            'cpu' => 'chipset',
            'gpu' => 'gpu',
            'main camera' => 'main_camera_mp',
            'rear camera' => 'main_camera_mp',
            'front camera' => 'front_camera_mp',
            'selfie camera' => 'front_camera_mp',
            'resolution' => 'resolution_width',
            'pixel density' => 'pixel_density',
            'refresh rate' => 'refresh_rate',
            '5g' => '5g_support',
            'water resistance' => 'water_resistance',
            'os' => 'os_version',
            'android' => 'android_version',
            'operating system' => 'os_version',
            'charging' => 'charging_speed',
            'fast charging' => 'charging_speed',
            'wireless charging' => 'wireless_charging',
            'headphone jack' => 'headphone_jack',
            'nfc' => 'nfc',
            'bluetooth' => 'bluetooth_version',
            'wifi' => 'wifi_version',
            'usb' => 'usb_type',
        ];
        
        foreach ($mapping as $key => $value) {
            if (strpos($label, $key) !== false) {
                return $value;
            }
        }
        
        return str_replace([' ', '-'], '_', $label);
    }

    protected function savePhoneSpecifications($product, $specs)
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
                // Extract numeric value with unit (e.g., "6.8 inch", "12 GB")
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

    protected function addSampleSpecifications($product, $phoneData)
    {
        $sampleSpecs = [
            'screen_size' => 6.8,
            'ram' => 12,
            'internal_storage' => 256,
            'battery_capacity' => 5000,
            'main_camera_mp' => 200,
            'front_camera_mp' => 12,
            'charging_speed' => 45,
            'refresh_rate' => 120,
            '5g_support' => 'Da',
            'headphone_jack' => 'Nu',
            'water_resistance' => 'IP68',
        ];

        foreach ($sampleSpecs as $key => $value) {
            if (!isset($this->specKeys[$key])) continue;

            SpecValue::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'spec_key_id' => $this->specKeys[$key]->id,
                ],
                [
                    'value_string' => is_numeric($value) ? null : $value,
                    'value_number' => is_numeric($value) ? $value : null,
                    'value_bool' => in_array(strtolower($value), ['da', 'yes', 'true']) ? 1 : 
                                   (in_array(strtolower($value), ['nu', 'no', 'false']) ? 0 : null),
                ]
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
                'price' => $price ?? 4999.00,
                'currency' => 'RON',
                'url' => 'https://versus.com/en/' . Str::slug($product->name),
                'url_affiliate' => 'https://versus.com/en/' . Str::slug($product->name),
                'in_stock' => true,
            ]
        );
    }

    protected function generatePlaceholderImage($phoneName)
    {
        $slug = Str::slug($phoneName);
        return "https://ui-avatars.com/api/?name=" . urlencode($phoneName) . "&size=400&background=random";
    }
}
