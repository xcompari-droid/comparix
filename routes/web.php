// Mobile comparison demo route
use Inertia\Inertia;
Route::get('/compare/laptops-mobile', function () {
    // Exemplu de date demo pentru testare rapidă
    $items = [
        [
            'id' => 1,
            'name' => 'Asus ZenBook 14',
            'slug' => 'asus-zenbook-14',
            'image' => 'https://m.media-amazon.com/images/I/71M0sABTNxL._AC_SL1500_.jpg',
            'price' => 3999,
            'specs' => [
                'cpu_model' => 'Intel i7-1165G7',
                'gpu_model' => 'Iris Xe',
                'ram_gb' => 16,
                'storage_gb' => 512,
                'display_size_in' => 14,
                'display_brightness_nits' => 400,
                'battery_wh' => 67,
                'weight_kg' => 1.2,
            ],
            'metrics' => [
                'cpu_score' => 8200,
                'gpu_score' => 2100,
                'display_nits' => 400,
                'battery_wh' => 67,
                'weight_kg' => 1.2,
                'price_ron' => 3999,
            ],
        ],
        [
            'id' => 2,
            'name' => 'HP Envy 13',
            'slug' => 'hp-envy-13',
            'image' => 'https://m.media-amazon.com/images/I/81S0fHXEURL._AC_SL1500_.jpg',
            'price' => 4299,
            'specs' => [
                'cpu_model' => 'Intel i5-1135G7',
                'gpu_model' => 'Iris Xe',
                'ram_gb' => 8,
                'storage_gb' => 256,
                'display_size_in' => 13.3,
                'display_brightness_nits' => 300,
                'battery_wh' => 51,
                'weight_kg' => 1.3,
            ],
            'metrics' => [
                'cpu_score' => 6500,
                'gpu_score' => 1800,
                'display_nits' => 300,
                'battery_wh' => 51,
                'weight_kg' => 1.3,
                'price_ron' => 4299,
            ],
        ],
    ];
    $metrics = [
        [ 'key' => 'cpu_score', 'label' => 'CPU', 'better' => 'higher' ],
        [ 'key' => 'gpu_score', 'label' => 'GPU', 'better' => 'higher' ],
        [ 'key' => 'display_nits', 'label' => 'Luminozitate', 'better' => 'higher', 'unit' => 'nits' ],
        [ 'key' => 'battery_wh', 'label' => 'Baterie', 'better' => 'higher', 'unit' => 'Wh' ],
        [ 'key' => 'weight_kg', 'label' => 'Greutate', 'better' => 'lower', 'unit' => 'kg' ],
        [ 'key' => 'price_ron', 'label' => 'Preț', 'better' => 'lower', 'unit' => 'RON' ],
    ];
    return Inertia::render('Compare/LaptopsMobile', [
        'items' => $items,
        'metrics' => $metrics,
        'title' => 'Comparație laptopuri (mobil demo)'
    ]);
});
<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\PriceAlertController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\VersusCompareController;
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
    $popularComparisons = [
        [
            'id1' => 323,
            'id2' => 324,
            'name1' => 'Apple Watch Series 10',
            'name2' => 'Apple Watch Ultra 2',
            'image1' => 'https://m.media-amazon.com/images/I/71M0sABTNxL._AC_SL1500_.jpg',
            'image2' => 'https://m.media-amazon.com/images/I/81S0fHXEURL._AC_SL1500_.jpg',
        ],
        [
            'id1' => 383,
            'id2' => 384,
            'name1' => 'Apple AirPods Pro 2',
            'name2' => 'Apple AirPods 3',
            'image1' => 'https://m.media-amazon.com/images/I/61SUj2aKoEL._AC_SL1500_.jpg',
            'image2' => 'https://m.media-amazon.com/images/I/61NiiCNtWWL._AC_SL1500_.jpg',
        ],
        [
            'id1' => 416,
            'id2' => 417,
            'name1' => 'Samsung WW90T554DAW/S7',
            'name2' => 'LG F4WV710P2E',
            'image1' => '/storage/products/416-1762782228.jpg',
            'image2' => '/storage/products/417-1762782234.jpg',
        ],
    ];
    
    return view('home', compact('popularComparisons'));
});

Route::get('/health', fn() => response()->json(['ok' => true, 'time' => now()], 200));

// Frontend routes
Route::get('/categorii', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categorii/{slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/produse/{id}', [ProductController::class, 'show'])->name('products.show');
Route::get('/cautare', [ProductController::class, 'search'])->name('products.search');
Route::get('/compara', [ComparisonController::class, 'compare'])->name('compare');
Route::get('/oferta/{id}', [ComparisonController::class, 'redirect'])->name('offer.redirect');

// Versus-style comparison widget
Route::get('/compare/demo', [VersusCompareController::class, 'demo'])->name('compare.demo');
Route::get('/compare/versus', [VersusCompareController::class, 'compare'])->name('compare.versus');

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
