<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductType;
use App\Models\SpecValue;
use Illuminate\Console\Command;
use League\Csv\Writer;

class ExportWashingMachines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comparix:export-washing-machines 
                            {output=storage/app/exports/washing_machines.csv : Output CSV file path}
                            {--brand= : Filter by brand (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export washing machines to CSV file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $outputPath = $this->argument('output');
        $brand = $this->option('brand');

        $this->info("Exporting washing machines to CSV...");
        
        try {
            // Find washing machine product type
            $productType = ProductType::where('slug', 'masina-de-spalat')->first();
            
            if (!$productType) {
                $this->error("Washing machine product type not found!");
                return self::FAILURE;
            }

            // Query products
            $query = Product::where('product_type_id', $productType->id)
                ->with(['specValues.specKey', 'offers']);

            if ($brand) {
                $query->where('brand', $brand);
            }

            $products = $query->get();

            if ($products->isEmpty()) {
                $this->warn("No washing machines found to export.");
                return self::SUCCESS;
            }

            $this->info("Found {$products->count()} products to export");

            // Ensure output directory exists
            $directory = dirname($outputPath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Create CSV writer
            $csv = Writer::createFromPath($outputPath, 'w+');
            $csv->setDelimiter(',');
            
            // Add BOM for Excel UTF-8 support
            $csv->insertOne(["\xEF\xBB\xBF"]);

            // Collect all unique spec keys
            $allSpecKeys = collect();
            foreach ($products as $product) {
                foreach ($product->specValues as $specValue) {
                    $allSpecKeys->put($specValue->specKey->key, $specValue->specKey->name);
                }
            }

            // Build header
            $header = [
                'ID',
                'Brand',
                'Model',
                'Name',
                'Image URL',
                'Source URL',
                'Price (RON)',
                'In Stock',
                'Score',
            ];

            foreach ($allSpecKeys as $key => $name) {
                $header[] = $name;
            }

            $csv->insertOne($header);

            // Export products
            $progressBar = $this->output->createProgressBar($products->count());
            $progressBar->start();

            foreach ($products as $product) {
                $row = [
                    $product->id,
                    $product->brand,
                    $product->mpn,
                    $product->name,
                    $product->image_url,
                    $product->source_url,
                    $product->offers->first()?->price ?? '',
                    $product->offers->first()?->in_stock ? 'Yes' : 'No',
                    $product->score,
                ];

                // Add spec values
                $specs = $product->specValues->keyBy(function ($specValue) {
                    return $specValue->specKey->key;
                });

                foreach ($allSpecKeys->keys() as $key) {
                    $specValue = $specs->get($key);
                    if ($specValue) {
                        $value = $specValue->value_number 
                            ?? $specValue->value_string 
                            ?? ($specValue->value_bool ? 'Yes' : 'No');
                        $row[] = $value;
                    } else {
                        $row[] = '';
                    }
                }

                $csv->insertOne($row);
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);

            $this->info("✓ Export successful!");
            $this->info("  File: {$outputPath}");
            $this->info("  Products: {$products->count()}");
            $this->info("  Specifications: {$allSpecKeys->count()}");

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Export failed: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return self::FAILURE;
        }
    }
}
