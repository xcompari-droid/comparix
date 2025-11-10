<?php

namespace App\Console\Commands;

use App\Services\Importers\SmartphoneImporter;
use Illuminate\Console\Command;

class ImportSmartphonesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:smartphones {file : Path to CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import smartphones from CSV file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $this->info("Starting import from: {$filePath}");
        
        try {
            $importer = new SmartphoneImporter();
            $result = $importer->importFromCsv($filePath);

            $this->newLine();
            $this->info("Import completed successfully!");
            $this->info("Imported: {$result['imported']} products");
            
            if ($result['failed'] > 0) {
                $this->warn("Failed: {$result['failed']} products");
                
                if (!empty($result['errors'])) {
                    $this->newLine();
                    $this->error("Errors:");
                    foreach ($result['errors'] as $error) {
                        $this->line("  - {$error['product']}: {$error['error']}");
                    }
                }
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("Import failed: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
