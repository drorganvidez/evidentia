<?php

namespace Tests\Feature\Notifications;

use App\Models\Configuration;
use App\Models\MeetingRequest;
use App\Notifications\Catalog;
use App\Notifications\Reminders\MeetingsDeadlineReminder;
use App\Notifications\Reminders\UploadDeadlineReminder;
use App\Notifications\Reminders\ValidationDeadlineReminder;
use App\Services\Notifications\NotificationPreferences;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * notifications:remind, seven and two days before each deadline, once per person, threshold and closing instant.
 */
class RemindersTest extends NotificationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->travelTo(now()->setTime(8, 0));
        $this->closing(upload: null, validate: null, meetings: null);
    }

    /** Sets the closing instants (days from now at 20:00; null leaves the deadline unset). */
    private function closing(?float $upload = null, ?float $validate = null, ?float $meetings = null): void
    {
        $at = fn (?float $days) => $days === null ? null : now()->addDays((int) $days)->setTime(20, 0);
        Configuration::query()->update([
            'upload_evidences_timestamp' => $at($upload),
            'validate_evidences_timestamp' => $at($validate),
            'meetings_timestamp' => $at($meetings),
        ]);
        $this->app->forgetScopedInstances();
    }

    private function remind(array $options = []): void
    {
        $this->app->forgetScopedInstances();
        $this->artisan('notifications:remind', $options)->assertSuccessful();
    }

    public function test_the_upload_deadline_is_reminded_once_per_threshold(): void
    {
        $this->evidence($this->student, 'DRAFT', title: 'Borrador de la memoria');
        $this->evidence($this->student, 'REJECTED', title: 'Recogida de material');
        $this->evidence($this->student, 'PENDING', title: 'Montaje');
        $this->evidence($this->secretary, 'ACCEPTED', title: 'Cartelería');
        $this->closing(upload: 5);

        $this->remind();
        $this->remind();

        Notification::assertSentToTimes($this->student, UploadDeadlineReminder::class, 1);
        Notification::assertNotSentTo($this->secretary, UploadDeadlineReminder::class);
        Notification::assertSentTo($this->student, UploadDeadlineReminder::class, function (UploadDeadlineReminder $notice, array $channels): bool {
            $this->assertSame(['database', 'mail'], $channels);
            $this->assertSame('Quedan 5 días para subir evidencias', $notice->title($this->student));
            $this->assertSame('2 evidencias en borrador o rechazadas sin corregir', $notice->text($this->student));
            $this->assertSame(route('evidence.list'), $notice->url($this->student));
            $this->assertSame(['Borrador de la memoria', 'Recogida de material'], $notice->mailItems($this->student));
            $mail = $this->mailText($notice, $this->student);
            $this->assertStringContainsString('El plazo de subida cierra el '.now()->addDays(5)->format('d/m/Y').' a las 20:00.', $mail);
            $this->assertStringContainsString('Tienes 2 evidencias en borrador o rechazadas sin corregir.', $mail);
            $this->assertStringContainsString('Ver mis evidencias', $mail);

            return true;
        });

        $this->travel(4)->days();
        $this->remind();
        $this->remind();

        Notification::assertSentToTimes($this->student, UploadDeadlineReminder::class, 2);
        $this->assertSame(2, DB::table('notification_reminders')->where('user_id', $this->student->id)->count());
    }

    public function test_a_late_run_only_sends_the_nearest_threshold(): void
    {
        $this->evidence($this->student, 'DRAFT');
        $this->closing(upload: 1);

        $this->remind();
        $this->remind();

        Notification::assertSentToTimes($this->student, UploadDeadlineReminder::class, 1);
        Notification::assertSentTo($this->student, UploadDeadlineReminder::class, fn (UploadDeadlineReminder $notice): bool => $notice->title($this->student) === 'Queda un día para subir evidencias');
        $this->assertSame(2, DB::table('notification_reminders')->where('user_id', $this->student->id)->count(), 'The seven-day threshold is claimed in silence');
    }

    public function test_a_deadline_that_moves_is_reminded_again_and_the_last_day_says_so(): void
    {
        $this->evidence($this->student, 'DRAFT');
        $this->closing(upload: 6);
        $this->remind();

        $this->closing(upload: 0);
        $this->remind();

        Notification::assertSentToTimes($this->student, UploadDeadlineReminder::class, 2);
        Notification::assertSentTo($this->student, UploadDeadlineReminder::class, fn (UploadDeadlineReminder $notice): bool => $notice->title($this->student) === 'Último día para subir evidencias');
    }

    public function test_nothing_is_reminded_far_from_a_deadline_after_it_or_without_it(): void
    {
        $this->evidence($this->student, 'DRAFT');

        $this->remind();
        $this->closing(upload: 20);
        $this->remind();
        Configuration::query()->update(['upload_evidences_timestamp' => now()->subHour()]);
        $this->remind();

        Notification::assertNothingSent();
    }

    public function test_the_validation_deadline_counts_the_queue_of_each_reviewer(): void
    {
        $this->evidence($this->student, 'PENDING');
        $this->evidence($this->student, 'PENDING', title: 'Otra');
        // the presidency reviews what a coordinator sends to their own committee
        $this->evidence($this->otherCoordinator, 'PENDING', $this->programme, 'Propia');
        $this->closing(validate: 2);

        $this->remind();

        Notification::assertSentToTimes($this->coordinator, ValidationDeadlineReminder::class, 1);
        Notification::assertNotSentTo($this->otherCoordinator, ValidationDeadlineReminder::class);
        Notification::assertNotSentTo($this->lecturer, ValidationDeadlineReminder::class);
        Notification::assertSentTo($this->coordinator, ValidationDeadlineReminder::class, function (ValidationDeadlineReminder $notice): bool {
            $this->assertSame('Quedan 2 días para validar evidencias', $notice->title($this->coordinator));
            $this->assertSame('2 pendientes en Logística', $notice->text($this->coordinator));
            $this->assertSame(route('coordinator.evidence.list.pending'), $notice->url($this->coordinator));
            $this->assertStringContainsString('La cola de Logística tiene 2 evidencias pendientes de revisión.', $this->mailText($notice, $this->coordinator));

            return true;
        });
        Notification::assertSentTo($this->president, ValidationDeadlineReminder::class, function (ValidationDeadlineReminder $notice): bool {
            $this->assertSame('1 pendiente en Presidencia', $notice->text($this->president));
            $this->assertSame(route('president.review.pending'), $notice->url($this->president));

            return true;
        });
    }

    public function test_the_meetings_deadline_lists_the_requests_held_without_minutes(): void
    {
        $profile = $this->secretary->secretary;
        $request = fn (string $title, $at, ?int $meeting = null) => MeetingRequest::forceCreate([
            'title' => $title, 'place' => 'Aula A3.10', 'datetime' => $at, 'type' => '1', 'modality' => '1',
            'committee_id' => $this->logistics->id, 'secretary_id' => $profile->id, 'meeting_id' => $meeting,
        ]);
        $request('Convocatoria de montaje', now()->subDays(3));
        $request('Convocatoria futura', now()->addDay());
        $this->closing(meetings: 6);

        $this->remind();

        Notification::assertSentToTimes($this->secretary, MeetingsDeadlineReminder::class, 1);
        Notification::assertSentTo($this->secretary, MeetingsDeadlineReminder::class, function (MeetingsDeadlineReminder $notice): bool {
            $this->assertSame('Quedan 6 días para registrar actas', $notice->title($this->secretary));
            $this->assertSame('1 convocatoria celebrada sin acta', $notice->text($this->secretary));
            $this->assertSame(['Convocatoria de montaje · '.now()->subDays(3)->format('d/m/Y')], $notice->mailItems($this->secretary));
            $this->assertSame(route('secretary.meeting.manage.minutes.list'), $notice->url($this->secretary));

            return true;
        });
    }

    public function test_the_reminders_always_go_by_mail_and_the_dry_run_sends_nothing(): void
    {
        $this->evidence($this->student, 'DRAFT');
        app(NotificationPreferences::class)->update($this->student, [], false);
        $this->closing(upload: 2);

        $this->remind(['--dry-run' => true]);
        Notification::assertNothingSent();
        $this->assertSame(0, DB::table('notification_reminders')->count());

        $this->remind();
        Notification::assertSentTo($this->student, UploadDeadlineReminder::class, fn ($notice, array $channels): bool => $channels === ['database', 'mail']);
    }

    public function test_the_reminders_are_fixed_notices_of_the_roles_with_that_work(): void
    {
        $fixed = fn ($user): array => array_map(fn (string $class): string => $class::key(), Catalog::forPreferences($user)['fixed']);

        $this->assertContains('deadline.upload', $fixed($this->student));
        $this->assertNotContains('deadline.validate', $fixed($this->student));
        $this->assertContains('deadline.validate', $fixed($this->coordinator));
        $this->assertContains('deadline.meetings', $fixed($this->secretary));
        $this->assertNotContains('deadline.upload', $fixed($this->lecturer));
    }
}
