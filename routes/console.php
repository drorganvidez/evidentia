<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run by php artisan schedule:work
Schedule::command('activity:prune')->dailyAt('04:00')->withoutOverlapping()->onOneServer();
Schedule::command('notifications:remind')->dailyAt('08:00')->withoutOverlapping()->onOneServer();
Schedule::command('notifications:digest')->dailyAt('18:00')->withoutOverlapping()->onOneServer();
// Monday the summary of the review queues, Thursday the insistence on what has waited more than a week
Schedule::command('notifications:review-queue')->days([1, 4])->at('08:30')->withoutOverlapping()->onOneServer();
