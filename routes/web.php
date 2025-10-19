<?php

use Illuminate\Support\Facades\Route;

Route::get('/ping', fn () => 'pong-' . now());

Route::get('/', fn () => view('welcome'));

<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

