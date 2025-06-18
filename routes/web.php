<?php

use App\Http\Controllers\AttendeeController;
use App\Http\Controllers\AvatarController;
// Controladores importados explícitamente
use App\Http\Controllers\BonusSecretaryController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\DefaultListSecretaryController;
use App\Http\Controllers\EventbriteController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\EvidenceCoordinatorController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\GeneralPurposeController;
use App\Http\Controllers\GitController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\IntegrityController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\MeetingSecretaryController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProofController;
use App\Http\Controllers\RandomizeController;
use App\Http\Controllers\SignController;
use App\Http\Controllers\SuggestionsMailboxController;
use App\Http\Controllers\UploadController;
use App\Http\Middleware\CheckNotNull;
use App\Http\Middleware\CheckProofDownload;
use App\Http\Middleware\CheckRegisterBonus;
use App\Http\Middleware\CheckRegisterEventsAndAttendings;
use App\Http\Middleware\CheckRoles;
use App\Http\Middleware\CheckUploadEvidences;
use App\Http\Middleware\CheckValidateEvidences;
use App\Http\Middleware\EvidenceCanBeEdited;
use App\Http\Middleware\EvidenceFromMyCommittee;
use App\Http\Middleware\EvidenceMine;
use App\Http\Middleware\MeetingMinutesMine;
use App\Http\Middleware\MeetingRequestMine;
use App\Http\Middleware\SignatureSheetMine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Rutas de autenticación
Auth::routes();

