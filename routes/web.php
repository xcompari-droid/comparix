<?php

use Illuminate\Support\Facades\Route;

// TEMP de verificare – poți lăsa direct welcome dacă vrei
Route::get('/', function () {
    return view('welcome'); // ← nu 'home'
});

