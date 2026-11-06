<?php

namespace Tests\Feature\Activity\WriteRoutes;

use App\Models\Attendee;
use App\Models\Committee;
use App\Models\Event;
use App\Models\Evidence;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\Feature\Activity\AuthenticationActivityTest;
use Tests\Feature\Activity\EventbriteSyncActivityTest;
use Tests\TestCase;

/**
 * Write routes of the screens that existed before the activity log. Each route is in exactly one of the four lists.
 */
final class LegacyWrites
{
    /** Form of an evidence of the fixtures, with the proof uploaded to the temporary folder of the session. */
    private static function evidence(string $verb, string $uri, ?string $id = null, array $expect = []): array
    {
        return [
            'role' => 'STUDENT',
            'method' => 'POST',
            'setup' => function (TestCase $test, array $ids): void {
                $test->withSession(['_token' => 'barrido']);
                Storage::put('tmp/'.User::find($ids['student'])->username.'/barrido/certificado.pdf', '%PDF-1.4');
            },
            'uri' => fn (array $ids) => route($uri),
            'data' => fn (array $ids) => [
                'title' => 'Montaje del escenario', 'hours' => 2, 'minutes' => 30, 'committee' => $ids['logistica'],
                'description' => 'Monté el escenario del congreso con el equipo.',
            ] + ($id === null ? [] : ['_id' => $ids[$id]]),
            'expect' => [...$expect, $verb],
            'acting_role' => 'STUDENT',
        ];
    }

    /** A case of the secretary that posts the given data. */
    private static function secretary(string $route, array $expect, array $data): array
    {
        return [
            'role' => 'SECRETARY',
            'method' => 'POST',
            'uri' => fn (array $ids) => route($route),
            'data' => fn (array $ids) => array_map(fn ($value) => $value instanceof \Closure ? $value($ids) : $value, $data),
            'expect' => $expect,
            'acting_role' => 'SECRETARY',
        ];
    }

    private static function meetingRequest(): array
    {
        return [
            'title' => 'Reunión de montaje', 'place' => 'Aula A3.10', 'date' => now()->addWeek()->format('Y-m-d'),
            'time' => '18:00', 'type' => 1, 'modality' => 1, 'points_list' => json_encode(['Reparto de tareas', 'Turnos']),
        ];
    }

    private static function minutes(): array
    {
        return [
            'title' => 'Reunión de montaje', 'type' => 1, 'modality' => 1, 'hours' => 2, 'minutes' => 30,
            'place' => 'Aula A3.10', 'date' => now()->format('Y-m-d'), 'time' => '16:30',
            'users' => fn (array $ids) => [$ids['student']],
            'points_json' => json_encode([['title' => 'Reparto', 'duration' => 10, 'description' => 'Equipos', 'agreements' => [['description' => 'Cada equipo nombra un responsable.']]]]),
        ];
    }

    /** A case of the management (PRESIDENT or LECTURE) that posts to a route. */
    private static function management(string $role, string $route, array $expect, ?\Closure $data = null, ?\Closure $setup = null): array
    {
        return array_filter([
            'role' => $role,
            'method' => 'POST',
            'setup' => $setup,
            'uri' => fn (array $ids) => route($route),
            'data' => $data,
            'expect' => $expect,
            'acting_role' => $role,
        ], fn ($value) => $value !== null);
    }

    /** The form of the person in charge for the student of the fixtures, with a new name. */
    private static function userForm(array $ids): array
    {
        $student = User::find($ids['student']);

        return [
            'user_id' => $student->id, 'pass' => 'on', 'roles' => [Role::where('rol', 'STUDENT')->value('id')],
            'committee' => $ids['logistica'], 'username' => $student->username, 'name' => 'Lucía',
            'surname' => $student->surname, 'email' => $student->email,
        ];
    }

    private static function config(): array
    {
        $date = now()->addWeeks(2)->format('Y-m-d');

        return [
            'upload_evidences_date' => $date, 'upload_evidences_time' => '20:00', 'validate_evidences_date' => $date,
            'validate_evidences_time' => '23:59', 'meetings_date' => $date, 'meetings_time' => '23:59',
            'bonus_date' => $date, 'bonus_time' => '23:59', 'attendees_date' => $date, 'attendees_time' => '23:59',
        ];
    }

