<?php

/**
 * Huawei Product Scraper
 * Scrapes products from consumer.huawei.com/ro/phones/
 */

require __DIR__.'/vendor/autoload.php';

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

$client = new Client([
    'timeout' => 30,
    'verify' => false,
    'headers' => [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language' => 'ro-RO,ro;q=0.9,en-US;q=0.8,en;q=0.7',
    ]
]);

echo "🔍 Scraping Huawei phones from Romania website..." . PHP_EOL . PHP_EOL;

try {
    // Get main phones page
    echo "📄 Fetching phones list..." . PHP_EOL;
    $response = $client->get('https://consumer.huawei.com/ro/phones/');
    $html = $response->getBody()->getContents();
    
    // Parse HTML
    $crawler = new Crawler($html);
    
    $products = [];
    
    // Find all product links
    // Note: The actual selectors will need to be adjusted based on the real HTML structure
    $crawler->filter('.product-item, .phone-item, a[href*="/phones/"]')->each(function (Crawler $node) use (&$products, $client) {
        try {
            $productUrl = $node->attr('href');
            
            // Skip if not a product page
            if (!$productUrl || strpos($productUrl, '/phones/') === false) {
                return;
            }
            
            // Make URL absolute
            if (strpos($productUrl, 'http') !== 0) {
                $productUrl = 'https://consumer.huawei.com' . $productUrl;
            }
            
            // Skip duplicates
            if (isset($products[$productUrl])) {
                return;
            }
            
            echo "  → Found product: " . $productUrl . PHP_EOL;
            
            // Get product name from link text or image alt
            $name = trim($node->text());
            if (empty($name)) {
                $imgNode = $node->filter('img')->first();
                if ($imgNode->count() > 0) {
                    $name = $imgNode->attr('alt');
                }
            }
            
            // Get product image
            $imageUrl = null;
            $imgNode = $node->filter('img')->first();
            if ($imgNode->count() > 0) {
                $imageUrl = $imgNode->attr('src') ?: $imgNode->attr('data-src');
                if ($imageUrl && strpos($imageUrl, 'http') !== 0) {
                    $imageUrl = 'https://consumer.huawei.com' . $imageUrl;
                }
            }
            
            $products[$productUrl] = [
                'name' => $name,
                'url' => $productUrl,
                'image' => $imageUrl,
            ];
            
        } catch (\Exception $e) {
            echo "  ✗ Error parsing product: " . $e->getMessage() . PHP_EOL;
        }
    });
    
    echo PHP_EOL . "✅ Found " . count($products) . " products" . PHP_EOL . PHP_EOL;
    
    // Now scrape details for each product
    $csvData = [];
    $csvData[] = [
        'brand', 'series', 'name', 'model_mpn', 'ean_gtin', 'product_url', 'price_ron', 
        'currency', 'availability', 'display', 'refresh_rate_hz', 'chipset', 'gpu', 
        'ram_gb', 'storage_gb', 'expandable_storage', 'rear_camera', 'front_camera', 
        'video', 'battery_mah', 'charging_watt', 'os', 'ui', 'network', 'sim', 
        'dimensions_mm', 'weight_g', 'ip_rating', 'colors', 'wifi', 'bluetooth', 
        'nfc', 'usb', 'audio_jack', 'image_urls', 'specs_raw_json'
    ];
    
    foreach ($products as $product) {
        echo "📱 Scraping: " . $product['name'] . PHP_EOL;
        
        try {
            sleep(2); // Be nice to the server
            
            $response = $client->get($product['url']);
            $html = $response->getBody()->getContents();
            $crawler = new Crawler($html);
            
            // Extract specifications (selectors need to be adjusted based on actual HTML)
            $specs = [
                'brand' => 'Huawei',
                'series' => '',
                'name' => $product['name'],
                'product_url' => $product['url'],
                'image_urls' => $product['image'],
                'display' => '',
                'refresh_rate_hz' => '',
                'chipset' => '',
                'ram_gb' => '',
                'storage_gb' => '',
                'rear_camera' => '',
                'front_camera' => '',
                'battery_mah' => '',
                'os' => 'HarmonyOS',
                'network' => '5G',
            ];
            
            // Try to extract specifications from the page
            // This is a generic approach - needs to be customized
            $crawler->filter('.spec-item, .specification, [class*="spec"]')->each(function (Crawler $node) use (&$specs) {
                $text = strtolower(trim($node->text()));
                
                if (strpos($text, 'display') !== false || strpos($text, 'ecran') !== false) {
                    $specs['display'] = trim($node->text());
                }
                if (strpos($text, 'processor') !== false || strpos($text, 'procesor') !== false) {
                    $specs['chipset'] = trim($node->text());
                }
                if (strpos($text, 'ram') !== false) {
                    preg_match('/(\d+)\s*GB/', $text, $matches);
                    if (!empty($matches[1])) {
                        $specs['ram_gb'] = $matches[1];
                    }
                }
                if (strpos($text, 'storage') !== false || strpos($text, 'stocare') !== false) {
                    preg_match('/(\d+)\s*GB/', $text, $matches);
                    if (!empty($matches[1])) {
                        $specs['storage_gb'] = $matches[1];
                    }
                }
                if (strpos($text, 'camera') !== false || strpos($text, 'cameră') !== false) {
                    if (strpos($text, 'front') !== false) {
                        $specs['front_camera'] = trim($node->text());
                    } else {
                        $specs['rear_camera'] = trim($node->text());
                    }
                }
                if (strpos($text, 'battery') !== false || strpos($text, 'baterie') !== false) {
                    preg_match('/(\d+)\s*mAh/', $text, $matches);
                    if (!empty($matches[1])) {
                        $specs['battery_mah'] = $matches[1];
                    }
                }
            });
            
            // Add to CSV data
            $csvData[] = [
                $specs['brand'],
                $specs['series'],
                $specs['name'],
                '', // mpn
                '', // ean
                $specs['product_url'],
                '', // price
                'RON',
                'in_stock',
                $specs['display'],
                $specs['refresh_rate_hz'],
                $specs['chipset'],
                '', // gpu
                $specs['ram_gb'],
                $specs['storage_gb'],
                'No',
                $specs['rear_camera'],
                $specs['front_camera'],
                '', // video
                $specs['battery_mah'],
                '', // charging
                $specs['os'],
                'EMUI',
                $specs['network'],
                'Dual SIM',
                '', // dimensions
                '', // weight
                '', // ip_rating
                '', // colors
                'Wi-Fi 6',
                '5.2',
                'Yes',
                'USB Type-C',
                'No',
                $specs['image_urls'],
                ''
            ];
            
            echo "  ✓ Scraped successfully" . PHP_EOL;
            
        } catch (\Exception $e) {
            echo "  ✗ Error: " . $e->getMessage() . PHP_EOL;
        }
        
        echo PHP_EOL;
    }
    
    // Write to CSV
    $outputFile = __DIR__ . '/huawei_scraped_products.csv';
    $fp = fopen($outputFile, 'w');
    
    foreach ($csvData as $row) {
        fputcsv($fp, $row);
    }
    
    fclose($fp);
    
    echo "✅ Scraping complete!" . PHP_EOL;
    echo "📁 Data saved to: " . $outputFile . PHP_EOL;
    echo "📊 Total products: " . (count($csvData) - 1) . PHP_EOL;
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
