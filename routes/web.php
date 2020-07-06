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

Route::get('/', 'MetaAdminController@list')->name('instances.home');;

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

    /**
     *  GENERAL PURPOSE
     */
    Route::get('/gp/users/all', 'GeneralPurposeController@users_all')->name('gp.users.all');

    Route::get('/', 'HomeController@index')->name('home');
    Route::get('/login', 'LoginInstanceController@login')->name('instance.login');
    Route::post('/login_p', 'LoginInstanceController@login_p')->name('instance.login_p');

    /**
     *  PROFILE
     */
    Route::get('/profile', 'ProfileController@view')->name('profile.view');
    Route::post('/profile/upload/info', 'ProfileController@upload_info')->name('profile.upload.info');
    Route::post('/profile/upload/biography', 'ProfileController@upload_biography')->name('profile.upload.biography');
    Route::post('/profile/upload/pass', 'ProfileController@upload_pass')->name('profile.upload.pass');

    /**
     *  MESSAGES
     */
    Route::get('/mailbox', 'MessageController@mailbox')->name('message.mailbox');

    /**
     *  EVIDENCES
     */
    Route::get('/evidence/list', 'EvidenceController@list')->name('evidence.list');
    Route::get('/evidence/create', 'EvidenceController@create')->name('evidence.create');
    Route::post('/evidence/draft', 'EvidenceController@draft')->name('evidence.draft');
    Route::post('/evidence/publish', 'EvidenceController@publish')->name('evidence.publish');
    Route::post('/evidence/draft/edit', 'EvidenceController@draft_edit')->name('evidence.draft.edit');
    Route::post('/evidence/publish/edit', 'EvidenceController@publish_edit')->name('evidence.publish.edit');

    Route::middleware(['checknotnull:Evidence'])->group(function () {
        Route::get('/evidence/view/{id}', 'EvidenceController@view')->name('evidence.view')->middleware('evidencemine');
        Route::get('/evidence/edit/{id}', 'EvidenceController@edit')->name('evidence.edit')->middleware(['evidencecanbeedited','evidencemine']);
        Route::post('/evidence/reedit', 'EvidenceController@reedit')->name('evidence.reedit')->middleware('evidencemine');
        Route::post('/evidence/remove', 'EvidenceController@remove')->name('evidence.remove')->middleware('evidencemine');
    });

    // EVIDENCES MANAGEMENT BY A COORDINATOR
    Route::prefix('coordinator')->group(function () {
        Route::get('/evidence/list/search', 'SearchController@coordinator_search_evidences')->name('coordinator.evidence.list.search');
        Route::get('/evidence/list/all', 'EvidenceCoordinatorController@all')->name('coordinator.evidence.list.all');
        Route::get('/evidence/list/pending', 'EvidenceCoordinatorController@pending')->name('coordinator.evidence.list.pending');
        Route::get('/evidence/list/accepted', 'EvidenceCoordinatorController@accepted')->name('coordinator.evidence.list.accepted');
        Route::get('/evidence/list/rejected', 'EvidenceCoordinatorController@rejected')->name('coordinator.evidence.list.rejected');

        Route::middleware(['checknotnull:Evidence'])->group(function () {
            Route::get('/evidence/view/{id}', 'EvidenceController@view')->name('coordinator.evidence.view')->middleware('evidencefrommycommittee');
            Route::get('/evidence/accept/{id}', 'EvidenceCoordinatorController@accept')->name('coordinator.evidence.accept')->middleware('evidencefrommycommittee');
            Route::post('/evidence/reject/', 'EvidenceCoordinatorController@reject')->name('coordinator.evidence.reject')->middleware('evidencefrommycommittee');
        });

    });

    /**
     *  MEETINGS
     */

    Route::get('/meeting/list/', 'MeetingController@list')->name('meeting.list');

    Route::prefix('secretary')->group(function () {

        /*
         *  DEFAULT LISTS
         */
        Route::get('/defaultlist/list/', 'DefaultListSecretaryController@list')->name('secretary.defaultlist.list');
        Route::get('/defaultlist/list/create', 'DefaultListSecretaryController@create')->name('secretary.defaultlist.create');
        Route::post('/defaultlist/list/new', 'DefaultListSecretaryController@new')->name('secretary.defaultlist.new');

        Route::middleware(['checknotnull:DefaultList'])->group(function () {
            Route::get('/defaultlist/edit/{id}', 'DefaultListSecretaryController@edit')->name('secretary.defaultlist.edit');
            Route::post('/defaultlist/save', 'DefaultListSecretaryController@save')->name('secretary.defaultlist.save');
            Route::post('/defaultlist/remove', 'DefaultListSecretaryController@remove')->name('secretary.defaultlist.remove');
        });

        /*
         * MEETINGS
         */
        Route::get('/meeting/list/', 'MeetingSecretaryController@list')->name('secretary.meeting.list');
        Route::get('/meeting/create/', 'MeetingSecretaryController@create')->name('secretary.meeting.create');
        Route::post('/meeting/list/new', 'MeetingSecretaryController@new')->name('secretary.meeting.new');
        Route::get('/meeting/defaultlist/{id}', 'MeetingSecretaryController@defaultlist')->name('secretary.meeting.defaultlist');

        Route::middleware(['checknotnull:Meeting'])->group(function () {
            Route::get('/meeting/edit/{id}', 'MeetingSecretaryController@edit')->name('secretary.meeting.edit');
            Route::post('/meeting/save', 'MeetingSecretaryController@save')->name('secretary.meeting.save');
            Route::post('/meeting/remove', 'MeetingSecretaryController@remove')->name('secretary.meeting.remove');
        });

    });


    /**
     *  PROOFS
     */
    Route::post('/proof/remove/', 'ProofController@remove')->name('proof.remove');

    /**
     *  FILES
     */

    Route::post('/file/remove/', 'FileController@remove')->name('file.remove');
    Route::middleware(['checknotnull:File'])->group(function () {
        Route::get('/file/download/{id}', 'FileController@download')->name('file.download');
    });

    /**
     *  AVATAR
     */
    Route::get('/avatar/{id}', 'AvatarController@avatar')->name('avatar');

    /**
     *  LECTURE
     */
    Route::get('/lecture/user/list','UserManagerController@user_list')->name('lecture.user.list');

    Route::get('/lecture/config','ConfigController@config')->name('lecture.config');
    Route::post('/lecture/config/save','ConfigController@config_save')->name('lecture.config.save');

    Route::get('/lecture/integrity','IntegrityController@integrity')->name('lecture.integrity');

    Route::get('/lecture/import','ImportExportController@import')->name('lecture.import');
    Route::post('/lecture/import/save','ImportExportController@import_save')->name('lecture.import.save');

    Route::get('/lecture/export','ImportExportController@export')->name('lecture.export');
    Route::post('/lecture/export/save','ImportExportController@export_save')->name('lecture.export.save');

    /**
     *  PROFILES
     */
    Route::get('/profiles/view/{id}','ProfileController@profiles_view')->name('profiles.view');


});
