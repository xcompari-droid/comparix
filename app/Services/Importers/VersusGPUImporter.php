<?php

namespace App\Services\Importers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\SpecKey;
use App\Models\SpecValue;
use App\Models\Offer;
use App\Services\PuppeteerScraper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VersusGPUImporter
{
    protected $baseUrl = 'https://versus.com';
    protected $category;
    protected $productType;
    protected $specKeys = [];

    public function __construct()
    {
        // Create Graphics Cards category
        $this->category = Category::firstOrCreate(
            ['name' => 'Plăci video'],
            [
                'slug' => 'placi-video',
                'description' => 'Comparație plăci video (GPU) pentru gaming și aplicații profesionale',
                'icon' => '🎮',
                'is_active' => true,
            ]
        );

        // Create GPU product type
        $this->productType = ProductType::firstOrCreate(
            ['name' => 'Placă video'],
            [
                'slug' => 'placa-video',
                'category_id' => $this->category->id,
            ]
        );

        $this->initializeSpecKeys();
    }

    protected function initializeSpecKeys()
    {
        $specs = [
            // Performance
            'gpu_clock' => ['name' => 'Frecvență GPU', 'unit' => 'MHz'],
            'boost_clock' => ['name' => 'Frecvență Boost', 'unit' => 'MHz'],
            'memory_clock' => ['name' => 'Frecvență memorie', 'unit' => 'MHz'],
            'memory_size' => ['name' => 'Memorie video', 'unit' => 'GB'],
            'memory_type' => ['name' => 'Tip memorie', 'unit' => ''],
            'memory_bus' => ['name' => 'Magistrală memorie', 'unit' => 'bit'],
            'memory_bandwidth' => ['name' => 'Lățime bandă memorie', 'unit' => 'GB/s'],
            
            // Architecture
            'gpu_chip' => ['name' => 'Chip GPU', 'unit' => ''],
            'architecture' => ['name' => 'Arhitectură', 'unit' => ''],
            'process_size' => ['name' => 'Proces fabricație', 'unit' => 'nm'],
            'transistor_count' => ['name' => 'Număr tranzistori', 'unit' => 'milioane'],
            'die_size' => ['name' => 'Dimensiune die', 'unit' => 'mm²'],
            
            // Cores & Units
            'cuda_cores' => ['name' => 'CUDA Cores', 'unit' => ''],
            'stream_processors' => ['name' => 'Stream Processors', 'unit' => ''],
            'tensor_cores' => ['name' => 'Tensor Cores', 'unit' => ''],
            'rt_cores' => ['name' => 'RT Cores', 'unit' => ''],
            'tmus' => ['name' => 'TMUs', 'unit' => ''],
            'rops' => ['name' => 'ROPs', 'unit' => ''],
            
            // Performance metrics
            'tflops_fp32' => ['name' => 'TFLOPS FP32', 'unit' => 'TFLOPS'],
            'tflops_fp16' => ['name' => 'TFLOPS FP16', 'unit' => 'TFLOPS'],
            'ray_tracing_performance' => ['name' => 'Performanță Ray Tracing', 'unit' => ''],
            
            // Power & Thermal
            'tdp' => ['name' => 'TDP', 'unit' => 'W'],
            'power_connector' => ['name' => 'Conector alimentare', 'unit' => ''],
            'recommended_psu' => ['name' => 'PSU recomandat', 'unit' => 'W'],
            
            // Display & Outputs
            'max_resolution' => ['name' => 'Rezoluție maximă', 'unit' => ''],
            'max_displays' => ['name' => 'Monitoare maxime', 'unit' => ''],
            'hdmi_ports' => ['name' => 'Porturi HDMI', 'unit' => ''],
            'displayport' => ['name' => 'DisplayPort', 'unit' => ''],
            'hdmi_version' => ['name' => 'Versiune HDMI', 'unit' => ''],
            'displayport_version' => ['name' => 'Versiune DisplayPort', 'unit' => ''],
            
            // Features
            'ray_tracing' => ['name' => 'Ray Tracing', 'unit' => ''],
            'dlss' => ['name' => 'DLSS', 'unit' => ''],
            'fsr' => ['name' => 'FSR', 'unit' => ''],
            'directx_version' => ['name' => 'DirectX', 'unit' => ''],
            'opengl_version' => ['name' => 'OpenGL', 'unit' => ''],
            'vulkan' => ['name' => 'Vulkan', 'unit' => ''],
            'opencl' => ['name' => 'OpenCL', 'unit' => ''],
            
            // Physical
            'length' => ['name' => 'Lungime', 'unit' => 'mm'],
            'width' => ['name' => 'Lățime', 'unit' => 'mm'],
            'height' => ['name' => 'Înălțime', 'unit' => 'mm'],
            'slot_width' => ['name' => 'Sloturi ocupate', 'unit' => ''],
            'cooling_type' => ['name' => 'Tip răcire', 'unit' => ''],
            
            // Other
            'release_date' => ['name' => 'Data lansare', 'unit' => ''],
            'price' => ['name' => 'Preț lansare', 'unit' => 'USD'],
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
        echo "Starting import of {$limit} graphics cards from Versus.com...\n";

        try {
            $gpus = $this->scrapeGPUList($limit);
            
            $imported = 0;
            foreach ($gpus as $gpuData) {
                try {
                    $this->importGPU($gpuData);
                    echo "✓ Imported: {$gpuData['name']}\n";
                    $imported++;
                    sleep(2); // Rate limiting
                } catch (\Exception $e) {
                    echo "✗ Error importing {$gpuData['name']}: " . $e->getMessage() . "\n";
                    Log::error("Error importing GPU: " . $e->getMessage(), ['gpu' => $gpuData]);
                }
            }

            echo "✓ Import completed successfully!\n";
            echo "Imported: {$imported} / " . count($gpus) . " graphics cards\n";
            
        } catch (\Exception $e) {
            echo "✗ Import failed: " . $e->getMessage() . "\n";
            Log::error("GPU import failed: " . $e->getMessage());
            throw $e;
        }
    }

    protected function scrapeGPUList($limit)
    {
        try {
            echo "Fetching GPU list from {$this->baseUrl}/en/graphics-card...\n";
            
            $response = Http::timeout(30)
                ->withoutVerifying()
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                ])
                ->get($this->baseUrl . '/en/graphics-card');
            
            if (!$response->successful()) {
                throw new \Exception("Failed to fetch GPU list: " . $response->status());
            }
            
            $html = $response->body();
            $gpus = [];
            $seenSlugs = [];
            
            // Extract GPU URLs from the page
            preg_match_all('/<a[^>]+href=["\'](\/en\/[^"\']+)["\']/i', $html, $matches);
            
            foreach ($matches[1] as $path) {
                $slug = str_replace('/en/', '', $path);
                
                // Skip non-GPU pages
                $excludePatterns = ['categories', 'search', 'compare', 'news', 'blog', 'about'];
                $isExcluded = false;
                foreach ($excludePatterns as $pattern) {
                    if (strpos($slug, $pattern) !== false) {
                        $isExcluded = true;
                        break;
                    }
                }
                
                // Must contain GPU brand patterns
                $gpuBrands = ['nvidia', 'geforce', 'rtx', 'gtx', 'amd', 'radeon', 'rx', 'intel-arc'];
                $hasGPUBrand = false;
                foreach ($gpuBrands as $brand) {
                    if (strpos(strtolower($slug), $brand) !== false) {
                        $hasGPUBrand = true;
                        break;
                    }
                }
                
                if ($isExcluded || !$hasGPUBrand || isset($seenSlugs[$slug])) {
                    continue;
                }
                
                $seenSlugs[$slug] = true;
                
                // Generate name from slug
                $name = ucwords(str_replace('-', ' ', $slug));
                
                $gpus[] = [
                    'name' => $name,
                    'slug' => $slug,
                    'url' => $this->baseUrl . '/en/' . $slug
                ];
                
                if (count($gpus) >= $limit) {
                    break;
                }
            }
            
            if (count($gpus) === 0) {
                throw new \Exception("No GPUs found on page");
            }
            
            echo "✓ Found " . count($gpus) . " graphics cards\n";
            return $gpus;
            
        } catch (\Exception $e) {
            Log::error("Error scraping GPU list: " . $e->getMessage());
            echo "✗ Error scraping GPU list, using fallback list\n";
            return $this->getFallbackGPUList($limit);
        }
    }

    protected function getFallbackGPUList($limit)
    {
        // Comprehensive fallback list of popular GPUs
        return array_slice([
            // NVIDIA RTX 40 Series
            ['name' => 'NVIDIA GeForce RTX 4090', 'slug' => 'nvidia-geforce-rtx-4090', 'url' => 'https://versus.com/en/nvidia-geforce-rtx-4090'],
            ['name' => 'NVIDIA GeForce RTX 4080 Super', 'slug' => 'nvidia-geforce-rtx-4080-super', 'url' => 'https://versus.com/en/nvidia-geforce-rtx-4080-super'],
            ['name' => 'NVIDIA GeForce RTX 4080', 'slug' => 'nvidia-geforce-rtx-4080', 'url' => 'https://versus.com/en/nvidia-geforce-rtx-4080'],
            ['name' => 'NVIDIA GeForce RTX 4070 Ti Super', 'slug' => 'nvidia-geforce-rtx-4070-ti-super', 'url' => 'https://versus.com/en/nvidia-geforce-rtx-4070-ti-super'],
            ['name' => 'NVIDIA GeForce RTX 4070 Ti', 'slug' => 'nvidia-geforce-rtx-4070-ti', 'url' => 'https://versus.com/en/nvidia-geforce-rtx-4070-ti'],
            ['name' => 'NVIDIA GeForce RTX 4070 Super', 'slug' => 'nvidia-geforce-rtx-4070-super', 'url' => 'https://versus.com/en/nvidia-geforce-rtx-4070-super'],
            ['name' => 'NVIDIA GeForce RTX 4070', 'slug' => 'nvidia-geforce-rtx-4070', 'url' => 'https://versus.com/en/nvidia-geforce-rtx-4070'],
            ['name' => 'NVIDIA GeForce RTX 4060 Ti', 'slug' => 'nvidia-geforce-rtx-4060-ti', 'url' => 'https://versus.com/en/nvidia-geforce-rtx-4060-ti'],
            ['name' => 'NVIDIA GeForce RTX 4060', 'slug' => 'nvidia-geforce-rtx-4060', 'url' => 'https://versus.com/en/nvidia-geforce-rtx-4060'],
            
            // NVIDIA RTX 30 Series
            ['name' => 'NVIDIA GeForce RTX 3090 Ti', 'slug' => 'nvidia-geforce-rtx-3090-ti', 'url' => 'https://versus.com/en/nvidia-geforce-rtx-3090-ti'],
            ['name' => 'NVIDIA GeForce RTX 3090', 'slug' => 'nvidia-geforce-rtx-3090', 'url' => 'https://versus.com/en/nvidia-geforce-rtx-3090'],
            ['name' => 'NVIDIA GeForce RTX 3080 Ti', 'slug' => 'nvidia-geforce-rtx-3080-ti', 'url' => 'https://versus.com/en/nvidia-geforce-rtx-3080-ti'],
            ['name' => 'NVIDIA GeForce RTX 3080', 'slug' => 'nvidia-geforce-rtx-3080', 'url' => 'https://versus.com/en/nvidia-geforce-rtx-3080'],
            ['name' => 'NVIDIA GeForce RTX 3070 Ti', 'slug' => 'nvidia-geforce-rtx-3070-ti', 'url' => 'https://versus.com/en/nvidia-geforce-rtx-3070-ti'],
            ['name' => 'NVIDIA GeForce RTX 3070', 'slug' => 'nvidia-geforce-rtx-3070', 'url' => 'https://versus.com/en/nvidia-geforce-rtx-3070'],
            ['name' => 'NVIDIA GeForce RTX 3060 Ti', 'slug' => 'nvidia-geforce-rtx-3060-ti', 'url' => 'https://versus.com/en/nvidia-geforce-rtx-3060-ti'],
            ['name' => 'NVIDIA GeForce RTX 3060', 'slug' => 'nvidia-geforce-rtx-3060', 'url' => 'https://versus.com/en/nvidia-geforce-rtx-3060'],
            
            // AMD RX 7000 Series
            ['name' => 'AMD Radeon RX 7900 XTX', 'slug' => 'amd-radeon-rx-7900-xtx', 'url' => 'https://versus.com/en/amd-radeon-rx-7900-xtx'],
            ['name' => 'AMD Radeon RX 7900 XT', 'slug' => 'amd-radeon-rx-7900-xt', 'url' => 'https://versus.com/en/amd-radeon-rx-7900-xt'],
            ['name' => 'AMD Radeon RX 7800 XT', 'slug' => 'amd-radeon-rx-7800-xt', 'url' => 'https://versus.com/en/amd-radeon-rx-7800-xt'],
            ['name' => 'AMD Radeon RX 7700 XT', 'slug' => 'amd-radeon-rx-7700-xt', 'url' => 'https://versus.com/en/amd-radeon-rx-7700-xt'],
            ['name' => 'AMD Radeon RX 7600 XT', 'slug' => 'amd-radeon-rx-7600-xt', 'url' => 'https://versus.com/en/amd-radeon-rx-7600-xt'],
            ['name' => 'AMD Radeon RX 7600', 'slug' => 'amd-radeon-rx-7600', 'url' => 'https://versus.com/en/amd-radeon-rx-7600'],
            
            // AMD RX 6000 Series
            ['name' => 'AMD Radeon RX 6950 XT', 'slug' => 'amd-radeon-rx-6950-xt', 'url' => 'https://versus.com/en/amd-radeon-rx-6950-xt'],
            ['name' => 'AMD Radeon RX 6900 XT', 'slug' => 'amd-radeon-rx-6900-xt', 'url' => 'https://versus.com/en/amd-radeon-rx-6900-xt'],
            ['name' => 'AMD Radeon RX 6800 XT', 'slug' => 'amd-radeon-rx-6800-xt', 'url' => 'https://versus.com/en/amd-radeon-rx-6800-xt'],
            ['name' => 'AMD Radeon RX 6800', 'slug' => 'amd-radeon-rx-6800', 'url' => 'https://versus.com/en/amd-radeon-rx-6800'],
            ['name' => 'AMD Radeon RX 6700 XT', 'slug' => 'amd-radeon-rx-6700-xt', 'url' => 'https://versus.com/en/amd-radeon-rx-6700-xt'],
            ['name' => 'AMD Radeon RX 6600 XT', 'slug' => 'amd-radeon-rx-6600-xt', 'url' => 'https://versus.com/en/amd-radeon-rx-6600-xt'],
            ['name' => 'AMD Radeon RX 6600', 'slug' => 'amd-radeon-rx-6600', 'url' => 'https://versus.com/en/amd-radeon-rx-6600'],
            
            // Intel Arc
            ['name' => 'Intel Arc A770', 'slug' => 'intel-arc-a770', 'url' => 'https://versus.com/en/intel-arc-a770'],
            ['name' => 'Intel Arc A750', 'slug' => 'intel-arc-a750', 'url' => 'https://versus.com/en/intel-arc-a750'],
            ['name' => 'Intel Arc A580', 'slug' => 'intel-arc-a580', 'url' => 'https://versus.com/en/intel-arc-a580'],
        ], 0, $limit);
    }

    protected function importGPU($gpuData)
    {
        $specs = $this->scrapeGPUSpecs($gpuData['url']);
        
        // Extract brand from name
        $brand = 'Unknown';
        if (stripos($gpuData['name'], 'nvidia') !== false || stripos($gpuData['name'], 'geforce') !== false) {
            $brand = 'NVIDIA';
        } elseif (stripos($gpuData['name'], 'amd') !== false || stripos($gpuData['name'], 'radeon') !== false) {
            $brand = 'AMD';
        } elseif (stripos($gpuData['name'], 'intel') !== false) {
            $brand = 'Intel';
        }

        Product::withoutSyncingToSearch(function () use ($gpuData, $brand, $specs) {
            // Create or update product
            $product = Product::updateOrCreate(
                [
                    'product_type_id' => $this->productType->id,
                    'brand' => $brand,
                    'name' => $gpuData['name'],
                ],
                [
                    'category_id' => $this->category->id,
                    'mpn' => $gpuData['slug'],
                    'slug' => $gpuData['slug'],
                    'short_desc' => $specs['description'] ?? "Placă video {$gpuData['name']}",
                    'image_url' => $specs['image_url'] ?? $this->generatePlaceholderImage($gpuData['name']),
                    'source_url' => "https://versus.com/en/{$gpuData['slug']}",
                    'score' => $specs['score'] ?? 75,
                ]
            );

            // Save all specifications
            $this->saveGPUSpecifications($product, $specs);

            // Create offer
            $this->createOffer($product, $specs['price'] ?? null);

            return $product;
        });
    }

    protected function scrapeGPUSpecs($url)
    {
        try {
            echo "  Fetching specs from {$url}...\n";
            
            // Use Puppeteer for dynamic content
            $scraper = new PuppeteerScraper();
            $result = $scraper->scrapeAndParse($url);
            
            $specs = $result['specs'];
            
            // Add image URL if found
            if (!empty($result['image_url'])) {
                $specs['image_url'] = $result['image_url'];
            }
            
            // Try to extract additional metadata from HTML if needed
            $html = $scraper->scrape($url);
            
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadHTML($html);
            libxml_clear_errors();
            
            $xpath = new \DOMXPath($dom);
            
            // Extract description if not in specs
            if (empty($specs['description'])) {
                $descNodes = $xpath->query('//meta[@name="description"]/@content');
                if ($descNodes->length > 0) {
                    $specs['description'] = $descNodes->item(0)->nodeValue;
                }
            }
            
            // Extract score if available
            $scoreNodes = $xpath->query('//*[@data-score]');
            if ($scoreNodes->length > 0) {
                $specs['score'] = (int)$scoreNodes->item(0)->getAttribute('data-score');
            }
            
            echo "  ✓ Found " . count($specs) . " specifications\n";
            
            return $specs;
            
        } catch (\Exception $e) {
            Log::error("Error scraping GPU specs: " . $e->getMessage(), ['url' => $url]);
            echo "  ✗ Error: " . $e->getMessage() . "\n";
            return [];
        }
    }

    protected function saveGPUSpecifications($product, $specs)
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
                // Extract numeric value with unit
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
        $finalPrice = $price ?? 1999.00; // Default price
        
        Offer::updateOrCreate(
            [
                'product_id' => $product->id,
                'seller_name' => 'Versus.com',
            ],
            [
                'url' => "https://versus.com/en/{$product->mpn}",
                'price' => $finalPrice,
                'currency' => 'RON',
                'in_stock' => true,
                'merchant' => 'Versus.com',
                'url_affiliate' => "https://versus.com/en/{$product->mpn}",
            ]
        );
    }

    protected function generatePlaceholderImage($gpuName)
    {
        return "https://ui-avatars.com/api/?name=" . urlencode($gpuName) . "&size=400&background=random";
    }
}
