<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GoogleImageService
{
    private $apiKey;
    private $searchEngineId;
    private $baseUrl = 'https://www.googleapis.com/customsearch/v1';

    public function __construct()
    {
        $this->apiKey = config('services.google.api_key');
        $this->searchEngineId = config('services.google.search_engine_id');
    }

    /**
     * Search for product image using Google Custom Search API
     * 
     * @param string $productName Full product name (e.g., "Samsung RB38A7B6AS9/EF")
     * @param string $category Product category for context
     * @return array|null Image data or null if not found
     */
    public function searchProductImage($productName, $category = '')
    {
        try {
            // Build search query
            $query = $this->buildSearchQuery($productName, $category);
            
            Log::info("Google Image Search", [
                'product' => $productName,
                'query' => $query
            ]);

            // Make API request
            $response = Http::timeout(10)->get($this->baseUrl, [
                'key' => $this->apiKey,
                'cx' => $this->searchEngineId,
                'q' => $query,
                'searchType' => 'image',
                'imgSize' => 'large', // large images (400x400+)
                'num' => 5, // get top 5 results
                'safe' => 'off', // no filtering for product images
                'fileType' => 'jpg,png',
            ]);

            if (!$response->successful()) {
                Log::error("Google API Error", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $data = $response->json();

            // Check if we have results
            if (!isset($data['items']) || empty($data['items'])) {
                Log::warning("No images found", ['query' => $query]);
                return null;
            }

            // Return best result
            return $this->selectBestImage($data['items'], $productName);

        } catch (\Exception $e) {
            Log::error("Google Image Search Exception", [
                'product' => $productName,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Build optimized search query for product images
     */
    private function buildSearchQuery($productName, $category)
    {
        // Clean product name
        $clean = $this->cleanProductName($productName);
        
        // Add context terms for better results
        $terms = [$clean];
        
        // Add "official" or "product" for better quality
        $terms[] = 'official product image';
        
        // Add category context
        if ($category) {
            $terms[] = $category;
        }

        return implode(' ', $terms);
    }

    /**
     * Clean product name for search
     */
    private function cleanProductName($name)
    {
        // Remove common unnecessary words
        $remove = ['Serie', 'Series', 'Model', 'Version'];
        $name = str_replace($remove, '', $name);
        
        // Keep brand + model code
        return trim($name);
    }

    /**
     * Select best image from results
     */
    private function selectBestImage($items, $productName)
    {
        // Score each image
        $scored = [];
        
        foreach ($items as $item) {
            $score = 0;
            $url = $item['link'] ?? '';
            $title = strtolower($item['title'] ?? '');
            $context = strtolower($item['snippet'] ?? '');
            
            // Skip if no URL
            if (empty($url)) continue;
            
            // Prefer certain domains (official manufacturers)
            if (preg_match('/samsung\.com|lg\.com|bosch|apple\.com|emag\.ro|altex\.ro/i', $url)) {
                $score += 50;
            }
            
            // Prefer if product code is in title
            $modelCode = $this->extractModelCode($productName);
            if ($modelCode && stripos($title, $modelCode) !== false) {
                $score += 30;
            }
            
            // Prefer larger images
            $width = $item['image']['width'] ?? 0;
            $height = $item['image']['height'] ?? 0;
            if ($width >= 800 && $height >= 800) {
                $score += 20;
            } elseif ($width >= 400 && $height >= 400) {
                $score += 10;
            }
            
            // Prefer white background (common for product images)
            if (stripos($context, 'white background') !== false) {
                $score += 10;
            }
            
            $scored[] = [
                'url' => $url,
                'title' => $item['title'] ?? '',
                'width' => $width,
                'height' => $height,
                'score' => $score,
                'context' => $item['snippet'] ?? ''
            ];
        }

        // Sort by score
        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        // Return best result
        return $scored[0] ?? null;
    }

    /**
     * Extract model code from product name
     */
    private function extractModelCode($productName)
    {
        // Look for model codes like: RB38A7B6AS9, SM-S928B, KGN39VIDT
        if (preg_match('/[A-Z0-9]{6,}/', $productName, $matches)) {
            return $matches[0];
        }
        return null;
    }

    /**
     * Download and store image locally
     */
    public function downloadAndStore($imageUrl, $productSlug)
    {
        try {
            // Download image
            $response = Http::timeout(15)->get($imageUrl);

            if (!$response->successful()) {
                return null;
            }

            // Generate filename
            $extension = $this->getExtensionFromUrl($imageUrl);
            $filename = $productSlug . '-' . time() . '.' . $extension;
            $path = 'products/' . $filename;

            // Store in public disk
            Storage::disk('public')->put($path, $response->body());

            Log::info("Image downloaded", [
                'url' => $imageUrl,
                'path' => $path
            ]);

            return $path;

        } catch (\Exception $e) {
            Log::error("Image download failed", [
                'url' => $imageUrl,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get file extension from URL
     */
    private function getExtensionFromUrl($url)
    {
        $path = parse_url($url, PHP_URL_PATH);
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        
        // Default to jpg if no extension
        return in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp']) ? $ext : 'jpg';
    }

    /**
     * Check remaining API quota (approximate)
     */
    public function checkQuota()
    {
        // Google doesn't provide quota check via API
        // This is a placeholder - track usage in your app
        return [
            'daily_limit' => 100,
            'message' => 'First 100 queries/day are free. Track usage manually.'
        ];
    }
}
