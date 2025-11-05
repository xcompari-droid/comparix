<?php

namespace App\Console\Commands;

use App\Models\ProductType;
use App\Services\Importers\TwoPerformantImporter;
use Illuminate\Console\Command;

class FeedImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feed:import 
                            {--file= : Path to feed file (CSV or XML)}
                            {--type=csv : Feed type (csv or xml)}
                            {--product-type= : Product type ID}
                            {--category=farma : Category slug (default: farma)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products from 2Performant feed file';

    /**
     * Execute the console command.
     */
    public function handle(TwoPerformantImporter $importer)
    {
        $filePath = $this->option('file');
        $type = $this->option('type');
        $productTypeId = $this->option('product-type');
        $categorySlug = $this->option('category');

        if (!$filePath) {
            $this->error('Please provide a feed file path with --file option');
            return Command::FAILURE;
        }

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return Command::FAILURE;
        }

        // Get or create product type
        if (!$productTypeId) {
            $productType = $this->getOrCreateProductType($categorySlug);
            $productTypeId = $productType->id;
            $this->info("Using product type: {$productType->name} (ID: {$productTypeId})");
        }

        $this->info("Starting import from {$filePath}...");
        $this->newLine();

        try {
            $stats = match($type) {
                'xml' => $importer->importFromXml($filePath, $productTypeId),
                default => $importer->importFromCsv($filePath, $productTypeId),
            };

            $this->displayStats($stats);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Import failed: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Get or create product type for category
     */
    protected function getOrCreateProductType(string $categorySlug): ProductType
    {
        $category = \App\Models\Category::firstOrCreate(
            ['slug' => $categorySlug],
            ['name' => ucfirst($categorySlug), 'slug' => $categorySlug]
        );

        return ProductType::firstOrCreate(
            [
                'category_id' => $category->id,
                'slug' => $categorySlug . '-products',
            ],
            [
                'name' => ucfirst($categorySlug) . ' Products',
            ]
        );
    }

    /**
     * Display import statistics
     */
    protected function displayStats(array $stats): void
    {
        $this->newLine();
        $this->info('Import completed!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Products processed', $stats['processed']],
                ['Products created', $stats['created']],
                ['Products updated', $stats['updated']],
                ['Errors', $stats['errors']],
            ]
        );
    }
}
