<?php

namespace Tests\Feature\Activity;

use App\Enums\ActivityAction;
use App\Enums\ActivityFlag;
use App\Enums\Deadline;
use App\Events\ActivityRecorded;
use App\Facades\Activity;
use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Evidence;
use App\Models\Role;
use App\Models\User;
use App\Services\Activity\ActivityRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class ActivityRecorderTest extends TestCase
{
    use RefreshDatabase;

    private Committee $committee;

    private User $student;

    private User $coordinator;

    private Evidence $evidence;

    protected function setUp(): void
    {
        parent::setUp();
        Configuration::forceCreate(['id' => 1, 'upload_evidences_timestamp' => now()->addWeek(), 'validate_evidences_timestamp' => now()->addWeek()]);
        $this->committee = Committee::create(['name' => 'Logística']);
        $this->student = User::factory()->create(['name' => 'Juan', 'surname' => 'Pérez López', 'username' => 'juaperlop']);
        $this->student->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));
        $this->coordinator = User::factory()->create(['name' => 'Carlos', 'surname' => 'Navarro', 'username' => 'carnav']);
        $this->coordinator->roles()->attach(Role::forceCreate(['rol' => 'COORDINATOR', 'slug' => 'coordinator']));
        $this->evidence = Evidence::create([
            'title' => 'Organización de mesas', 'hours' => 2, 'status' => 'PENDING',
            'user_id' => $this->student->id, 'committee_id' => $this->committee->id,
        ]);
    }

    public function test_it_copies_the_names_the_title_and_the_committee(): void
    {
        $this->actingAs($this->coordinator);

        $log = Activity::record(ActivityAction::EvidenceRejected, $this->evidence, $this->student, ['reason' => 'Falta el certificado.'])->fresh();

        $this->assertSame($this->coordinator->id, $log->user_id);
        $this->assertSame('Navarro, Carlos', $log->user_name);
        $this->assertSame('carnav', $log->user_username);
        $this->assertSame('evidence.rejected', $log->action);
        $this->assertSame('evidence', $log->subject_type);
        $this->assertSame($this->evidence->id, $log->subject_id);
        $this->assertSame($this->evidence->id, $log->subject_root_id);
        $this->assertSame('Organización de mesas', $log->subject_title);
        $this->assertSame($this->student->id, $log->target_user_id);
        $this->assertSame('Pérez López, Juan', $log->target_name);
        $this->assertSame('juaperlop', $log->target_username);
        $this->assertSame($this->committee->id, $log->committee_id);
        $this->assertSame('Logística', $log->committee_name);
        $this->assertSame(['reason' => 'Falta el certificado.'], $log->details);
        $this->assertTrue($log->created_at->isToday());
        $this->assertSame('127.0.0.1', $log->ip);
    }

    public function test_the_copies_survive_the_deletion_of_the_people_and_the_subject(): void
    {
        $this->actingAs($this->coordinator);
        $log = Activity::record(ActivityAction::EvidenceAccepted, $this->evidence, $this->student);

        $this->evidence->delete();
        $this->student->delete();
        $this->committee->delete();

        $log = $log->fresh();
        $this->assertSame('Organización de mesas', $log->subject_title);
        $this->assertSame('Pérez López, Juan', $log->target_name);
        $this->assertSame('Logística', $log->committee_name);
        $this->assertNull($log->subject);
    }

    public function test_the_annotations_of_a_request_share_its_id_and_the_next_request_gets_another(): void
    {
        Route::get('/_test/activity', function () {
            Activity::record(ActivityAction::EvidenceCreated, Evidence::first());
            Activity::record(ActivityAction::ProofUploaded, Evidence::first(), details: ['file' => 'certificado.pdf']);

            return 'ok';
        });
        $this->actingAs($this->student);

        $this->get('/_test/activity')->assertOk();
        $this->get('/_test/activity')->assertOk();

        $ids = ActivityLog::orderBy('id')->pluck('request_id');
        $this->assertCount(4, $ids);
        $this->assertSame($ids[0], $ids[1]);
        $this->assertSame($ids[2], $ids[3]);
        $this->assertNotSame($ids[0], $ids[2]);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $ids[0]);
    }

    public function test_a_new_request_id_can_be_started_by_hand(): void
    {
        $first = Activity::requestId();

        $this->assertSame($first, Activity::requestId());
        $second = Activity::startRequest();
        $this->assertNotSame($first, $second);
        $this->assertSame($second, Activity::record(ActivityAction::ConfigChanged, actor: $this->student)->request_id);
    }

    public function test_passwords_and_tokens_never_reach_the_details(): void
    {
        $log = Activity::record(ActivityAction::UserEdited, $this->student, $this->student, [
            'password' => 'secreto', 'password_confirmation' => 'secreto', 'token' => 'abc', 'eventbrite_token' => 'xyz',
            '_token' => 'csrf', 'before' => ['email' => 'a@b.es', 'remember_token' => 'r'], 'note' => str_repeat('a', 3000),
        ], actor: $this->coordinator)->fresh();

        $this->assertEqualsCanonicalizing(['before', 'note'], array_keys($log->details));
        $this->assertSame(['email' => 'a@b.es'], $log->details['before']);
        $this->assertSame(2000, mb_strlen($log->details['note']));
        $this->assertStringNotContainsString('secreto', json_encode($log->details));
    }

    public function test_without_flags_the_annotation_is_not_flagged(): void
    {
        $log = Activity::record(ActivityAction::EvidencePublished, $this->evidence, actor: $this->student)->fresh();

        $this->assertNull($log->flags);
        $this->assertFalse($log->flagged);
        $this->assertNull($log->details);
    }

    public function test_the_flags_are_stored_once(): void
    {
        $log = Activity::record(
            ActivityAction::EvidencePublished, $this->evidence,
            flags: [ActivityFlag::RoleOverlap, ActivityFlag::RoleOverlap], actor: $this->student,
        )->fresh();

        $this->assertSame(['role_overlap'], $log->flags);
        $this->assertTrue($log->flagged);
    }

    public function test_a_closed_deadline_flags_the_annotation_after_deadline(): void
    {
        Configuration::first()->update(['validate_evidences_timestamp' => now()->subDay()]);

        $log = Activity::record(ActivityAction::EvidenceAccepted, $this->evidence, actor: $this->coordinator);

        $this->assertSame(['after_deadline'], $log->fresh()->flags);
    }

    public function test_an_open_or_unset_deadline_or_no_check_leave_no_flag(): void
    {
        Configuration::first()->update(['upload_evidences_timestamp' => null, 'validate_evidences_timestamp' => now()->subDay()]);

        $this->assertNull(Activity::record(ActivityAction::EvidencePublished, $this->evidence, actor: $this->student)->fresh()->flags);
        $this->assertNull(Activity::record(ActivityAction::EvidenceAccepted, $this->evidence, actor: $this->coordinator, deadline: false)->fresh()->flags);
        $this->assertNull(Activity::record(ActivityAction::RequestCreated, actor: $this->coordinator)->fresh()->flags);
    }

    public function test_an_explicit_deadline_replaces_the_one_of_the_verb(): void
    {
        Configuration::first()->update(['upload_evidences_timestamp' => now()->subDay()]);

        $log = Activity::record(ActivityAction::EvidenceMoved, $this->evidence, actor: $this->coordinator, deadline: Deadline::UploadEvidences);

        $this->assertSame(['after_deadline'], $log->fresh()->flags);
    }

    public function test_the_affected_people_go_to_the_pivot_once(): void
    {
        $other = User::factory()->create();

        $log = Activity::record(ActivityAction::BonusCreated, targets: [$this->student, $other->id, $this->student->id, null], actor: $this->coordinator);

        $this->assertEqualsCanonicalizing(
            [$this->student->id, $other->id],
            DB::table('activity_log_targets')->where('activity_log_id', $log->id)->pluck('user_id')->all(),
        );
        $this->assertSame(2, $log->targets()->count());
    }

    public function test_the_system_annotates_without_actor_or_role(): void
    {
        $this->actingAs($this->coordinator);

        $log = Activity::system(ActivityAction::NotificationSent, $this->evidence, $this->student, committeeId: false)->fresh();

        $this->assertNull($log->user_id);
        $this->assertNull($log->user_name);
        $this->assertNull($log->acting_role);
        $this->assertNull($log->committee_id);
        $this->assertSame($this->student->id, $log->target_user_id);
    }

    public function test_a_draft_has_no_committee(): void
    {
        $draft = Evidence::create(['title' => 'Montaje', 'hours' => 1, 'status' => 'DRAFT', 'user_id' => $this->student->id, 'committee_id' => $this->committee->id]);

        $log = Activity::record(ActivityAction::EvidenceCreated, $draft, actor: $this->student);

        $this->assertNull($log->committee_id);
        $this->assertNull($log->committee_name);
    }

    public function test_the_committee_and_the_title_can_be_given(): void
    {
        $programa = Committee::create(['name' => 'Programa']);

        $log = Activity::record(ActivityAction::EvidenceDeleted, $this->evidence, actor: $this->student, subjectTitle: 'Título copiado', committeeId: $programa->id);

        $this->assertSame('Título copiado', $log->subject_title);
        $this->assertSame('Programa', $log->committee_name);
    }

    public function test_the_role_can_be_given_and_otherwise_comes_from_the_route(): void
    {
        $this->assertSame('SECRETARY', Activity::record(ActivityAction::MessageSent, actor: $this->student, actingRole: 'SECRETARY')->acting_role);
        // sin ruta (consola o tests sin petición) no hay rol
        $this->assertNull(Activity::record(ActivityAction::MessageSent, actor: $this->student)->acting_role);
    }

    public function test_the_event_waits_for_the_commit_and_never_fires_if_it_rolls_back(): void
    {
        $fired = [];
        Event::listen(ActivityRecorded::class, function (ActivityRecorded $event) use (&$fired): void {
            $fired[] = $event->activity->action;
        });

        DB::transaction(function () use (&$fired): void {
            Activity::record(ActivityAction::EvidenceAccepted, $this->evidence, actor: $this->coordinator);
            $this->assertSame([], $fired);
        });
        $this->assertSame(['evidence.accepted'], $fired);

        try {
            DB::transaction(function (): void {
                Activity::record(ActivityAction::EvidenceRejected, $this->evidence, actor: $this->coordinator);
                throw new RuntimeException('rollback');
            });
        } catch (RuntimeException) {
        }
        $this->assertSame(['evidence.accepted'], $fired);
        $this->assertSame(0, ActivityLog::where('action', 'evidence.rejected')->count());

        Activity::record(ActivityAction::EvidencePublished, $this->evidence, actor: $this->student);
        $this->assertSame(['evidence.accepted', 'evidence.published'], $fired);
    }

    public function test_the_recorder_is_one_per_request(): void
    {
        $this->assertSame(app(ActivityRecorder::class), app(ActivityRecorder::class));
    }

    public function test_the_fake_keeps_the_annotations_in_memory(): void
    {
        Event::fake([ActivityRecorded::class]);
        $fake = Activity::fake();

        $log = Activity::record(ActivityAction::EvidenceAccepted, $this->evidence, $this->student, targets: [$this->student], actor: $this->coordinator);

        $this->assertFalse($log->exists);
        $this->assertSame(0, ActivityLog::count());
        Event::assertNotDispatched(ActivityRecorded::class);
        $fake->assertRecorded(ActivityAction::EvidenceAccepted);
        $fake->assertRecorded(ActivityAction::EvidenceAccepted, fn (ActivityLog $log, array $targets): bool => $log->target_user_id === $this->student->id && $targets === [$this->student->id]);
        $fake->assertNotRecorded(ActivityAction::EvidenceRejected);
        $this->assertSame(app(ActivityRecorder::class), $fake);
    }

    public function test_the_fake_records_nothing_until_asked_and_fails_on_demand(): void
    {
        $fake = Activity::fake()->failOn(ActivityAction::ProofUploaded);
        $fake->assertNothingRecorded();

        $this->expectException(RuntimeException::class);
        Activity::record(ActivityAction::ProofUploaded, $this->evidence, actor: $this->student);
    }
}
