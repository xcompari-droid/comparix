<?php

namespace App\Services\Importers;

use App\Models\Product;
use App\Models\ProductType;
use App\Models\Offer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TwoPerformantImporter
{
    protected array $stats = [
        'processed' => 0,
        'created' => 0,
        'updated' => 0,
        'errors' => 0,
    ];

    /**
     * Import products from CSV file
     */
    public function importFromCsv(string $filePath, int $productTypeId): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $handle = fopen($filePath, 'r');
        $headers = fgetcsv($handle);
        
        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($headers, $row);
            $this->processProduct($data, $productTypeId);
        }
        
        fclose($handle);
        
        return $this->stats;
    }

    /**
     * Import products from XML file
     */
    public function importFromXml(string $filePath, int $productTypeId): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $xml = simplexml_load_file($filePath);
        
        foreach ($xml->product as $productXml) {
            $data = [
                'name' => (string) $productXml->name,
                'brand' => (string) $productXml->brand,
                'price' => (float) $productXml->price,
                'ean' => (string) $productXml->ean,
                'mpn' => (string) $productXml->mpn,
                'url' => (string) $productXml->url,
                'image' => (string) $productXml->image,
                'merchant' => (string) $productXml->merchant,
                'currency' => (string) ($productXml->currency ?? 'RON'),
                'in_stock' => (string) $productXml->availability === 'in stock',
            ];
            
            $this->processProduct($data, $productTypeId);
        }
        
        return $this->stats;
    }

    /**
     * Process individual product from feed
     */
    protected function processProduct(array $data, int $productTypeId): void
    {
        try {
            $this->stats['processed']++;

            // Normalize brand and name
            $brand = $this->normalizeBrand($data['brand'] ?? '');
            $name = $this->normalizeName($data['name'] ?? '');
            $ean = $data['ean'] ?? null;
            $mpn = $data['mpn'] ?? null;

            if (empty($name)) {
                Log::warning('Product skipped: missing name', $data);
                $this->stats['errors']++;
                return;
            }

            // Find or create product
            $product = $this->findOrCreateProduct([
                'product_type_id' => $productTypeId,
                'brand' => $brand,
                'name' => $name,
                'ean' => $ean,
                'mpn' => $mpn,
            ]);

            // Download and attach image if available
            if (!empty($data['image']) && $product->getMedia('images')->isEmpty()) {
                try {
                    $product->addMediaFromUrl($data['image'])
                        ->toMediaCollection('images');
                } catch (\Exception $e) {
                    Log::warning('Failed to download image', [
                        'product_id' => $product->id,
                        'image_url' => $data['image'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Create or update offer
            $this->upsertOffer($product, [
                'merchant' => $data['merchant'] ?? 'Unknown',
                'price' => $data['price'] ?? 0,
                'currency' => $data['currency'] ?? 'RON',
                'url_affiliate' => $data['url'] ?? '',
                'in_stock' => $data['in_stock'] ?? true,
            ]);

        } catch (\Exception $e) {
            $this->stats['errors']++;
            Log::error('Error processing product', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Find or create product by EAN/MPN or brand+name
     */
    protected function findOrCreateProduct(array $data): Product
    {
        $query = Product::where('product_type_id', $data['product_type_id']);

        // Try to find by EAN first
        if (!empty($data['ean'])) {
            $product = $query->where('ean', $data['ean'])->first();
            if ($product) {
                $this->stats['updated']++;
                return $product;
            }
        }

        // Try to find by MPN
        if (!empty($data['mpn'])) {
            $product = $query->where('mpn', $data['mpn'])->first();
            if ($product) {
                $this->stats['updated']++;
                return $product;
            }
        }

        // Try fuzzy match by brand and name
        $product = $query
            ->where('brand', $data['brand'])
            ->where('name', 'LIKE', '%' . substr($data['name'], 0, 30) . '%')
            ->first();

        if ($product) {
            // Update EAN/MPN if we found by fuzzy match
            if (!$product->ean && !empty($data['ean'])) {
                $product->ean = $data['ean'];
            }
            if (!$product->mpn && !empty($data['mpn'])) {
                $product->mpn = $data['mpn'];
            }
            $product->save();
            
            $this->stats['updated']++;
            return $product;
        }

        // Create new product
        $product = Product::create($data);
        $this->stats['created']++;
        
        return $product;
    }

    /**
     * Create or update offer for product
     */
    protected function upsertOffer(Product $product, array $offerData): void
    {
        $offer = Offer::updateOrCreate(
            [
                'product_id' => $product->id,
                'merchant' => $offerData['merchant'],
            ],
            [
                'price' => $offerData['price'],
                'currency' => $offerData['currency'],
                'url_affiliate' => $offerData['url_affiliate'],
                'in_stock' => $offerData['in_stock'],
                'last_seen_at' => now(),
            ]
        );

        // Log price changes
        if ($offer->wasChanged('price')) {
            Log::info('Price updated', [
                'product_id' => $product->id,
                'merchant' => $offerData['merchant'],
                'old_price' => $offer->getOriginal('price'),
                'new_price' => $offer->price,
            ]);
        }
    }

    /**
     * Normalize brand name
     */
    protected function normalizeBrand(?string $brand): string
    {
        if (empty($brand)) {
            return 'Unknown';
        }

        return trim(ucwords(strtolower($brand)));
    }

    /**
     * Normalize product name
     */
    protected function normalizeName(?string $name): string
    {
        return trim($name);
    }

    /**
     * Get import statistics
     */
    public function getStats(): array
    {
        return $this->stats;
    }
}
