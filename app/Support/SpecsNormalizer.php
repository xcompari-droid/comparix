<?php
namespace App\Support;

class SpecsNormalizer
{
    // convertește la valoare numerică + unit canonic (cm, kg, L/ciclu, kWh/100 cicluri)
    public static function normalize($raw, string $targetUnit)
    {
        if ($raw === null || $raw === '' || $raw === '-') return [null, $targetUnit];

        $s = trim(strtolower((string)$raw));

        // extrage număr cu zecimale
        preg_match('/-?\d+(?:[.,]\d+)?/', $s, $m);
        $num = $m ? (float) str_replace(',', '.', $m[0]) : null;

        // detectează unitatea din text
        $unit = $targetUnit;
        if (str_contains($s, 'mm')) $unit = 'mm';
        elseif (str_contains($s, 'cm')) $unit = 'cm';
        elseif (str_contains($s, 'kg')) $unit = 'kg';
        elseif (str_contains($s, 'l/ciclu')) $unit = 'L/ciclu';
        elseif (str_contains($s, 'kwh/100')) $unit = 'kWh/100 cicluri';

        // conversii la unitatea țintă
        if ($num !== null && $unit !== $targetUnit) {
            if ($unit === 'mm' && $targetUnit === 'cm') { $num = $num / 10; $unit = 'cm'; }
            if ($unit === 'cm' && $targetUnit === 'mm') { $num = $num * 10; $unit = 'mm'; }
            // adaugă conversii după nevoie
        }

        return [$num, $unit];
    }
}
