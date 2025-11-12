// Pagina Cum funcționează
Route::get('/cum-functioneaza', function() {
    return view('cum-functioneaza');
});
// Rută temporară pentru resetare parolă admin (șterge după folosire!)
use Illuminate\Support\Facades\Hash;
Route::get('/reset-admin-pass', function() {
    $email = request('email');
    $pass = request('pass');
    if (!$email || !$pass) return 'Furnizează email și parolă: /reset-admin-pass?email=...&pass=...';
    $user = \App\Models\User::where('email', $email)->first();
    if (!$user) return 'User not found';
    $user->password = Hash::make($pass);
    $user->save();
    return 'Parola a fost resetată cu succes pentru ' . $email;
});

// Rută dedicată pentru login admin cu resetare parolă vizibilă
Route::get('/admin/login', function () {
    return inertia('Auth/Login', [
        'canResetPassword' => true,
    ]);
})->name('admin.login');

// Pagini legal: Termeni și Confidențialitate
Route::view('/termeni', 'legal.terms')->name('terms');
Route::view('/confidentialitate', 'legal.privacy')->name('privacy');

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
use Laravel\Fortify\Fortify;

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

// Înregistrează rutele Fortify explicit dacă nu apar automat
Fortify::loginView(function () {
    return inertia('Auth/Login', [
        'canResetPassword' => true,
    ]);
});
Fortify::requestPasswordResetLinkView(function () {
    return inertia('Auth/ForgotPassword');
});
Fortify::resetPasswordView(function ($request) {
    return inertia('Auth/ResetPassword', [
        'email' => $request->email,
        'token' => $request->route('token'),
    ]);
});
