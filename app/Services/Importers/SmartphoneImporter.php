<?php

namespace App\Services\Importers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\SpecKey;
use App\Models\SpecValue;
use App\Models\Offer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SmartphoneImporter
{
    protected $category;
    protected $productType;
    protected $specKeys = [];

    public function __construct()
    {
        $this->initializeCategory();
        $this->initializeSpecKeys();
    }

    protected function initializeCategory()
    {
        $this->category = Category::firstOrCreate(
            ['slug' => 'electronice'],
            ['name' => 'Electronice', 'description' => 'Produse electronice și gadgeturi']
        );

        $this->productType = ProductType::where('slug', 'smartphone')
            ->where('category_id', $this->category->id)
            ->first();
            
        if (!$this->productType) {
            $this->productType = ProductType::firstOrCreate(
                ['slug' => 'smartphone'],
                [
                    'category_id' => $this->category->id,
                    'name' => 'Smartphone',
                    'description' => 'Telefoane inteligente'
                ]
            );
        }
    }

    protected function initializeSpecKeys()
    {
        $specs = [
            'display' => ['name' => 'Display', 'unit' => null],
            'display_size' => ['name' => 'Dimensiune ecran', 'unit' => 'inch'],
            'refresh_rate' => ['name' => 'Refresh Rate', 'unit' => 'Hz'],
            'chipset' => ['name' => 'Procesor', 'unit' => null],
            'gpu' => ['name' => 'GPU', 'unit' => null],
            'ram' => ['name' => 'RAM', 'unit' => 'GB'],
            'storage' => ['name' => 'Stocare', 'unit' => 'GB'],
            'rear_camera' => ['name' => 'Camera Principala', 'unit' => 'MP'],
            'front_camera' => ['name' => 'Camera Frontala', 'unit' => 'MP'],
            'video' => ['name' => 'Video', 'unit' => null],
            'battery' => ['name' => 'Baterie', 'unit' => 'mAh'],
            'charging' => ['name' => 'Incarcare', 'unit' => 'W'],
            'os' => ['name' => 'Sistem de Operare', 'unit' => null],
            'network' => ['name' => '5G', 'unit' => null],
            'sim' => ['name' => 'SIM', 'unit' => null],
            'dimensions' => ['name' => 'Dimensiuni', 'unit' => 'mm'],
            'weight' => ['name' => 'Greutate', 'unit' => 'g'],
            'ip_rating' => ['name' => 'Rezistenta Apa', 'unit' => null],
            'wifi' => ['name' => 'Wi-Fi', 'unit' => null],
            'bluetooth' => ['name' => 'Bluetooth', 'unit' => null],
            'nfc' => ['name' => 'NFC', 'unit' => null],
            'usb' => ['name' => 'USB', 'unit' => null],
            'audio_jack' => ['name' => 'Jack Audio', 'unit' => null],
        ];

        foreach ($specs as $key => $data) {
            $this->specKeys[$key] = SpecKey::firstOrCreate(
                [
                    'product_type_id' => $this->productType->id,
                    'slug' => $key
                ],
                [
                    'name' => $data['name'],
                    'unit' => $data['unit']
                ]
            );
        }
    }

    public function importFromCsv(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("CSV file not found: {$filePath}");
        }

        $imported = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            $handle = fopen($filePath, 'r');
            $headers = fgetcsv($handle);
            
            if (!$headers) {
                throw new \Exception("CSV file is empty or invalid");
            }

            while (($row = fgetcsv($handle)) !== false) {
                // Skip empty rows
                if (count(array_filter($row)) === 0) {
                    continue;
                }

                $data = array_combine($headers, $row);

                try {
                    $this->importProduct($data);
                    $imported++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = [
                        'product' => $data['name'] ?? 'Unknown',
                        'error' => $e->getMessage()
                    ];
                    Log::error("Failed to import product: " . $e->getMessage(), ['data' => $data]);
                }
            }

            fclose($handle);
            DB::commit();

            return [
                'success' => true,
                'imported' => $imported,
                'failed' => $failed,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function importProduct(array $data): Product
    {
        // Disable Scout syncing during import
        Product::withoutSyncingToSearch(function () use ($data, &$product) {
            // Create or update product
            $product = Product::updateOrCreate(
                [
                    'brand' => $data['brand'] ?? 'Unknown',
                    'name' => $data['name'],
                ],
                [
                    'product_type_id' => $this->productType->id,
                    'mpn' => $data['model_mpn'] ?? null,
                    'ean' => $data['ean_gtin'] ?? null,
                    'short_desc' => $this->generateDescription($data),
                    'image_url' => $this->getFirstImageUrl($data['image_urls'] ?? ''),
                ]
            );

            // Create offer if price is available
            if (!empty($data['price_ron']) && is_numeric($data['price_ron'])) {
                $inStock = !empty($data['availability']) && 
                           strtolower($data['availability']) === 'in_stock';
                
                Offer::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'merchant' => $data['brand'] ?? 'Official Store',
                    ],
                    [
                        'price' => floatval($data['price_ron']),
                        'currency' => $data['currency'] ?? 'RON',
                        'url_affiliate' => $data['product_url'] ?? '#',
                        'in_stock' => $inStock,
                        'last_seen_at' => now(),
                    ]
                );
            }

            // Add specifications
            $this->addSpecifications($product, $data);
        });

        return $product;
    }

    protected function addSpecifications(Product $product, array $data): void
    {
        // Display
        if (!empty($data['display'])) {
            $this->addSpec($product, 'display', $data['display']);
        }

        // Display size (extract from display or use separate field)
        if (!empty($data['display'])) {
            $displaySize = $this->extractDisplaySize($data['display']);
            if ($displaySize) {
                $this->addSpec($product, 'display_size', $displaySize, true);
            }
        }

        // Refresh rate
        if (!empty($data['refresh_rate_hz'])) {
            $this->addSpec($product, 'refresh_rate', $data['refresh_rate_hz'] . 'Hz');
        }

        // Chipset
        if (!empty($data['chipset'])) {
            $this->addSpec($product, 'chipset', $data['chipset']);
        }

        // GPU
        if (!empty($data['gpu'])) {
            $this->addSpec($product, 'gpu', $data['gpu']);
        }

        // RAM
        if (!empty($data['ram_gb'])) {
            $this->addSpec($product, 'ram', $data['ram_gb'], true);
        }

        // Storage
        if (!empty($data['storage_gb'])) {
            $storage = $data['storage_gb'] . 'GB';
            if (!empty($data['expandable_storage']) && $data['expandable_storage'] !== 'No') {
                $storage .= ' (expandabil ' . $data['expandable_storage'] . ')';
            }
            $this->addSpec($product, 'storage', $storage);
        }

        // Rear camera
        if (!empty($data['rear_camera'])) {
            $this->addSpec($product, 'rear_camera', $data['rear_camera']);
        }

        // Front camera
        if (!empty($data['front_camera'])) {
            $this->addSpec($product, 'front_camera', $data['front_camera']);
        }

        // Video
        if (!empty($data['video'])) {
            $this->addSpec($product, 'video', $data['video']);
        }

        // Battery
        if (!empty($data['battery_mah'])) {
            $this->addSpec($product, 'battery', $data['battery_mah'], true);
        }

        // Charging
        if (!empty($data['charging_watt'])) {
            $this->addSpec($product, 'charging', $data['charging_watt'] . 'W');
        }

        // OS
        if (!empty($data['os'])) {
            $os = $data['os'];
            if (!empty($data['ui'])) {
                $os .= ' cu ' . $data['ui'];
            }
            $this->addSpec($product, 'os', $os);
        }

        // Network (5G)
        if (!empty($data['network'])) {
            $this->addSpec($product, 'network', $data['network']);
        }

        // SIM
        if (!empty($data['sim'])) {
            $this->addSpec($product, 'sim', $data['sim']);
        }

        // Dimensions
        if (!empty($data['dimensions_mm'])) {
            $this->addSpec($product, 'dimensions', $data['dimensions_mm']);
        }

        // Weight
        if (!empty($data['weight_g'])) {
            $this->addSpec($product, 'weight', $data['weight_g'], true);
        }

        // IP Rating
        if (!empty($data['ip_rating'])) {
            $this->addSpec($product, 'ip_rating', $data['ip_rating']);
        }

        // WiFi
        if (!empty($data['wifi'])) {
            $this->addSpec($product, 'wifi', $data['wifi']);
        }

        // Bluetooth
        if (!empty($data['bluetooth'])) {
            $this->addSpec($product, 'bluetooth', $data['bluetooth']);
        }

        // NFC
        if (!empty($data['nfc'])) {
            $this->addSpec($product, 'nfc', $data['nfc']);
        }

        // USB
        if (!empty($data['usb'])) {
            $this->addSpec($product, 'usb', $data['usb']);
        }

        // Audio jack
        if (!empty($data['audio_jack'])) {
            $this->addSpec($product, 'audio_jack', $data['audio_jack']);
        }
    }

    protected function addSpec(Product $product, string $key, $value, bool $isNumeric = false): void
    {
        if (!isset($this->specKeys[$key])) {
            return;
        }

        SpecValue::updateOrCreate(
            [
                'product_id' => $product->id,
                'spec_key_id' => $this->specKeys[$key]->id,
            ],
            [
                'value_string' => $isNumeric ? null : $value,
                'value_number' => $isNumeric ? floatval($value) : null,
            ]
        );
    }

    protected function extractDisplaySize(string $display): ?string
    {
        // Try to extract size like "6.7 inch" or "6.7\"" from display text
        if (preg_match('/(\d+\.?\d*)\s*(inch|"|″)/', $display, $matches)) {
            return $matches[1];
        }
        return null;
    }

    protected function generateSlug(string $name): string
    {
        return \Illuminate\Support\Str::slug($name);
    }

    protected function generateDescription(array $data): string
    {
        $parts = [];
        
        if (!empty($data['display'])) {
            $parts[] = "Display " . $data['display'];
        }
        
        if (!empty($data['ram_gb']) && !empty($data['storage_gb'])) {
            $parts[] = $data['ram_gb'] . 'GB RAM + ' . $data['storage_gb'] . 'GB stocare';
        }
        
        if (!empty($data['rear_camera'])) {
            $parts[] = "Cameră " . $data['rear_camera'];
        }
        
        if (!empty($data['battery_mah'])) {
            $parts[] = "Baterie " . $data['battery_mah'] . 'mAh';
        }

        return implode(', ', $parts);
    }

    protected function getFirstImageUrl(string $imageUrls): ?string
    {
        if (empty($imageUrls)) {
            return null;
        }

        // Split by comma or semicolon
        $urls = preg_split('/[,;]/', $imageUrls);
        $url = trim($urls[0]);
        
        return !empty($url) ? $url : null;
    }
}
