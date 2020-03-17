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

    // Instances
    Route::get('', 'InstanceController@admin')->name('admin');
    Route::get('/instance/create', 'InstanceController@create')->name('admin.instance.create');
    Route::post('/instance/new', 'InstanceController@new')->name('admin.instance.new');
    Route::get('/instance/manage', 'InstanceController@manage')->name('admin.instance.manage');
    Route::get('/instance/manage/delete/{id}', 'InstanceController@delete')->name('admin.instance.manage.delete');
    Route::post('/instance/manage/remove/', 'InstanceController@remove')->name('admin.instance.manage.remove');
    Route::get('/instance/manage/edit/{id}', 'InstanceController@edit')->name('admin.instance.manage.edit');

});

/*
 *  ALL ROUTES
 */

Route::prefix('{instance}')->group(function () {

    Route::get('/', 'HomeController@index')->name('home');

});


//Route::get('/home', 'HomeController@index')->name('home');
