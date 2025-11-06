<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index()
    {
        $favorites = auth()->user()->favorites()->with('product.offers')->get();
        
        return view('favorites.index', compact('favorites'));
    }

    public function toggle(Product $product)
    {
        $favorite = auth()->user()->favorites()->where('product_id', $product->id)->first();
        
        if ($favorite) {
            $favorite->delete();
            return response()->json([
                'success' => true,
                'favorited' => false,
                'message' => 'Produs eliminat din favorite',
            ]);
        }
        
        auth()->user()->favorites()->create([
            'product_id' => $product->id,
        ]);
        
        return response()->json([
            'success' => true,
            'favorited' => true,
            'message' => 'Produs adăugat la favorite',
        ]);
    }
}
