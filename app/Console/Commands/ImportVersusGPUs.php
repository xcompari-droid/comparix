<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Importers\VersusGPUImporter;

class ImportVersusGPUs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:versus-gpus {--limit=50 : Number of GPUs to import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import graphics cards from Versus.com with all specifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        
        $this->info("Starting import of {$limit} graphics cards...");
        
        try {
            $importer = new VersusGPUImporter();
            $importer->import($limit);
            
            $this->info('Import completed successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            return 1;
        }
    }
}
