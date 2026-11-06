<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Coordinator;
use App\Models\Evidence;
use App\Models\Role;
use App\Models\User;
use App\Services\ReviewQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Who reviews each evidence: the coordination of its committee, unless one of its coordinators wrote it or it goes to
 * the committee of the presidency, which the presidents review. Nobody reviews their own; the lecturer may review for
 * the presidency but has no queue.
 */
class ReviewQueueTest extends TestCase
{
    use RefreshDatabase;

    private Committee $logistics;

    private Committee $presidency;

    private User $student;

    private User $coordinator;

    private User $cocoordinator;

    private User $otherCoordinator;

    private User $president;

    private User $lecturer;

    protected function setUp(): void
    {
        parent::setUp();
        Configuration::forceCreate(['validate_evidences_timestamp' => now()->addWeek()]);
        $this->logistics = Committee::create(['name' => 'Logística']);
        $programme = Committee::create(['name' => 'Programa']);
        $this->presidency = Committee::presidency();

        $this->student = $this->person('STUDENT');
        $this->coordinator = $this->person('STUDENT', 'COORDINATOR');
        $this->cocoordinator = $this->person('STUDENT', 'COORDINATOR');
        $this->otherCoordinator = $this->person('STUDENT', 'COORDINATOR');
        Coordinator::forceCreate(['user_id' => $this->coordinator->id, 'committee_id' => $this->logistics->id]);
        Coordinator::forceCreate(['user_id' => $this->cocoordinator->id, 'committee_id' => $this->logistics->id]);
        Coordinator::forceCreate(['user_id' => $this->otherCoordinator->id, 'committee_id' => $programme->id]);
        $this->president = $this->person('PRESIDENT');
        $this->lecturer = $this->person('LECTURE');
    }

    private function person(string ...$roles): User
    {
        $user = User::factory()->create();
        foreach ($roles as $role) {
            $user->roles()->attach(Role::where('rol', $role)->first() ?? Role::forceCreate(['rol' => $role, 'slug' => strtolower($role)]));
        }

        return $user->fresh();
    }

    private function evidence(User $author, ?Committee $committee = null, string $status = 'PENDING', string $title = 'Montaje'): Evidence
    {
        return Evidence::create([
            'title' => $title, 'description' => '<p>'.$title.'.</p>', 'hours' => 1, 'status' => $status,
            'user_id' => $author->id, 'committee_id' => ($committee ?? $this->logistics)->id,
        ]);
    }

    private function queue(): ReviewQueue
    {
        return app(ReviewQueue::class);
    }

    public function test_the_migrations_mark_one_committee_of_the_presidency(): void
    {
        $this->assertNotNull($this->presidency);
        $this->assertSame('Presidencia', $this->presidency->name);
        $this->assertTrue($this->queue()->isPresidency($this->presidency));
        $this->assertFalse($this->queue()->isPresidency($this->logistics));
        $this->assertFalse($this->queue()->isPresidency(null));
    }

    public function test_the_coordination_reviews_what_others_send_to_its_committee(): void
    {
        $evidence = $this->evidence($this->student);

        $this->assertFalse($this->queue()->goesToPresidency($evidence));
        $this->assertTrue($this->queue()->reviewsAsCoordinator($this->coordinator, $evidence));
        $this->assertTrue($this->queue()->reviewsAsCoordinator($this->cocoordinator, $evidence));
        $this->assertFalse($this->queue()->canReview($this->otherCoordinator, $evidence));
        $this->assertFalse($this->queue()->canReview($this->president, $evidence));
        $this->assertSame([$this->coordinator->id, $this->cocoordinator->id], $this->queue()->reviewers($evidence)->pluck('id')->all());
    }

    public function test_what_a_coordinator_sends_to_their_committee_goes_to_the_presidency(): void
    {
        $evidence = $this->evidence($this->coordinator);

        $this->assertTrue($this->queue()->goesToPresidency($evidence));
        $this->assertFalse($this->queue()->canReview($this->coordinator, $evidence), 'Nobody reviews their own');
        $this->assertFalse($this->queue()->canReview($this->cocoordinator, $evidence), 'Nor the coordination they belong to');
        $this->assertTrue($this->queue()->reviewsAsPresidency($this->president, $evidence));
        $this->assertTrue($this->queue()->reviewsAsPresidency($this->lecturer, $evidence));
        $this->assertSame([$this->president->id], $this->queue()->reviewers($evidence)->pluck('id')->all());
    }

    public function test_what_a_coordinator_sends_to_another_committee_stays_with_its_coordination(): void
    {
        $evidence = $this->evidence($this->otherCoordinator);

        $this->assertFalse($this->queue()->goesToPresidency($evidence));
        $this->assertTrue($this->queue()->reviewsAsCoordinator($this->coordinator, $evidence));
    }

