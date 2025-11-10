<?php

namespace App\Console\Commands;

use App\Services\Importers\AltexFridgeImporter;
use Illuminate\Console\Command;

class ImportAltexFridges extends Command
{
    protected $signature = 'import:altex-fridges {--limit=50 : Number of products to import}';
    protected $description = 'Import refrigerators from Altex.ro';

    public function handle()
    {
        $this->info('Starting Altex refrigerators import...');
        
        $limit = (int) $this->option('limit');
        
        $importer = new AltexFridgeImporter();
        
        $this->withProgressBar($limit, function () use ($importer, $limit) {
            $importer->import($limit);
        });
        
        $this->info("\n✓ Import completed!");
        
        return Command::SUCCESS;
    }
}
