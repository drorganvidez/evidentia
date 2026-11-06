<?php

namespace Tests\Feature\Activity;

use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Coordinator;
use App\Models\Evidence;
use App\Models\Meeting;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\User;
use App\Services\Activity\ActivityAccess;
use App\Services\Deadlines;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityAccessTest extends TestCase
{
    use RefreshDatabase;

    private ActivityAccess $access;

    private Committee $logistica;

    private Committee $programa;

    /** @var array<string, User> */
    private array $u = [];

    private Secretary $secretary;

    protected function setUp(): void
    {
        parent::setUp();
        Configuration::forceCreate(['id' => 1, 'upload_evidences_timestamp' => now()->addWeek(), 'validate_evidences_timestamp' => now()->addWeek()]);
        $this->logistica = Committee::create(['name' => 'Logística']);
        $this->programa = Committee::create(['name' => 'Programa']);
        $this->u = [
            'author' => $this->userWith('STUDENT'),
            'other' => $this->userWith('STUDENT'),
            'coordinator' => $this->userWith('STUDENT', 'COORDINATOR'),
            'secretary' => $this->userWith('STUDENT', 'SECRETARY'),
            'coordinatorPrograma' => $this->userWith('STUDENT', 'COORDINATOR'),
            'president' => $this->userWith('STUDENT', 'PRESIDENT'),
            'lecturer' => $this->userWith('LECTURE'),
        ];
        Coordinator::forceCreate(['user_id' => $this->u['coordinator']->id, 'committee_id' => $this->logistica->id]);
        Coordinator::forceCreate(['user_id' => $this->u['coordinatorPrograma']->id, 'committee_id' => $this->programa->id]);
        $this->secretary = Secretary::forceCreate(['user_id' => $this->u['secretary']->id, 'committee_id' => $this->logistica->id]);
        $this->access = app(ActivityAccess::class);
    }

    private function userWith(string ...$roles): User
    {
        $user = User::factory()->create();
        foreach ($roles as $role) {
            $user->roles()->attach(Role::where('rol', $role)->first() ?? Role::forceCreate(['rol' => $role, 'slug' => strtolower($role)]));
        }

        return $user;
    }

    private function evidence(string $status, string $author = 'author', array $attributes = []): Evidence
    {
        return Evidence::create([
            'title' => 'Mesas', 'hours' => 2, 'status' => $status, 'user_id' => $this->u[$author]->id,
            'committee_id' => $this->logistica->id, ...$attributes,
        ])->fresh();
    }

    private function closeDeadline(string $column): void
    {
        Configuration::first()->update([$column => now()->subDay()]);
        app(Deadlines::class)->refresh();
    }

    public function test_drafts_are_only_seen_by_their_author(): void
    {
        $pending = $this->evidence('PENDING');
        $draft = $this->evidence('DRAFT');
        $expected = [
            'author' => [true, true], 'other' => [false, false], 'coordinator' => [true, false], 'secretary' => [true, false],
            'coordinatorPrograma' => [false, false], 'president' => [true, false], 'lecturer' => [true, false],
        ];

        foreach ($expected as $who => [$seesPending, $seesDraft]) {
            $this->assertSame($seesPending, $this->access->canViewEvidence($this->u[$who], $pending), "$who pending");
            $this->assertSame($seesDraft, $this->access->canViewEvidence($this->u[$who], $draft), "$who draft");
        }
    }

    public function test_the_whole_history_of_an_evidence_is_for_its_author_and_the_management(): void
    {
        $pending = $this->evidence('PENDING');

        $this->assertTrue($this->access->seesFullEvidenceHistory($this->u['author'], $pending));
        $this->assertTrue($this->access->seesFullEvidenceHistory($this->u['lecturer'], $pending));
        $this->assertTrue($this->access->seesFullEvidenceHistory($this->u['president'], $pending));
        $this->assertFalse($this->access->seesFullEvidenceHistory($this->u['coordinator'], $pending));
    }

    public function test_the_log_is_narrowed_to_the_committee_and_exported_only_by_the_management(): void
    {
        $this->assertNull($this->access->committeeScope($this->u['lecturer']));
        $this->assertNull($this->access->committeeScope($this->u['president']));
        $this->assertSame([$this->logistica->id], $this->access->committeeScope($this->u['coordinator']));
        $this->assertSame([$this->logistica->id], $this->access->committeeScope($this->u['secretary']));
        $this->assertSame([], $this->access->committeeScope($this->u['author']));

        $this->assertTrue($this->access->canViewLog($this->u['lecturer']));
        $this->assertTrue($this->access->canViewLog($this->u['coordinator']));
        $this->assertFalse($this->access->canViewLog($this->u['author']));
        $this->assertTrue($this->access->canExport($this->u['president']));
        $this->assertFalse($this->access->canExport($this->u['coordinator']));
    }

    public function test_the_histories_of_minutes_and_requests_and_the_signatures(): void
    {
        $meeting = Meeting::forceCreate(['title' => 'Reunión', 'datetime' => now(), 'hours' => 1, 'committee_id' => $this->logistica->id]);
        $meeting->users()->attach($this->u['author']);
        $minutes = MeetingMinutes::create(['meeting_id' => $meeting->id, 'secretary_id' => $this->secretary->id]);
        $request = MeetingRequest::forceCreate(['title' => 'Convocatoria', 'committee_id' => $this->logistica->id, 'secretary_id' => $this->secretary->id]);
        $expected = [
            'author' => [true, false, false], 'other' => [false, false, false], 'coordinator' => [true, true, false],
            'secretary' => [true, true, true], 'coordinatorPrograma' => [false, false, false], 'president' => [true, true, true],
            'lecturer' => [true, true, true],
        ];

        foreach ($expected as $who => [$minutesHistory, $requestHistory, $signatures]) {
            $user = $this->u[$who]->fresh();
            $this->assertSame($minutesHistory, $this->access->canViewMinutesHistory($user, $minutes), "$who minutes");
            $this->assertSame($requestHistory, $this->access->canViewRequestHistory($user, $request), "$who request");
            $this->assertSame($signatures, $this->access->seesSignatures($user, $request), "$who signatures");
        }
    }

    public function test_the_student_moves_their_own_last_unaccepted_evidence_while_upload_is_open(): void
    {
        foreach (['DRAFT' => true, 'REJECTED' => true, 'PENDING' => true, 'ACCEPTED' => false] as $status => $can) {
            $this->assertSame($can, $this->access->canMoveAsStudent($this->u['author'], $this->evidence($status)), $status);
        }
        $this->assertFalse($this->access->canMoveAsStudent($this->u['author'], $this->evidence('REJECTED', attributes: ['last' => false])));
        $this->assertFalse($this->access->canMoveAsStudent($this->u['other'], $this->evidence('PENDING')));

        $this->closeDeadline('upload_evidences_timestamp');
        $this->assertFalse($this->access->canMoveAsStudent($this->u['author'], $this->evidence('PENDING')));
    }

    public function test_the_coordinator_moves_a_pending_evidence_of_someone_else_while_validation_is_open(): void
    {
        $coordinator = $this->u['coordinator']->fresh();

        $this->assertTrue($this->access->canMoveAsCoordinator($coordinator, $this->evidence('PENDING')));
        $this->assertFalse($this->access->canMoveAsCoordinator($coordinator, $this->evidence('PENDING', 'coordinator')));
        $this->assertFalse($this->access->canMoveAsCoordinator($coordinator, $this->evidence('ACCEPTED')));
        $this->assertFalse($this->access->canMoveAsCoordinator($this->u['coordinatorPrograma']->fresh(), $this->evidence('PENDING')));
        $this->assertFalse($this->access->canMoveAsCoordinator($this->u['secretary']->fresh(), $this->evidence('PENDING')));

        $this->closeDeadline('validate_evidences_timestamp');
        $this->assertFalse($this->access->canMoveAsCoordinator($coordinator, $this->evidence('PENDING')));
    }

    public function test_the_management_moves_and_reviews_any_last_version_but_drafts_and_never_reviews_its_own(): void
    {
        $lecturer = $this->u['lecturer'];

        $this->assertTrue($this->access->canMoveAsManager($lecturer, $this->evidence('ACCEPTED')));
        $this->assertFalse($this->access->canMoveAsManager($lecturer, $this->evidence('DRAFT')));
        $this->assertFalse($this->access->canMoveAsManager($lecturer, $this->evidence('PENDING', attributes: ['last' => false])));
        $this->assertFalse($this->access->canMoveAsManager($this->u['coordinator'], $this->evidence('PENDING')));
        $this->assertTrue($this->access->canReviewAsManager($lecturer, $this->evidence('PENDING')));
        $this->assertFalse($this->access->canReviewAsManager($this->u['president'], $this->evidence('PENDING', 'president')));

        // fuera de plazo la gestión sigue pudiendo
        $this->closeDeadline('validate_evidences_timestamp');
        $this->assertTrue($this->access->canReviewAsManager($lecturer, $this->evidence('PENDING')));
    }

    public function test_the_old_detail_page_of_an_evidence_for_each_person(): void
    {
        $pending = $this->evidence('PENDING');
        $draft = $this->evidence('DRAFT');

        $this->assertSame(route('evidence.view', ['id' => $pending->id]), $this->access->evidenceDetailHref($this->u['author'], $pending));
        $this->assertSame(route('coordinator.evidence.view', ['id' => $pending->id]), $this->access->evidenceDetailHref($this->u['coordinator']->fresh(), $pending));
        $this->assertSame(
            route('profiles.view.evidence', ['id_user' => $this->u['author']->id, 'id_evidence' => $pending->id]),
            $this->access->evidenceDetailHref($this->u['lecturer'], $pending),
        );
        $this->assertNull($this->access->evidenceDetailHref($this->u['secretary']->fresh(), $pending));
        $this->assertNull($this->access->evidenceDetailHref($this->u['coordinator']->fresh(), $draft));
        $this->assertNull($this->access->evidenceDetailHref($this->u['lecturer'], $draft));
    }

    public function test_the_profile_link_is_only_for_the_management_and_existing_people(): void
    {
        $this->assertSame(route('profiles.view', ['id' => $this->u['author']->id]), $this->access->profileHref($this->u['lecturer'], $this->u['author']->id));
        $this->assertNull($this->access->profileHref($this->u['lecturer'], 999999));
        $this->assertNull($this->access->profileHref($this->u['lecturer'], null));
        $this->assertNull($this->access->profileHref($this->u['coordinator'], $this->u['author']->id));
    }
}
