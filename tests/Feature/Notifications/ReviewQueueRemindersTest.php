<?php

namespace Tests\Feature\Notifications;

use App\Enums\NotificationLevel;
use App\Models\Committee;
use App\Models\Evidence;
use App\Notifications\Catalog;
use App\Notifications\Reminders\ReviewQueueReminder;
use App\Services\Notifications\NotificationPreferences;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * notifications:review-queue, the weekly summary of the review queues: on Monday to every coordinator and president
 * with pending evidences, on Thursday again to whoever has some of more than a week; once per person, queue and week,
 * nothing to whoever has nothing pending, and nobody can turn it off.
 */
class ReviewQueueRemindersTest extends NotificationTestCase
{
    private CarbonImmutable $monday;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->monday = CarbonImmutable::parse('2026-11-09 08:30');
        $this->travelTo($this->monday);
    }

    private function waiting(string $title, int $days, ?Committee $committee = null, $author = null): Evidence
    {
        $evidence = $this->evidence($author ?? $this->student, 'PENDING', $committee, $title);
        DB::table('evidences')->where('id', $evidence->id)->update(['created_at' => now()->subDays($days), 'updated_at' => now()->subDays($days)]);

        return $evidence->fresh();
    }

    private function summarise(array $options = []): void
    {
        $this->artisan('notifications:review-queue', $options)->assertSuccessful();
    }

    public function test_on_monday_each_reviewer_gets_the_summary_of_their_queue(): void
    {
        $this->waiting('Montaje del photocall', 9);
        $this->waiting('Atención en el registro', 2);
        // the presidency reviews what a coordinator sends to their own committee
        $this->waiting('Programa de mano', 4, $this->programme, $this->otherCoordinator);

        $this->summarise();

        Notification::assertSentToTimes($this->coordinator, ReviewQueueReminder::class, 1);
        Notification::assertSentToTimes($this->president, ReviewQueueReminder::class, 1);
        foreach ([$this->otherCoordinator, $this->secretary, $this->student, $this->lecturer] as $nobody) {
            Notification::assertNotSentTo($nobody, ReviewQueueReminder::class);
        }

        Notification::assertSentTo($this->coordinator, ReviewQueueReminder::class, function (ReviewQueueReminder $notice, array $channels): bool {
            $this->assertSame(['database', 'mail'], $channels);
            $this->assertSame('Resumen semanal · 2 evidencias por revisar', $notice->title($this->coordinator));
            $this->assertSame('Cola de Logística · la más antigua espera 9 días', $notice->text($this->coordinator));
            $this->assertSame(route('coordinator.evidence.list.pending'), $notice->url($this->coordinator));
            $this->assertSame('pending', $notice->tone());
            $this->assertSame([
                'Montaje del photocall · Lucía Pérez Romero · 9 días en cola',
                'Atención en el registro · Lucía Pérez Romero · 2 días en cola',
            ], $notice->mailItems($this->coordinator));
            $mail = $this->mailText($notice, $this->coordinator);
            $this->assertStringContainsString('Tienes 2 evidencias pendientes de revisar en la cola de Logística.', $mail);
            $this->assertStringContainsString('La más antigua espera 9 días.', $mail);
            $this->assertStringContainsString('El plazo de validación cierra el ', $mail);
            $this->assertStringContainsString('Revisar pendientes', $mail);

            return true;
        });
        Notification::assertSentTo($this->president, ReviewQueueReminder::class, function (ReviewQueueReminder $notice): bool {
            $this->assertSame('Resumen semanal · 1 evidencia por revisar', $notice->title($this->president));
            $this->assertSame('Cola de Presidencia · la más antigua espera 4 días', $notice->text($this->president));
            $this->assertSame(route('president.review.pending'), $notice->url($this->president));
            $this->assertSame(['Programa de mano · Ana Vega Sanz · 4 días en cola'], $notice->mailItems($this->president));

            return true;
        });
    }

    public function test_the_same_summary_never_goes_twice_and_comes_back_next_week(): void
    {
        $this->waiting('Montaje del photocall', 1);

        $this->summarise();
        $this->summarise();
        Notification::assertSentToTimes($this->coordinator, ReviewQueueReminder::class, 1);

        $this->travelTo($this->monday->addWeek());
        $this->summarise();
        Notification::assertSentToTimes($this->coordinator, ReviewQueueReminder::class, 2);
        $this->assertSame(2, DB::table('notification_reminders')->where('user_id', $this->coordinator->id)->where('key', 'like', 'review.weekly:%')->count());
    }

    public function test_on_thursday_it_insists_only_on_what_waits_more_than_a_week(): void
    {
        $this->waiting('Montaje del photocall', 6);
        $this->waiting('Programa de mano', 2, $this->programme, $this->otherCoordinator);
        $this->travelTo($this->monday->addDays(3));

        $this->summarise();

        // the evidence of Logística now waits 9 days; the one of the presidency only 5
        Notification::assertSentToTimes($this->coordinator, ReviewQueueReminder::class, 1);
        Notification::assertNotSentTo($this->president, ReviewQueueReminder::class);
        Notification::assertSentTo($this->coordinator, ReviewQueueReminder::class, function (ReviewQueueReminder $notice): bool {
            $this->assertTrue($notice->insist);
            $this->assertSame('1 evidencia espera más de 7 días', $notice->title($this->coordinator));
            $this->assertSame('rejected', $notice->tone());
            $this->assertSame(['Montaje del photocall · Lucía Pérez Romero · 9 días en cola'], $notice->mailItems($this->coordinator));
            $this->assertStringContainsString('A mitad de semana siguen en la cola de Logística 1 evidencia de hace más de 7 días.', $this->mailText($notice, $this->coordinator));

            return true;
        });

        $this->summarise();
        Notification::assertSentToTimes($this->coordinator, ReviewQueueReminder::class, 1);
    }

    public function test_the_insistence_and_the_summary_of_the_same_week_are_different_notices(): void
    {
        $this->waiting('Montaje del photocall', 8);

        $this->summarise();
        $this->travelTo($this->monday->addDays(3));
        $this->summarise();

        Notification::assertSentToTimes($this->coordinator, ReviewQueueReminder::class, 2);
    }

    public function test_nothing_goes_out_on_other_days_nor_to_whoever_has_nothing_pending(): void
    {
        $this->evidence($this->student, 'ACCEPTED');
        $this->evidence($this->student, 'DRAFT', title: 'Borrador');
        // nobody reviews their own: the coordinator's evidence is not in their queue
        $this->waiting('Inventario', 3, author: $this->coordinator);

        $this->summarise();
        Notification::assertNotSentTo($this->coordinator, ReviewQueueReminder::class);
        Notification::assertSentToTimes($this->president, ReviewQueueReminder::class, 1);

        $this->waiting('Montaje del photocall', 10);
        foreach ([1, 2, 4, 5, 6] as $day) {
            $this->travelTo($this->monday->addDays($day));
            $this->summarise();
        }
        Notification::assertNotSentTo($this->coordinator, ReviewQueueReminder::class);
    }

    public function test_the_kind_can_be_forced_and_a_dry_run_sends_nothing(): void
    {
        $this->waiting('Montaje del photocall', 1);
        $this->travelTo($this->monday->addDay());

        $this->artisan('notifications:review-queue', ['--kind' => 'summary', '--dry-run' => true])
            ->expectsOutputToContain('review.weekly · summary · coordination:'.$this->logistics->id.' · '.$this->coordinator->email.' · 1')
            ->assertSuccessful();
        Notification::assertNothingSent();

        $this->summarise(['--kind' => 'summary']);
        Notification::assertSentToTimes($this->coordinator, ReviewQueueReminder::class, 1);
        $this->artisan('notifications:review-queue', ['--kind' => 'weekly'])->assertFailed();
    }

    public function test_it_is_fixed_so_nobody_can_turn_it_off(): void
    {
        $this->assertSame(NotificationLevel::Fixed, ReviewQueueReminder::level());
        $this->assertContains(ReviewQueueReminder::class, Catalog::forPreferences($this->president)['fixed']);
        $this->assertContains(ReviewQueueReminder::class, Catalog::forPreferences($this->coordinator)['fixed']);
        $this->assertNotContains(ReviewQueueReminder::class, Catalog::forPreferences($this->student)['fixed']);

        app(NotificationPreferences::class)->update($this->coordinator, [], true);
        $this->assertTrue(app(NotificationPreferences::class)->mailEnabled($this->coordinator->fresh(), ReviewQueueReminder::key()));
    }
}
