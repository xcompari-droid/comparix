<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PriceAlert;
use Illuminate\Http\Request;

class PriceAlertController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'target_price' => 'required|numeric|min:0',
        ]);

        $alert = auth()->user()->priceAlerts()->updateOrCreate(
            ['product_id' => $product->id],
            [
                'target_price' => $validated['target_price'],
                'is_active' => true,
            ]
        );

        return redirect()->back()->with('success', 'Vei primi o notificare când prețul scade sub ' . number_format($validated['target_price'], 2) . ' RON');
    }

    public function destroy(PriceAlert $priceAlert)
    {
        if ($priceAlert->user_id !== auth()->id()) {
            abort(403);
        }

        $priceAlert->delete();

        return redirect()->back()->with('success', 'Alertă de preț ștearsă');
    }
}
