<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Importers\AltexWashingMachineImporter;

class ImportAltexWashingMachines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:altex-washing-machines {--limit=20 : Number of washing machines to import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import washing machines from Altex';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        
        $this->info("Starting import of {$limit} washing machines from Altex...");
        
        $importer = new AltexWashingMachineImporter();
        $result = $importer->import($limit);
        
        $this->newLine();
        $this->info('Import completed!');
        $this->line("  ✓ Imported: {$result['imported']}");
        $this->line("  ✗ Skipped: {$result['skipped']}");
        
        return 0;
    }
}
