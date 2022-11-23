<?php

use App\Http\Controllers\AttendeeController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\MeetingSecretaryController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\IncidenceController;
use App\Http\Controllers\SignController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;

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

Route::post('/deploy', 'DeployController@deploy')->name('deploy');

Auth::routes();

/*
 *  MAIN ROUTE
 */

Route::get('/', 'MetaHomeController@home')->name('instances.home');

/*
 *  ADMIN ROUTES
 */
Route::group(['prefix' => 'admin'], function(){

    Route::get('', 'AdminController@home')->name('admin.home');
    Route::get('login', 'LoginAdminController@login')->name('admin.login');
    Route::post('login_p', 'LoginAdminController@login_p')->name('admin.login_p');
    Route::post('logout', 'LoginAdminController@logout')->name('admin.logout');

    Route::group(['middleware' => ['checkisadministrator']], function(){
        /*
         *  MANAGE INSTANCES
         */
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

/*
 *  ALL ROUTES
 */

// Sign
Route::get('/{instance}/sign/{random_identifier}',[SignController::class,'sign'])->name('sign');
Route::post('/{instance}/sign_p',[SignController::class,'sign_p'])->name('sign_p');
Route::get('/{instance}/finish',[SignController::class,'finish'])->name('sign.finish');

Route::group(['prefix' => '{instance}', 'middleware' => ['checkblock']], function(){

    Route::get('/change/{evidence}', function (Request $request) {
        // ...
    });

    /*
     *  DOWNLOADS
     */
    Route::get('download/request/{id}', [DownloadController::class, 'request_download'])->name('download.request');
    Route::get('download/minutes/{id}', [DownloadController::class, 'minutes_download'])->name('download.minutes');

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
    Route::post('/logout', 'LoginInstanceController@logout')->name('instance.logout');

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
     *
     *  UPLOADS
     */
    Route::post('/evidence/upload/process','UploadController@process')->name('upload.process');
    Route::delete('/evidence/upload/process','UploadController@delete')->name('upload.revert');

    Route::get('/evidence/upload/load/{file_name}','UploadController@load')->name('upload.load');
    Route::get('/evidence/upload/remove/{file_name}','UploadController@remove')->name('upload.remove');

    Route::post('/xls/upload/process','UploadController@process')->name('xls.upload.process');
    Route::get('/xls/upload/remove/{file_name}','UploadController@remove')->name('xls.upload.remove');

    /**
     * TRANSACTION
     */
    Route::get('/transaction/list', 'TransactionController@list')->name('transaction.list');
    Route::get('/transaction/create', 'TransactionController@create')->name('transaction.create');

    Route::post('/transaction/publish', 'TransactionController@publish')->name('transaction.publish');


    Route::get('/transaction/list/rejected', 'TransactionController@rejected')->name('transaction.rejected');
    Route::get('/transaction/list/acepted', 'TransactionController@accepted')->name('transaction.accepted');
    


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

    Route::get('/evidence/list/export/{ext}',[EvidenceController::class , 'export'])->name('evidence.list.export');

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
        Route::get('/evidence/export/{type}/{ext}','EvidenceCoordinatorController@evidences_export')->name('coordinator.evidence.export');

        Route::middleware(['checknotnull:Evidence','evidencefrommycommittee'])->group(function () {
            Route::get('/evidence/view/{id}', 'EvidenceController@view')->name('coordinator.evidence.view');

            Route::middleware(['checkvalidateevidences'])->group(function () {
                Route::get('/evidence/accept/{id}', 'EvidenceCoordinatorController@accept')->name('coordinator.evidence.accept');
                Route::post('/evidence/reject/', 'EvidenceCoordinatorController@reject')->name('coordinator.evidence.reject');
            });
        });
    });

    /**
     *  INCIDENCES
     */
    Route::get('/incidence/list/export/{ext}',[IncidenceController::class , 'export'])->name('incidence.list.export');
    Route::get('/incidence/list', 'IncidenceController@list')->name('incidence.list');
    
    Route::get('/incidence/create', 'IncidenceController@create')->name('incidence.createAndEditIncidence');

    Route::middleware(['checkuploadincidence'])->group(function () {
        Route::post('/incidence/publish', 'IncidenceController@publish')->name('incidence.publish');
    });
    Route::middleware(['checkuploadincidence' , 'checkincidenceisnotinreview'])->group(function () {
        Route::post('/incidence/remove', 'IncidenceController@remove')->name('incidence.remove');
    });
    Route::middleware(['checknotnull:IncidenceProof','checkincidenceproofdownload'])->group(function () {
        Route::get('/incidence/proof/download/{id}', 'IncidenceProofController@download')->name('incidence.proof.download');
    });

   
    Route::get('/incidence/view/{id}', 'IncidenceController@view')->name('incidence.view');

    /**
     *  COORDINATOR INCIDENCES
     */

    Route::prefix('coordinator')->group(function () {
        Route::get('/incidence/list/all', 'IncidenceCoordinatorController@all')->name('coordinator.incidence.list.all');
        Route::get('/incidence/list/pending', 'IncidenceCoordinatorController@pending')->name('coordinator.incidence.list.pending');
        Route::get('/incidence/list/inreview', 'IncidenceCoordinatorController@inreview')->name('coordinator.incidence.list.inreview');
        Route::get('/incidence/list/closed', 'IncidenceCoordinatorController@closed')->name('coordinator.incidence.list.closed');
        Route::get('/incidence/export/{type}/{ext}','IncidenceCoordinatorController@incidences_export')->name('coordinator.incidence.export');


        Route::middleware(['checknotnull:Incidence', 'incidencefrommycommittee'])->group(function () {
            Route::get('/incidence/view/{id}', 'IncidenceController@view')->name('coordinator.incidence.view');

            Route::middleware(['checkvalidateincidences'])->group(function () {
                Route::post('/incidence/close', 'IncidenceCoordinatorController@close')->name('coordinator.incidence.close');
                Route::get('/incidence/review/{id}', 'IncidenceCoordinatorController@review')->name('coordinator.incidence.review');
            });
        });
    });
        

       
    /**
     *  MEETINGS, LISTS AND BONUS
     */

    Route::get('/meeting/list/', 'MeetingController@list')->name('meeting.list');
    Route::get('/president/meeting/export/{ext}','MeetingController@meeting_export')->name('president.manage.meeting.export');

    Route::get('/meeting/list/export/{ext}',[MeetingController::class , 'export'])->name('meeting.list.export');

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

        Route::prefix('meeting/manage')->group(function () {
            Route::get('/', [MeetingSecretaryController::class, 'manage'])->name('secretary.meeting.manage');

            // Convocatorias
            Route::prefix('request')->group(function () {
                Route::get('list', [MeetingSecretaryController::class, 'request_list'])->name('secretary.meeting.manage.request.list');

                Route::get('create', [MeetingSecretaryController::class, 'request_create'])->name('secretary.meeting.manage.request.create');
                Route::post('new', [MeetingSecretaryController::class, 'request_new'])->name('secretary.meeting.manage.request.new');

                Route::get('download/{id}', [MeetingSecretaryController::class, 'request_download'])->name('secretary.meeting.manage.request.download');

                Route::middleware(['meetingrequestmine'])->group(function () {
                    Route::get('edit/{id}', [MeetingSecretaryController::class, 'request_edit'])->name('secretary.meeting.manage.request.edit');
                    Route::post('save', [MeetingSecretaryController::class, 'request_save'])->name('secretary.meeting.manage.request.save');

                    Route::post('remove', [MeetingSecretaryController::class, 'request_remove'])->name('secretary.meeting.manage.request.remove');
                });
                Route::get('export/{ext}',[MeetingSecretaryController::class , 'meeting_requests_export'])->name('secretary.meeting.manage.request.export');
            });


            // Hojas de firmas
            Route::prefix('signaturesheet')->group(function () {
                Route::get('list', [MeetingSecretaryController::class, 'signaturesheet_list'])->name('secretary.meeting.manage.signaturesheet.list');

                Route::get('create', [MeetingSecretaryController::class, 'signaturesheet_create'])->name('secretary.meeting.manage.signaturesheet.create');
                Route::post('new', [MeetingSecretaryController::class, 'signaturesheet_new'])->name('secretary.meeting.manage.signaturesheet.new');

                Route::middleware(['signaturesheetmine'])->group(function () {
                    Route::get('edit/{id}', [MeetingSecretaryController::class, 'signaturesheet_edit'])->name('secretary.meeting.manage.signaturesheet.edit');
                    Route::post('save', [MeetingSecretaryController::class, 'signaturesheet_save'])->name('secretary.meeting.manage.signaturesheet.save');
                    Route::post('remove', [MeetingSecretaryController::class, 'signaturesheet_remove'])->name('secretary.meeting.manage.signaturesheet.remove');
                });

                Route::get('view/{signature_sheet}', [MeetingSecretaryController::class, 'signaturesheet_view'])->name('secretary.meeting.manage.signaturesheet.view');
                Route::get('export/{ext}',[MeetingSecretaryController::class , 'signaturesheet_export'])->name('secretary.meeting.manage.signaturesheet.export');
            });

            // Actas
            Route::prefix('minutes')->group(function () {
                Route::get('list', [MeetingSecretaryController::class, 'minutes_list'])->name('secretary.meeting.manage.minutes.list');
                Route::get('create', [MeetingSecretaryController::class, 'minutes_create'])->name('secretary.meeting.manage.minutes.create');

                Route::get('create/step1', [MeetingSecretaryController::class, 'minutes_create_step1'])->name('secretary.meeting.manage.minutes.create.step1');
                Route::post('create/step1_p', [MeetingSecretaryController::class, 'minutes_create_step1_p'])->name('secretary.meeting.manage.minutes.create.step1_p');

                Route::get('create/step2', [MeetingSecretaryController::class, 'minutes_create_step2'])->name('secretary.meeting.manage.minutes.create.step2');
                Route::post('create/step2_p', [MeetingSecretaryController::class, 'minutes_create_step2_p'])->name('secretary.meeting.manage.minutes.create.step2_p');

                Route::get('create/step3', [MeetingSecretaryController::class, 'minutes_create_step3'])->name('secretary.meeting.manage.minutes.create.step3');
                Route::post('create/step3_p', [MeetingSecretaryController::class, 'minutes_create_step3_p'])->name('secretary.meeting.manage.minutes.create.step3_p');

                Route::get('download/{id}', [MeetingSecretaryController::class, 'minutes_download'])->name('secretary.meeting.manage.minutes.download');
                Route::get('export/{ext}',[MeetingSecretaryController::class , 'meeting_minutes_export'])->name('secretary.meeting.manage.minutes.export');


                Route::middleware(['meetingminutesmine'])->group(function () {
                    Route::get('edit/{id}', [MeetingSecretaryController::class, 'minutes_edit'])->name('secretary.meeting.manage.minutes.edit');
                    Route::post('save', [MeetingSecretaryController::class, 'minutes_save'])->name('secretary.meeting.manage.minutes.save');

                    Route::post('remove', [MeetingSecretaryController::class, 'minutes_remove'])->name('secretary.meeting.manage.minutes.remove');
                });

            });


        });

        // Consulta AJAX
        Route::get('/meeting/defaultlist/{id}', 'MeetingSecretaryController@defaultlist')->name('secretary.meeting.defaultlist');

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

    Route::get('/attendee/list/export/{ext}',[AttendeeController::class , 'export'])->name('attendee.list.export');

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

    Route::get('/registercoordinator/attendee/export','EventbriteController@attendee_export')->name('registercoordinator.attendee.export');

    Route::get('/registercoordinator/event/export/{ext}','EventbriteController@events_export')->name('registercoordinator.events.export');


    /**
     *  PRESIDENT
     */
    Route::middleware(['checkroles:PRESIDENT'])->group(function () {
        Route::get('/president/config', 'ConfigController@config')->name('president.config');
        Route::post('/president/config/save', 'ConfigController@config_save')->name('president.config.save');
    });

    Route::get('/president/user/list','ManagementController@user_list')->name('president.user.list');
    Route::get('/president/evidence/list','ManagementController@evidence_list')->name('president.evidence.list');
    Route::get('/president/transaction/list','ManagementController@transaction_list')->name('president.transaction.list');
    Route::get('/president/transaction/accept/{id}','ManagementController@accept')->name('president.transaction.accept');
    Route::get('/president/transaction/reject/{id}','ManagementController@reject')->name('president.transaction.reject');
    Route::get('/transaction/export/{type}/{ext}', 'TransactionController@transaction_export')->name('transaction.export');
    Route::get('/president/meeting/list','ManagementController@meeting_list')->name('president.meeting.list');

    Route::get('/president/comittee/list','ManagementController@comittee_list')->name('president.comittee.list');
    Route::post('/president/comittee/management/save','ManagementController@comittee_save')->name('president.comittee.management.save');
    Route::post('/president/comittee/management/new','ManagementController@comittee_new')->name('president.comittee.management.new');

    Route::middleware(['checknotnull:Comittee'])->group(function () {
        Route::post('/president/comittee/management/remove', 'ManagementController@comittee_remove')->name('president.comittee.management.remove');
    });

    Route::get('/president/user/management/{id}','ManagementController@user_management')->name('president.user.management');
    Route::post('/president/user/management/save','ManagementController@user_management_save')->name('president.user.management.save');


    Route::get('/president/export','ImportExportController@export')->name('president.export');
    Route::get('/management/export/{ext}','ManagementController@evidences_export')->name('management.export');
    Route::post('/president/export/save','ImportExportController@export_save')->name('president.export.save');

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

    Route::get('/lecture/user/management/{id}','ManagementController@user_management')->name('lecture.user.management');
    Route::post('/lecture/user/management/save','ManagementController@user_management_save')->name('lecture.user.management.save');

    Route::get('/lecture/instances','QuickInstances@list')->name('lecture.instances.list');
    Route::post('/lecture/instances/save','QuickInstances@save')->name('lecture.instances.save');

    /*
     *  User Management
     */
    Route::middleware(['checkroles:LECTURE'])->group(function () {
        Route::post('/management/user/delete/all',['App\Http\Controllers\ManagementController','user_management_delete_all'])->name('management.user.delete.all');
    });
    Route::post('/management/user/new',['App\Http\Controllers\ManagementController','user_management_new'])->name('management.user.new');
    Route::get('/president/user/export/{ext}','ManagementController@management_student_export')->name('president.manage.student.export');


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

    /**
     * SUGGESTIONS MAILBOX
     */

    Route::get('/suggestionsmailbox','SuggestionsMailboxController@suggestionsmailbox')->name('suggestionsmailbox');
    Route::post('/suggestionsmailbox_p','SuggestionsMailboxController@suggestionsmailbox_p')->name('suggestionsmailbox_p');

    /**
     *  GIT
     */
    Route::get('/updates','GitController@list')->name('updates.list');

});
