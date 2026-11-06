<?php

namespace Tests\Feature\Activity\WriteRoutes;

use App\Models\Evidence;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Activity\AuthenticationActivityTest;
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

    /**
     * Routes that annotate, with a happy request that the sweep sends.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function writes(): array
    {
        return [
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
                'data' => fn (array $ids) => ['password' => 'nueva-clave', 'password_confirmation' => 'nueva-clave'],
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
            'suggestionsmailbox_p' => 'Se sustituye por los mensajes',
        ];
    }

    /** @return array<string, string> route => test class that checks its annotation */
    public static function coveredElsewhere(): array
    {
        return [
            'POST login' => AuthenticationActivityTest::class,
            'logout' => AuthenticationActivityTest::class,
        ];
    }

    /** @return array<string, string> route => batch that will annotate it */
    public static function pending(): array
    {
        return [
            'attendee.store' => 'A',
            'attendee.update' => 'A',
            'attendee.destroy' => 'A',
            'registercoordinator.event.hide' => 'A',
            'registercoordinator.event.unhide' => 'A',
            'registercoordinator.attendee.export' => 'A',
            'registercoordinator.token.save' => 'A',
            'registercoordinator.event.load' => 'A',
            'registercoordinator.attendee.load' => 'A',
            'registercoordinator.event.store' => 'F',
            'registercoordinator.event.update' => 'F',
            'registercoordinator.event.destroy' => 'F',
            'president.committee.management.new' => 'A',
            'president.committee.management.save' => 'A',
            'president.committee.management.remove' => 'A',
            'lecture.committee.management.new' => 'A',
            'lecture.committee.management.save' => 'A',
            'lecture.committee.management.remove' => 'A',
            'president.user.management.save' => 'A',
            'lecture.user.management.save' => 'A',
            'management.user.new' => 'A',
            'management.user.delete.all' => 'A',
            'president.config.save' => 'A',
            'lecture.config.save' => 'A',
            'lecture.import.save' => 'A',
            'president.export.save' => 'A',
            'lecture.export.save' => 'A',
            'randomize.save' => 'A',
            'profile.upload.info' => 'A',
            'profile.upload.biography' => 'A',
            'profile.upload.pass' => 'A',
            'sign_p' => 'A',
        ];
    }
}
