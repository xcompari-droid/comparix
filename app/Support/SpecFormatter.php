<?php
namespace App\Support;

class SpecFormatter
{
    public static function value($value, ?string $unit = null): string
    {
        if ($value === null || $value === '' || $value === '-') return '—';

        // dacă e numeric, formatează-l și adaugă unit
        if (is_numeric($value)) {
            $v = rtrim(rtrim(number_format((float)$value, 2, '.', ''), '0'), '.');
            return $unit ? "{$v} {$unit}" : $v;
        }

        // e string: dedupe unitățile repetate
        $s = trim((string)$value);
        $patterns = [
            '/\\b(cm)\\b(?:\\s*\\1\\b)+/i'                   => '$1',
            '/\\b(kg)\\b(?:\\s*\\1\\b)+/i'                   => '$1',
            '/\\b(L\/ciclu)\\b(?:\\s*\\1\\b)+/i'             => '$1',
            '/\\b(kWh\/100 cicluri)\\b(?:\\s*\\1\\b)+/i'     => '$1',
        ];
        $out = $s;
        foreach ($patterns as $re => $rep) $out = preg_replace($re, $rep, $out);

        // dacă unit e dată, dar stringul o conține deja, nu o mai adăuga
        if ($unit && stripos($out, $unit) === false) $out .= " {$unit}";
        return trim($out);
    }
}
