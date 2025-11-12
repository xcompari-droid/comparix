<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;

class CleanDuplicateUnits extends Command
{
    protected $signature = 'comparix:clean-dup-units';
    protected $description = 'Elimină unități duplicate din specs (cm cm, kg kg, etc.)';

    public function handle(): int
    {
        $replacements = [
            '/\\b(cm)\\b(\\s*\\1\\b)+/i'               => '$1',
            '/\\b(kg)\\b(\\s*\\1\\b)+/i'               => '$1',
            '/\\b(L\/ciclu)\\b(\\s*\\1\\b)+/i'         => '$1',
            '/\\b(kWh\/100 cicluri)\\b(\\s*\\1\\b)+/i' => '$1',
        ];

        Product::whereNotNull('specs')->chunkById(200, function($chunk) use ($replacements) {
            foreach ($chunk as $p) {
                $changed = false;
                $specs = $p->specs;
                if (!is_array($specs)) continue;
                foreach ($specs as $k=>$v) {
                    if (!is_string($v)) continue;
                    $s = $v;
                    foreach ($replacements as $re => $rep) $s = preg_replace($re, $rep, $s);
                    if ($s !== $v) { $specs[$k] = $s; $changed = true; }
                }
                if ($changed) $p->update(['specs' => $specs]);
            }
        });

        $this->info('Curățare finalizată.');
        return self::SUCCESS;
    }
}
