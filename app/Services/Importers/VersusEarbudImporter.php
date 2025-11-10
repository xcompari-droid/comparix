<?php

namespace App\Services\Importers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\SpecKey;
use App\Models\SpecValue;
use App\Models\Offer;
use App\Services\PuppeteerScraper;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Str;

class VersusEarbudImporter
{
    private $category;
    private $productType;
    private $specKeys = [];

    public function __construct()
    {
        // Create or get category
        $this->category = Category::firstOrCreate(
            ['name' => 'Căști wireless'],
            [
                'slug' => 'casti-wireless',
                'description' => 'Cele mai bune căști wireless și earbuds pentru muzică, sport și apeluri',
                'icon' => '🎧',
                'is_active' => true,
            ]
        );

        // Create or get product type
        $this->productType = ProductType::firstOrCreate(
            ['name' => 'Căști wireless'],
            [
                'slug' => 'casti-wireless',
                'category_id' => $this->category->id,
            ]
        );

        $this->initializeSpecKeys();
    }

    private function initializeSpecKeys()
    {
        $specs = [
            // Audio Quality
            'driver_size' => ['name' => 'Dimensiune driver', 'unit' => 'mm'],
            'frequency_response' => ['name' => 'Răspuns în frecvență', 'unit' => 'Hz'],
            'impedance' => ['name' => 'Impedanță', 'unit' => 'Ohm'],
            'sensitivity' => ['name' => 'Sensibilitate', 'unit' => 'dB'],
            'audio_codec' => ['name' => 'Codecuri audio', 'unit' => ''],
            'has_anc' => ['name' => 'Anulare zgomot activă (ANC)', 'unit' => ''],
            'anc_microphones' => ['name' => 'Microfoane ANC', 'unit' => ''],
            'has_transparency_mode' => ['name' => 'Mod transparență', 'unit' => ''],
            'spatial_audio' => ['name' => 'Audio spațial', 'unit' => ''],
            'sound_modes' => ['name' => 'Moduri de sunet', 'unit' => ''],
            
            // Battery Life
            'battery_life' => ['name' => 'Autonomie căști', 'unit' => 'ore'],
            'battery_case' => ['name' => 'Autonomie cu carcasă', 'unit' => 'ore'],
            'charging_time' => ['name' => 'Timp încărcare', 'unit' => 'minute'],
            'fast_charging' => ['name' => 'Încărcare rapidă', 'unit' => ''],
            'wireless_charging' => ['name' => 'Încărcare wireless', 'unit' => ''],
            'battery_capacity' => ['name' => 'Capacitate baterie', 'unit' => 'mAh'],
            'case_battery' => ['name' => 'Baterie carcasă', 'unit' => 'mAh'],
            
            // Design & Build
            'weight' => ['name' => 'Greutate (per earbud)', 'unit' => 'g'],
            'case_weight' => ['name' => 'Greutate carcasă', 'unit' => 'g'],
            'water_resistance' => ['name' => 'Rezistență la apă', 'unit' => ''],
            'dust_resistance' => ['name' => 'Rezistență la praf', 'unit' => ''],
            'form_factor' => ['name' => 'Tip formă', 'unit' => ''],
            'ear_tips' => ['name' => 'Tip căpăcele', 'unit' => ''],
            'colors' => ['name' => 'Culori disponibile', 'unit' => ''],
            'case_type' => ['name' => 'Tip carcasă', 'unit' => ''],
            
            // Connectivity
            'bluetooth_version' => ['name' => 'Versiune Bluetooth', 'unit' => ''],
            'bluetooth_range' => ['name' => 'Rază Bluetooth', 'unit' => 'm'],
            'multipoint' => ['name' => 'Conectare multipunct', 'unit' => ''],
            'nfc_pairing' => ['name' => 'Împerechere NFC', 'unit' => ''],
            'usb_type' => ['name' => 'Port încărcare', 'unit' => ''],
            
            // Features
            'touch_controls' => ['name' => 'Controale tactile', 'unit' => ''],
            'voice_assistant' => ['name' => 'Asistent vocal', 'unit' => ''],
            'find_my' => ['name' => 'Găsire căști', 'unit' => ''],
            'auto_pause' => ['name' => 'Pauză automată', 'unit' => ''],
            'ambient_sound' => ['name' => 'Sunet ambient', 'unit' => ''],
            'eq_customization' => ['name' => 'Personalizare EQ', 'unit' => ''],
            'app_support' => ['name' => 'Aplicație companion', 'unit' => ''],
            'game_mode' => ['name' => 'Mod gaming', 'unit' => ''],
            'low_latency' => ['name' => 'Latență redusă', 'unit' => ''],
            
            // Microphone & Calls
            'microphone_count' => ['name' => 'Număr microfoane', 'unit' => ''],
            'call_quality' => ['name' => 'Calitate apeluri', 'unit' => ''],
            'wind_noise_reduction' => ['name' => 'Reducere zgomot vânt', 'unit' => ''],
            
            // Other
            'release_date' => ['name' => 'Dată lansare', 'unit' => ''],
            'price' => ['name' => 'Preț', 'unit' => 'RON'],
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

    public function import($limit = 50)
    {
        echo "Starting import of {$limit} wireless earbuds from Versus.com...\n";

        $earbuds = $this->scrapeEarbudList($limit);

        if (empty($earbuds)) {
            echo "No earbuds found, using fallback list...\n";
            $earbuds = $this->getFallbackEarbudList($limit);
        }

        echo "✓ Found " . count($earbuds) . " wireless earbuds\n";

        $imported = 0;
        foreach ($earbuds as $earbud) {
            try {
                $this->importEarbud($earbud);
                $imported++;
                echo "✓ Imported: {$earbud['name']}\n";
                
                // Rate limiting
                sleep(2);
            } catch (\Exception $e) {
                echo "✗ Failed to import {$earbud['name']}: {$e->getMessage()}\n";
            }
        }

        echo "✓ Import completed successfully! Imported: {$imported} / " . count($earbuds) . " wireless earbuds\n";
    }

    private function scrapeEarbudList($limit)
    {
        $earbuds = [];
        $url = 'https://versus.com/en/wireless-earbud';

        try {
            $html = @file_get_contents($url);
            if ($html === false) {
                return [];
            }

            // Extract earbud URLs using regex
            preg_match_all('/href="(\/en\/[a-z0-9\-]+)"/', $html, $matches);
            
            if (!empty($matches[1])) {
                $urls = array_unique($matches[1]);
                
                // Filter for earbud URLs (exclude category pages, comparisons, etc.)
                $earbudBrands = ['airpods', 'galaxy-buds', 'pixel-buds', 'sony', 'bose', 'jabra', 'beats', 
                                 'sennheiser', 'jbl', 'anker', 'soundcore', 'nothing', 'oneplus', 'oppo',
                                 'xiaomi', 'realme', 'huawei', 'samsung', 'apple', 'google', 'wf-', 'wy-',
                                 'earbuds', 'ear-', 'buds', 'pods', 'freepods'];
                
                foreach ($urls as $url) {
                    if (count($earbuds) >= $limit) break;
                    
                    $isEarbud = false;
                    foreach ($earbudBrands as $brand) {
                        if (stripos($url, $brand) !== false) {
                            $isEarbud = true;
                            break;
                        }
                    }
                    
                    if ($isEarbud && !preg_match('/\/(vs|headphone|comparison|category|categories)/', $url)) {
                        $name = ucwords(str_replace('-', ' ', basename($url)));
                        $earbuds[] = [
                            'name' => $name,
                            'url' => 'https://versus.com' . $url,
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            echo "Error scraping earbud list: {$e->getMessage()}\n";
        }

        return array_slice($earbuds, 0, $limit);
    }

    private function getFallbackEarbudList($limit)
    {
        $earbuds = [
            // Apple AirPods
            [
                'brand' => 'Apple',
                'model' => 'AirPods Pro 2',
                'name' => 'Apple AirPods Pro 2',
                'url' => 'https://versus.com/en/apple-airpods-pro-2nd-generation',
                'price' => 1299.99,
                'image_url' => 'https://m.media-amazon.com/images/I/61SUj2aKoEL._AC_SL1500_.jpg',
                'specs' => [
                    'driver_size' => '11',
                    'has_anc' => 'Da',
                    'has_transparency_mode' => 'Da',
                    'spatial_audio' => 'Da',
                    'battery_life' => '6',
                    'battery_case' => '30',
                    'fast_charging' => 'Da',
                    'wireless_charging' => 'Da',
                    'weight' => '5.3',
                    'water_resistance' => 'IPX4',
                    'bluetooth_version' => '5.3',
                    'touch_controls' => 'Da',
                    'voice_assistant' => 'Siri',
                    'find_my' => 'Da',
                    'auto_pause' => 'Da',
                ],
            ],
            [
                'brand' => 'Apple',
                'model' => 'AirPods 3',
                'name' => 'Apple AirPods 3',
                'url' => 'https://versus.com/en/apple-airpods-3rd-generation',
                'price' => 899.99,
                'image_url' => 'https://m.media-amazon.com/images/I/61NiiCNtWWL._AC_SL1500_.jpg',
                'specs' => [
                    'driver_size' => '11',
                    'has_anc' => 'Nu',
                    'spatial_audio' => 'Da',
                    'battery_life' => '6',
                    'battery_case' => '30',
                    'wireless_charging' => 'Da',
                    'weight' => '4.3',
                    'water_resistance' => 'IPX4',
                    'bluetooth_version' => '5.0',
                    'touch_controls' => 'Da',
                    'voice_assistant' => 'Siri',
                    'find_my' => 'Da',
                    'auto_pause' => 'Da',
                ],
            ],
            [
                'brand' => 'Samsung',
                'model' => 'Galaxy Buds 3 Pro',
                'name' => 'Samsung Galaxy Buds 3 Pro',
                'url' => 'https://versus.com/en/samsung-galaxy-buds-3-pro',
                'price' => 1099.99,
                'image_url' => 'https://images.samsung.com/is/image/samsung/p6pim/ro/sm-r630nzaaeue/gallery/ro-galaxy-buds3-pro-r630-sm-r630nzaaeue-543022229',
                'specs' => [
                    'driver_size' => '10.5',
                    'has_anc' => 'Da',
                    'anc_microphones' => '6',
                    'has_transparency_mode' => 'Da',
                    'spatial_audio' => 'Da',
                    'battery_life' => '6',
                    'battery_case' => '30',
                    'fast_charging' => 'Da',
                    'wireless_charging' => 'Da',
                    'weight' => '5.4',
                    'water_resistance' => 'IP57',
                    'bluetooth_version' => '5.4',
                    'multipoint' => 'Da',
                    'touch_controls' => 'Da',
                    'voice_assistant' => 'Bixby, Google Assistant',
                    'eq_customization' => 'Da',
                ],
            ],
            [
                'brand' => 'Samsung',
                'model' => 'Galaxy Buds 2 Pro',
                'name' => 'Samsung Galaxy Buds 2 Pro',
                'url' => 'https://versus.com/en/samsung-galaxy-buds-2-pro',
                'price' => 799.99,
                'image_url' => 'https://images.samsung.com/is/image/samsung/p6pim/ro/sm-r510nzaaeue/gallery/ro-galaxy-buds2-pro-r510-sm-r510nzaaeue-533117127',
                'specs' => [
                    'driver_size' => '10',
                    'has_anc' => 'Da',
                    'anc_microphones' => '6',
                    'has_transparency_mode' => 'Da',
                    'spatial_audio' => 'Da',
                    'battery_life' => '5',
                    'battery_case' => '18',
                    'fast_charging' => 'Da',
                    'wireless_charging' => 'Da',
                    'weight' => '5.5',
                    'water_resistance' => 'IPX7',
                    'bluetooth_version' => '5.3',
                    'touch_controls' => 'Da',
                    'voice_assistant' => 'Bixby, Google Assistant',
                    'eq_customization' => 'Da',
                ],
            ],
            [
                'brand' => 'Sony',
                'model' => 'WF-1000XM5',
                'name' => 'Sony WF-1000XM5',
                'url' => 'https://versus.com/en/sony-wf-1000xm5',
                'price' => 1499.99,
                'image_url' => 'https://m.media-amazon.com/images/I/51K+GOvmNBL._AC_SL1500_.jpg',
                'specs' => [
                    'driver_size' => '8.4',
                    'has_anc' => 'Da',
                    'anc_microphones' => '8',
                    'has_transparency_mode' => 'Da',
                    'spatial_audio' => 'Da',
                    'audio_codec' => 'LDAC, AAC, SBC',
                    'battery_life' => '8',
                    'battery_case' => '24',
                    'fast_charging' => 'Da',
                    'wireless_charging' => 'Da',
                    'weight' => '5.9',
                    'water_resistance' => 'IPX4',
                    'bluetooth_version' => '5.3',
                    'multipoint' => 'Da',
                    'touch_controls' => 'Da',
                    'voice_assistant' => 'Google Assistant, Alexa',
                    'eq_customization' => 'Da',
                ],
            ],
            [
                'brand' => 'Sony',
                'model' => 'WF-1000XM4',
                'name' => 'Sony WF-1000XM4',
                'url' => 'https://versus.com/en/sony-wf-1000xm4',
                'price' => 1199.99,
                'image_url' => 'https://m.media-amazon.com/images/I/61RnXmpAmIL._AC_SL1500_.jpg',
                'specs' => [
                    'driver_size' => '6',
                    'has_anc' => 'Da',
                    'has_transparency_mode' => 'Da',
                    'spatial_audio' => 'Da',
                    'audio_codec' => 'LDAC, AAC, SBC',
                    'battery_life' => '8',
                    'battery_case' => '24',
                    'wireless_charging' => 'Da',
                    'weight' => '7.3',
                    'water_resistance' => 'IPX4',
                    'bluetooth_version' => '5.2',
                    'touch_controls' => 'Da',
                    'voice_assistant' => 'Google Assistant, Alexa',
                    'eq_customization' => 'Da',
                ],
            ],
            [
                'brand' => 'Bose',
                'model' => 'QuietComfort Ultra Earbuds',
                'name' => 'Bose QuietComfort Ultra Earbuds',
                'url' => 'https://versus.com/en/bose-quietcomfort-ultra-earbuds',
                'price' => 1599.99,
                'image_url' => 'https://assets.bose.com/content/dam/cloudassets/Bose_DAM/Web/consumer_electronics/global/products/headphones/qc_ultra_earbuds/product_silo_images/QCUE_PDP_Earbud-Black_001.png',
                'specs' => [
                    'has_anc' => 'Da',
                    'anc_microphones' => '6',
                    'has_transparency_mode' => 'Da',
                    'spatial_audio' => 'Da',
                    'battery_life' => '6',
                    'battery_case' => '24',
                    'fast_charging' => 'Da',
                    'wireless_charging' => 'Nu',
                    'weight' => '6.24',
                    'water_resistance' => 'IPX4',
                    'bluetooth_version' => '5.3',
                    'multipoint' => 'Da',
                    'touch_controls' => 'Da',
                    'eq_customization' => 'Da',
                    'app_support' => 'Da',
                ],
            ],
            [
                'brand' => 'Bose',
                'model' => 'QuietComfort Earbuds II',
                'name' => 'Bose QuietComfort Earbuds II',
                'url' => 'https://versus.com/en/bose-quietcomfort-earbuds-ii',
                'price' => 1299.99,
                'image_url' => 'https://assets.bose.com/content/dam/cloudassets/Bose_DAM/Web/consumer_electronics/global/products/headphones/qc_earbuds_ii/product_silo_images/QCEII_PDP_Earbud-Soapstone_001.png',
                'specs' => [
                    'has_anc' => 'Da',
                    'has_transparency_mode' => 'Da',
                    'battery_life' => '6',
                    'battery_case' => '24',
                    'wireless_charging' => 'Nu',
                    'weight' => '6.24',
                    'water_resistance' => 'IPX4',
                    'bluetooth_version' => '5.3',
                    'touch_controls' => 'Da',
                    'eq_customization' => 'Da',
                    'app_support' => 'Da',
                ],
            ],
            [
                'brand' => 'Google',
                'model' => 'Pixel Buds Pro 2',
                'name' => 'Google Pixel Buds Pro 2',
                'url' => 'https://versus.com/en/google-pixel-buds-pro-2',
                'price' => 1099.99,
                'image_url' => 'https://lh3.googleusercontent.com/7Og3YlCZT2wHbqJXKKrzQPJ5SCF4hNULSP9kf8t9GH4',
                'specs' => [
                    'driver_size' => '11',
                    'has_anc' => 'Da',
                    'has_transparency_mode' => 'Da',
                    'spatial_audio' => 'Da',
                    'battery_life' => '8',
                    'battery_case' => '30',
                    'fast_charging' => 'Da',
                    'wireless_charging' => 'Da',
                    'weight' => '4.7',
                    'water_resistance' => 'IP54',
                    'bluetooth_version' => '5.4',
                    'multipoint' => 'Da',
                    'touch_controls' => 'Da',
                    'voice_assistant' => 'Google Assistant',
                    'find_my' => 'Da',
                ],
            ],
            [
                'brand' => 'Google',
                'model' => 'Pixel Buds Pro',
                'name' => 'Google Pixel Buds Pro',
                'url' => 'https://versus.com/en/google-pixel-buds-pro',
                'price' => 899.99,
                'image_url' => 'https://lh3.googleusercontent.com/4QN9DYPMh8X9J_T_WhYmQ8Jz6K9t8PH9F7f5D8qUgV0',
                'specs' => [
                    'driver_size' => '11',
                    'has_anc' => 'Da',
                    'has_transparency_mode' => 'Da',
                    'spatial_audio' => 'Da',
                    'battery_life' => '7',
                    'battery_case' => '20',
                    'wireless_charging' => 'Da',
                    'weight' => '6.2',
                    'water_resistance' => 'IPX4',
                    'bluetooth_version' => '5.0',
                    'multipoint' => 'Da',
                    'touch_controls' => 'Da',
                    'voice_assistant' => 'Google Assistant',
                ],
            ],
            [
                'brand' => 'Nothing',
                'model' => 'Ear 2',
                'name' => 'Nothing Ear 2',
                'url' => 'https://versus.com/en/nothing-ear-2',
                'price' => 699.99,
                'image_url' => 'https://nothing.tech/cdn/shop/files/Ear-2-White_1.png',
                'specs' => [
                    'driver_size' => '11.6',
                    'has_anc' => 'Da',
                    'has_transparency_mode' => 'Da',
                    'battery_life' => '6.3',
                    'battery_case' => '36',
                    'fast_charging' => 'Da',
                    'wireless_charging' => 'Da',
                    'weight' => '4.5',
                    'water_resistance' => 'IP54',
                    'bluetooth_version' => '5.3',
                    'multipoint' => 'Da',
                    'touch_controls' => 'Da',
                    'eq_customization' => 'Da',
                    'app_support' => 'Da',
                ],
            ],
            [
                'brand' => 'Nothing',
                'model' => 'Ear (a)',
                'name' => 'Nothing Ear (a)',
                'url' => 'https://versus.com/en/nothing-ear-a',
                'price' => 499.99,
                'image_url' => 'https://nothing.tech/cdn/shop/files/Ear-a-Yellow_1.png',
                'specs' => [
                    'driver_size' => '11.6',
                    'has_anc' => 'Da',
                    'has_transparency_mode' => 'Da',
                    'battery_life' => '5.5',
                    'battery_case' => '27',
                    'weight' => '4.4',
                    'water_resistance' => 'IP54',
                    'bluetooth_version' => '5.3',
                    'touch_controls' => 'Da',
                    'eq_customization' => 'Da',
                    'app_support' => 'Da',
                ],
            ],
            [
                'brand' => 'Jabra',
                'model' => 'Elite 10',
                'name' => 'Jabra Elite 10',
                'url' => 'https://versus.com/en/jabra-elite-10',
                'price' => 1299.99,
                'image_url' => 'https://www.jabra.com/siteassets/products/jabra-elite-10/images/jabra-elite-10-product-image-gloss-black.png',
                'specs' => [
                    'driver_size' => '10',
                    'has_anc' => 'Da',
                    'anc_microphones' => '6',
                    'has_transparency_mode' => 'Da',
                    'spatial_audio' => 'Da',
                    'battery_life' => '6',
                    'battery_case' => '27',
                    'fast_charging' => 'Da',
                    'wireless_charging' => 'Da',
                    'weight' => '5.7',
                    'water_resistance' => 'IP57',
                    'bluetooth_version' => '5.3',
                    'multipoint' => 'Da',
                    'eq_customization' => 'Da',
                ],
            ],
            [
                'brand' => 'Jabra',
                'model' => 'Elite 8 Active',
                'name' => 'Jabra Elite 8 Active',
                'url' => 'https://versus.com/en/jabra-elite-8-active',
                'price' => 1099.99,
                'image_url' => 'https://www.jabra.com/siteassets/products/jabra-elite-8-active/images/jabra-elite-8-active-product-image-navy.png',
                'specs' => [
                    'driver_size' => '6',
                    'has_anc' => 'Da',
                    'has_transparency_mode' => 'Da',
                    'spatial_audio' => 'Da',
                    'battery_life' => '8',
                    'battery_case' => '32',
                    'fast_charging' => 'Da',
                    'wireless_charging' => 'Da',
                    'weight' => '5',
                    'water_resistance' => 'IP68',
                    'bluetooth_version' => '5.3',
                    'multipoint' => 'Da',
                ],
            ],
            [
                'brand' => 'Beats',
                'model' => 'Studio Buds Plus',
                'name' => 'Beats Studio Buds Plus',
                'url' => 'https://versus.com/en/beats-studio-buds-plus',
                'price' => 899.99,
                'image_url' => 'https://www.beatsbydre.com/content/dam/beats/web/product/earbuds/studio-buds-plus/global/images/carousel/ivory/studio-buds-plus-ivory-01.jpg',
                'specs' => [
                    'driver_size' => '8.2',
                    'has_anc' => 'Da',
                    'has_transparency_mode' => 'Da',
                    'spatial_audio' => 'Da',
                    'battery_life' => '9',
                    'battery_case' => '36',
                    'fast_charging' => 'Da',
                    'weight' => '5.1',
                    'water_resistance' => 'IPX4',
                    'bluetooth_version' => '5.3',
                    'touch_controls' => 'Da',
                    'voice_assistant' => 'Siri, Google Assistant',
                ],
            ],
            [
                'brand' => 'Beats',
                'model' => 'Fit Pro',
                'name' => 'Beats Fit Pro',
                'url' => 'https://versus.com/en/beats-fit-pro',
                'price' => 999.99,
                'image_url' => 'https://www.beatsbydre.com/content/dam/beats/web/product/earbuds/fit-pro/global/images/carousel/stone-purple/fit-pro-stone-purple-01.jpg',
                'specs' => [
                    'driver_size' => '9.5',
                    'has_anc' => 'Da',
                    'has_transparency_mode' => 'Da',
                    'spatial_audio' => 'Da',
                    'battery_life' => '6',
                    'battery_case' => '24',
                    'fast_charging' => 'Da',
                    'weight' => '5.6',
                    'water_resistance' => 'IPX4',
                    'bluetooth_version' => '5.2',
                    'touch_controls' => 'Da',
                ],
            ],
            [
                'brand' => 'Sennheiser',
                'model' => 'Momentum True Wireless 4',
                'name' => 'Sennheiser Momentum True Wireless 4',
                'url' => 'https://versus.com/en/sennheiser-momentum-true-wireless-4',
                'price' => 1599.99,
                'image_url' => 'https://www.sennheiser.com/images/MTW4_Black_Front_sq_1200x1200px.png',
                'specs' => [
                    'driver_size' => '7',
                    'has_anc' => 'Da',
                    'has_transparency_mode' => 'Da',
                    'spatial_audio' => 'Nu',
                    'audio_codec' => 'aptX Adaptive, AAC, SBC',
                    'battery_life' => '7.5',
                    'battery_case' => '30',
                    'fast_charging' => 'Da',
                    'wireless_charging' => 'Da',
                    'weight' => '6',
                    'water_resistance' => 'IP54',
                    'bluetooth_version' => '5.4',
                    'multipoint' => 'Da',
                    'eq_customization' => 'Da',
                ],
            ],
            [
                'brand' => 'Sennheiser',
                'model' => 'Momentum True Wireless 3',
                'name' => 'Sennheiser Momentum True Wireless 3',
                'url' => 'https://versus.com/en/sennheiser-momentum-true-wireless-3',
                'price' => 1299.99,
                'image_url' => 'https://www.sennheiser.com/images/MTW3_Black_Front_sq_1200x1200px.png',
                'specs' => [
                    'driver_size' => '7',
                    'has_anc' => 'Da',
                    'has_transparency_mode' => 'Da',
                    'audio_codec' => 'aptX Adaptive, AAC, SBC',
                    'battery_life' => '7',
                    'battery_case' => '28',
                    'wireless_charging' => 'Da',
                    'weight' => '5.8',
                    'water_resistance' => 'IPX4',
                    'bluetooth_version' => '5.2',
                    'multipoint' => 'Da',
                    'eq_customization' => 'Da',
                ],
            ],
            [
                'brand' => 'Soundcore',
                'model' => 'Liberty 4 NC',
                'name' => 'Soundcore Liberty 4 NC',
                'url' => 'https://versus.com/en/soundcore-liberty-4-nc',
                'price' => 499.99,
                'image_url' => 'https://us.soundcore.com/cdn/shop/files/A3947_TD01_1.png',
                'specs' => [
                    'driver_size' => '11',
                    'has_anc' => 'Da',
                    'has_transparency_mode' => 'Da',
                    'audio_codec' => 'LDAC, AAC, SBC',
                    'battery_life' => '10',
                    'battery_case' => '50',
                    'fast_charging' => 'Da',
                    'wireless_charging' => 'Da',
                    'weight' => '4.9',
                    'water_resistance' => 'IPX4',
                    'bluetooth_version' => '5.3',
                    'eq_customization' => 'Da',
                    'app_support' => 'Da',
                ],
            ],
            [
                'brand' => 'Soundcore',
                'model' => 'Space A40',
                'name' => 'Soundcore Space A40',
                'url' => 'https://versus.com/en/soundcore-space-a40',
                'price' => 399.99,
                'image_url' => 'https://us.soundcore.com/cdn/shop/files/A3936_TD01_1.png',
                'specs' => [
                    'driver_size' => '10',
                    'has_anc' => 'Da',
                    'has_transparency_mode' => 'Da',
                    'audio_codec' => 'LDAC, AAC, SBC',
                    'battery_life' => '10',
                    'battery_case' => '50',
                    'wireless_charging' => 'Da',
                    'weight' => '4.9',
                    'water_resistance' => 'IPX4',
                    'bluetooth_version' => '5.2',
                    'multipoint' => 'Da',
                    'eq_customization' => 'Da',
                ],
            ],
            [
                'brand' => 'OnePlus',
                'model' => 'Buds Pro 3',
                'name' => 'OnePlus Buds Pro 3',
                'url' => 'https://versus.com/en/oneplus-buds-pro-3',
                'price' => 899.99,
                'image_url' => 'https://image01.oneplus.net/ebp/202408/13/1-m00-5d-9f-cpgm7mbbdiqascwyaahbkhwlzcu811.png',
                'specs' => [
                    'driver_size' => '11',
                    'has_anc' => 'Da',
                    'anc_microphones' => '6',
                    'has_transparency_mode' => 'Da',
                    'spatial_audio' => 'Da',
                    'audio_codec' => 'LHDC, AAC, SBC',
                    'battery_life' => '6',
                    'battery_case' => '43',
                    'fast_charging' => 'Da',
                    'wireless_charging' => 'Nu',
                    'weight' => '5.3',
                    'water_resistance' => 'IP55',
                    'bluetooth_version' => '5.4',
                    'eq_customization' => 'Da',
                ],
            ],
            [
                'brand' => 'OnePlus',
                'model' => 'Buds Pro 2',
                'name' => 'OnePlus Buds Pro 2',
                'url' => 'https://versus.com/en/oneplus-buds-pro-2',
                'price' => 699.99,
                'image_url' => 'https://image01.oneplus.net/ebp/202302/07/1-m00-45-8b-cpgm7mptcveaelqlaacixfqtpd4505.png',
                'specs' => [
                    'driver_size' => '11',
                    'has_anc' => 'Da',
                    'has_transparency_mode' => 'Da',
                    'spatial_audio' => 'Da',
                    'audio_codec' => 'LHDC, AAC, SBC',
                    'battery_life' => '6',
                    'battery_case' => '39',
                    'wireless_charging' => 'Nu',
                    'weight' => '4.9',
                    'water_resistance' => 'IP55',
                    'bluetooth_version' => '5.3',
                    'eq_customization' => 'Da',
                ],
            ],
            [
                'brand' => 'Samsung',
                'model' => 'Galaxy Buds FE',
                'name' => 'Samsung Galaxy Buds FE',
                'url' => 'https://versus.com/en/samsung-galaxy-buds-fe',
                'price' => 399.99,
                'image_url' => 'https://images.samsung.com/is/image/samsung/p6pim/ro/sm-r400nzaaeue/gallery/ro-galaxy-buds-fe-r400-sm-r400nzaaeue-537638462',
                'specs' => [
                    'driver_size' => '6.5',
                    'has_anc' => 'Da',
                    'has_transparency_mode' => 'Da',
                    'battery_life' => '6',
                    'battery_case' => '21',
                    'weight' => '5.6',
                    'water_resistance' => 'IPX2',
                    'bluetooth_version' => '5.2',
                    'touch_controls' => 'Da',
                    'eq_customization' => 'Da',
                ],
            ],
            [
                'brand' => 'Sony',
                'model' => 'LinkBuds S',
                'name' => 'Sony LinkBuds S',
                'url' => 'https://versus.com/en/sony-linkbuds-s',
                'price' => 899.99,
                'image_url' => 'https://m.media-amazon.com/images/I/61h5fG8JUHL._AC_SL1500_.jpg',
                'specs' => [
                    'driver_size' => '5',
                    'has_anc' => 'Da',
                    'has_transparency_mode' => 'Da',
                    'spatial_audio' => 'Da',
                    'audio_codec' => 'LDAC, AAC, SBC',
                    'battery_life' => '6',
                    'battery_case' => '20',
                    'wireless_charging' => 'Da',
                    'weight' => '4.8',
                    'water_resistance' => 'IPX4',
                    'bluetooth_version' => '5.2',
                    'multipoint' => 'Da',
                    'eq_customization' => 'Da',
                ],
            ],
            [
                'brand' => 'Samsung',
                'model' => 'Galaxy Buds 2',
                'name' => 'Samsung Galaxy Buds 2',
                'url' => 'https://versus.com/en/samsung-galaxy-buds-2',
                'price' => 599.99,
                'image_url' => 'https://images.samsung.com/is/image/samsung/p6pim/ro/sm-r177nzwaeue/gallery/ro-galaxy-buds2-r177-sm-r177nzwaeue-475055857',
                'specs' => [
                    'driver_size' => '11',
                    'has_anc' => 'Da',
                    'has_transparency_mode' => 'Da',
                    'battery_life' => '5',
                    'battery_case' => '20',
                    'weight' => '5',
                    'water_resistance' => 'IPX2',
                    'bluetooth_version' => '5.2',
                    'touch_controls' => 'Da',
                    'eq_customization' => 'Da',
                ],
            ],
            [
                'brand' => 'Apple',
                'model' => 'AirPods Max',
                'name' => 'Apple AirPods Max',
                'url' => 'https://versus.com/en/apple-airpods-max',
                'price' => 2999.99,
                'image_url' => 'https://m.media-amazon.com/images/I/81p5ZmrXNuL._AC_SL1500_.jpg',
                'specs' => [
                    'driver_size' => '40',
                    'has_anc' => 'Da',
                    'has_transparency_mode' => 'Da',
                    'spatial_audio' => 'Da',
                    'battery_life' => '20',
                    'weight' => '384',
                    'bluetooth_version' => '5.0',
                    'touch_controls' => 'Nu',
                    'voice_assistant' => 'Siri',
                    'find_my' => 'Da',
                ],
            ],
            [
                'brand' => 'Samsung',
                'model' => 'Galaxy Buds 3',
                'name' => 'Samsung Galaxy Buds 3',
                'url' => 'https://versus.com/en/samsung-galaxy-buds-3',
                'price' => 799.99,
                'image_url' => 'https://images.samsung.com/is/image/samsung/p6pim/ro/sm-r530nzaaeue/gallery/ro-galaxy-buds3-r530-sm-r530nzaaeue-543022196',
                'specs' => [
                    'driver_size' => '11',
                    'has_anc' => 'Nu',
                    'has_transparency_mode' => 'Da',
                    'battery_life' => '6',
                    'battery_case' => '30',
                    'fast_charging' => 'Da',
                    'wireless_charging' => 'Da',
                    'weight' => '4.7',
                    'water_resistance' => 'IP57',
                    'bluetooth_version' => '5.4',
                    'touch_controls' => 'Da',
                    'eq_customization' => 'Da',
                ],
            ],
            [
                'brand' => 'Google',
                'model' => 'Pixel Buds A-Series',
                'name' => 'Google Pixel Buds A-Series',
                'url' => 'https://versus.com/en/google-pixel-buds-a-series',
                'price' => 499.99,
                'image_url' => 'https://lh3.googleusercontent.com/qG8i2T9N9K8fPJqf7OHHYPw7LNBx5OQ9F6Q5z2qH8k',
                'specs' => [
                    'driver_size' => '12',
                    'has_anc' => 'Nu',
                    'has_transparency_mode' => 'Nu',
                    'spatial_audio' => 'Nu',
                    'battery_life' => '5',
                    'battery_case' => '24',
                    'weight' => '5.1',
                    'water_resistance' => 'IPX4',
                    'bluetooth_version' => '5.0',
                    'touch_controls' => 'Da',
                    'voice_assistant' => 'Google Assistant',
                ],
            ],
            [
                'brand' => 'Sony',
                'model' => 'WF-C700N',
                'name' => 'Sony WF-C700N',
                'url' => 'https://versus.com/en/sony-wf-c700n',
                'price' => 599.99,
                'image_url' => 'https://m.media-amazon.com/images/I/61DVxnm1BfL._AC_SL1500_.jpg',
                'specs' => [
                    'driver_size' => '5',
                    'has_anc' => 'Da',
                    'has_transparency_mode' => 'Da',
                    'audio_codec' => 'AAC, SBC',
                    'battery_life' => '7.5',
                    'battery_case' => '15',
                    'weight' => '4.6',
                    'water_resistance' => 'IPX4',
                    'bluetooth_version' => '5.2',
                    'multipoint' => 'Da',
                    'eq_customization' => 'Da',
                ],
            ],
            [
                'brand' => 'Jabra',
                'model' => 'Elite 7 Pro',
                'name' => 'Jabra Elite 7 Pro',
                'url' => 'https://versus.com/en/jabra-elite-7-pro',
                'price' => 999.99,
                'image_url' => 'https://www.jabra.com/siteassets/products/jabra-elite-7-pro/images/jabra-elite-7-pro-product-image-gold-beige.png',
                'specs' => [
                    'driver_size' => '6',
                    'has_anc' => 'Da',
                    'has_transparency_mode' => 'Da',
                    'battery_life' => '8',
                    'battery_case' => '30',
                    'fast_charging' => 'Da',
                    'wireless_charging' => 'Da',
                    'weight' => '5.4',
                    'water_resistance' => 'IP57',
                    'bluetooth_version' => '5.2',
                    'multipoint' => 'Da',
                    'eq_customization' => 'Da',
                ],
            ],
            [
                'brand' => 'Bose',
                'model' => 'Sport Earbuds',
                'name' => 'Bose Sport Earbuds',
                'url' => 'https://versus.com/en/bose-sport-earbuds',
                'price' => 799.99,
                'image_url' => 'https://assets.bose.com/content/dam/cloudassets/Bose_DAM/Web/consumer_electronics/global/products/headphones/sport_earbuds/product_silo_images/Sport_Earbuds_PDP_Earbud-Black_001.png',
                'specs' => [
                    'has_anc' => 'Nu',
                    'has_transparency_mode' => 'Nu',
                    'battery_life' => '5',
                    'battery_case' => '15',
                    'weight' => '6',
                    'water_resistance' => 'IPX4',
                    'bluetooth_version' => '5.1',
                    'touch_controls' => 'Da',
                    'app_support' => 'Da',
                ],
            ],
            [
                'brand' => 'Apple',
                'model' => 'AirPods 2',
                'name' => 'Apple AirPods 2',
                'url' => 'https://versus.com/en/apple-airpods-2019',
                'price' => 699.99,
                'image_url' => 'https://m.media-amazon.com/images/I/61Rdva+J3rL._AC_SL1500_.jpg',
                'specs' => [
                    'has_anc' => 'Nu',
                    'has_transparency_mode' => 'Nu',
                    'battery_life' => '5',
                    'battery_case' => '24',
                    'weight' => '4',
                    'bluetooth_version' => '5.0',
                    'touch_controls' => 'Nu',
                    'voice_assistant' => 'Siri',
                    'find_my' => 'Da',
                ],
            ],
            [
                'brand' => 'Apple',
                'model' => 'AirPods Pro',
                'name' => 'Apple AirPods Pro',
                'url' => 'https://versus.com/en/apple-airpods-pro',
                'price' => 999.99,
                'image_url' => 'https://m.media-amazon.com/images/I/71zny7BTRlL._AC_SL1500_.jpg',
                'specs' => [
                    'has_anc' => 'Da',
                    'has_transparency_mode' => 'Da',
                    'spatial_audio' => 'Da',
                    'battery_life' => '4.5',
                    'battery_case' => '24',
                    'wireless_charging' => 'Da',
                    'weight' => '5.4',
                    'water_resistance' => 'IPX4',
                    'bluetooth_version' => '5.0',
                    'touch_controls' => 'Da',
                    'voice_assistant' => 'Siri',
                    'find_my' => 'Da',
                ],
            ],
        ];

        return array_slice($earbuds, 0, $limit);
    }

    private function importEarbud($earbudData)
    {
        // Extract brand from data (if available) or from name
        $brand = $earbudData['brand'] ?? explode(' ', $earbudData['name'])[0];

        // Use hardcoded specs if available, otherwise scrape
        if (isset($earbudData['specs'])) {
            // Using hardcoded specifications
            $specs = [
                'image' => $earbudData['image_url'] ?? null,
                'description' => $earbudData['name'] . ' - Căști wireless premium',
                'price' => $earbudData['price'] ?? 599.00,
                'specifications' => $earbudData['specs'],
            ];
        } else {
            // Fallback to scraping
            $specs = $this->scrapeEarbudSpecs($earbudData['url']);
        }

        // Create or update product
        $product = Product::withoutSyncingToSearch(function () use ($earbudData, $brand, $specs) {
            return Product::updateOrCreate(
                [
                    'name' => $earbudData['name'],
                    'product_type_id' => $this->productType->id,
                ],
                [
                    'brand' => $brand,
                    'image_url' => $specs['image'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($earbudData['name']) . '&size=400&background=3c59fc&color=fff',
                    'description' => $specs['description'] ?? $earbudData['name'] . ' - Căști wireless premium',
                    'affiliate_link' => $earbudData['url'],
                ]
            );
        });

        // Save specifications
        if (!empty($specs['specifications'])) {
            $this->saveEarbudSpecifications($product, $specs['specifications']);
        }

        // Create offer
        $price = $specs['price'] ?? 599.00;
        $this->createOffer($product, $price);

        return $product;
    }

    private function scrapeEarbudSpecs($url)
    {
        $specs = [
            'image' => null,
            'description' => null,
            'price' => null,
            'score' => null,
            'specifications' => [],
        ];

        try {
            echo "  Fetching specs from {$url}...\n";
            
            // Use Puppeteer for dynamic content
            $scraper = new PuppeteerScraper();
            $result = $scraper->scrapeAndParse($url);
            
            $specs['specifications'] = $result['specs'];
            
            // Add image URL if found
            if (!empty($result['image_url'])) {
                $specs['image'] = $result['image_url'];
            }
            
            // Try to extract additional metadata from HTML if needed
            $html = $scraper->scrape($url);
            
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);

            // Get description if not already found
            if (empty($specs['description'])) {
                $descNodes = $xpath->query('//meta[@property="og:description"]/@content');
                if ($descNodes->length > 0) {
                    $specs['description'] = $descNodes->item(0)->value;
                }
            }
            
            // Extract score if available
            $scoreNodes = $xpath->query('//*[@data-score]');
            if ($scoreNodes->length > 0) {
                $specs['score'] = (int)$scoreNodes->item(0)->getAttribute('data-score');
            }
            
            echo "  ✓ Found " . count($specs['specifications']) . " specifications\n";

        } catch (\Exception $e) {
            echo "  ✗ Error scraping specs: {$e->getMessage()}\n";
        }

        return $specs;
    }

    private function saveEarbudSpecifications($product, $specifications)
    {
        foreach ($specifications as $key => $value) {
            if (!isset($this->specKeys[$key]) || empty($value)) {
                continue;
            }

            // Prepare data structure with typed columns
            $data = [
                'value_string' => null,
                'value_number' => null,
                'value_bool' => null,
            ];

            // Check for boolean values (including Romanian)
            if (in_array(strtolower($value), ['yes', 'da', 'true'])) {
                $data['value_bool'] = true;
            } elseif (in_array(strtolower($value), ['no', 'nu', 'false'])) {
                $data['value_bool'] = false;
            } elseif (preg_match('/^[\d,.]+$/', $value)) {
                // Extract number
                $data['value_number'] = floatval(str_replace(',', '', $value));
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
    }

    private function createOffer($product, $price)
    {
        $finalPrice = is_numeric($price) ? $price : 599.00;
        $affiliateUrl = $product->affiliate_link ?? 'https://versus.com/en/wireless-earbud';

        Offer::updateOrCreate(
            [
                'product_id' => $product->id,
                'seller_name' => 'Versus.com',
            ],
            [
                'url' => $affiliateUrl,
                'price' => $finalPrice,
                'merchant' => 'Versus.com',
                'currency' => 'RON',
                'is_available' => true,
                'url_affiliate' => $affiliateUrl,
            ]
        );
    }
}
