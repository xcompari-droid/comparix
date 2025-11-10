<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Importers\VersusPhoneImporter;

class ImportVersusPhones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:versus-phones {--limit=100 : Number of phones to import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import smartphones from Versus.com with all specifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        
        $this->info("Starting import of {$limit} phones from Versus.com...");
        
        $importer = new VersusPhoneImporter();
        
        try {
            $importer->import($limit);
            $this->info("✓ Import completed successfully!");
        } catch (\Exception $e) {
            $this->error("✗ Import failed: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
