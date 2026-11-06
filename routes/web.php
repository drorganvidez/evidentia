<?php

use App\Http\Controllers\AttendeeController;
// Controladores importados explícitamente
use App\Http\Controllers\BlockController;
use App\Http\Controllers\BonusSecretaryController;
use App\Http\Controllers\CommitteeCoordinatorController;
use App\Http\Controllers\CommitteeOverviewController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\DefaultListSecretaryController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\ErrorReportController;
use App\Http\Controllers\EventbriteController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\EvidenceCoordinatorController;
use App\Http\Controllers\GeneralPurposeController;
use App\Http\Controllers\GitController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\IntegrityController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\MeetingSecretaryController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProofController;
use App\Http\Controllers\RandomizeController;
use App\Http\Controllers\SignController;
use App\Http\Controllers\UploadController;
use App\Http\Middleware\BonusMine;
use App\Http\Middleware\CheckNotNull;
use App\Http\Middleware\CheckProofDownload;
use App\Http\Middleware\CheckRegisterBonus;
use App\Http\Middleware\CheckRegisterEventsAndAttendings;
use App\Http\Middleware\CheckRegisterMeeting;
use App\Http\Middleware\CheckRoles;
use App\Http\Middleware\CheckUploadEvidences;
use App\Http\Middleware\CheckValidateEvidences;
use App\Http\Middleware\DefaultListMine;
use App\Http\Middleware\EvidenceCanBeEdited;
use App\Http\Middleware\EvidenceFromMyCommittee;
use App\Http\Middleware\EvidenceMine;
use App\Http\Middleware\MeetingMinutesMine;
use App\Http\Middleware\MeetingRequestMine;
use App\Http\Middleware\SignatureSheetMine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

// Rutas de autenticación: solo login y logout (las cuentas las crea la gestión y la contraseña se recupera en /password)
Auth::routes(['register' => false, 'reset' => false, 'confirm' => false, 'verify' => false]);

// Aviso para los usuarios bloqueados
Route::get('block', [BlockController::class, 'block'])->name('block');

