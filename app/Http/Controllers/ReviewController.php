<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:200',
            'content' => 'required|string|min:20',
            'pros' => 'nullable|array',
            'cons' => 'nullable|array',
        ]);

        $review = $product->reviews()->create([
            'user_id' => auth()->id(),
            'rating' => $validated['rating'],
            'title' => $validated['title'],
            'content' => $validated['content'],
            'pros' => $validated['pros'] ?? null,
            'cons' => $validated['cons'] ?? null,
            'is_approved' => false, // Require admin approval
        ]);

        return redirect()->back()->with('success', 'Review-ul tău a fost trimis și așteaptă aprobare.');
    }

    public function markHelpful(Review $review)
    {
        $review->increment('helpful_count');
        
        return response()->json([
            'success' => true,
            'helpful_count' => $review->helpful_count,
        ]);
    }
}
