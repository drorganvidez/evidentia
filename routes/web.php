<?php

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

/*
 *  DEPLOY ROUTE
 */

Route::get('/deploy/{token}', 'DeployController@deploy')->name('deploy');
Route::get('/deploy/instance/{token}', 'DeployController@deploy_default_instance')->name('deploy.instance');

Auth::routes();

/*
 *  MAIN ROUTE
 */

Route::get('/', 'MetaAdminController@list')->name('instances.home');

/*
 *  META ADMIN ROUTES
 */

Route::get('administration', 'MetaAdminController@admin')->name('admin');



Route::prefix('admin')->group(function () {

    Route::get('/instance/manage', 'InstanceController@manage')->name('admin.instance.manage');

    Route::get('instance/create', 'InstanceController@create')->name('admin.instance.create');
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


Route::group(['prefix' => '{instance}', 'middleware' => ['checkblock']], function(){

    /**
     *  BLOCK
     */
    Route::get('/block','BlockController@block')->name('block');

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
    Route::middleware(['checkuploadevidences'])->group(function () {
        Route::get('/evidence/create', 'EvidenceController@create')->name('evidence.create');
        Route::post('/evidence/draft', 'EvidenceController@draft')->name('evidence.draft');
        Route::post('/evidence/publish', 'EvidenceController@publish')->name('evidence.publish');
        Route::post('/evidence/draft/edit', 'EvidenceController@draft_edit')->name('evidence.draft.edit');
        Route::post('/evidence/publish/edit', 'EvidenceController@publish_edit')->name('evidence.publish.edit');
    });

    Route::middleware(['checknotnull:Evidence','evidencemine'])->group(function () {
        Route::get('/evidence/view/{id}', 'EvidenceController@view')->name('evidence.view');

        Route::middleware(['checkuploadevidences'])->group(function () {
            Route::get('/evidence/edit/{id}', 'EvidenceController@edit')->name('evidence.edit')->middleware('evidencecanbeedited');
            Route::post('/evidence/reedit', 'EvidenceController@reedit')->name('evidence.reedit');
            Route::post('/evidence/remove', 'EvidenceController@remove')->name('evidence.remove');
        });
    });

    // EVIDENCES MANAGEMENT BY A COORDINATOR
    Route::prefix('coordinator')->group(function () {
        Route::get('/evidence/list/all', 'EvidenceCoordinatorController@all')->name('coordinator.evidence.list.all');
        Route::get('/evidence/list/pending', 'EvidenceCoordinatorController@pending')->name('coordinator.evidence.list.pending');
        Route::get('/evidence/list/accepted', 'EvidenceCoordinatorController@accepted')->name('coordinator.evidence.list.accepted');
        Route::get('/evidence/list/rejected', 'EvidenceCoordinatorController@rejected')->name('coordinator.evidence.list.rejected');

        Route::middleware(['checknotnull:Evidence','evidencefrommycommittee'])->group(function () {
            Route::get('/evidence/view/{id}', 'EvidenceController@view')->name('coordinator.evidence.view');

            Route::middleware(['checkvalidateevidences'])->group(function () {
                Route::get('/evidence/accept/{id}', 'EvidenceCoordinatorController@accept')->name('coordinator.evidence.accept');
                Route::post('/evidence/reject/', 'EvidenceCoordinatorController@reject')->name('coordinator.evidence.reject');
            });
        });

    });

    /**
     *  MEETINGS, LISTS AND BONUS
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

        Route::middleware(['checkregistermeetings'])->group(function () {
            Route::get('/meeting/create/', 'MeetingSecretaryController@create')->name('secretary.meeting.create');
            Route::post('/meeting/new', 'MeetingSecretaryController@new')->name('secretary.meeting.new');
        });

        // Consulta AJAX
        Route::get('/meeting/defaultlist/{id}', 'MeetingSecretaryController@defaultlist')->name('secretary.meeting.defaultlist');

        Route::middleware(['checknotnull:Meeting','checkregistermeetings'])->group(function () {
            Route::get('/meeting/edit/{id}', 'MeetingSecretaryController@edit')->name('secretary.meeting.edit');
            Route::post('/meeting/save', 'MeetingSecretaryController@save')->name('secretary.meeting.save');
            Route::post('/meeting/remove', 'MeetingSecretaryController@remove')->name('secretary.meeting.remove');
        });

        /*
         * BONUS
         */
        Route::get('/bonus/list/', 'BonusSecretaryController@list')->name('secretary.bonus.list');

        Route::middleware(['checkregisterbonus'])->group(function () {
            Route::get('/bonus/create/', 'BonusSecretaryController@create')->name('secretary.bonus.create');
            Route::post('/bonus/new', 'BonusSecretaryController@new')->name('secretary.bonus.new');
        });

        Route::middleware(['checknotnull:Bonus','checkregisterbonus'])->group(function () {
            Route::get('/bonus/edit/{id}', 'BonusSecretaryController@edit')->name('secretary.bonus.edit');
            Route::post('/bonus/save', 'BonusSecretaryController@save')->name('secretary.bonus.save');
            Route::post('/bonus/remove', 'BonusSecretaryController@remove')->name('secretary.bonus.remove');
        });

    });

    /**
     *  ATTENDEES
     */
    Route::get('/attendee/list/', 'AttendeeController@list')->name('attendee.list');


    /**
     *  PROOFS
     */
    Route::post('/proof/remove/', 'ProofController@remove')->name('proof.remove');

    /**
     *  FILES
     */

    Route::post('/file/remove/', 'FileController@remove')->name('file.remove');
    Route::middleware(['checknotnull:Proof','checkproofdownload'])->group(function () {
        Route::get('/proof/download/{id}', 'ProofController@download')->name('proof.download');
    });

    /**
     *  AVATAR
     */
    Route::middleware(['checknotnull:User'])->group(function () {
        Route::get('/avatar/{id}', 'AvatarController@avatar')->name('avatar');
    });

    /**
     *  REGISTER COORDINATOR
     */
    Route::middleware(['checkregistereventsandattendings'])->group(function () {
        Route::get('/registercoordinator/token/', 'EventbriteController@token')->name('registercoordinator.token');
        Route::post('/registercoordinator/token/save', 'EventbriteController@token_save')->name('registercoordinator.token.save');

        Route::get('/registercoordinator/event/load', 'EventbriteController@event_load')->name('registercoordinator.event.load');
        Route::get('/registercoordinator/attendee/load', 'EventbriteController@attendee_load')->name('registercoordinator.attendee.load');
    });

    Route::get('/registercoordinator/event/list','EventbriteController@event_list')->name('registercoordinator.event.list');
    Route::get('/registercoordinator/attendee/list','EventbriteController@attendee_list')->name('registercoordinator.attendee.list');

    /**
     *  PRESIDENT
     */
    Route::middleware(['checkroles:PRESIDENT'])->group(function () {
        Route::get('/president/config', 'ConfigController@config')->name('president.config');
        Route::post('/president/config/save', 'ConfigController@config_save')->name('president.config.save');
    });

    Route::get('/president/user/list','ManagementController@user_list')->name('president.user.list');
    Route::get('/president/evidence/list','ManagementController@evidence_list')->name('president.evidence.list');
    Route::get('/president/meeting/list','ManagementController@meeting_list')->name('president.meeting.list');

    Route::get('/president/comittee/list','ManagementController@comittee_list')->name('president.comittee.list');
    Route::post('/president/comittee/management/save','ManagementController@comittee_save')->name('president.comittee.management.save');
    Route::post('/president/comittee/management/new','ManagementController@comittee_new')->name('president.comittee.management.new');

    Route::middleware(['checknotnull:Comittee'])->group(function () {
        Route::post('/president/comittee/management/remove', 'ManagementController@comittee_remove')->name('president.comittee.management.remove');
    });

    Route::middleware(['checknotnull:User'])->group(function () {
        Route::get('/president/user/management/{id}','ManagementController@user_management')->name('president.user.management');
        Route::post('/president/user/management/save','ManagementController@user_management_save')->name('president.user.management.save');
    });

    /**
     *  LECTURE
     */
    Route::get('/lecture/user/list','ManagementController@user_list')->name('lecture.user.list');
    Route::get('/lecture/evidence/list','ManagementController@evidence_list')->name('lecture.evidence.list');
    Route::get('/lecture/meeting/list','ManagementController@meeting_list')->name('lecture.meeting.list');

    Route::middleware(['checkroles:LECTURE'])->group(function () {
        Route::get('/lecture/config', 'ConfigController@config')->name('lecture.config');
        Route::post('/lecture/config/save', 'ConfigController@config_save')->name('lecture.config.save');
    });

    Route::get('/lecture/comittee/list','ManagementController@comittee_list')->name('lecture.comittee.list');
    Route::post('/lecture/comittee/management/save','ManagementController@comittee_save')->name('lecture.comittee.management.save');
    Route::post('/lecture/comittee/management/new','ManagementController@comittee_new')->name('lecture.comittee.management.new');

    Route::middleware(['checknotnull:Comittee'])->group(function () {
        Route::post('/lecture/comittee/management/remove', 'ManagementController@comittee_remove')->name('lecture.comittee.management.remove');
    });

    Route::get('/lecture/integrity','IntegrityController@integrity')->name('lecture.integrity');

    Route::get('/lecture/import','ImportExportController@import')->name('lecture.import');
    Route::post('/lecture/import/save','ImportExportController@import_save')->name('lecture.import.save');

    Route::get('/lecture/export','ImportExportController@export')->name('lecture.export');
    Route::post('/lecture/export/save','ImportExportController@export_save')->name('lecture.export.save');

    Route::middleware(['checknotnull:User'])->group(function () {
        Route::get('/lecture/user/management/{id}','ManagementController@user_management')->name('lecture.user.management');
        Route::post('/lecture/user/management/save','ManagementController@user_management_save')->name('lecture.user.management.save');
    });

    Route::get('/lecture/instances','QuickInstances@list')->name('lecture.instances.list');
    Route::post('/lecture/instances/save','QuickInstances@save')->name('lecture.instances.save');

    /**
     *  PROFILES
     */

    // Solo visibles para profesores y presidentes
    Route::middleware(['checkroles:LECTURE|PRESIDENT'])->group(function () {
        Route::middleware(['checknotnull:User'])->group(function () {
            Route::get('/profiles/view/{id}','ProfileController@profiles_view')->name('profiles.view');
        });
        Route::get('/profiles/view/{id_user}/evidence/{id_evidence}','ProfileController@evidences_view')->name('profiles.view.evidence');
    });

    /**
     *  RANDOMIZE EVIDENCES
     */

    Route::middleware(['checkroles:LECTURE'])->group(function () {
        Route::get('/randomize','RandomizeController@randomize')->name('randomize.randomize');
        Route::post('/randomize/save','RandomizeController@randomize_save')->name('randomize.save');
    });

    /**
     *  RESET PASSWORDS
     */

    Route::prefix('password')->group(function () {
        Route::get('/reset','PasswordResetController@reset')->name('password.reset');
        Route::post('/reset_p','PasswordResetController@reset_p')->name('password.reset_p');

        Route::get('/update/{token}','PasswordResetController@update')->name('password.update');
        Route::post('/update_p/{token}','PasswordResetController@update_p')->name('password.update_p');
    });

});
