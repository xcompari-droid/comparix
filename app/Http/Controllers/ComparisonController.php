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
            ->with(['offers', 'specValues.specKey', 'category'])
            ->get();
        
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
