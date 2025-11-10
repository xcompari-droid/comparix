<?php

namespace App\Console\Commands;

use App\Services\Importers\LGWashingIcecatImporter;
use Illuminate\Console\Command;

class ImportWashingMachines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comparix:import-washing-machines 
                            {--brand=LG : Brand to import (currently only LG supported)}
                            {--limit=100 : Maximum number of products to import}
                            {--no-media : Skip downloading media/images}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import washing machines from manufacturers using Open Icecat API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $brand = $this->option('brand');
        $limit = (int) $this->option('limit');
        $includeMedia = !$this->option('no-media');

        $this->info("Starting washing machine import...");
        $this->info("Brand: {$brand}");
        $this->info("Limit: {$limit}");
        $this->info("Media: " . ($includeMedia ? 'Yes' : 'No'));
        $this->newLine();

        try {
            if (strtoupper($brand) === 'LG') {
                $importer = new LGWashingIcecatImporter();
                $stats = $importer->import($limit, $includeMedia);

                $this->newLine();
                $this->info("Import completed!");
                $this->table(
                    ['Metric', 'Count'],
                    [
                        ['Total Processed', $stats['total']],
                        ['Successful', $stats['success']],
                        ['Skipped', $stats['skipped']],
                        ['Failed', $stats['failed']],
                    ]
                );

                return self::SUCCESS;
            } else {
                $this->error("Brand '{$brand}' is not supported yet. Currently only LG is available.");
                return self::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("Import failed: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
