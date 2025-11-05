<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule feed imports and maintenance tasks
Schedule::command('offers:sync')->everyThirtyMinutes();
Schedule::command('reindex:search')->daily();
