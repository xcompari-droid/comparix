<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Importers\CityImporter;

class ImportCitiesCommand extends Command
{
    protected $signature = 'import:cities {file}';
    protected $description = 'Import cities from CSV file';

    public function handle(): int
    {
        $filePath = $this->argument('file');
        
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return Command::FAILURE;
        }

        $this->info("Starting import from: {$filePath}");
        $this->newLine();

        try {
            $importer = new CityImporter();
            $result = $importer->importFromCsv($filePath);

            $this->newLine();
            $this->info("Import completed successfully!");
            $this->info("Imported: {$result['imported']} cities");
            
            if ($result['failed'] > 0) {
                $this->warn("Failed: {$result['failed']} cities");
                $this->newLine();
                
                foreach ($result['errors'] as $error) {
                    $this->error("- {$error['city']}: {$error['error']}");
                }
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Import failed: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