    /** Every management case, once for each of the two prefixes that share the controller. */
    private static function managementCases(): array
    {
        $cases = [];
        foreach (['president' => 'PRESIDENT', 'lecture' => 'LECTURE'] as $prefix => $role) {
            $cases += [
                "$prefix.committee.management.new" => self::management($role, "$prefix.committee.management.new", ['committee.created'],
                    fn (array $ids) => ['icon' => 'megaphone', 'name' => 'Comunicación']),
                "$prefix.committee.management.save" => self::management($role, "$prefix.committee.management.save", ['committee.edited'],
                    fn (array $ids) => ['name_'.$ids['logistica'] => 'Logística y montaje', 'icon_'.$ids['logistica'] => 'truck']),
                "$prefix.committee.management.remove" => self::management($role, "$prefix.committee.management.remove", ['committee.deleted'],
                    fn (array $ids) => ['_id' => Committee::create(['name' => 'Sin uso'])->id]),
                "$prefix.config.save" => self::management($role, "$prefix.config.save", ['config.changed'], fn (array $ids) => self::config()),
                "$prefix.export.save" => self::management($role, "$prefix.export.save", ['export.evidences'],
                    fn (array $ids) => ['evidences' => 'on', 'bonus' => 'on']),
                "$prefix.user.management.save" => self::management($role, "$prefix.user.management.save", ['user.edited'],
                    fn (array $ids) => self::userForm($ids)),
            ];
        }

        return $cases + [
            'management.user.new' => self::management('LECTURE', 'management.user.new', ['user.created'],
                fn (array $ids) => ['name' => 'Lucía', 'surname' => 'Pérez', 'email' => 'lucia@alum.us.es', 'username' => 'lucper']),
            'management.user.delete.all' => self::management('LECTURE', 'management.user.delete.all', ['user.deleted']),
            'randomize.save' => self::management('LECTURE', 'randomize.save', ['randomize.run']),
            'lecture.import.save' => self::management('LECTURE', 'lecture.import.save', ['import.users'], setup: function (TestCase $test, array $ids): void {
                $spreadsheet = new Spreadsheet;
                $spreadsheet->getActiveSheet()->fromArray([['apellidos', 'nombre', 'uvus', 'email'], ['Pérez', 'Lucía', 'lucper', 'lucia@alum.us.es']]);
                $path = tempnam(sys_get_temp_dir(), 'xlsx');
                (new Xlsx($spreadsheet))->save($path);
                $lecture = User::whereHas('roles', fn ($query) => $query->where('rol', 'LECTURE'))->first();
                $test->withSession(['_token' => 'barrido']);
                Storage::put('tmp/'.$lecture->username.'/barrido/alumnos.xlsx', file_get_contents($path));
                unlink($path);
            }),
        ];
    }

