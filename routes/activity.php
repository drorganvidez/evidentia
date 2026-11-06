<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\EvidenceMoveController;
use App\Http\Controllers\ManagementEvidenceController;
use App\Http\Middleware\CheckNotNull;
use App\Http\Middleware\CheckRoles;
use App\Http\Middleware\CheckUploadEvidences;
use App\Http\Middleware\CheckValidateEvidences;
use App\Http\Middleware\EvidenceFromMyCommittee;
use App\Http\Middleware\EvidenceMine;
use Illuminate\Support\Facades\Route;

/*
 * Activity log, histories of evidences, minutes and meeting requests, and the moves and reviews made from them.
 * Every action checks in the controller who may see each row.
 */
Route::middleware(['auth', 'csp:enforce'])->group(function (): void {
    Route::prefix('activity')->name('activity.')->controller(ActivityController::class)->group(function (): void {
        Route::get('/', 'index')->middleware(CheckRoles::class.':PRESIDENT,LECTURE,COORDINATOR,SECRETARY')->name('index');
        Route::get('/export', 'export')->middleware(CheckRoles::class.':PRESIDENT,LECTURE')->name('export');
        Route::get('/mine', 'mine')->name('mine');
        Route::get('/evidence/{id}', 'evidence')->whereNumber('id')->middleware(CheckNotNull::class.':Evidence')->name('evidence');
        Route::get('/minutes/{id}', 'minutes')->whereNumber('id')->middleware(CheckNotNull::class.':MeetingMinutes')->name('minutes');
        Route::get('/request/{id}', 'meetingRequest')->whereNumber('id')->middleware(CheckNotNull::class.':MeetingRequest')->name('request');
    });

    Route::post('/evidence/move', [EvidenceMoveController::class, 'student'])
        ->middleware([
            CheckRoles::class.':PRESIDENT,COORDINATOR,REGISTER_COORDINATOR,SECRETARY,STUDENT',
            CheckNotNull::class.':Evidence',
            EvidenceMine::class,
            CheckUploadEvidences::class,
        ])
        ->name('evidence.move');

    Route::post('/coordinator/evidence/move', [EvidenceMoveController::class, 'coordinator'])
        ->middleware([
            CheckRoles::class.':COORDINATOR',
            CheckNotNull::class.':Evidence',
            EvidenceFromMyCommittee::class,
            CheckValidateEvidences::class,
        ])
        ->name('coordinator.evidence.move');

    Route::prefix('management/evidence')->name('management.evidence.')
        ->middleware([CheckRoles::class.':PRESIDENT,LECTURE', CheckNotNull::class.':Evidence'])
        ->controller(ManagementEvidenceController::class)->group(function (): void {
            Route::post('/move', 'move')->name('move');
            Route::post('/review', 'review')->name('review');
        });
});