    public function test_the_presidents_coordinate_the_committee_of_the_presidency(): void
    {
        $evidence = $this->evidence($this->student, $this->presidency);

        $this->assertTrue($this->queue()->goesToPresidency($evidence));
        $this->assertTrue($this->queue()->reviewsAsPresidency($this->president, $evidence));
        $this->assertFalse($this->queue()->canReview($this->coordinator, $evidence));
        $this->assertSame([$this->president->id], $this->queue()->reviewers($evidence)->pluck('id')->all());
    }

    public function test_a_coordinator_who_loses_the_role_sends_to_the_coordination_again(): void
    {
        $evidence = $this->evidence($this->coordinator);
        $this->coordinator->roles()->detach(Role::where('rol', 'COORDINATOR')->sole());

        $this->assertFalse($this->queue()->goesToPresidency($evidence));
        $this->assertTrue($this->queue()->reviewsAsCoordinator($this->cocoordinator, $evidence));
    }

    public function test_drafts_and_replaced_versions_are_reviewed_by_nobody(): void
    {
        $draft = $this->evidence($this->student, status: 'DRAFT');
        $replaced = $this->evidence($this->coordinator);
        $replaced->update(['last' => false]);

        foreach ([$this->coordinator, $this->president, $this->lecturer] as $person) {
            $this->assertFalse($this->queue()->canReview($person, $draft));
            $this->assertFalse($this->queue()->canReview($person, $replaced));
        }
    }

    public function test_the_queues_of_each_person(): void
    {
        $this->evidence($this->student, title: 'De Lucía');
        $this->evidence($this->cocoordinator, title: 'De la coordinación');
        $this->evidence($this->student, $this->presidency, title: 'A la presidencia');
        $this->evidence($this->otherCoordinator, title: 'De otro comité');
        $this->evidence($this->student, status: 'ACCEPTED', title: 'Aceptada');
        $this->evidence($this->student, status: 'DRAFT', title: 'Borrador');
        $titles = fn (array $queue): array => ReviewQueue::pending($queue)->orderBy('id')->pluck('title')->all();

        $coordination = $this->queue()->queuesOf($this->coordinator);
        $this->assertSame(['coordination:'.$this->logistics->id], array_column($coordination, 'key'));
        $this->assertSame('Logística', $coordination[0]['name']);
        $this->assertSame('coordinator.evidence.list.pending', $coordination[0]['route']);
        $this->assertSame(['De Lucía', 'De otro comité'], $titles($coordination[0]));
        $this->assertSame(['De Lucía', 'De otro comité', 'Aceptada'], $coordination[0]['evidences']->orderBy('id')->pluck('title')->all());

        $presidency = $this->queue()->queuesOf($this->president);
        $this->assertSame(['presidency'], array_column($presidency, 'key'));
        $this->assertSame('Presidencia', $presidency[0]['name']);
        $this->assertSame('president.review.pending', $presidency[0]['route']);
        $this->assertSame(['De la coordinación', 'A la presidencia'], $titles($presidency[0]));

        $this->assertSame([], $this->queue()->queuesOf($this->lecturer), 'The lecturer has no queue');
        $this->assertSame([], $this->queue()->queuesOf($this->student));
        $this->assertSame(
            [$this->coordinator->id, $this->cocoordinator->id, $this->otherCoordinator->id, $this->president->id],
            $this->queue()->reviewersWithQueue()->pluck('id')->all(),
        );
    }

    public function test_the_scopes_narrow_any_query_of_evidences(): void
    {
        $this->evidence($this->student);
        $this->evidence($this->coordinator);

        $this->assertSame(1, $this->queue()->forCommittee($this->logistics)->count());
        $this->assertSame(1, $this->queue()->delegatedFrom($this->logistics)->count());
        $this->assertSame(1, $this->queue()->forPresidency()->count());
        $counted = Committee::withCount(['evidences as queued' => fn ($query) => $this->queue()->coordination($query)])
            ->whereKey($this->logistics->id)->sole();
        $this->assertSame(1, (int) $counted->queued);
    }

    public function test_without_a_committee_of_the_presidency_only_the_coordinators_go_to_the_presidents(): void
    {
        Committee::query()->update(['presidency' => false]);
        $sent = $this->evidence($this->student, $this->presidency);
        $own = $this->evidence($this->coordinator);

        $this->assertNull($this->queue()->presidency());
        $this->assertFalse($this->queue()->goesToPresidency($sent));
        $this->assertTrue($this->queue()->goesToPresidency($own));
        $this->assertSame([$own->id], $this->queue()->forPresidency()->pluck('id')->all());
    }

    public function test_the_committee_of_the_presidency_is_never_removed(): void
    {
        $this->assertFalse($this->presidency->can_be_removed());
        $this->assertTrue(Committee::create(['name' => 'Voluntariado'])->can_be_removed());
    }
}