    /**
     * Routes that annotate, with a happy request that the sweep sends.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function writes(): array
    {
        return self::managementCases() + [
            'evidence.draft' => self::evidence('evidence.created', 'evidence.draft', expect: ['proof.uploaded']),
            'evidence.publish' => self::evidence('evidence.published', 'evidence.publish', expect: ['proof.uploaded']),
            'evidence.draft.edit' => self::evidence('evidence.edited', 'evidence.draft.edit', 'draft'),
            'evidence.publish.edit' => self::evidence('evidence.published', 'evidence.publish.edit', 'draft'),
            'evidence.reedit' => [
                'role' => 'STUDENT',
                'method' => 'POST',
                'setup' => fn (TestCase $test, array $ids) => Evidence::whereKey($ids['evidence'])->update(['status' => 'REJECTED']),
                'uri' => fn (array $ids) => route('evidence.reedit'),
                'data' => fn (array $ids) => ['_id' => $ids['evidence']],
                'expect' => ['evidence.reedited'],
                'acting_role' => 'STUDENT',
            ],
            'evidence.remove' => [
                'role' => 'STUDENT',
                'method' => 'POST',
                'uri' => fn (array $ids) => route('evidence.remove'),
                'data' => fn (array $ids) => ['_id' => $ids['evidence']],
                'expect' => ['proof.deleted', 'evidence.deleted'],
                'acting_role' => 'STUDENT',
            ],
            'coordinator.evidence.accept' => [
                'role' => 'COORDINATOR',
                'method' => 'POST',
                'uri' => fn (array $ids) => route('coordinator.evidence.accept', ['id' => $ids['evidence']]),
                'expect' => ['evidence.accepted'],
                'acting_role' => 'COORDINATOR',
            ],
            'coordinator.evidence.reject' => [
                'role' => 'COORDINATOR',
                'method' => 'POST',
                'uri' => fn (array $ids) => route('coordinator.evidence.reject'),
                'data' => fn (array $ids) => ['_id' => $ids['evidence'], 'reasonrejection' => 'Falta el certificado de asistencia.'],
                'expect' => ['evidence.rejected'],
                'acting_role' => 'COORDINATOR',
            ],
            'secretary.meeting.manage.request.new' => self::secretary('secretary.meeting.manage.request.new', ['request.created', 'sheet.created'],
                self::meetingRequest() + ['create_signature_sheet' => 'on']),
            'secretary.meeting.manage.request.save' => self::secretary('secretary.meeting.manage.request.save', ['request.edited'],
                ['_id' => fn (array $ids) => $ids['request']] + self::meetingRequest()),
            'secretary.meeting.manage.request.remove' => self::secretary('secretary.meeting.manage.request.remove', ['request.deleted'],
                ['_id' => fn (array $ids) => $ids['request']]),
            'secretary.meeting.manage.signaturesheet.new' => self::secretary('secretary.meeting.manage.signaturesheet.new', ['sheet.created'],
                ['title' => 'Hoja de firmas del montaje']),
            'secretary.meeting.manage.signaturesheet.save' => self::secretary('secretary.meeting.manage.signaturesheet.save', ['sheet.edited'],
                ['_id' => fn (array $ids) => $ids['sheet'], 'title' => 'Hoja de firmas corregida']),
            'secretary.meeting.manage.signaturesheet.remove' => self::secretary('secretary.meeting.manage.signaturesheet.remove', ['sheet.deleted'],
                ['_id' => fn (array $ids) => $ids['sheet']]),
            'secretary.meeting.manage.minutes.create.step3_p' => self::secretary('secretary.meeting.manage.minutes.create.step3_p', ['minutes.created'],
                ['meeting_request' => fn (array $ids) => $ids['request']] + self::minutes()),
            'secretary.meeting.manage.minutes.save' => self::secretary('secretary.meeting.manage.minutes.save', ['minutes.edited'],
                ['_id' => fn (array $ids) => $ids['minutes']] + self::minutes()),
            'secretary.meeting.manage.minutes.remove' => self::secretary('secretary.meeting.manage.minutes.remove', ['minutes.deleted'],
                ['_id' => fn (array $ids) => $ids['minutes']]),
            'secretary.bonus.new' => self::secretary('secretary.bonus.new', ['bonus.created'],
                ['reason' => 'Montaje del escenario', 'hours' => 3, 'users' => fn (array $ids) => [$ids['student']]]),
            'secretary.bonus.save' => self::secretary('secretary.bonus.save', ['bonus.edited'],
                ['_id' => fn (array $ids) => $ids['bonus'], 'reason' => 'Montaje del escenario', 'hours' => 4, 'users' => fn (array $ids) => [$ids['student']]]),
            'secretary.bonus.remove' => self::secretary('secretary.bonus.remove', ['bonus.deleted'],
                ['_id' => fn (array $ids) => $ids['bonus']]),
            'secretary.defaultlist.new' => self::secretary('secretary.defaultlist.new', ['defaultlist.created'],
                ['name' => 'Equipo de sonido', 'users' => fn (array $ids) => [$ids['student']]]),
            'secretary.defaultlist.save' => self::secretary('secretary.defaultlist.save', ['defaultlist.edited'],
                ['_id' => fn (array $ids) => $ids['list'], 'name' => 'Equipo de sonido', 'users' => fn (array $ids) => [$ids['student']]]),
            'secretary.defaultlist.remove' => self::secretary('secretary.defaultlist.remove', ['defaultlist.deleted'],
                ['_id' => fn (array $ids) => $ids['list']]),
            'attendee.store' => [
                'role' => 'REGISTER_COORDINATOR',
                'method' => 'POST',
                'uri' => fn (array $ids) => route('attendee.store'),
                'data' => fn (array $ids) => ['event_id' => $ids['manualEvent'], 'user_id' => $ids['student'], 'status' => 'Checked In'],
                'expect' => ['attendee.created'],
                'acting_role' => 'REGISTER_COORDINATOR',
            ],
            'attendee.update' => [
                'role' => 'REGISTER_COORDINATOR',
                'method' => 'POST',
                'uri' => fn (array $ids) => route('attendee.update', ['id' => $ids['attendee']]),
                'data' => fn (array $ids) => ['event_id' => $ids['event'], 'user_id' => $ids['student'], 'status' => 'Attending'],
                'expect' => ['attendee.edited'],
                'acting_role' => 'REGISTER_COORDINATOR',
            ],
            'attendee.destroy' => [
                'role' => 'REGISTER_COORDINATOR',
                'method' => 'DELETE',
                'uri' => fn (array $ids) => route('attendee.destroy', ['id' => $ids['attendee']]),
                'expect' => ['attendee.deleted'],
                'acting_role' => 'REGISTER_COORDINATOR',
            ],
            'registercoordinator.event.store' => [
                'role' => 'REGISTER_COORDINATOR',
                'method' => 'POST',
                'uri' => fn () => route('registercoordinator.event.store'),
                'data' => fn () => ['name' => 'Taller de Git', 'start_datetime' => '2026-10-28T08:30', 'end_datetime' => '2026-10-28T10:00'],
                'expect' => ['event.created'],
                'acting_role' => 'REGISTER_COORDINATOR',
            ],
            'registercoordinator.event.update' => [
                'role' => 'REGISTER_COORDINATOR',
                'method' => 'POST',
                'setup' => fn (TestCase $test, array $ids) => Event::whereKey($ids['event'])->update(['id_eventbrite' => null]),
                'uri' => fn (array $ids) => route('registercoordinator.event.update', $ids['event']),
                'data' => fn () => ['name' => 'Charla de IA', 'start_datetime' => '2026-10-28T08:30', 'end_datetime' => '2026-10-28T11:00'],
                'expect' => ['event.edited'],
                'acting_role' => 'REGISTER_COORDINATOR',
            ],
            'registercoordinator.event.destroy' => [
                'role' => 'REGISTER_COORDINATOR',
                'method' => 'DELETE',
                'setup' => function (TestCase $test, array $ids) {
                    Event::whereKey($ids['event'])->update(['id_eventbrite' => null]);
                    Attendee::where('event_id', $ids['event'])->delete();
                },
                'uri' => fn (array $ids) => route('registercoordinator.event.destroy', $ids['event']),
                'expect' => ['event.deleted'],
                'acting_role' => 'REGISTER_COORDINATOR',
            ],
            'registercoordinator.event.hide' => [
                'role' => 'REGISTER_COORDINATOR',
                'method' => 'POST',
                'uri' => fn (array $ids) => route('registercoordinator.event.hide', ['id' => $ids['event']]),
                'expect' => ['event.hidden'],
                'acting_role' => 'REGISTER_COORDINATOR',
            ],
            'registercoordinator.event.unhide' => [
                'role' => 'REGISTER_COORDINATOR',
                'method' => 'POST',
                'setup' => fn (TestCase $test, array $ids) => Event::whereKey($ids['event'])->update(['hidden' => true]),
                'uri' => fn (array $ids) => route('registercoordinator.event.unhide', ['id' => $ids['event']]),
                'expect' => ['event.unhidden'],
                'acting_role' => 'REGISTER_COORDINATOR',
            ],
            'registercoordinator.attendee.export' => [
                'role' => 'REGISTER_COORDINATOR',
                'method' => 'GET',
                'uri' => fn (array $ids) => route('registercoordinator.attendee.export'),
                'expect' => ['export.attendees'],
                'acting_role' => 'REGISTER_COORDINATOR',
            ],
            'profile.upload.info' => [
                'role' => 'STUDENT',
                'method' => 'POST',
                'uri' => fn (array $ids) => route('profile.upload.info'),
                'data' => fn (array $ids) => ['name' => 'Lucía', 'surname' => 'Pérez', 'email' => 'lucia@alum.us.es'],
                'expect' => ['profile.edited'],
                'acting_role' => null,
            ],
            'profile.upload.biography' => [
                'role' => 'STUDENT',
                'method' => 'POST',
                'uri' => fn (array $ids) => route('profile.upload.biography'),
                'data' => fn (array $ids) => ['biography' => '<p>Estudiante de cuarto curso.</p>', 'participation' => 2],
                'expect' => ['profile.edited'],
                'acting_role' => null,
            ],
            'profile.upload.pass' => [
                'role' => 'STUDENT',
                'method' => 'POST',
                'uri' => fn (array $ids) => route('profile.upload.pass'),
                'data' => fn (array $ids) => ['current_password' => 'password', 'password' => 'Nueva-Clave-26', 'password_confirmation' => 'Nueva-Clave-26'],
                'expect' => ['user.password_changed'],
                'acting_role' => null,
            ],
            'password.reset_p' => [
                'role' => 'GUEST',
                'method' => 'POST',
                'setup' => fn () => Mail::fake(),
                'uri' => fn (array $ids) => route('password.reset_p'),
                'data' => fn (array $ids) => ['email' => User::find($ids['student'])->email],
                'expect' => ['password_reset_requested'],
                'acting_role' => null,
            ],
            'password.update_p' => [
                'role' => 'GUEST',
                'method' => 'POST',
                'uri' => fn (array $ids) => route('password.update_p', ['token' => $ids['token']]),
                'data' => fn (array $ids) => ['password' => 'Nueva-Clave-26', 'password_confirmation' => 'Nueva-Clave-26'],
                'expect' => ['password_reset'],
                'acting_role' => null,
            ],
        ];
    }

    /** @return array<string, string> route => why it does not annotate */
    public static function excluded(): array
    {
        return [
            'upload.process' => 'Fichero temporal de FilePond',
            'upload.revert' => 'Fichero temporal de FilePond',
            'upload.remove' => 'Fichero temporal de FilePond',
            'xls.upload.process' => 'Fichero temporal de FilePond',
            'xls.upload.remove' => 'Fichero temporal de FilePond',
            'secretary.meeting.manage.minutes.create.step1_p' => 'No escribe',
            'secretary.meeting.manage.minutes.create.step2_p' => 'No escribe',
            'csp.report' => 'Sin sesión, solo log',
            'storage.local.upload' => 'Del framework',
            'ui.demo.upload' => 'Catálogo',
            'ui.demo.revert' => 'Catálogo',
            'ui.demo.remove' => 'Catálogo',
            'ui.demo.submit' => 'Catálogo',
            'lecture.errors.resolve' => 'Errores del servidor, fuera del registro de actividad (ErrorReportingTest)',
            'lecture.errors.reopen' => 'Errores del servidor, fuera del registro de actividad (ErrorReportingTest)',
            'president.errors.resolve' => 'Errores del servidor, fuera del registro de actividad (ErrorReportingTest)',
            'president.errors.reopen' => 'Errores del servidor, fuera del registro de actividad (ErrorReportingTest)',
        ];
    }

    /** @return array<string, string> route => test class that checks its annotation */
    public static function coveredElsewhere(): array
    {
        return [
            'POST login' => AuthenticationActivityTest::class,
            'logout' => AuthenticationActivityTest::class,
            'sign_p' => AuthenticationActivityTest::class,
            'registercoordinator.token.save' => EventbriteSyncActivityTest::class,
            'registercoordinator.event.load' => EventbriteSyncActivityTest::class,
            'registercoordinator.attendee.load' => EventbriteSyncActivityTest::class,
        ];
    }

    /** @return array<string, string> route => batch that will annotate it */
    public static function pending(): array
    {
        return [
        ];
    }
}
