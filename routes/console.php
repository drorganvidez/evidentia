<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run by php artisan schedule:work
Schedule::command('activity:prune')->dailyAt('04:00')->withoutOverlapping()->onOneServer();