// Home autenticado
Route::middleware('auth')->group(function () {

    Route::get('/', [HomeController::class, 'index'])->name('home');

    // PROFILE
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'view'])->name('profile.view');
        Route::post('upload/info', [ProfileController::class, 'upload_info'])->name('profile.upload.info');
        Route::post('upload/biography', [ProfileController::class, 'upload_biography'])->name('profile.upload.biography');
        Route::post('upload/pass', [ProfileController::class, 'upload_pass'])->name('profile.upload.pass');
    });

    // GIT
    Route::get('/updates', [GitController::class, 'list'])->name('updates.list');

    // MEETINGS, LISTS AND BONUS
    Route::prefix('meeting')->group(function () {
        Route::get('list', [MeetingController::class, 'list'])->name('meeting.list');
        Route::get('list/export/{ext}', [MeetingController::class, 'export'])->name('meeting.list.export');
    });

    Route::prefix('president')->group(function () {
        Route::get('meeting/export/{ext}', [MeetingController::class, 'meeting_export'])->name('president.manage.meeting.export');
    });

    Route::prefix('secretary')->middleware('auth')->group(function () {

        // Default Lists
        Route::controller(DefaultListSecretaryController::class)->group(function () {
            Route::get('defaultlist/list', 'list')->name('secretary.defaultlist.list');
            Route::get('defaultlist/list/create', 'create')->name('secretary.defaultlist.create');
            Route::post('defaultlist/list/new', 'new')->name('secretary.defaultlist.new');

            Route::middleware(CheckNotNull::class, ':DefaultList')->group(function () {
                Route::get('defaultlist/edit/{id}', 'edit')->name('secretary.defaultlist.edit');
                Route::post('defaultlist/save', 'save')->name('secretary.defaultlist.save');
                Route::post('defaultlist/remove', 'remove')->name('secretary.defaultlist.remove');
            });
        });

        // Meeting Manage
        Route::prefix('meeting/manage')->group(function () {

            Route::get('/', [MeetingSecretaryController::class, 'manage'])->name('secretary.meeting.manage');

            // Request
            Route::prefix('request')->group(function () {
                Route::get('list', [MeetingSecretaryController::class, 'request_list'])->name('secretary.meeting.manage.request.list');
                Route::get('create', [MeetingSecretaryController::class, 'request_create'])->name('secretary.meeting.manage.request.create');
                Route::post('new', [MeetingSecretaryController::class, 'request_new'])->name('secretary.meeting.manage.request.new');
                Route::get('download/{id}', [MeetingSecretaryController::class, 'request_download'])->name('secretary.meeting.manage.request.download');
                Route::middleware(MeetingRequestMine::class)->group(function () {
                    Route::get('edit/{id}', [MeetingSecretaryController::class, 'request_edit'])->name('secretary.meeting.manage.request.edit');
                    Route::post('save', [MeetingSecretaryController::class, 'request_save'])->name('secretary.meeting.manage.request.save');
                    Route::post('remove', [MeetingSecretaryController::class, 'request_remove'])->name('secretary.meeting.manage.request.remove');
                });
                Route::get('export/{ext}', [MeetingSecretaryController::class, 'meeting_requests_export'])->name('secretary.meeting.manage.request.export');
            });

            // Signature Sheets
            Route::prefix('signaturesheet')->group(function () {
                Route::get('list', [MeetingSecretaryController::class, 'signaturesheet_list'])->name('secretary.meeting.manage.signaturesheet.list');
                Route::get('create', [MeetingSecretaryController::class, 'signaturesheet_create'])->name('secretary.meeting.manage.signaturesheet.create');
                Route::post('new', [MeetingSecretaryController::class, 'signaturesheet_new'])->name('secretary.meeting.manage.signaturesheet.new');
                Route::middleware(SignatureSheetMine::class)->group(function () {
                    Route::get('edit/{id}', [MeetingSecretaryController::class, 'signaturesheet_edit'])->name('secretary.meeting.manage.signaturesheet.edit');
                    Route::post('save', [MeetingSecretaryController::class, 'signaturesheet_save'])->name('secretary.meeting.manage.signaturesheet.save');
                    Route::post('remove', [MeetingSecretaryController::class, 'signaturesheet_remove'])->name('secretary.meeting.manage.signaturesheet.remove');
                });
                Route::get('view/{signature_sheet}', [MeetingSecretaryController::class, 'signaturesheet_view'])->name('secretary.meeting.manage.signaturesheet.view');
                Route::get('export/{ext}', [MeetingSecretaryController::class, 'signaturesheet_export'])->name('secretary.meeting.manage.signaturesheet.export');
            });

            // Minutes
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
                Route::get('export/{ext}', [MeetingSecretaryController::class, 'meeting_minutes_export'])->name('secretary.meeting.manage.minutes.export');

                Route::middleware(MeetingMinutesMine::class)->group(function () {
                    Route::get('edit/{id}', [MeetingSecretaryController::class, 'minutes_edit'])->name('secretary.meeting.manage.minutes.edit');
                    Route::post('save', [MeetingSecretaryController::class, 'minutes_save'])->name('secretary.meeting.manage.minutes.save');
                    Route::post('remove', [MeetingSecretaryController::class, 'minutes_remove'])->name('secretary.meeting.manage.minutes.remove');
                });
            });
        });

        // AJAX default list
        Route::get('meeting/defaultlist/{id}', [MeetingSecretaryController::class, 'defaultlist'])->name('secretary.meeting.defaultlist');

        // Bonus
        Route::get('bonus/list', [BonusSecretaryController::class, 'list'])->name('secretary.bonus.list');

        Route::middleware([CheckRegisterBonus::class])->group(function () {
            Route::get('bonus/create', [BonusSecretaryController::class, 'create'])->name('secretary.bonus.create');
            Route::post('bonus/new', [BonusSecretaryController::class, 'new'])->name('secretary.bonus.new');
        });

        Route::middleware([
            CheckNotNull::class.':Bonus',
            CheckRegisterBonus::class,
        ])->group(function () {
            Route::get('bonus/edit/{id}', [BonusSecretaryController::class, 'edit'])->name('secretary.bonus.edit');
            Route::post('bonus/save', [BonusSecretaryController::class, 'save'])->name('secretary.bonus.save');
            Route::post('bonus/remove', [BonusSecretaryController::class, 'remove'])->name('secretary.bonus.remove');
        });
    });

    // ATTENDEES
    Route::get('attendee/list', [AttendeeController::class, 'list'])->name('attendee.list');
    Route::get('attendee/list/export/{ext}', [AttendeeController::class, 'export'])->name('attendee.list.export');

    // PROOFS
    Route::post('proof/remove', [ProofController::class, 'remove'])->name('proof.remove');

    Route::middleware([
        CheckNotNull::class.':Proof',
        CheckProofDownload::class,
    ])->group(function () {
        Route::get('proof/download/{id}', [ProofController::class, 'download'])->name('proof.download');
    });

    // FILES
    Route::post('file/remove', [FileController::class, 'remove'])->name('file.remove');

    // AVATAR
    Route::middleware([CheckNotNull::class.':User'])->group(function () {
        Route::get('avatar/{id}', [AvatarController::class, 'avatar'])->name('avatar');
    });

    // REGISTER COORDINATOR
    Route::middleware(CheckRegisterEventsAndAttendings::class)->group(function () {
        Route::get('registercoordinator/token', [EventbriteController::class, 'token'])->name('registercoordinator.token');
        Route::post('registercoordinator/token/save', [EventbriteController::class, 'token_save'])->name('registercoordinator.token.save');

        Route::get('registercoordinator/event/load', [EventbriteController::class, 'event_load'])->name('registercoordinator.event.load');
        Route::get('registercoordinator/attendee/load/{id}', [EventbriteController::class, 'attendee_load'])->name('registercoordinator.attendee.load');
    });

    Route::get('registercoordinator/event/list', [EventbriteController::class, 'event_list'])->name('registercoordinator.event.list');
    Route::get('registercoordinator/attendee/list', [EventbriteController::class, 'attendee_list'])->name('registercoordinator.attendee.list');
    Route::get('registercoordinator/attendee/export', [EventbriteController::class, 'attendee_export'])->name('registercoordinator.attendee.export');
    Route::get('registercoordinator/event/export/{ext}', [EventbriteController::class, 'events_export'])->name('registercoordinator.events.export');

    // PRESIDENT routes with middleware checkroles:PRESIDENT
    Route::middleware([CheckRoles::class.':PRESIDENT,LECTURE'])->group(function () {
        Route::get('president/config', [ConfigController::class, 'config'])->name('president.config');
        Route::post('president/config/save', [ConfigController::class, 'config_save'])->name('president.config.save');

        Route::get('president/user/list', [ManagementController::class, 'user_list'])->name('president.user.list');
        Route::get('president/evidence/list', [ManagementController::class, 'evidence_list'])->name('president.evidence.list');
        Route::get('president/meeting/list', [ManagementController::class, 'meeting_list'])->name('president.meeting.list');
        Route::get('president/committee/list', [ManagementController::class, 'committee_list'])->name('president.committee.list');

        Route::post('president/committee/management/save', [ManagementController::class, 'committee_save'])->name('president.committee.management.save');
        Route::post('president/committee/management/new', [ManagementController::class, 'committee_new'])->name('president.committee.management.new');
        Route::middleware(CheckNotNull::class, ':Committee')->post('president/committee/management/remove', [ManagementController::class, 'committee_remove'])->name('president.committee.management.remove');

        Route::get('president/user/management/{id}', [ManagementController::class, 'user_management'])->name('president.user.management');
        Route::post('president/user/management/save', [ManagementController::class, 'user_management_save'])->name('president.user.management.save');

        Route::get('president/export', [ImportExportController::class, 'export'])->name('president.export');
        Route::post('president/export/save', [ImportExportController::class, 'export_save'])->name('president.export.save');

        Route::get('president/user/export/{ext}', [ManagementController::class, 'management_student_export'])->name('president.manage.student.export');
    });

    // MANAGEMENT export
    Route::get('management/export/{ext}', [ManagementController::class, 'evidences_export'])->name('management.export');

    // LECTURE routes with middleware checkroles:LECTURE
    Route::middleware([CheckRoles::class.':LECTURE'])->group(function () {
        Route::get('lecture/config', [ConfigController::class, 'config'])->name('lecture.config');
        Route::post('lecture/config/save', [ConfigController::class, 'config_save'])->name('lecture.config.save');

        Route::get('lecture/committee/list', [ManagementController::class, 'committee_list'])->name('lecture.committee.list');
        Route::post('lecture/committee/management/save', [ManagementController::class, 'committee_save'])->name('lecture.committee.management.save');
        Route::post('lecture/committee/management/new', [ManagementController::class, 'committee_new'])->name('lecture.committee.management.new');

        Route::middleware(CheckNotNull::class, ':Committee')->post('lecture/committee/management/remove', [ManagementController::class, 'committee_remove'])->name('lecture.committee.management.remove');

        Route::get('lecture/integrity', [IntegrityController::class, 'integrity'])->name('lecture.integrity');

        Route::get('lecture/import', [ImportExportController::class, 'import'])->name('lecture.import');
        Route::post('lecture/import/save', [ImportExportController::class, 'import_save'])->name('lecture.import.save');

        Route::get('lecture/export', [ImportExportController::class, 'export'])->name('lecture.export');
        Route::post('lecture/export/save', [ImportExportController::class, 'export_save'])->name('lecture.export.save');

        Route::get('lecture/user/management/{id}', [ManagementController::class, 'user_management'])->name('lecture.user.management');
        Route::post('lecture/user/management/save', [ManagementController::class, 'user_management_save'])->name('lecture.user.management.save');

        Route::get('lecture/user/list', [ManagementController::class, 'user_list'])->name('lecture.user.list');
        Route::get('lecture/evidence/list', [ManagementController::class, 'evidence_list'])->name('lecture.evidence.list');
        Route::get('lecture/meeting/list', [ManagementController::class, 'meeting_list'])->name('lecture.meeting.list');

        // User management delete all
        Route::post('management/user/delete/all', [ManagementController::class, 'user_management_delete_all'])->name('management.user.delete.all');
    });

    // User management new (open)
    Route::post('management/user/new', [ManagementController::class, 'user_management_new'])->name('management.user.new');

    // PROFILES - visible for LECTURE and PRESIDENT only
    Route::middleware([CheckRoles::class.':PRESIDENT,LECTURE'])->group(function () {
        Route::middleware(CheckNotNull::class.':User')->group(function () {
            Route::get('profiles/view/{id}', [ProfileController::class, 'profiles_view'])->name('profiles.view');
        });
        Route::get('profiles/view/{id_user}/evidence/{id_evidence}', [ProfileController::class, 'evidences_view'])->name('profiles.view.evidence');
    });

    // RANDOMIZE EVIDENCES - LECTURE only
    Route::middleware([CheckRoles::class.':LECTURE'])->group(function () {
        Route::get('randomize', [RandomizeController::class, 'randomize'])->name('randomize.randomize');
        Route::post('randomize/save', [RandomizeController::class, 'randomize_save'])->name('randomize.save');
    });

    // SUGGESTIONS MAILBOX
    Route::get('suggestionsmailbox', [SuggestionsMailboxController::class, 'suggestionsmailbox'])->name('suggestionsmailbox');
    Route::post('suggestionsmailbox_p', [SuggestionsMailboxController::class, 'suggestionsmailbox_p'])->name('suggestionsmailbox_p');

    // EVIDENCES
    Route::get('evidence/list', [EvidenceController::class, 'list'])->name('evidence.list');

    Route::middleware([CheckUploadEvidences::class])->group(function () {
        Route::get('evidence/create', [EvidenceController::class, 'create'])->name('evidence.create');
        Route::post('evidence/draft', [EvidenceController::class, 'draft'])->name('evidence.draft');
        Route::post('evidence/publish', [EvidenceController::class, 'publish'])->name('evidence.publish');
        Route::post('evidence/draft/edit', [EvidenceController::class, 'draft_edit'])->name('evidence.draft.edit');
        Route::post('evidence/publish/edit', [EvidenceController::class, 'publish_edit'])->name('evidence.publish.edit');
    });

    Route::get('evidence/list/export/{ext}', [EvidenceController::class, 'export'])->name('evidence.list.export');

    Route::middleware([
        CheckNotNull::class.':Evidence',
        EvidenceMine::class,
    ])->group(function () {
        Route::get('evidence/view/{id}', [EvidenceController::class, 'view'])->name('evidence.view');

        Route::middleware(CheckUploadEvidences::class)->group(function () {
            Route::get('evidence/edit/{id}', [EvidenceController::class, 'edit'])->name('evidence.edit')->middleware(EvidenceCanBeEdited::class);
            Route::post('evidence/reedit', [EvidenceController::class, 'reedit'])->name('evidence.reedit');
            Route::post('evidence/remove', [EvidenceController::class, 'remove'])->name('evidence.remove');
        });
    });

    /**
     * UPLOADS
     */
    Route::post('/evidence/upload/process', [UploadController::class, 'process'])->name('upload.process');
    Route::delete('/evidence/upload/process', [UploadController::class, 'delete'])->name('upload.revert');

    Route::get('/evidence/upload/load/{file_name}', [UploadController::class, 'load'])->name('upload.load');
    Route::get('/evidence/upload/remove/{file_name}', [UploadController::class, 'remove'])->name('upload.remove');

    Route::post('/xls/upload/process', [UploadController::class, 'process'])->name('xls.upload.process');
    Route::get('/xls/upload/remove/{file_name}', [UploadController::class, 'remove'])->name('xls.upload.remove');

    // MESSAGES
    Route::get('mailbox', [MessageController::class, 'mailbox'])->name('message.mailbox');

    // Sign
    Route::prefix('sign')->group(function () {
        Route::get('/{random_identifier}', [SignController::class, 'sign'])->name('sign');
        Route::post('/sign_p', [SignController::class, 'sign_p'])->name('sign_p');
        Route::get('/finish', [SignController::class, 'finish'])->name('sign.finish');
    });

    /**
     *  GENERAL PURPOSE
     */
    Route::get('/gp/users/all', [GeneralPurposeController::class, 'users_all'])->name('gp.users.all');

    // EVIDENCES MANAGEMENT BY A COORDINATOR
    Route::prefix('coordinator')->group(function () {
        Route::get('/evidence/list/all', [EvidenceCoordinatorController::class, 'all'])->name('coordinator.evidence.list.all');
        Route::get('/evidence/list/pending', [EvidenceCoordinatorController::class, 'pending'])->name('coordinator.evidence.list.pending');
        Route::get('/evidence/list/accepted', [EvidenceCoordinatorController::class, 'accepted'])->name('coordinator.evidence.list.accepted');
        Route::get('/evidence/list/rejected', [EvidenceCoordinatorController::class, 'rejected'])->name('coordinator.evidence.list.rejected');
        Route::get('/evidence/export/{type}/{ext}', [EvidenceCoordinatorController::class, 'evidences_export'])->name('coordinator.evidence.export');

        Route::middleware([
            CheckNotNull::class.':Evidence',
            EvidenceFromMyCommittee::class,
        ])->group(function () {
            Route::get('/evidence/view/{id}', [EvidenceController::class, 'view'])->name('coordinator.evidence.view');

            Route::middleware([CheckValidateEvidences::class])->group(function () {
                Route::get('/evidence/accept/{id}', [EvidenceCoordinatorController::class, 'accept'])->name('coordinator.evidence.accept');
                Route::post('/evidence/reject/', [EvidenceCoordinatorController::class, 'reject'])->name('coordinator.evidence.reject');
            });
        });
    });

});

/**
 *  RESET PASSWORDS
 */
Route::prefix('password')->group(function () {
    Route::get('/reset', [PasswordResetController::class, 'reset'])->name('password.custom_reset');
    Route::post('/reset_p', [PasswordResetController::class, 'reset_p'])->name('password.reset_p');

    Route::get('/update/{token}', [PasswordResetController::class, 'update'])->name('password.update');
    Route::post('/update_p/{token}', [PasswordResetController::class, 'update_p'])->name('password.update_p');
});
