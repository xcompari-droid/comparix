<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;

class PuppeteerScraper
{
    /**
     * Scrape a URL using Puppeteer and return the rendered HTML
     */
    public function scrape(string $url): string
    {
        $scraperPath = base_path('scraper.cjs');
        
        // Ensure scraper exists
        if (!file_exists($scraperPath)) {
            throw new \Exception("Puppeteer scraper not found at: {$scraperPath}");
        }
        
        // Build the command
        $command = "node " . escapeshellarg($scraperPath) . " " . escapeshellarg($url) . " 2>&1";
        
        // Execute and capture output
        $html = shell_exec($command);
        
        if (empty($html)) {
            throw new \Exception("Puppeteer returned empty response for: {$url}");
        }
        
        // Check for error messages in output
        if (stripos($html, 'Error:') !== false || stripos($html, 'failed') !== false) {
            throw new \Exception("Puppeteer error for {$url}: " . substr($html, 0, 200));
        }
        
        return $html;
    }
    
    /**
     * Parse specifications from Versus.com HTML
     * 
     * @param string $html The rendered HTML from Puppeteer
     * @return array Associative array of specifications [name => value]
     */
    public function parseSpecifications(string $html): array
    {
        $dom = new DOMDocument();
        
        // Suppress warnings from malformed HTML
        @$dom->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
        
        $xpath = new DOMXPath($dom);
        $specs = [];
        
        // Find all property containers
        $propertyNodes = $xpath->query("//div[contains(@class, 'Property__property')]");
        
        foreach ($propertyNodes as $propertyNode) {
            // Find the label (specification name)
            $labelNodes = $xpath->query(".//span[contains(@class, 'Property__label')]", $propertyNode);
            if ($labelNodes->length === 0) {
                continue;
            }
            
            $specName = trim($labelNodes->item(0)->textContent);
            if (empty($specName)) {
                continue;
            }
            
            // Find the value - check for Number, String, or Boolean
            $value = null;
            
            // Try Number first
            $numberNodes = $xpath->query(".//p[contains(@class, 'Number__number')]", $propertyNode);
            if ($numberNodes->length > 0) {
                $value = trim($numberNodes->item(0)->textContent);
            }
            
            // Try String
            if ($value === null) {
                $stringNodes = $xpath->query(".//div[contains(@class, 'String__string')]", $propertyNode);
                if ($stringNodes->length > 0) {
                    $value = trim($stringNodes->item(0)->textContent);
                    // Remove rival name suffix if present
                    $value = preg_replace('/\s+Nvidia\s+GeForce\s+RTX\s+\d+/i', '', $value);
                    $value = preg_replace('/\s+AMD\s+Radeon\s+RX\s+\d+/i', '', $value);
                    $value = trim($value);
                }
            }
            
            // Try Boolean
            if ($value === null) {
                // First check for the entire boolean container
                $boolContainerNodes = $xpath->query(".//div[contains(@class, 'Boolean__boolean')]", $propertyNode);
                if ($boolContainerNodes->length > 0) {
                    // Now look for the yes or no span within
                    $yesNodes = $xpath->query(".//span[contains(@class, 'boolean_yes')]", $propertyNode);
                    $noNodes = $xpath->query(".//span[contains(@class, 'boolean_no')]", $propertyNode);
                    
                    if ($yesNodes->length > 0) {
                        $value = 'Yes';
                    } elseif ($noNodes->length > 0) {
                        $value = 'No';
                    }
                }
            }
            
            // Try FuzzyTime (for dates)
            if ($value === null) {
                $dateNodes = $xpath->query(".//span[contains(@class, 'FuzzyTime__fulldate')]", $propertyNode);
                if ($dateNodes->length > 0) {
                    $value = trim($dateNodes->item(0)->textContent);
                }
            }
            
            // Only add non-empty values
            if ($value !== null && $value !== '') {
                // Clean up the spec name (remove colons, normalize)
                $specName = str_replace(':', '', $specName);
                $specName = trim($specName);
                
                // Convert to snake_case for consistency
                $specKey = strtolower(preg_replace('/\s+/', '_', $specName));
                $specKey = preg_replace('/[^a-z0-9_]/', '', $specKey);
                
                $specs[$specKey] = $value;
            }
        }
        
        return $specs;
    }
    
    /**
     * Extract image URL from Versus.com HTML
     * 
     * @param string $html The rendered HTML from Puppeteer
     * @return string|null Image URL or null if not found
     */
    public function extractImageUrl(string $html): ?string
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
        
        $xpath = new DOMXPath($dom);
        
        // Try to find the main product image
        // Versus.com typically uses img tags with specific classes or in hero sections
        $imageQueries = [
            "//img[contains(@class, 'ProductImage')]/@src",
            "//img[contains(@class, 'hero')]/@src",
            "//div[contains(@class, 'ProductCard')]//img/@src",
            "//meta[@property='og:image']/@content",
        ];
        
        foreach ($imageQueries as $query) {
            $results = $xpath->query($query);
            if ($results->length > 0) {
                $imageUrl = $results->item(0)->nodeValue;
                
                // Ensure it's a valid URL
                if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                    return $imageUrl;
                }
                
                // Handle relative URLs
                if (strpos($imageUrl, '//') === 0) {
                    return 'https:' . $imageUrl;
                } elseif (strpos($imageUrl, '/') === 0) {
                    return 'https://versus.com' . $imageUrl;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Full scrape and parse in one call
     * 
     * @param string $url The URL to scrape
     * @return array ['specs' => [...], 'image_url' => '...']
     */
    public function scrapeAndParse(string $url): array
    {
        $html = $this->scrape($url);
        
        return [
            'specs' => $this->parseSpecifications($html),
            'image_url' => $this->extractImageUrl($html),
        ];
    }
}
