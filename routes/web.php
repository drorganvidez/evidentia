<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\EvidenceCoordinatorController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
//use \Livewire\Controllers\HttpConnectionHandler;

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
Route::group(['prefix' => '{instance}'], function(){

    // Livewire routes
    Route::post('livewire/message/{name}', [
        'uses' => '\Livewire\Controllers\HttpConnectionHandler@__invoke'
    ]);

    // App login routes
    Route::get('/login', 'LoginInstanceController@login')->name('instance.login');
    Route::post('/login_p', 'LoginInstanceController@login_p')->name('instance.login_p');
    Route::post('/logout', 'LoginInstanceController@logout')->name('instance.logout');

    Route::group(['middleware' => ['checksession', 'checkblock']], function(){

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
                        Route::get('edit/{id}', 'edit_api_token')->name('developer.editapitoken');
                        Route::post('edit', 'edit_api_token_p')->name('developer.editapitoken_p');
                        Route::post('create_p', 'create_api_token_p')->name('developer.createapitoken_p');
                        Route::post('delete', 'delete_api_token_p')->name('developer.deleteapitoken_p');
                        Route::post('delete/mass', 'delete_mass_api_token_p')->name('developer.deletemassapitoken_p');
                        Route::get('', 'list_api_tokens')->name('developer.apitokens');
                    });

                });

            });
        });

        // Evidences
        Route::group(['prefix' => 'evidences'], function() {

            Route::controller(EvidenceController::class)->group(function () {

                // Create evidence
                Route::group(['prefix' => 'create'], function() {
                    Route::get('', 'create')->name('evidences.create');
                    Route::post('draft', 'create_draft')->name('evidences.create.draft');
                    Route::post('publish', 'create_publish')->name('evidences.create.publish');
                });

                // Edit evidence
                Route::group(['prefix' => 'edit', 'middleware' => 'evidencemine'], function() {
                    Route::get('{id}', 'edit')->name('evidences.edit');
                    Route::post('draft', 'edit_draft')->name('evidences.edit.draft');
                    Route::post('publish', 'edit_publish')->name('evidences.edit.publish');
                });

                // Delete evidence
                Route::group(['prefix' => 'delete', 'middleware' => 'evidencemine'], function() {
                    Route::post('', 'delete')->name('evidences.delete_p');
                });

                // List evidences
                Route::get('draft', 'list_draft')->name('evidences.draft');
                Route::get('pending', 'list_pending')->name('evidences.pending');
                Route::get('accepted', 'list_accepted')->name('evidences.accepted');
                Route::get('rejected', 'list_rejected')->name('evidences.rejected');

                // Delete autosaved
                Route::post('autosaved', 'delete_autosaved')->name('evidences.delete.autosaved')->middleware('evidencemine');

                // View evidence
                Route::get('view/{id}', 'view')->name('evidences.view');

                // Export evidences
                Route::get('export/{id}', 'export')->name('evidences.export');
                Route::post('export/mass', 'export_mass')->name('evidences.export.mass');

                // Reassign evidence
                Route::get('reassign/{id}', 'reassign')->name('evidences.reassign');
                Route::post('reassign', 'reassign_p')->name('evidences.reassign_p');

            });

        });

        Route::group(['prefix' => 'coordinator'], function() {

            Route::group(['prefix' => 'evidences'], function() {
                Route::controller(EvidenceCoordinatorController::class)->group(function () {
                    Route::get('', 'evidences_list')->name('coordinator.evidences.list');
                    Route::get('moderate', 'evidences_moderate')->name('coordinator.evidences.moderate');
                    Route::get('moderate/{id}', 'evidences_moderate_evidence')->name('coordinator.evidences.moderate.evidence');
                    Route::post('moderate', 'evidences_moderate_evidence_p')->name('coordinator.evidences.moderate.evidence_p');
                });
            });

            Route::group(['prefix' => 'tasks'], function() {
                Route::controller(EvidenceCoordinatorController::class)->group(function () {
                    Route::get('kanban', 'kanban')->name('coordinator.tasks.kanban');
                    Route::get('reports', 'reports')->name('coordinator.tasks.reports');
                });
            });



        });

        // Settings
        Route::group(['prefix' => 'download'], function() {

            Route::controller(DownloadController::class)->group(function () {
                Route::get('file/{file_id}', 'download_file')->name('download.file');
            });

        });
    });



});

