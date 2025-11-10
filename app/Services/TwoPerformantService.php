<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class TwoPerformantService
{
    protected $client;
    protected $apiKey;
    protected $uniqueCode;
    
    public function __construct()
    {
        $this->apiKey = config('services.2performant.api_key');
        $this->uniqueCode = config('services.2performant.unique_code');
        
        $this->client = new Client([
            'base_uri' => 'https://api.2performant.com/',
            'headers' => [
                'Accept' => 'application/json',
            ],
            'verify' => false,
        ]);
    }
    
    /**
     * Search for products
     */
    public function searchProducts($query, $limit = 20)
    {
        try {
            $response = $this->client->get('affiliate/products', [
                'query' => [
                    'query' => $query,
                    'limit' => $limit,
                    'api_key' => $this->apiKey,
                ]
            ]);
            
            $data = json_decode($response->getBody(), true);
            return $data['products'] ?? [];
            
        } catch (\Exception $e) {
            Log::error('2Performant API Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get product by ID
     */
    public function getProduct($productId)
    {
        try {
            $response = $this->client->get("affiliate/products/{$productId}", [
                'query' => [
                    'api_key' => $this->apiKey,
                ]
            ]);
            
            return json_decode($response->getBody(), true);
            
        } catch (\Exception $e) {
            Log::error('2Performant API Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get affiliate link for product
     */
    public function getAffiliateLink($productUrl)
    {
        if ($this->uniqueCode) {
            return "https://event.2performant.com/events/click?ad_type=quicklink&aff_code={$this->uniqueCode}&unique={$this->uniqueCode}&redirect_to=" . urlencode($productUrl);
        }
        return $productUrl;
    }
}
