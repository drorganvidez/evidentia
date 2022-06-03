<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\AuthController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\v1\CommiteeController;
use App\Http\Controllers\Api\v1\EvidenceController;
use App\Http\Controllers\Api\v1\UserController;
use App\Http\Controllers\Api\v1\MeetingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => '{instance}'], function () {
    Route::group(['prefix' => 'v1'], function () {
        Route::group(['prefix' => 'auth'], function () {
            Route::group(['middleware' => 'jwt.verify'], function () {
                Route::post('logout',  [AuthController::class, 'logout']);
                Route::get('me',  [AuthController::class, 'me']);
                Route::post('refresh',  [AuthController::class, 'refresh']);
            });
            Route::post('login', [AuthController::class, 'login']);
        });

        Route::group(['middleware' => 'jwt.verify'], function () {
            Route::get('commitee', [CommiteeController::class, 'getAll']);
            Route::put('commitee', [CommiteeController::class, 'create']);
            Route::get('commitee/{id}', [CommiteeController::class, 'getById']);
            Route::post('commitee/{id}', [CommiteeController::class, 'updateById']);
            Route::delete('commitee/{id}', [CommiteeController::class, 'deleteById']);

            Route::get('evidence', [EvidenceController::class, 'getAll']);
            Route::put('evidence', [EvidenceController::class, 'create']);
            Route::get('evidence/{id}', [EvidenceController::class, 'getById']);
            Route::post('evidence/{id}', [EvidenceController::class, 'updateById']);
            Route::delete('evidence/{id}', [EvidenceController::class, 'deleteById']);

            Route::get('user', [UserController::class, 'getAll']);
            Route::put('user', [UserController::class, 'create']);
            Route::get('user/{id}', [UserController::class, 'getById']);
            Route::post('user/{id}', [UserController::class, 'updateById']);
            Route::delete('user/{id}', [UserController::class, 'deleteById']);

            Route::get('meeting', [MeetingController::class, 'getAll']);
            Route::put('meeting', [MeetingController::class, 'create']);
            Route::get('meeting/{id}', [MeetingController::class, 'getById']);
            Route::post('meeting/{id}', [MeetingController::class, 'updateById']);
            Route::delete('meeting/{id}', [MeetingController::class, 'deleteById']);
        });
    });
});