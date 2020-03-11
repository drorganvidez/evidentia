<?php

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

Route::get('/', 'InstanceController@list');

/*
 *  ADMIN ROUTES
 */

Route::prefix('admin')->group(function () {

    Route::get('', 'InstanceController@admin')->name('admin');
    Route::get('/instance/create', 'InstanceController@create')->name('admin.instance.create');
    Route::post('/instance/new', 'InstanceController@new')->name('admin.instance.new');

});

/*
 *  ALL ROUTES
 */

Route::prefix('{instance}')->group(function () {

    Route::get('/', 'HomeController@index')->name('home');

});


//Route::get('/home', 'HomeController@index')->name('home');
