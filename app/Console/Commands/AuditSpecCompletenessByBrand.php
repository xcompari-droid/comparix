<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\SpecValue;

class AuditSpecCompletenessByBrand extends Command
{
    protected $signature = 'comparix:audit-spec-completeness-brand';
    protected $description = 'Afișează completitudinea specificațiilor pe fiecare brand';

    public function handle()
    {
        $brands = Product::select('brand')->distinct()->pluck('brand');
        $rows = [];
        foreach ($brands as $brand) {
            $productIds = Product::where('brand', $brand)->pluck('id');
            $totalSpecs = SpecValue::whereIn('product_id', $productIds)->count();
            $emptySpecs = SpecValue::whereIn('product_id', $productIds)
                ->whereNull('value_string')
                ->whereNull('value_number')
                ->whereNull('value_bool')
                ->count();
            $filledSpecs = $totalSpecs - $emptySpecs;
            $percent = $totalSpecs > 0 ? round($filledSpecs / $totalSpecs * 100, 2) : 0;
            $rows[] = [
                'brand' => $brand,
                'products' => count($productIds),
                'specs' => $totalSpecs,
                'filled' => $filledSpecs,
                'empty' => $emptySpecs,
                'percent' => $percent
            ];
        }
        $this->table(
            ['Brand', 'Produse', 'Specificații', 'Completate', 'Fără valoare', 'Completitudine %'],
            $rows
        );
        return Command::SUCCESS;
    }
}
