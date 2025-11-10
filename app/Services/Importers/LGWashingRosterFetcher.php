<?php

namespace App\Services\Importers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class LGWashingRosterFetcher
{
    protected string $baseUrl = 'https://www.lg.com';
    protected string $categoryUrl = '/ro/masini-de-spalat';
    protected int $timeout = 30;

    /**
     * Fetch list of washing machine products from LG Romania
     */
    public function fetch(int $limit = 100): array
    {
        $products = [];
        
        try {
            echo "Fetching washing machines from LG Romania...\n";
            
            $url = $this->baseUrl . $this->categoryUrl;
            
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'ro-RO,ro;q=0.9,en;q=0.8',
                ])
                ->get($url);

            if (!$response->successful()) {
                throw new \Exception("Failed to fetch LG catalog: HTTP {$response->status()}");
            }

            $html = $response->body();
            $crawler = new Crawler($html);

            // Try multiple selector patterns for LG's product cards
            $selectors = [
                '.product-item',
                '.product-card',
                '.c-product-item',
                'article[data-product-id]',
                'div[class*="product"]',
            ];

            foreach ($selectors as $selector) {
                try {
                    $crawler->filter($selector)->each(function (Crawler $node) use (&$products, $limit) {
                        if (count($products) >= $limit) {
                            return;
                        }

                        $product = $this->extractProductInfo($node);
                        
                        if ($product && $this->isWashingMachine($product)) {
                            $products[] = $product;
                            echo "  ✓ Found: {$product['name']}\n";
                        }
                    });

                    if (count($products) > 0) {
                        break; // Found products with this selector
                    }
                } catch (\Exception $e) {
                    continue; // Try next selector
                }
            }

            if (empty($products)) {
                // Fallback: Try to find any links to product pages
                $products = $this->fallbackLinkExtraction($crawler, $limit);
            }

