<?php

namespace App\Services\Importers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IcecatImporter
{
    protected string $baseUrl = 'https://live.icecat.biz/api';
    protected ?string $username;
    protected ?string $password;
    
    public function __construct()
    {
        // Open Icecat - free tier credentials
        // Register at: https://icecat.biz/en/menu/partners/index.html
        $this->username = env('ICECAT_USERNAME');
        $this->password = env('ICECAT_PASSWORD');
    }
    
    /**
     * Get product data by EAN/GTIN
     */
    public function getProductByEAN(string $ean): ?array
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(30)
                ->get("{$this->baseUrl}/?ean={$ean}&lang=ro");
            
            if ($response->successful()) {
                $data = $response->json();
                return $this->parseIcecatResponse($data);
            }
            
            Log::warning("Icecat EAN lookup failed for {$ean}", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error("Icecat API error for EAN {$ean}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get product data by Brand + Product Name
     */
    public function getProductByBrandAndName(string $brand, string $productName): ?array
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(30)
                ->get("{$this->baseUrl}/?prod_id={$productName}&brand={$brand}&lang=ro");
            
            if ($response->successful()) {
                $data = $response->json();
                return $this->parseIcecatResponse($data);
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error("Icecat API error for {$brand} {$productName}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Parse Icecat API response into standardized format
     */
    protected function parseIcecatResponse(array $data): array
    {
        if (!isset($data['data'])) {
            return [];
        }
        
        $product = $data['data'];
        
        $parsed = [
            'name' => $product['GeneralInfo']['Title'] ?? '',
            'brand' => $product['GeneralInfo']['Brand'] ?? '',
            'mpn' => $product['GeneralInfo']['PartCode'] ?? '',
            'ean' => $product['GeneralInfo']['GTIN'][0] ?? '',
            'description' => $product['GeneralInfo']['Description']['LongDesc'] ?? '',
            'short_desc' => $product['GeneralInfo']['Description']['ShortDesc'] ?? '',
            'category' => $product['GeneralInfo']['Category']['Name']['Value'] ?? '',
            'image_url' => $product['Image']['Pic500x500'] ?? $product['Image']['Pic'] ?? '',
            'images' => [],
            'specifications' => [],
        ];
        
        // Additional product images
        if (isset($product['Gallery'])) {
            foreach ($product['Gallery'] as $image) {
                if (isset($image['Pic500x500'])) {
                    $parsed['images'][] = $image['Pic500x500'];
                }
            }
        }
        
        // Parse specifications
        if (isset($product['FeaturesGroups'])) {
            foreach ($product['FeaturesGroups'] as $group) {
                if (!isset($group['Features'])) {
                    continue;
                }
                
                foreach ($group['Features'] as $feature) {
                    $featureName = $feature['Feature']['Name']['Value'] ?? '';
                    $featureValue = $feature['Value'] ?? $feature['RawValue'] ?? '';
                    
                    if ($featureName && $featureValue) {
                        $parsed['specifications'][$this->normalizeSpecKey($featureName)] = [
                            'name' => $featureName,
                            'value' => $featureValue,
                            'group' => $group['FeatureGroup']['Name']['Value'] ?? 'General'
                        ];
                    }
                }
            }
        }
        
        return $parsed;
    }
    
    /**
     * Normalize specification key for database storage
     */
    protected function normalizeSpecKey(string $name): string
    {
        $key = strtolower($name);
        $key = preg_replace('/[^a-z0-9]+/', '_', $key);
        $key = trim($key, '_');
        return $key;
    }
    
    /**
     * Get refrigerator specifications from Icecat
     */
    public function getRefrigeratorSpecs(string $ean, string $brand, string $model): ?array
    {
        // Try EAN first (most reliable)
        if ($ean) {
            $data = $this->getProductByEAN($ean);
            if ($data) {
                return $this->extractRefrigeratorData($data);
            }
        }
        
        // Fallback to brand + model
        if ($brand && $model) {
            $data = $this->getProductByBrandAndName($brand, $model);
            if ($data) {
                return $this->extractRefrigeratorData($data);
            }
        }
        
        return null;
    }
    
    /**
     * Extract refrigerator-specific data
     */
    protected function extractRefrigeratorData(array $data): array
    {
        $specs = $data['specifications'] ?? [];
        
        return [
            'name' => $data['name'],
            'brand' => $data['brand'],
            'mpn' => $data['mpn'],
            'ean' => $data['ean'],
            'description' => $data['description'],
            'short_desc' => $data['short_desc'],
            'image_url' => $data['image_url'],
            'images' => $data['images'] ?? [],
            'specifications' => [
                // Energy & Efficiency
                'energy_class' => $this->findSpec($specs, ['energy_class', 'energy_efficiency_class', 'energie']),
                'annual_energy_consumption' => $this->findSpec($specs, ['annual_energy_consumption', 'energie_anuala', 'kwh_year']),
                'noise_level' => $this->findSpec($specs, ['noise_level', 'nivel_zgomot', 'zgomot', 'db']),
                'climate_class' => $this->findSpec($specs, ['climate_class', 'clasa_climatica']),
                
                // Capacity
                'total_capacity' => $this->findSpec($specs, ['total_capacity', 'capacitate_totala', 'net_capacity']),
                'fridge_capacity' => $this->findSpec($specs, ['fridge_capacity', 'capacitate_frigider', 'refrigerator_net_capacity']),
                'freezer_capacity' => $this->findSpec($specs, ['freezer_capacity', 'capacitate_congelator', 'freezer_net_capacity']),
                
                // Features
                'no_frost' => $this->findSpec($specs, ['no_frost', 'nofrost', 'frost_free']),
                'freezer_position' => $this->findSpec($specs, ['freezer_position', 'pozitie_congelator', 'freezer_location']),
                'door_type' => $this->findSpec($specs, ['door_type', 'tip_usa', 'number_of_doors']),
                'color' => $this->findSpec($specs, ['color', 'culoare', 'colour']),
                'water_dispenser' => $this->findSpec($specs, ['water_dispenser', 'dozator_apa', 'water_dispenser']),
                'ice_maker' => $this->findSpec($specs, ['ice_maker', 'producator_gheata', 'ice_cube_maker']),
                
                // Dimensions
                'width' => $this->findSpec($specs, ['width', 'latime', 'w']),
                'height' => $this->findSpec($specs, ['height', 'inaltime', 'h']),
                'depth' => $this->findSpec($specs, ['depth', 'adancime', 'd']),
                'weight' => $this->findSpec($specs, ['weight', 'greutate']),
                
                // Technology
                'inverter_compressor' => $this->findSpec($specs, ['inverter', 'inverter_compressor', 'compresor_inverter']),
                'display_type' => $this->findSpec($specs, ['display', 'display_type', 'tip_afisaj']),
                'smart_features' => $this->findSpec($specs, ['smart', 'wifi', 'app_control', 'iot']),
            ]
        ];
    }
    
    /**
     * Find specification value by multiple possible keys
     */
    protected function findSpec(array $specs, array $possibleKeys): ?string
    {
        foreach ($possibleKeys as $key) {
            if (isset($specs[$key])) {
                return $specs[$key]['value'] ?? $specs[$key];
            }
        }
        return null;
    }
    
    /**
     * Test API connection
     */
    public function testConnection(): bool
    {
        try {
            // Test with a known product (Samsung fridge EAN)
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(10)
                ->get("{$this->baseUrl}/?ean=8806094808919&lang=en");
            
            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Icecat connection test failed: " . $e->getMessage());
            return false;
        }
    }
}
