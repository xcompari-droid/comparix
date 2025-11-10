<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VersusCompareController extends Controller
{
    /**
     * Demo page cu date hardcoded
     */
    public function demo()
    {
        // Date demo pentru 3 GPU-uri
        $items = [
            [
                'id' => 1,
                'name' => 'NVIDIA RTX 4090',
                'brand' => 'NVIDIA',
                'image_url' => 'https://ui-avatars.com/api/?name=RTX+4090&size=400&background=76b900&color=fff',
                'product_url' => '#',
                'metrics' => [
                    'cuda_cores' => 16384,
                    'memory_gb' => 24,
                    'boost_clock_mhz' => 2520,
                    'tdp_watts' => 450,
                    'price_eur' => 1999,
                    'performance_score' => 100,
                    'memory_bandwidth_gbps' => 1008,
                ],
                'color' => '#76b900', // verde NVIDIA
            ],
            [
                'id' => 2,
                'name' => 'AMD RX 7900 XTX',
                'brand' => 'AMD',
                'image_url' => 'https://ui-avatars.com/api/?name=RX+7900&size=400&background=ed1c24&color=fff',
                'product_url' => '#',
                'metrics' => [
                    'cuda_cores' => 12288, // Stream processors pentru AMD
                    'memory_gb' => 24,
                    'boost_clock_mhz' => 2500,
                    'tdp_watts' => 355,
                    'price_eur' => 1099,
                    'performance_score' => 87,
                    'memory_bandwidth_gbps' => 960,
                ],
                'color' => '#ed1c24', // roșu AMD
            ],
            [
                'id' => 3,
                'name' => 'NVIDIA RTX 4080',
                'brand' => 'NVIDIA',
                'image_url' => 'https://ui-avatars.com/api/?name=RTX+4080&size=400&background=0071c5&color=fff',
                'product_url' => '#',
                'metrics' => [
                    'cuda_cores' => 9728,
                    'memory_gb' => 16,
                    'boost_clock_mhz' => 2505,
                    'tdp_watts' => 320,
                    'price_eur' => 1299,
                    'performance_score' => 82,
                    'memory_bandwidth_gbps' => 736,
                ],
                'color' => '#0071c5', // albastru
            ],
        ];

        $metricDefinitions = [
            ['key' => 'performance_score', 'label' => 'Performanță Gaming', 'direction' => 'higher'],
            ['key' => 'cuda_cores', 'label' => 'CUDA Cores / Stream Processors', 'direction' => 'higher'],
            ['key' => 'memory_gb', 'label' => 'Memorie Video (GB)', 'direction' => 'higher'],
            ['key' => 'memory_bandwidth_gbps', 'label' => 'Lățime Bandă (GB/s)', 'direction' => 'higher'],
            ['key' => 'boost_clock_mhz', 'label' => 'Boost Clock (MHz)', 'direction' => 'higher'],
            ['key' => 'tdp_watts', 'label' => 'Consum (Watts)', 'direction' => 'lower'],
            ['key' => 'price_eur', 'label' => 'Preț (EUR)', 'direction' => 'lower'],
        ];

        return Inertia::render('Compare/VersusDemo', [
            'items' => $items,
            'metricDefinitions' => $metricDefinitions,
        ]);
    }

    /**
     * Comparație reală cu date din DB
     * Apelează: /compare/versus?ids=160,161,162
     */
    public function compare(Request $request)
    {
        $request->validate([
            'ids' => 'required|string',
        ]);

        $ids = array_map('intval', explode(',', $request->input('ids')));
        $products = Product::with('specValues.specKey')->findMany($ids);

        if ($products->count() < 2) {
            return redirect()->back()->with('error', 'Selectează cel puțin 2 produse pentru comparație.');
        }

        // Mapare dinamic a specificațiilor în metrici
        $items = $products->map(function ($product, $index) {
            $specs = $product->specValues;
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'brand' => $product->brand,
                'image_url' => $product->image_url,
                'product_url' => route('products.show', $product->id),
                'metrics' => $this->extractMetrics($specs),
                'color' => $this->getColor($index),
            ];
        })->values()->all();

        $metricDefinitions = $this->getMetricDefinitions($products->first()->product_type_id);

        return Inertia::render('Compare/VersusDemo', [
            'items' => $items,
            'metricDefinitions' => $metricDefinitions,
        ]);
    }

    /**
     * Extrage metrici numerice din specificații
     */
    private function extractMetrics($specifications)
    {
        $metrics = [];
        
        foreach ($specifications as $spec) {
            $key = $this->normalizeSpecKey($spec->specKey->name);
            
            // Prioritizează value_number
            if ($spec->value_number !== null) {
                $metrics[$key] = $spec->value_number;
            } 
            // Apoi value_bool (convertit la boolean real)
            elseif ($spec->value_bool !== null) {
                $metrics[$key] = (bool) $spec->value_bool;
            }
            // Încearcă să extragi număr din value_string
            elseif ($spec->value_string !== null) {
                $numericValue = $this->extractNumber($spec->value_string);
                if ($numericValue !== null) {
                    $metrics[$key] = $numericValue;
                } else {
                    // Dacă nu e număr, păstrează string-ul
                    $metrics[$key] = $spec->value_string;
                }
            }
        }
        
        return $metrics;
    }

    /**
     * Normalizează cheile de specificație pentru matching
     */
    private function normalizeSpecKey(string $name): string
    {
        $map = [
            // GPU
            'CUDA Cores' => 'cuda_cores',
            'Stream Processors' => 'cuda_cores',
            'Memorie' => 'memory_gb',
            'Memorie video' => 'memory_gb',
            'Boost Clock' => 'boost_clock_mhz',
            'TDP' => 'tdp_watts',
            'Consum' => 'tdp_watts',
            'Preț' => 'price_eur',
            'Lățime de bandă memorie' => 'memory_bandwidth_gbps',
            
            // Laptop
            'RAM' => 'ram_gb',
            'Stocare' => 'storage_gb',
            'Procesor' => 'cpu_name',
            'Dimensiune ecran' => 'screen_size_inch',
            'Rezoluție' => 'resolution_width',
            'Greutate' => 'weight_kg',
            'Autonomie baterie' => 'battery_hours',
            
            // Smartphone
            'Cameră principală' => 'main_camera_mp',
            'Cameră frontală' => 'front_camera_mp',
            'Baterie' => 'battery_mah',
            'Display' => 'screen_size_inch',
            
            // Smartwatch
            'Dimensiune carcasă' => 'case_size_mm',
            'Rezistență apă' => 'water_resistance_atm',
            'Autonomie' => 'battery_days',
            
            // Frigidere
            'Capacitate totală' => 'capacity_liters',
            'Capacitate frigider' => 'fridge_capacity',
            'Capacitate congelator' => 'freezer_capacity',
            'Consum energetic anual' => 'energy_kwh_year',
            'Clasă energetică' => 'energy_class',
            'Nivel zgomot' => 'noise_db',
            'Lățime' => 'width_cm',
            'Înălțime' => 'height_cm',
        ];

        return $map[$name] ?? strtolower(str_replace([' ', 'ă', 'î', 'ș', 'ț', 'â'], ['_', 'a', 'i', 's', 't', 'a'], $name));
    }

    /**
     * Extrage primul număr dintr-un string
     */
    private function extractNumber(?string $value): ?float
    {
        if (!$value) return null;
        
        // Curăță stringul și extrage primul număr
        preg_match('/\d+(\.\d+)?/', str_replace(',', '.', $value), $matches);
        
        return $matches[0] ?? null;
    }

    /**
     * Definește metricile pentru fiecare tip de produs
     */
    private function getMetricDefinitions(int $productTypeId): array
    {
        // ProductType IDs din DB:
        // 1: Smartphone, 2: Smartwatch, 3: GPU, 5: Frigider, 6: TV, 7: Căști, 9: Laptop
        
        $definitions = [
            3 => [ // GPU
                ['key' => 'cuda_cores', 'label' => 'CUDA Cores', 'direction' => 'higher'],
                ['key' => 'memory_gb', 'label' => 'Memorie (GB)', 'direction' => 'higher'],
                ['key' => 'boost_clock_mhz', 'label' => 'Boost Clock (MHz)', 'direction' => 'higher'],
                ['key' => 'memory_bandwidth_gbps', 'label' => 'Lățime Bandă (GB/s)', 'direction' => 'higher'],
                ['key' => 'tdp_watts', 'label' => 'TDP (W)', 'direction' => 'lower'],
                ['key' => 'price_eur', 'label' => 'Preț (EUR)', 'direction' => 'lower'],
            ],
            9 => [ // Laptop
                ['key' => 'ram_gb', 'label' => 'RAM (GB)', 'direction' => 'higher'],
                ['key' => 'storage_gb', 'label' => 'Stocare (GB)', 'direction' => 'higher'],
                ['key' => 'screen_size_inch', 'label' => 'Ecran (inch)', 'direction' => 'higher'],
                ['key' => 'battery_hours', 'label' => 'Autonomie (ore)', 'direction' => 'higher'],
                ['key' => 'weight_kg', 'label' => 'Greutate (kg)', 'direction' => 'lower'],
                ['key' => 'price_eur', 'label' => 'Preț (EUR)', 'direction' => 'lower'],
            ],
            1 => [ // Smartphone
                ['key' => 'main_camera_mp', 'label' => 'Cameră Principală (MP)', 'direction' => 'higher'],
                ['key' => 'ram_gb', 'label' => 'RAM (GB)', 'direction' => 'higher'],
                ['key' => 'storage_gb', 'label' => 'Stocare (GB)', 'direction' => 'higher'],
                ['key' => 'battery_mah', 'label' => 'Baterie (mAh)', 'direction' => 'higher'],
                ['key' => 'screen_size_inch', 'label' => 'Ecran (inch)', 'direction' => 'higher'],
                ['key' => 'weight_kg', 'label' => 'Greutate (g)', 'direction' => 'lower'],
                ['key' => 'price_eur', 'label' => 'Preț (EUR)', 'direction' => 'lower'],
            ],
            5 => [ // Frigider
                ['key' => 'capacity_liters', 'label' => 'Capacitate Totală (L)', 'direction' => 'higher'],
                ['key' => 'fridge_capacity', 'label' => 'Capacitate Frigider (L)', 'direction' => 'higher'],
                ['key' => 'freezer_capacity', 'label' => 'Capacitate Congelator (L)', 'direction' => 'higher'],
                ['key' => 'energy_kwh_year', 'label' => 'Consum Anual (kWh)', 'direction' => 'lower'],
                ['key' => 'noise_db', 'label' => 'Zgomot (dB)', 'direction' => 'lower'],
                ['key' => 'price_eur', 'label' => 'Preț (EUR)', 'direction' => 'lower'],
            ],
        ];

        return $definitions[$productTypeId] ?? [
            ['key' => 'price_eur', 'label' => 'Preț (EUR)', 'direction' => 'lower'],
        ];
    }

    /**
     * Culori pentru produse (max 6 produse)
     */
    private function getColor(int $index): string
    {
        $colors = ['#76b900', '#ed1c24', '#0071c5', '#f7931e', '#8e44ad', '#16a085'];
        return $colors[$index % count($colors)];
    }
}
