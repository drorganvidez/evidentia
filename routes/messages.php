<?php

use App\Http\Controllers\MessageThreadController;
use App\Http\Middleware\CheckRoles;
use Illuminate\Support\Facades\Route;

/*
 * Message threads addressed to the people in charge. The policy decides who sees, answers, resolves and reopens.
 */
Route::middleware(['auth', 'csp:enforce'])->prefix('messages')->name('messages.')
    ->controller(MessageThreadController::class)->group(function (): void {
        Route::get('/', 'inbox')->middleware(CheckRoles::class.':COORDINATOR,SECRETARY,REGISTER_COORDINATOR,PRESIDENT,LECTURE')->name('inbox');
        Route::get('/mine', 'mine')->name('mine');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{thread}', 'show')->whereNumber('thread')->name('show');
        Route::post('/{thread}/reply', 'reply')->whereNumber('thread')->name('reply');
        Route::post('/{thread}/resolve', 'resolve')->whereNumber('thread')->name('resolve');
        Route::post('/{thread}/reopen', 'reopen')->whereNumber('thread')->name('reopen');
    });

