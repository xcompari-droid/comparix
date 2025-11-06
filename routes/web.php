<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\PriceAlertController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
*/

Route::get('/', function () {
    return view('home');
});

Route::get('/health', fn() => response()->json(['ok' => true, 'time' => now()], 200));

// Frontend routes
Route::get('/categorii', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categorii/{slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/produse/{id}', [ProductController::class, 'show'])->name('products.show');
Route::get('/compara', [ComparisonController::class, 'compare'])->name('compare');
Route::get('/oferta/{id}', [ComparisonController::class, 'redirect'])->name('offer.redirect');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    
    // Reviews
    Route::post('/produse/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::post('/reviews/{review}/helpful', [ReviewController::class, 'markHelpful'])->name('reviews.helpful');
    
    // Favorites
    Route::get('/favorite', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/produse/{product}/favorite', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    
    // Price Alerts
    Route::post('/produse/{product}/price-alert', [PriceAlertController::class, 'store'])->name('price-alerts.store');
    Route::delete('/price-alerts/{priceAlert}', [PriceAlertController::class, 'destroy'])->name('price-alerts.destroy');
});
