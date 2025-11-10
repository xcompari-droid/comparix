<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OpenIcecatClient
{
    protected string $baseUrl;
    protected ?string $token;
    protected int $timeout = 30;
    protected int $cacheTime = 3600; // 1 hour

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.icecat.base_url', 'https://api.openicecat.org/api/v1'), '/');
        $this->token = config('services.icecat.token');
    }

    /**
     * Search product by EAN/GTIN
     */
    public function searchByGtin(string $gtin, string $lang = 'ro'): ?array
    {
        $cacheKey = "icecat_gtin_{$gtin}_{$lang}";
        
        return Cache::remember($cacheKey, $this->cacheTime, function () use ($gtin, $lang) {
            try {
                $response = $this->makeRequest("/products/search", [
                    'gtin' => $gtin,
                    'lang' => $lang,
                ]);

                if ($response && isset($response['data']) && !empty($response['data'])) {
                    return $response['data'][0] ?? null;
                }

                return null;
            } catch (\Exception $e) {
                Log::warning("Icecat GTIN search failed for {$gtin}: " . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Search product by brand and model
     */
    public function searchByBrandModel(string $brand, string $model, string $lang = 'ro'): ?array
    {
        $cacheKey = "icecat_brand_" . md5("{$brand}_{$model}_{$lang}");
        
        return Cache::remember($cacheKey, $this->cacheTime, function () use ($brand, $model, $lang) {
            try {
                $response = $this->makeRequest("/products/search", [
                    'brand' => $brand,
                    'model' => $model,
                    'lang' => $lang,
                ]);

                if ($response && isset($response['data']) && !empty($response['data'])) {
                    // Return first match
                    return $response['data'][0] ?? null;
                }

                return null;
            } catch (\Exception $e) {
                Log::warning("Icecat brand/model search failed for {$brand} {$model}: " . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Get full product details by product ID
     */
    public function getProductById(int $productId, string $lang = 'ro'): ?array
    {
        $cacheKey = "icecat_product_{$productId}_{$lang}";
        
        return Cache::remember($cacheKey, $this->cacheTime, function () use ($productId, $lang) {
            try {
                $response = $this->makeRequest("/products/{$productId}", [
                    'lang' => $lang,
                ]);

                return $response['data'] ?? null;
            } catch (\Exception $e) {
                Log::warning("Icecat product fetch failed for ID {$productId}: " . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Extract specifications from Icecat product data
     */
    public function extractSpecifications(array $productData): array
    {
        $specs = [];

        // Extract from features/specifications array
        if (isset($productData['features']) && is_array($productData['features'])) {
            foreach ($productData['features'] as $feature) {
                $name = $feature['name'] ?? $feature['feature_name'] ?? null;
                $value = $feature['value'] ?? $feature['feature_value'] ?? null;

                if ($name && $value !== null) {
                    $key = $this->normalizeSpecKey($name);
                    $specs[$key] = [
                        'name' => $name,
                        'value' => $value,
                        'unit' => $feature['unit'] ?? '',
                    ];
                }
            }
        }

        // Extract from specifications (alternative structure)
        if (isset($productData['specifications']) && is_array($productData['specifications'])) {
            foreach ($productData['specifications'] as $spec) {
                $name = $spec['name'] ?? $spec['spec_name'] ?? null;
                $value = $spec['value'] ?? $spec['spec_value'] ?? null;

                if ($name && $value !== null) {
                    $key = $this->normalizeSpecKey($name);
                    $specs[$key] = [
                        'name' => $name,
                        'value' => $value,
                        'unit' => $spec['unit'] ?? '',
                    ];
                }
            }
        }

        // Extract from general info
        if (isset($productData['general_info']) && is_array($productData['general_info'])) {
            foreach ($productData['general_info'] as $key => $value) {
                if ($value && !isset($specs[$key])) {
                    $specs[$key] = [
                        'name' => ucfirst(str_replace('_', ' ', $key)),
                        'value' => $value,
                        'unit' => '',
                    ];
                }
            }
        }

        return $specs;
    }

    /**
     * Extract images from product data
     */
    public function extractImages(array $productData): array
    {
        $images = [];

        // Main product image
        if (isset($productData['image_url'])) {
            $images[] = $productData['image_url'];
        }

        if (isset($productData['image'])) {
            $images[] = $productData['image'];
        }

        // Gallery images
        if (isset($productData['images']) && is_array($productData['images'])) {
            foreach ($productData['images'] as $image) {
                if (is_string($image)) {
                    $images[] = $image;
                } elseif (isset($image['url'])) {
                    $images[] = $image['url'];
                } elseif (isset($image['high_pic'])) {
                    $images[] = $image['high_pic'];
                } elseif (isset($image['low_pic'])) {
                    $images[] = $image['low_pic'];
                }
            }
        }

        // Gallery (alternative structure)
        if (isset($productData['gallery']) && is_array($productData['gallery'])) {
            foreach ($productData['gallery'] as $image) {
                if (is_string($image)) {
                    $images[] = $image;
                } elseif (isset($image['url'])) {
                    $images[] = $image['url'];
                }
            }
        }

        return array_unique(array_filter($images));
    }

    /**
     * Make HTTP request to Icecat API
     */
    protected function makeRequest(string $endpoint, array $params = []): ?array
    {
        $url = $this->baseUrl . $endpoint;

        $headers = [
            'Accept' => 'application/json',
            'User-Agent' => 'Comparix/1.0',
        ];

        if ($this->token) {
            $headers['Authorization'] = 'Bearer ' . $this->token;
        }

        $response = Http::timeout($this->timeout)
            ->withHeaders($headers)
            ->withoutVerifying() // For development
            ->get($url, $params);

        if (!$response->successful()) {
            $status = $response->status();
            $body = $response->body();
            
            Log::error("Icecat API error", [
                'url' => $url,
                'status' => $status,
                'response' => substr($body, 0, 500),
            ]);

            throw new \Exception("Icecat API returned status {$status}");
        }

        return $response->json();
    }

    /**
     * Normalize specification key for database storage
     */
    protected function normalizeSpecKey(string $name): string
    {
        $key = strtolower(trim($name));
        $key = preg_replace('/[^a-z0-9]+/', '_', $key);
        $key = trim($key, '_');
        
        return $key;
    }

    /**
     * Clear cache for specific product
     */
    public function clearCache(string $identifier): void
    {
        $patterns = [
            "icecat_gtin_{$identifier}_*",
            "icecat_brand_*{$identifier}*",
            "icecat_product_{$identifier}_*",
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }
}
