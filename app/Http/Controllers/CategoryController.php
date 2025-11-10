<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount(['productTypes as products_count' => function ($query) {
            $query->join('products', 'product_types.id', '=', 'products.product_type_id');
        }])->get();
        
        return view('categories.index', compact('categories'));
    }

    public function show($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        
        $products = Product::whereHas('productType', function ($query) use ($category) {
            $query->where('category_id', $category->id);
        })
            ->with(['offers', 'productType.category', 'specValues.specKey'])
            ->paginate(12);
        
        return view('categories.show', compact('category', 'products'));
    }
}
