<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show($id)
    {
        $product = Product::with([
            'offers' => function($query) {
                $query->where('in_stock', true)
                      ->orderBy('price', 'asc');
            },
            'specValues.specKey',
            'productType.category'
        ])->findOrFail($id);

        // Get similar products from same product type
        $similarProducts = Product::where('product_type_id', $product->product_type_id)
            ->where('id', '!=', $product->id)
            ->with(['offers' => function($query) {
                $query->where('in_stock', true)->orderBy('price', 'asc');
            }])
            ->limit(4)
            ->get();

        return view('products.show', compact('product', 'similarProducts'));
    }
}
