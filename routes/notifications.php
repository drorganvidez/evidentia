<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// The notices of the signed-in person; the web group comes from withRouting
Route::middleware(['auth', 'csp:enforce'])->prefix('notifications')->name('notifications.')
    ->controller(NotificationController::class)->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/preferences', 'preferences')->name('preferences');
        Route::post('/preferences', 'savePreferences')->name('preferences.save');
        Route::post('/read-all', 'readAll')->name('read_all');
        Route::get('/{id}', 'open')->whereUuid('id')->name('open');
    });