            echo "✓ Found " . count($products) . " washing machines\n";
            
        } catch (\Exception $e) {
            Log::error("LG roster fetch failed: " . $e->getMessage());
            echo "✗ Error: " . $e->getMessage() . "\n";
            
            // Return fallback list
            return $this->getFallbackList($limit);
        }

        return array_slice($products, 0, $limit);
    }

    /**
     * Extract product information from DOM node
     */
    protected function extractProductInfo(Crawler $node): ?array
    {
        try {
            // Extract product URL
            $link = $node->filter('a')->first();
            if ($link->count() === 0) {
                return null;
            }

            $url = $link->attr('href');
            if (!$url) {
                return null;
            }

            // Make absolute URL
            if (!str_starts_with($url, 'http')) {
                $url = $this->baseUrl . (str_starts_with($url, '/') ? '' : '/') . $url;
            }

            // Extract product name
            $nameSelectors = [
                '.product-title',
                '.product-name',
                'h3',
                'h4',
                '.c-product-item__title',
                '[class*="title"]',
            ];

            $name = null;
            foreach ($nameSelectors as $selector) {
                try {
                    $nameNode = $node->filter($selector)->first();
                    if ($nameNode->count() > 0) {
                        $name = trim($nameNode->text());
                        if ($name) {
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            if (!$name) {
                $name = basename($url);
            }

            // Extract model number from various sources
            $model = $this->extractModelNumber($node, $name, $url);

            // Extract image
            $image = null;
            try {
                $imgNode = $node->filter('img')->first();
                if ($imgNode->count() > 0) {
                    $image = $imgNode->attr('src') ?: $imgNode->attr('data-src');
                    if ($image && !str_starts_with($image, 'http')) {
                        $image = $this->baseUrl . (str_starts_with($image, '/') ? '' : '/') . $image;
                    }
                }
            } catch (\Exception $e) {
                // Image extraction failed
            }

            return [
                'name' => $name,
                'model' => $model,
                'url' => $url,
                'image' => $image,
                'brand' => 'LG',
            ];

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Extract model number from various sources
     */
    protected function extractModelNumber(Crawler $node, string $name, string $url): ?string
    {
        // Try from dedicated model field
        try {
            $modelNode = $node->filter('[class*="model"], [data-model]')->first();
            if ($modelNode->count() > 0) {
                $model = $modelNode->attr('data-model') ?: $modelNode->text();
                if ($model) {
                    return trim($model);
                }
            }
        } catch (\Exception $e) {
            // Continue to next method
        }

        // Try from name (LG model numbers usually have pattern like F2WV5S8S0E)
        if (preg_match('/([A-Z0-9]{8,})/i', $name, $matches)) {
            return $matches[1];
        }

        // Try from URL
        if (preg_match('/\/([A-Z0-9]{8,})/i', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Check if product is a washing machine
     */
    protected function isWashingMachine(array $product): bool
    {
        $name = strtolower($product['name']);
        $url = strtolower($product['url']);

        $keywords = [
            'spalat',
            'washing',
            'laundry',
            'masina',
            'washer',
        ];

        $excludeKeywords = [
            'uscator',
            'dryer',
            'vase',
            'dish',
        ];

        // Check for washing machine keywords
        $hasKeyword = false;
        foreach ($keywords as $keyword) {
            if (str_contains($name, $keyword) || str_contains($url, $keyword)) {
                $hasKeyword = true;
                break;
            }
        }

        if (!$hasKeyword) {
            return false;
        }

        // Exclude dryers and dishwashers
        foreach ($excludeKeywords as $keyword) {
            if (str_contains($name, $keyword) || str_contains($url, $keyword)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Fallback method to extract product links
     */
    protected function fallbackLinkExtraction(Crawler $crawler, int $limit): array
    {
        $products = [];

        try {
            $crawler->filter('a[href*="product"], a[href*="masini-de-spalat"]')->each(function (Crawler $node) use (&$products, $limit) {
                if (count($products) >= $limit) {
                    return;
                }

                $url = $node->attr('href');
                if (!$url) {
                    return;
                }

                // Make absolute URL
                if (!str_starts_with($url, 'http')) {
                    $url = $this->baseUrl . (str_starts_with($url, '/') ? '' : '/') . $url;
                }

                $name = trim($node->text());
                if (!$name) {
                    $name = basename($url);
                }

                $product = [
                    'name' => $name,
                    'model' => null,
                    'url' => $url,
                    'image' => null,
                    'brand' => 'LG',
                ];

                if ($this->isWashingMachine($product)) {
                    $products[] = $product;
                }
            });
        } catch (\Exception $e) {
            // Fallback extraction failed
        }

        return $products;
    }

    /**
     * Get hardcoded fallback list of LG washing machines
     */
    protected function getFallbackList(int $limit): array
    {
        $fallbacks = [
            ['name' => 'LG F2WV5S8S0E', 'model' => 'F2WV5S8S0E', 'url' => 'https://www.lg.com/ro/masini-de-spalat/f2wv5s8s0e', 'brand' => 'LG'],
            ['name' => 'LG F4WV708P1E', 'model' => 'F4WV708P1E', 'url' => 'https://www.lg.com/ro/masini-de-spalat/f4wv708p1e', 'brand' => 'LG'],
            ['name' => 'LG F2WV9S8P2E', 'model' => 'F2WV9S8P2E', 'url' => 'https://www.lg.com/ro/masini-de-spalat/f2wv9s8p2e', 'brand' => 'LG'],
            ['name' => 'LG F4WV509S0E', 'model' => 'F4WV509S0E', 'url' => 'https://www.lg.com/ro/masini-de-spalat/f4wv509s0e', 'brand' => 'LG'],
            ['name' => 'LG F2WV5S8S0', 'model' => 'F2WV5S8S0', 'url' => 'https://www.lg.com/ro/masini-de-spalat/f2wv5s8s0', 'brand' => 'LG'],
            ['name' => 'LG F4WV710P2E', 'model' => 'F4WV710P2E', 'url' => 'https://www.lg.com/ro/masini-de-spalat/f4wv710p2e', 'brand' => 'LG'],
            ['name' => 'LG F4V709STSA', 'model' => 'F4V709STSA', 'url' => 'https://www.lg.com/ro/masini-de-spalat/f4v709stsa', 'brand' => 'LG'],
            ['name' => 'LG F2WV3S6S0E', 'model' => 'F2WV3S6S0E', 'url' => 'https://www.lg.com/ro/masini-de-spalat/f2wv3s6s0e', 'brand' => 'LG'],
            ['name' => 'LG F4WN409S0T', 'model' => 'F4WN409S0T', 'url' => 'https://www.lg.com/ro/masini-de-spalat/f4wn409s0t', 'brand' => 'LG'],
            ['name' => 'LG F2V5GS0W', 'model' => 'F2V5GS0W', 'url' => 'https://www.lg.com/ro/masini-de-spalat/f2v5gs0w', 'brand' => 'LG'],
        ];

        echo "⚠ Using fallback list of LG washing machines\n";
        
        return array_slice($fallbacks, 0, $limit);
    }
}
