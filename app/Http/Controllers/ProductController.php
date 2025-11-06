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

        return view('products.show', compact('product'));
    }
}
