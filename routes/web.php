<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ComparisonController;
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
});
