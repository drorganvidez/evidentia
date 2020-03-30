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

/*
 *  MAIN ROUTE
 */

Route::get('/', 'MetaAdminController@list');

/*
 *  META ADMIN ROUTES
 */

Route::get('/admin', 'MetaAdminController@admin')->name('admin');

Route::prefix('admin')->group(function () {

    Route::get('/instance/manage', 'InstanceController@manage')->name('admin.instance.manage');

    Route::get('/instance/create', 'InstanceController@create')->name('admin.instance.create');
    Route::post('/instance/new', 'InstanceController@new')->name('admin.instance.new');

    Route::middleware(['checknotnull:Instance'])->group(function () {
        Route::get('/instance/manage/edit/{id}', 'InstanceController@edit')->name('admin.instance.manage.edit');
        Route::get('/instance/manage/delete/{id}', 'InstanceController@delete')->name('admin.instance.manage.delete');
    });

    Route::post('/instance/manage/save', 'InstanceController@save')->name('admin.instance.manage.save');

    Route::post('/instance/manage/remove/', 'InstanceController@remove')->name('admin.instance.manage.remove');

});

/*
 *  ALL ROUTES
 */

Route::prefix('{instance}')->group(function () {

    Route::get('/', 'HomeController@index')->name('home');
    Route::get('/login', 'LoginInstanceController@login')->name('instance.login');
    Route::post('/login_p', 'LoginInstanceController@login_p')->name('instance.login_p');

    Route::get('/evidence/list', 'EvidenceController@list')->name('evidence.list');
    Route::get('/evidence/create', 'EvidenceController@create')->name('evidence.create');
    Route::post('/evidence/new', 'EvidenceController@new')->name('evidence.new');

    Route::middleware(['checknotnull:Evidence'])->group(function () {

    });

});

//Route::get('20/login', 'LoginInstanceController@login')->name('instance.login');


//Route::get('/home', 'HomeController@index')->name('home');
