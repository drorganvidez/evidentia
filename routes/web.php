<?php

use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::get('/', 'RootController@root')->name('root');

// Admin routes
Route::group(['prefix' => 'admin'], function(){

    // Admin login routes
    Route::controller(AdminController::class)->group(function () {
        Route::get('login', 'login')->name('admin.login');
        Route::post('login_p', 'login_p')->name('admin.login_p');
    });


    // Admin main routes
    Route::group(['middleware' => ['checkisadministrator']], function(){
        Route::get('/', 'AdminController@home')->name('admin.home');
    });


});

// App routes
Route::group(['prefix' => '{instance}', 'middleware' => ['checkblock']], function(){

    // App login routes
    Route::get('/login', 'LoginInstanceController@login')->name('instance.login');
    Route::post('/login_p', 'LoginInstanceController@login_p')->name('instance.login_p');
    Route::post('/logout', 'LoginInstanceController@logout')->name('instance.logout');

    // Main routes
    Route::get('/', 'HomeController@index')->name('home');

    // Profile routes
    Route::group(['prefix' => 'profile'], function(){
        Route::controller(ProfileController::class)->group(function () {
            Route::get('data', 'data')->name('profile.data');
            Route::post('data_p', 'data_p')->name('profile.data_p');
            Route::get('avatar', 'avatar')->name('profile.avatar');
            Route::get('password', 'password')->name('profile.password');
        });
    });

    // Settings
    Route::group(['prefix' => 'settings'], function() {
        Route::controller(SettingController::class)->group(function () {
            Route::get('notifications', 'notifications')->name('settings.notifications');
        });
    });

    // Developer
    Route::group(['prefix' => 'developer'], function() {
        Route::controller(DeveloperController::class)->group(function () {

            Route::group(['prefix' => 'api'], function(){

                Route::get('docs', 'api_docs')->name('developer.apidocs');

                Route::group(['prefix' => 'tokens'], function(){
                    Route::get('create', 'create_api_token')->name('developer.createapitoken');
                    Route::post('create_p', 'create_api_token_p')->name('developer.createapitoken_p');
                    Route::post('delete', 'delete_api_token_p')->name('developer.deleteapitoken_p');
                    Route::get('', 'list_api_tokens')->name('developer.apitokens');
                });

            });


        });
    });

    // Evidences
    Route::group(['prefix' => 'evidences'], function() {
        Route::controller(EvidenceController::class)->group(function () {
            Route::get('create', 'create')->name('evidences.create');
            Route::get('draft', 'draft')->name('evidences.draft');
            Route::get('pending', 'pending')->name('evidences.pending');
            Route::get('accepted', 'accepted')->name('evidences.accepted');
            Route::get('rejected', 'rejected')->name('evidences.rejected');
        });
    });

});

