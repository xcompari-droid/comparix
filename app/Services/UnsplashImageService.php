<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class UnsplashImageService
{
    private $accessKey;
    private $baseUrl = 'https://api.unsplash.com';
    
    public function __construct()
    {
        $this->accessKey = config('services.unsplash.access_key');
    }
    
    /**
     * Caută imagini pentru un produs
     */
    public function searchProductImage($productName, $category = null)
    {
        if (!$this->accessKey) {
            Log::error('Unsplash access key not configured');
            return null;
        }
        
        $query = $this->buildSearchQuery($productName, $category);
        
        try {
            $response = Http::get("{$this->baseUrl}/search/photos", [
                'client_id' => $this->accessKey,
                'query' => $query,
                'per_page' => 5,
                'orientation' => 'squarish',
            ]);
            
            if ($response->successful()) {
                $results = $response->json()['results'] ?? [];
                
                if (!empty($results)) {
                    return $this->selectBestImage($results);
                }
            }
        } catch (\Exception $e) {
            Log::error("Unsplash API error: " . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Construiește query inteligent bazat pe produs
     */
    private function buildSearchQuery($productName, $category)
    {
        // Extrage brand și model
        $parts = explode(' ', $productName);
        $brand = $parts[0] ?? '';
        
        // Query-uri specifice per categorie
        $categoryQueries = [
            'masini-de-spalat' => 'modern white washing machine front load',
            'frigider' => 'modern stainless steel refrigerator kitchen appliance',
            'casti-wireless' => 'wireless earbuds headphones white background product',
            'smartwatch' => 'smartwatch wearable technology black modern',
            'smartphone' => 'smartphone mobile phone modern black',
            'placa-video' => 'graphics card GPU technology computer hardware',
        ];
        
        if ($category && isset($categoryQueries[$category])) {
            return $categoryQueries[$category];
        }
        
        return "modern {$brand} product white background";
    }
    
    /**
     * Selectează cea mai bună imagine (rezoluție + downloads)
     */
    private function selectBestImage($results)
    {
        // Sortează după popularitate
        usort($results, function($a, $b) {
            $likesA = $a['likes'] ?? 0;
            $likesB = $b['likes'] ?? 0;
            return $likesB - $likesA;
        });
        
        $bestImage = $results[0];
        
        return [
            'id' => $bestImage['id'],
            'url' => $bestImage['urls']['regular'], // 1080px
            'url_small' => $bestImage['urls']['small'], // 400px
            'url_thumb' => $bestImage['urls']['thumb'], // 200px
            'download_url' => $bestImage['links']['download_location'] ?? '',
            'photographer' => $bestImage['user']['name'] ?? 'Unknown',
            'photographer_url' => $bestImage['user']['links']['html'] ?? '',
        ];
    }
    
    /**
     * Descarcă și salvează imaginea local
     */
    public function downloadAndStore($imageUrl, $productSlug)
    {
        try {
            // Download imagine cu timeout
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'user_agent' => 'Comparix/1.0',
                ]
            ]);
            
            $imageData = @file_get_contents($imageUrl, false, $context);
            
            if ($imageData === false) {
                return null;
            }
            
            // Generează nume fișier unic
            $filename = Str::slug($productSlug) . '-' . time() . '.jpg';
            $path = "products/{$filename}";
            
            // Creează directorul dacă nu există
            if (!Storage::disk('public')->exists('products')) {
                Storage::disk('public')->makeDirectory('products');
            }
            
            // Salvează în storage/app/public/products/
            Storage::disk('public')->put($path, $imageData);
            
            // Returnează URL public
            return '/storage/' . $path;
            
        } catch (\Exception $e) {
            Log::error("Failed to download Unsplash image: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Trigger download credit pentru fotograf (obligatoriu per ToS Unsplash)
     */
    public function triggerDownload($downloadUrl)
    {
        if (!$downloadUrl || !$this->accessKey) {
            return;
        }
        
        try {
            Http::get($downloadUrl, [
                'client_id' => $this->accessKey,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to trigger Unsplash download: " . $e->getMessage());
        }
    }
}
