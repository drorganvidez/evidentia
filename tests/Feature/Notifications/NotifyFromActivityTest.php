<?php

namespace Tests\Feature\Notifications;

use App\Enums\ActivityAction;
use App\Facades\Activity;
use App\Models\User;
use App\Notifications\Catalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\Feature\Notifications\Fixtures\DummyInformativeNotice;
use Tests\Feature\Notifications\Fixtures\DummyNotice;
use Tests\TestCase;

/**
 * The notices come from the annotations: one per person and class, never to whoever did it, never if it rolled back.
 */
class NotifyFromActivityTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    private User $author;

    private User $other;

    protected function setUp(): void
    {
        parent::setUp();
        Catalog::using([DummyNotice::class, DummyInformativeNotice::class]);
        Notification::fake();
        [$this->actor, $this->author, $this->other] = User::factory()->count(3)->create()->all();
    }

    protected function tearDown(): void
    {
        Catalog::flush();
        parent::tearDown();
    }

    public function test_the_verb_sends_each_class_once_per_person_and_never_to_the_actor(): void
    {
        Activity::record(ActivityAction::EvidenceAccepted, target: $this->author, targets: [$this->other, $this->actor], actor: $this->actor);

        foreach ([DummyNotice::class, DummyInformativeNotice::class] as $class) {
            Notification::assertSentToTimes($this->author, $class, 1);
            Notification::assertSentToTimes($this->other, $class, 1);
            Notification::assertNotSentTo($this->actor, $class);
        }
    }

    public function test_the_notice_carries_its_annotation(): void
    {
        $log = Activity::record(ActivityAction::EvidenceAccepted, target: $this->author, actor: $this->actor, subjectTitle: 'Organización de mesas');

        Notification::assertSentTo($this->author, DummyNotice::class, fn (DummyNotice $notice): bool => $notice->activity->is($log));
    }

    public function test_other_verbs_send_only_their_classes(): void
    {
        Activity::record(ActivityAction::EvidenceRejected, target: $this->author, actor: $this->actor);
        Activity::record(ActivityAction::EvidenceCreated, target: $this->author, actor: $this->actor);

        Notification::assertSentToTimes($this->author, DummyInformativeNotice::class, 1);
        Notification::assertNotSentTo($this->author, DummyNotice::class);
    }

    public function test_nothing_is_sent_until_the_commit_and_nothing_if_it_rolls_back(): void
    {
        DB::transaction(function (): void {
            Activity::record(ActivityAction::EvidenceAccepted, target: $this->author, actor: $this->actor);
            Notification::assertNothingSent();
        });
        Notification::assertSentToTimes($this->author, DummyNotice::class, 1);

        try {
            DB::transaction(function (): void {
                Activity::record(ActivityAction::EvidenceAccepted, target: $this->other, actor: $this->actor);
                throw new RuntimeException('rollback');
            });
        } catch (RuntimeException) {
        }
        Notification::assertNotSentTo($this->other, DummyNotice::class);
    }

    public function test_a_system_annotation_reaches_everyone(): void
    {
        Activity::system(ActivityAction::EvidenceAccepted, target: $this->author, targets: [$this->actor]);

        Notification::assertSentToTimes($this->author, DummyNotice::class, 1);
        Notification::assertSentToTimes($this->actor, DummyNotice::class, 1);
    }
}
