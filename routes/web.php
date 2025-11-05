<?php

use Illuminate\Support\Facades\Route;

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
