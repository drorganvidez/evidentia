<?php

namespace Tests\Feature\Activity;

use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\Evidence;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The scopes of the log never let a row of someone else through, even combined with other conditions.
 */
class ActivityLogScopesTest extends TestCase
{
    use RefreshDatabase;

    private User $me;

    private User $other;

    protected function setUp(): void
    {
        parent::setUp();
        $this->me = User::factory()->create();
        $this->other = User::factory()->create();
    }

    private function row(array $attributes, array $targets = []): ActivityLog
    {
        $log = ActivityLog::create(['created_at' => now(), 'action' => 'evidence.created', ...$attributes]);
        foreach ($targets as $userId) {
            DB::table('activity_log_targets')->insert(['activity_log_id' => $log->id, 'user_id' => $userId]);
        }

        return $log;
    }

    private function ids($query): array
    {
        return $query->orderBy('id')->pluck('id')->all();
    }

    public function test_the_rows_of_a_person_are_the_ones_they_did_or_that_affect_them(): void
    {
        $did = $this->row(['user_id' => $this->me->id]);
        $target = $this->row(['user_id' => $this->other->id, 'target_user_id' => $this->me->id, 'action' => 'evidence.rejected']);
        $among = $this->row(['user_id' => $this->other->id, 'action' => 'minutes.created'], [$this->other->id, $this->me->id]);
        $this->row(['user_id' => $this->other->id]);
        $this->row(['user_id' => null, 'target_user_id' => $this->other->id, 'action' => 'login_failed']);
        $this->row(['user_id' => $this->other->id, 'action' => 'minutes.created'], [$this->other->id]);

        $this->assertSame([$did->id, $target->id, $among->id], $this->ids(ActivityLog::forUser($this->me)));
        $this->assertSame([$did->id, $target->id, $among->id], $this->ids(ActivityLog::forUser($this->me->id)));
    }

    public function test_the_or_of_a_person_stays_grouped_with_other_conditions(): void
    {
        $this->row(['user_id' => $this->me->id, 'action' => 'evidence.created']);
        $failed = $this->row(['user_id' => null, 'target_user_id' => $this->me->id, 'action' => 'login_failed']);
        $this->row(['user_id' => null, 'target_user_id' => $this->other->id, 'action' => 'login_failed']);

        $this->assertSame([$failed->id], $this->ids(ActivityLog::where('action', 'login_failed')->forUser($this->me)));
        $this->assertSame([$failed->id], $this->ids(ActivityLog::forUser($this->me)->where('action', 'login_failed')));
    }

    public function test_the_noise_is_hidden_from_the_first_view(): void
    {
        $this->row(['action' => 'login']);
        $this->row(['action' => 'logout']);
        $this->row(['action' => 'notification.sent']);
        $failed = $this->row(['action' => 'notification.failed']);
        $created = $this->row(['action' => 'evidence.created']);

        $this->assertSame([$failed->id, $created->id], $this->ids(ActivityLog::withoutNoise()));
        $this->assertCount(2, ActivityLog::access()->get());
    }

    public function test_the_type_is_a_group_or_a_verb_and_anything_else_is_ignored(): void
    {
        $login = $this->row(['action' => 'login']);
        $created = $this->row(['action' => 'evidence.created']);
        $moved = $this->row(['action' => 'evidence.moved']);

        $this->assertSame([$created->id, $moved->id], $this->ids(ActivityLog::ofType('group:evidence')));
        $this->assertSame([$moved->id], $this->ids(ActivityLog::ofType('evidence.moved')));
        $this->assertSame([$login->id, $created->id, $moved->id], $this->ids(ActivityLog::ofType('group:nothing')));
        $this->assertSame([$login->id, $created->id, $moved->id], $this->ids(ActivityLog::ofType("evidence.created' OR 1=1")));
    }

    public function test_the_committees_and_the_dates(): void
    {
        $logistica = Committee::create(['name' => 'Logística']);
        $programa = Committee::create(['name' => 'Programa']);
        $old = $this->row(['committee_id' => $logistica->id, 'created_at' => now()->subDays(10)]);
        $new = $this->row(['committee_id' => $logistica->id]);
        $this->row(['committee_id' => $programa->id]);
        $this->row(['committee_id' => null]);

        $this->assertSame([$old->id, $new->id], $this->ids(ActivityLog::inCommittees([$logistica->id])));
        $this->assertSame([$new->id], $this->ids(ActivityLog::inCommittees([$logistica->id])->between(now()->subDay(), null)));
        $this->assertSame([$old->id], $this->ids(ActivityLog::inCommittees([$logistica->id])->between(null, now()->subDays(5))));
        $this->assertSame([], $this->ids(ActivityLog::inCommittees([])));
    }

    public function test_the_history_of_an_evidence_gathers_every_version(): void
    {
        $first = Evidence::create(['title' => 'v1', 'status' => 'REJECTED', 'user_id' => $this->me->id, 'last' => false]);
        $second = Evidence::create(['title' => 'v2', 'status' => 'PENDING', 'user_id' => $this->me->id, 'points_to' => $first->id]);
        $a = $this->row(['subject_type' => 'evidence', 'subject_id' => $first->id, 'subject_root_id' => $first->id]);
        $b = $this->row(['subject_type' => 'evidence', 'subject_id' => $second->id, 'subject_root_id' => $first->id]);
        $this->row(['subject_type' => 'evidence', 'subject_id' => 999, 'subject_root_id' => 999]);
        $this->row(['subject_type' => 'meeting', 'subject_id' => $first->id, 'subject_root_id' => $first->id]);

        $this->assertSame([$a->id, $b->id], $this->ids(ActivityLog::about($second)));
        $this->assertSame([$a->id, $b->id], $this->ids(ActivityLog::about($first)));
        $this->assertSame([], $this->ids(ActivityLog::about($this->me)));
    }

    public function test_a_partial_history_hides_the_notices_of_others_and_the_drafts(): void
    {
        $committee = Committee::create(['name' => 'Logística']);
        $evidence = ['subject_type' => 'evidence', 'subject_id' => 1, 'subject_root_id' => 1];
        $draft = $this->row([...$evidence, 'action' => 'evidence.created', 'committee_id' => null]);
        $sent = $this->row([...$evidence, 'action' => 'evidence.published', 'committee_id' => $committee->id]);
        $noticeToMe = $this->row([...$evidence, 'action' => 'notification.sent', 'target_user_id' => $this->me->id]);
        $noticeToOther = $this->row([...$evidence, 'action' => 'notification.sent', 'target_user_id' => $this->other->id]);
        $thread = $this->row(['subject_type' => 'message_thread', 'subject_id' => 1, 'subject_root_id' => 1, 'action' => 'message.sent']);

        $this->assertSame(
            [$sent->id, $noticeToMe->id, $thread->id],
            $this->ids(ActivityLog::historyVisibleTo($this->me, false)),
        );
        $this->assertSame(
            [$draft->id, $sent->id, $noticeToMe->id, $noticeToOther->id, $thread->id],
            $this->ids(ActivityLog::historyVisibleTo($this->me, true)),
        );
        // agrupada con otras condiciones no deja pasar el aviso ajeno
        $this->assertSame([], $this->ids(ActivityLog::where('target_user_id', $this->other->id)->historyVisibleTo($this->me, false)));
    }
}
