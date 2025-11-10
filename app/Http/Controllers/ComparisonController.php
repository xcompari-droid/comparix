<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ComparisonController extends Controller
{
    public function compare(Request $request)
    {
        $ids = $request->input('ids');
        
        if (!$ids) {
            return redirect('/categorii');
        }
        
        $productIds = is_array($ids) ? $ids : explode(',', $ids);
        
        $products = Product::whereIn('id', $productIds)
            ->with(['offers', 'specValues.specKey', 'productType.category', 'category'])
            ->get();
        
        // Check if Versus style is requested
        if ($request->input('style') === 'versus' && $products->isNotEmpty()) {
            return $this->versusCompare($products);
        }
        
        // Check if comparing cities
        $isCityComparison = $products->isNotEmpty() && 
                           $products->first()->category && 
                           $products->first()->category->slug === 'orase';
        
        if ($isCityComparison) {
            return view('compare-cities', compact('products'));
        }
        
        // Determine winner (cheapest product)
        $winner = null;
        if ($products->isNotEmpty()) {
            $winner = $products->sortBy(function($product) {
                return $product->offers->min('price') ?? PHP_FLOAT_MAX;
            })->first();
            
            if ($winner && $winner->offers->isNotEmpty()) {
                $winner->best_price = $winner->offers->min('price');
                $winner->best_offer_id = $winner->offers->sortBy('price')->first()->id;
            }
        }
        
        return view('compare', compact('products', 'winner'));
    }
    
    private function versusCompare($products)
    {
        $items = $products->map(function($product, $index) {
            $colors = ['#76b900', '#ed1c24', '#0071c5', '#f7931e', '#8e44ad', '#16a085'];
            
            // Extract metrics from specValues
            $metrics = [];
            foreach ($product->specValues as $specValue) {
                $key = $this->normalizeSpecKey($specValue->specKey->name);
                $value = $specValue->value_number ?? 
                        $this->extractNumber($specValue->value_string) ?? 
                        $specValue->value_bool;
                
                if ($value !== null) {
                    $metrics[$key] = is_numeric($value) ? (float)$value : $value;
                }
            }
            
            // DON'T add price to metrics - it will be in separate section
            // if ($product->offers->isNotEmpty()) {
            //     $metrics['price_eur'] = (float)$product->offers->min('price');
            // }
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'brand' => $product->brand,
                'image_url' => $product->image_url,
                'product_url' => route('products.show', $product->id),
                'metrics' => $metrics,
                'price' => $product->offers->isNotEmpty() ? (float)$product->offers->min('price') : null,
                'color' => $colors[$index % count($colors)],
            ];
        })->values()->toArray();
        
        $metricDefinitions = $this->getMetricDefinitions($products->first()->product_type_id ?? 1);
        
        return \Inertia\Inertia::render('Compare/VersusDemo', [
            'items' => $items,
            'metricDefinitions' => $metricDefinitions,
        ]);
    }
    
    private function normalizeSpecKey($name)
    {
        $map = [
            'CUDA Cores' => 'cuda_cores',
            'Stream Processors' => 'cuda_cores',
            'Memorie Video' => 'memory_gb',
            'Memory' => 'memory_gb',
            'Boost Clock' => 'boost_clock_mhz',
            'TDP' => 'tdp_watts',
            'RAM' => 'ram_gb',
            'Stocare' => 'storage_gb',
            'Baterie' => 'battery_mah',
            'Ecran' => 'screen_inch',
            'Greutate' => 'weight_g',
            'Grosime' => 'thickness_mm',
            'Cameră' => 'camera_mp',
            'Capacitate' => 'capacity_l',
            'Consum energetic' => 'energy_kwh',
            'Zgomot' => 'noise_db',
            'Rezistență la apă' => 'water_resistance',
        ];
        
        return $map[$name] ?? strtolower(str_replace(' ', '_', $name));
    }
    
    private function extractNumber($string)
    {
        if (!is_string($string)) return null;
        if (preg_match('/(\d+(?:\.\d+)?)/', $string, $matches)) {
            return (float)$matches[1];
        }
        return null;
    }
    
    private function getMetricDefinitions($productTypeId)
    {
        // GPU (type 3)
        if ($productTypeId == 3) {
            return [
                ['key' => 'cuda_cores', 'label' => 'CUDA Cores', 'higherIsBetter' => true],
                ['key' => 'memory_gb', 'label' => 'Memorie (GB)', 'higherIsBetter' => true],
                ['key' => 'boost_clock_mhz', 'label' => 'Boost Clock (MHz)', 'higherIsBetter' => true],
                ['key' => 'tdp_watts', 'label' => 'TDP (W)', 'higherIsBetter' => false],
            ];
        }
        
        // Smartwatch (type 2)
        if ($productTypeId == 2) {
            return [
                ['key' => 'ecran', 'label' => 'Ecran (inch)', 'higherIsBetter' => true, 'unit' => '"'],
                ['key' => 'greutate', 'label' => 'Greutate (g)', 'higherIsBetter' => false, 'unit' => 'g'],
                ['key' => 'baterie', 'label' => 'Baterie (ore)', 'higherIsBetter' => true, 'unit' => 'h'],
                ['key' => 'rezistență_la_apă', 'label' => 'Rezistență Apă', 'higherIsBetter' => true],
            ];
        }
        
        // Default - no specific metrics
        return [];
    }

    public function redirect(Request $request, $offerId)
    {
        $offer = \App\Models\Offer::findOrFail($offerId);
        
        // Track click
        \App\Models\AffiliateClick::create([
            'offer_id' => $offer->id,
            'product_id' => $offer->product_id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        return redirect($offer->affiliate_url);
    }
}
