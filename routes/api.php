<?php

use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\v1\EvidenceController;
use Illuminate\Support\Facades\Route;

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

Route::middleware('selectdatabaseapi')->group( function () {

    Route::group(['prefix' => 'v1'], function () {

        Route::group(['prefix' => '{instance}'], function () {

            Route::controller(RegisterController::class)->group(function(){
                Route::post('register', 'register');
                Route::post('login', 'login');
            });

        });

    });

});





Route::get('prueba', [EvidenceController::class, 'index'])->middleware('auth:sanctum');

Route::group(['prefix' => 'v1'], function () {

    Route::group(['prefix' => '{instance}'], function () {

        Route::middleware('auth:sanctum')->group( function () {

            // evidences
            Route::group(['prefix' => 'evidences'], function () {
                Route::get('', [EvidenceController::class, 'index']);
            });

        });

    });
});


