<?php

if (!function_exists('format_number')) {
    /**
     * Format a number intelligently - integers without decimals, floats with max 2 decimals
     * Uses European formatting: dot for thousands, comma for decimals
     * 
     * @param mixed $value
     * @param int $maxDecimals
     * @return string
     */
    function format_number($value, $maxDecimals = 2) {
        if (is_null($value) || $value === '') {
            return '';
        }
        
        // Convert to float
        $number = floatval($value);
        
        // Check if it's an integer (no decimal part)
        if (floor($number) == $number) {
            // Integer: no decimals, use dot as thousands separator
            return number_format($number, 0, ',', '.');
        }
        
        // Has decimals: use maximum specified decimals, remove trailing zeros
        $formatted = number_format($number, $maxDecimals, ',', '.');
        
        // Remove trailing zeros after comma
        $formatted = rtrim($formatted, '0');
        $formatted = rtrim($formatted, ',');
        
        return $formatted;
    }
}
