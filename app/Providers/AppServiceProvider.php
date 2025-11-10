<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register global helper for smart number formatting
        if (!function_exists('format_number')) {
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
                
                // Has decimals: use maximum 2 decimals, remove trailing zeros
                $formatted = number_format($number, $maxDecimals, ',', '.');
                
                // Remove trailing zeros after comma
                $formatted = rtrim($formatted, '0');
                $formatted = rtrim($formatted, ',');
                
                return $formatted;
            }
        }
    }
}
