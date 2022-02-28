<?php

use App\Http\Controllers\ProfileController;
use App\Http\oldControllers\AttendeeController;
use App\Http\oldControllers\DownloadController;
use App\Http\oldControllers\MeetingSecretaryController;
use App\Http\oldControllers\MeetingController;
use App\Http\oldControllers\EvidenceController;
use App\Http\oldControllers\SignController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\oldControllers;

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
    Route::get('login', 'AdminController@login')->name('admin.login');
    Route::post('login_p', 'AdminController@login_p')->name('admin.login_p');

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
    Route::get('/configuration', 'HomeController@configuration')->name('configuration');

    // Profile routes
    Route::group(['prefix' => 'profile'], function(){
        Route::controller(ProfileController::class)->group(function () {
            Route::get('data', 'data')->name('profile.data');;
            Route::get('avatar', 'avatar')->name('profile.avatar');
            Route::get('password', 'password')->name('profile.password');
        });
    });



});



/*

Route::group(['prefix' => '{instance}', 'middleware' => ['checkblock']], function(){

    // Main routes
    Route::get('/', 'HomeController@index')->name('home');
    Route::get('/login', 'LoginInstanceController@login')->name('instance.login');
    Route::post('/login_p', 'LoginInstanceController@login_p')->name('instance.login_p');
    Route::post('/logout', 'LoginInstanceController@logout')->name('instance.logout');

});

Route::group(['prefix' => 'admin'], function(){

    //Route::get('', 'AdminController@home')->name('admin.home');
    Route::get('login', 'LoginAdminController@login')->name('admin.login');
    //Route::post('login_p', 'LoginAdminController@login_p')->name('admin.login_p');
    //Route::post('logout', 'LoginAdminController@logout')->name('admin.logout');

    Route::group(['middleware' => ['checkisadministrator']], function(){

        Route::prefix('instance')->group(function () {

            Route::get('manage', 'InstanceController@manage')->name('admin.instance.manage');

            Route::get('create', 'InstanceController@create')->name('admin.instance.create');
            Route::post('new', 'InstanceController@new')->name('admin.instance.new');

            Route::middleware(['checknotnull:Instance'])->group(function () {
                Route::get('manage/edit/{id}', 'InstanceController@edit')->name('admin.instance.manage.edit');
                Route::get('manage/delete/{id}', 'InstanceController@delete')->name('admin.instance.manage.delete');
            });

            Route::post('manage/save', 'InstanceController@save')->name('admin.instance.manage.save');

            Route::post('manage/remove/', 'InstanceController@remove')->name('admin.instance.manage.remove');
        });
    });


});

*/