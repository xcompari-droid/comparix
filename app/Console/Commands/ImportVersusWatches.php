<?php

namespace App\Console\Commands;

use App\Services\Importers\VersusWatchImporter;
use Illuminate\Console\Command;

class ImportVersusWatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:versus-watches {--limit=50 : Number of smartwatches to import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import smartwatches from Versus.com with all specifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        
        $this->info("Starting import of {$limit} smartwatches from Versus.com...");
        
        try {
            $importer = new VersusWatchImporter();
            $importer->import($limit);
            
            $this->info("✓ Import completed successfully!");
            return 0;
        } catch (\Exception $e) {
            $this->error("✗ Import failed: " . $e->getMessage());
            return 1;
        }
    }
}
