<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class ReindexSearchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reindex:search';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reindex all products in Meilisearch';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Reindexing products in Meilisearch...');

        $count = Product::count();
        
        if ($count === 0) {
            $this->warn('No products to index');
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        Product::chunk(100, function ($products) use ($bar) {
            $products->searchable();
            $bar->advance($products->count());
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Successfully indexed {$count} products!");

        return Command::SUCCESS;
    }
}

