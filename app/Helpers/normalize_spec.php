<?php
// app/Helpers/normalize_spec.php

if (!function_exists('normalize_spec_value')) {
    /**
     * Normalizează o valoare de specificație (valoare + unitate), elimină duplicate, placeholder, spații, etc.
     * @param string|null $value
     * @param string|null $unit
     * @return string
     */
    function normalize_spec_value($value, $unit = null)
    {
        if (!$value) return '';
        $value = trim($value);
        // Adaugă unitatea dacă nu există deja la final
        if ($unit && stripos($value, $unit) === false) {
            $value .= ' ' . $unit;
        }
        // Elimină orice secvență de unități duplicate (ex: "cm cm", "kg kg", "L/ciclu L/ciclu", "kWh/100 cicluri kWh/100 cicluri", "cm mm", "dB dB", "RPM RPM")
        $value = preg_replace('/\b([a-zA-ZăâîșțĂÂÎȘȚ0-9\/\.]+)(\s*\1)+\b/u', '$1', $value);
        // Elimină spații duble
        $value = preg_replace('/\s+/', ' ', $value);
        // Normalizează punctuația la numere (ex: "1.400" -> "1400" dacă nu e zecimal)
        $value = preg_replace('/^(\d+)\.(\d{3})$/', '$1$2', $value);
        // Elimină placeholder gen '-', 'N/A', 'n/a', 'N\A', etc.
        if (in_array(strtolower(trim($value)), ['-', 'n/a', 'n\a', 'n.a.', 'n.a', 'nu'])) {
            return '';
        }
        if (stripos($value, 'N/A') !== false || stripos($value, '-') === 0) {
            return '';
        }
        return trim($value);
    }
}
