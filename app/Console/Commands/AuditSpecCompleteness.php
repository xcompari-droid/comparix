<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\SpecValue;

class AuditSpecCompleteness extends Command
{
    protected $signature = 'comparix:audit-spec-completeness';
    protected $description = 'Afișează gradul de completitudine al specificațiilor produselor';

    public function handle()
    {
        $totalProducts = Product::count();
        $totalSpecs = SpecValue::count();
        $emptySpecs = SpecValue::whereNull('value_string')
            ->whereNull('value_number')
            ->whereNull('value_bool')
            ->count();
        $filledSpecs = $totalSpecs - $emptySpecs;
        $percent = $totalSpecs > 0 ? round($filledSpecs / $totalSpecs * 100, 2) : 0;

        $this->info("Total produse: $totalProducts");
        $this->info("Total specificatii: $totalSpecs");
        $this->info("Specificatii completate: $filledSpecs");
        $this->info("Specificatii fara valoare: $emptySpecs");
        $this->info("Completitudine: $percent%\n");

        return Command::SUCCESS;
    }
}
