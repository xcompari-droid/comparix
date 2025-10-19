Route::get('/ping', fn () => 'pong-' . now());
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

