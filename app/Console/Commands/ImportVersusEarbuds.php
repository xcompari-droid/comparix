<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Importers\VersusEarbudImporter;

class ImportVersusEarbuds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:versus-earbuds {--limit=50 : Number of earbuds to import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import wireless earbuds from Versus.com with all specifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        
        $this->info("Starting import of {$limit} wireless earbuds...");
        
        try {
            $importer = new VersusEarbudImporter();
            $importer->import($limit);
            
            $this->info('Import completed successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            return 1;
        }
    }
}