// Sign
Route::prefix('sign')->withoutMiddleware('auth')->group(function () {
    Route::get('/finish', [SignController::class, 'finish'])->name('sign.finish');
    Route::post('/sign_p', [SignController::class, 'sign_p'])->name('sign_p');
    Route::get('/{random_identifier}', [SignController::class, 'sign'])->name('sign');
});

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
    });

    // DOWNLOADS
    Route::get('download/request/{id}', [DownloadController::class, 'request_download'])->name('download.request');

    Route::prefix('secretary')->middleware('auth')->group(function () {

        // Default Lists
        Route::controller(DefaultListSecretaryController::class)->group(function () {
            Route::get('defaultlist/list', 'list')->name('secretary.defaultlist.list');
            Route::get('defaultlist/list/create', 'create')->name('secretary.defaultlist.create');
            Route::post('defaultlist/list/new', 'new')->name('secretary.defaultlist.new');

            Route::middleware([
                CheckNotNull::class.':DefaultList',
                DefaultListMine::class,
            ])->group(function () {
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
            });

            // Signature Sheets
            Route::prefix('signaturesheet')->group(function () {
                Route::get('list', [MeetingSecretaryController::class, 'signaturesheet_list'])->name('secretary.meeting.manage.signaturesheet.list');
                Route::get('create', [MeetingSecretaryController::class, 'signaturesheet_create'])->name('secretary.meeting.manage.signaturesheet.create');
                Route::post('new', [MeetingSecretaryController::class, 'signaturesheet_new'])->name('secretary.meeting.manage.signaturesheet.new');
                Route::middleware(SignatureSheetMine::class)->group(function () {
                    Route::get('view/{id}', [MeetingSecretaryController::class, 'signaturesheet_view'])->name('secretary.meeting.manage.signaturesheet.view');
                    Route::get('edit/{id}', [MeetingSecretaryController::class, 'signaturesheet_edit'])->name('secretary.meeting.manage.signaturesheet.edit');
                    Route::post('save', [MeetingSecretaryController::class, 'signaturesheet_save'])->name('secretary.meeting.manage.signaturesheet.save');
                    Route::post('remove', [MeetingSecretaryController::class, 'signaturesheet_remove'])->name('secretary.meeting.manage.signaturesheet.remove');
                });
            });

            // Minutes
            Route::prefix('minutes')->group(function () {
                Route::get('list', [MeetingSecretaryController::class, 'minutes_list'])->name('secretary.meeting.manage.minutes.list');
                Route::middleware(CheckRegisterMeeting::class)->group(function () {
                    Route::get('create', [MeetingSecretaryController::class, 'minutes_create'])->name('secretary.meeting.manage.minutes.create');

                    Route::get('create/step1', [MeetingSecretaryController::class, 'minutes_create_step1'])->name('secretary.meeting.manage.minutes.create.step1');
                    Route::post('create/step1_p', [MeetingSecretaryController::class, 'minutes_create_step1_p'])->name('secretary.meeting.manage.minutes.create.step1_p');

                    Route::get('create/step2', [MeetingSecretaryController::class, 'minutes_create_step2'])->name('secretary.meeting.manage.minutes.create.step2');
                    Route::post('create/step2_p', [MeetingSecretaryController::class, 'minutes_create_step2_p'])->name('secretary.meeting.manage.minutes.create.step2_p');

                    Route::get('create/step3', [MeetingSecretaryController::class, 'minutes_create_step3'])->name('secretary.meeting.manage.minutes.create.step3');
                    Route::post('create/step3_p', [MeetingSecretaryController::class, 'minutes_create_step3_p'])->name('secretary.meeting.manage.minutes.create.step3_p');
                });

                Route::get('download/{id}', [DownloadController::class, 'minutes_download'])->name('download.minutes');

                Route::middleware([MeetingMinutesMine::class, CheckRegisterMeeting::class])->group(function () {
                    Route::get('edit/{id}', [MeetingSecretaryController::class, 'minutes_edit'])->name('secretary.meeting.manage.minutes.edit');
                    Route::post('save', [MeetingSecretaryController::class, 'minutes_save'])->name('secretary.meeting.manage.minutes.save');
                    Route::post('remove', [MeetingSecretaryController::class, 'minutes_remove'])->name('secretary.meeting.manage.minutes.remove');
                });
            });
        });

        // AJAX default list
        Route::middleware([
            CheckNotNull::class.':DefaultList',
            DefaultListMine::class,
        ])->get('meeting/defaultlist/{id}', [MeetingSecretaryController::class, 'defaultlist'])->name('secretary.meeting.defaultlist');

        // Bonus
        Route::get('bonus/list', [BonusSecretaryController::class, 'list'])->name('secretary.bonus.list');

        Route::middleware([CheckRegisterBonus::class])->group(function () {
            Route::get('bonus/create', [BonusSecretaryController::class, 'create'])->name('secretary.bonus.create');
            Route::post('bonus/new', [BonusSecretaryController::class, 'new'])->name('secretary.bonus.new');
        });

        Route::middleware([
            CheckNotNull::class.':Bonus',
            BonusMine::class,
            CheckRegisterBonus::class,
        ])->group(function () {
            Route::get('bonus/edit/{id}', [BonusSecretaryController::class, 'edit'])->name('secretary.bonus.edit');
            Route::post('bonus/save', [BonusSecretaryController::class, 'save'])->name('secretary.bonus.save');
            Route::post('bonus/remove', [BonusSecretaryController::class, 'remove'])->name('secretary.bonus.remove');
        });
    });

    // ATTENDEES
    Route::get('attendee/list', [AttendeeController::class, 'list'])->name('attendee.list');

    // PROOFS
    Route::middleware([
        CheckNotNull::class.':Proof',
        CheckProofDownload::class,
    ])->group(function () {
        Route::get('proof/download/{id}', [ProofController::class, 'download'])->name('proof.download');
    });

    // REGISTER COORDINATOR
    Route::middleware(CheckRegisterEventsAndAttendings::class)->group(function () {
        Route::get('registercoordinator/token', [EventbriteController::class, 'token'])->name('registercoordinator.token');
        Route::post('registercoordinator/token/save', [EventbriteController::class, 'token_save'])->name('registercoordinator.token.save');

        Route::get('registercoordinator/event/load', [EventbriteController::class, 'event_load'])->name('registercoordinator.event.load');
        Route::get('registercoordinator/attendee/load/{id}', [EventbriteController::class, 'attendee_load'])->name('registercoordinator.attendee.load');

        // Hide/unhide events
        Route::post('registercoordinator/event/hide/{id}', [EventbriteController::class, 'hide'])->name('registercoordinator.event.hide');
        Route::post('registercoordinator/event/unhide/{id}', [EventbriteController::class, 'unhide'])->name('registercoordinator.event.unhide');

        // Events created by hand (sessions held before the edition used Eventbrite)
        Route::get('registercoordinator/event/create', [EventbriteController::class, 'event_create'])->name('registercoordinator.event.create');
        Route::post('registercoordinator/event/create', [EventbriteController::class, 'event_store'])->name('registercoordinator.event.store');
        Route::get('registercoordinator/event/{id}/edit', [EventbriteController::class, 'event_edit'])->whereNumber('id')->name('registercoordinator.event.edit');
        Route::post('registercoordinator/event/{id}/edit', [EventbriteController::class, 'event_update'])->whereNumber('id')->name('registercoordinator.event.update');
        Route::delete('registercoordinator/event/{id}', [EventbriteController::class, 'event_destroy'])->whereNumber('id')->name('registercoordinator.event.destroy');
    });

    Route::prefix('attendees')->middleware(['auth', CheckRoles::class.':REGISTER_COORDINATOR'])->group(function () {

        Route::get('/', [EventbriteController::class, 'attendee_list'])->name('registercoordinator.attendee.list');

        // gestión manual de asistencias, solo dentro del plazo de asistencias
        Route::middleware(CheckRegisterEventsAndAttendings::class)->group(function () {
            Route::get('/{id}/edit', [EventbriteController::class, 'attendee_edit'])->name('attendee.edit');
            Route::post('/{id}/edit', [EventbriteController::class, 'attendee_update'])->name('attendee.update');

            Route::get('/create', [EventbriteController::class, 'attendee_create'])->name('attendee.create');
            Route::post('/create', [EventbriteController::class, 'attendee_store'])->name('attendee.store');

            Route::delete('/{id}', [EventbriteController::class, 'attendee_destroy'])->name('attendee.destroy');
        });

    });

    Route::get('registercoordinator/event/list', [EventbriteController::class, 'event_list'])->name('registercoordinator.event.list');
    Route::get('registercoordinator/attendee/export', [EventbriteController::class, 'attendee_export'])->name('registercoordinator.attendee.export');

    // PRESIDENT routes with middleware checkroles:PRESIDENT
    Route::middleware([CheckRoles::class.':PRESIDENT,LECTURE'])->group(function () {
        Route::get('president/config', [ConfigController::class, 'config'])->name('president.config');
        Route::post('president/config/save', [ConfigController::class, 'config_save'])->name('president.config.save');

        Route::get('president/user/list', [ManagementController::class, 'user_list'])->name('president.user.list');
        Route::get('president/evidence/list', [ManagementController::class, 'evidence_list'])->name('president.evidence.list');
        Route::get('president/meeting/list', [ManagementController::class, 'meeting_list'])->name('president.meeting.list');
        Route::get('president/attendee/list', [ManagementController::class, 'attendee_list'])->name('president.attendee.list');
        Route::get('president/committee/list', [ManagementController::class, 'committee_list'])->name('president.committee.list');
        Route::get('president/committee/{id}', [CommitteeOverviewController::class, 'show'])->whereNumber('id')->name('president.committee.view');

        Route::post('president/committee/management/save', [ManagementController::class, 'committee_save'])->name('president.committee.management.save');
        Route::post('president/committee/management/new', [ManagementController::class, 'committee_new'])->name('president.committee.management.new');
        Route::middleware(CheckNotNull::class.':Committee')->post('president/committee/management/remove', [ManagementController::class, 'committee_remove'])->name('president.committee.management.remove');

        Route::get('president/user/management/{id}', [ManagementController::class, 'user_management'])->name('president.user.management');
        Route::post('president/user/management/save', [ManagementController::class, 'user_management_save'])->name('president.user.management.save');

        Route::get('president/integrity', [IntegrityController::class, 'integrity'])->name('president.integrity');

        Route::get('president/export', [ImportExportController::class, 'export'])->name('president.export');
        Route::post('president/export/save', [ImportExportController::class, 'export_save'])->name('president.export.save');

        // Server errors, grouped and announced by App\Services\Errors\ErrorRecorder
        Route::get('president/errors', [ErrorReportController::class, 'index'])->name('president.errors');
        Route::get('president/errors/{id}', [ErrorReportController::class, 'show'])->whereNumber('id')->name('president.errors.show');
        Route::post('president/errors/{id}/resolve', [ErrorReportController::class, 'resolve'])->whereNumber('id')->name('president.errors.resolve');
        Route::post('president/errors/{id}/reopen', [ErrorReportController::class, 'reopen'])->whereNumber('id')->name('president.errors.reopen');

    });

    // LECTURE routes with middleware checkroles:LECTURE
    Route::middleware([CheckRoles::class.':LECTURE,PRESIDENT'])->group(function () {
        Route::get('lecture/config', [ConfigController::class, 'config'])->name('lecture.config');
        Route::post('lecture/config/save', [ConfigController::class, 'config_save'])->name('lecture.config.save');

        Route::get('lecture/committee/list', [ManagementController::class, 'committee_list'])->name('lecture.committee.list');
        Route::get('lecture/committee/{id}', [CommitteeOverviewController::class, 'show'])->whereNumber('id')->name('lecture.committee.view');
        Route::post('lecture/committee/management/save', [ManagementController::class, 'committee_save'])->name('lecture.committee.management.save');
        Route::post('lecture/committee/management/new', [ManagementController::class, 'committee_new'])->name('lecture.committee.management.new');

        Route::middleware(CheckNotNull::class.':Committee')->post('lecture/committee/management/remove', [ManagementController::class, 'committee_remove'])->name('lecture.committee.management.remove');

        Route::get('lecture/integrity', [IntegrityController::class, 'integrity'])->name('lecture.integrity');

        Route::get('lecture/export', [ImportExportController::class, 'export'])->name('lecture.export');
        Route::post('lecture/export/save', [ImportExportController::class, 'export_save'])->name('lecture.export.save');

        Route::get('lecture/user/management/{id}', [ManagementController::class, 'user_management'])->name('lecture.user.management');
        Route::post('lecture/user/management/save', [ManagementController::class, 'user_management_save'])->name('lecture.user.management.save');

        Route::get('lecture/user/list', [ManagementController::class, 'user_list'])->name('lecture.user.list');
        Route::get('lecture/evidence/list', [ManagementController::class, 'evidence_list'])->name('lecture.evidence.list');
        Route::get('lecture/meeting/list', [ManagementController::class, 'meeting_list'])->name('lecture.meeting.list');
        Route::get('lecture/attendee/list', [ManagementController::class, 'attendee_list'])->name('lecture.attendee.list');

        // Server errors, grouped and announced by App\Services\Errors\ErrorRecorder
        Route::get('lecture/errors', [ErrorReportController::class, 'index'])->name('lecture.errors');
        Route::get('lecture/errors/{id}', [ErrorReportController::class, 'show'])->whereNumber('id')->name('lecture.errors.show');
        Route::post('lecture/errors/{id}/resolve', [ErrorReportController::class, 'resolve'])->whereNumber('id')->name('lecture.errors.resolve');
        Route::post('lecture/errors/{id}/reopen', [ErrorReportController::class, 'reopen'])->whereNumber('id')->name('lecture.errors.reopen');

    });

    Route::middleware([CheckRoles::class.':LECTURE'])->group(function () {

        Route::get('lecture/import', [ImportExportController::class, 'import'])->name('lecture.import');
        Route::post('lecture/import/save', [ImportExportController::class, 'import_save'])->name('lecture.import.save');

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

    // EVIDENCES
    Route::get('evidence/list', [EvidenceController::class, 'list'])->name('evidence.list');

    Route::middleware([CheckUploadEvidences::class])->group(function () {
        Route::get('evidence/create', [EvidenceController::class, 'create'])->name('evidence.create');
        Route::post('evidence/draft', [EvidenceController::class, 'draft'])->name('evidence.draft');
        Route::post('evidence/publish', [EvidenceController::class, 'publish'])->name('evidence.publish');
    });

    Route::middleware([
        CheckNotNull::class.':Evidence',
        EvidenceMine::class,
    ])->group(function () {
        Route::get('evidence/view/{id}', [EvidenceController::class, 'view'])->name('evidence.view');

        Route::middleware(CheckUploadEvidences::class)->group(function () {
            Route::get('evidence/edit/{id}', [EvidenceController::class, 'edit'])->name('evidence.edit')->middleware(EvidenceCanBeEdited::class);
            Route::post('evidence/draft/edit', [EvidenceController::class, 'draft_edit'])->name('evidence.draft.edit')->middleware(EvidenceCanBeEdited::class);
            Route::post('evidence/publish/edit', [EvidenceController::class, 'publish_edit'])->name('evidence.publish.edit')->middleware(EvidenceCanBeEdited::class);
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

    /**
     *  GENERAL PURPOSE
     */
    Route::get('/gp/users/all', [GeneralPurposeController::class, 'users_all'])->name('gp.users.all');

    // EVIDENCES MANAGEMENT BY A COORDINATOR
    Route::prefix('coordinator')->group(function () {
        // MY COMMITTEE
        Route::middleware([CheckRoles::class.':COORDINATOR'])->group(function () {
            Route::get('/committee', [CommitteeCoordinatorController::class, 'show'])->name('coordinator.committee');
            Route::get('/committee/export', [CommitteeCoordinatorController::class, 'export'])->name('coordinator.committee.export');
        });

        Route::get('/evidence/list/all', [EvidenceCoordinatorController::class, 'all'])->name('coordinator.evidence.list.all');
        Route::get('/evidence/list/pending', [EvidenceCoordinatorController::class, 'pending'])->name('coordinator.evidence.list.pending');
        Route::get('/evidence/list/accepted', [EvidenceCoordinatorController::class, 'accepted'])->name('coordinator.evidence.list.accepted');
        Route::get('/evidence/list/rejected', [EvidenceCoordinatorController::class, 'rejected'])->name('coordinator.evidence.list.rejected');

        Route::middleware([
            CheckNotNull::class.':Evidence',
            EvidenceFromMyCommittee::class,
        ])->group(function () {
            Route::get('/evidence/view/{id}', [EvidenceController::class, 'view'])->name('coordinator.evidence.view');

            Route::middleware([CheckValidateEvidences::class])->group(function () {
                Route::post('/evidence/accept/{id}', [EvidenceCoordinatorController::class, 'accept'])->name('coordinator.evidence.accept');
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

    Route::get('/update/{token}', [PasswordResetController::class, 'update'])->name('password.custom_update');
    Route::post('/update_p/{token}', [PasswordResetController::class, 'update_p'])->name('password.update_p');
});

/**
 *  DESIGN SYSTEM
 *  Developer catalogue of the new UI with demo endpoints for its interactive components.
 *  Only in local and testing; it already runs under the strict CSP.
 */
Route::middleware('csp:enforce')->prefix('_ui')->name('ui.')->group(function () {
    $development = fn () => abort_unless(app()->environment('local', 'testing'), 404);

    Route::get('/', function () use ($development) {
        $development();

        return view('ui.index');
    })->name('index');

    // 300 deterministic people for the person picker
    Route::get('/demo/people', function () use ($development) {
        $development();

        $names = ['Juan', 'María', 'Carlos', 'Lucía', 'Pedro', 'Ana', 'Miguel', 'Laura', 'Javier', 'Elena', 'Sergio', 'Marta', 'David', 'Paula', 'Álvaro', 'Carmen', 'Pablo', 'Sara', 'Diego', 'Nuria'];
        $surnames = ['García', 'Pérez', 'López', 'Martínez', 'Sánchez', 'Romero', 'Navarro', 'Torres', 'Ruiz', 'Díaz', 'Moreno', 'Jiménez', 'Muñoz', 'Álvarez', 'Gómez'];

        return response()->json(collect(range(1, 300))->map(fn (int $i): array => [
            'value' => $i,
            'label' => $surnames[$i % 15].' '.$surnames[($i * 7) % 15].', '.$names[$i % 20],
            'hint' => 'uvus'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
        ]));
    })->name('demo.people');

    // Ids of a preset list of people
    Route::get('/demo/list/{id}', function (int $id) use ($development) {
        $development();

        return response()->json(collect(range(1, 12))->map(fn (int $i): int => $i * $id));
    })->whereNumber('id')->name('demo.list');

    // Upload protocol of the upload component: an id as plain text, revert by id, remove by name
    Route::post('/demo/upload', function () use ($development) {
        $development();
        usleep(300000);

        return response('demo-'.Str::random(8), 200, ['Content-Type' => 'text/plain']);
    })->name('demo.upload');

    Route::delete('/demo/upload', function () use ($development) {
        $development();

        return response()->noContent();
    })->name('demo.revert');

    Route::get('/demo/remove/{name}', function () use ($development) {
        $development();

        return response()->noContent();
    })->name('demo.remove');

    // A server error on purpose, to see the error page and the errors screen of the management
    Route::get('/demo/boom', function () use ($development) {
        $development();

        throw new RuntimeException('Error de demostración del catálogo');
    })->name('demo.boom');

    // Sink for the forms of the catalogue: they post, put or delete as the real screens will and come back with a notice
    Route::match(['post', 'put', 'patch', 'delete'], '/demo/submit', function () use ($development) {
        $development();

        return back()->with('success', 'Formulario recibido');
    })->name('demo.submit');

    // Full page demos (ui/pages/<page>.blade.php)
    Route::get('/{page}', function (string $page) use ($development) {
        $development();
        abort_unless(view()->exists('ui.pages.'.$page), 404);

        return view('ui.pages.'.$page);
    })->where('page', '[a-z-]+')->name('page');
});
