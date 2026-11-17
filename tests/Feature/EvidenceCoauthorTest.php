<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Evidence;
use App\Models\Role;
use App\Models\User;
use App\Notifications\Events\EvidenceAccepted;
use App\Notifications\Events\EvidenceCoauthored;
use App\Notifications\Events\EvidenceCoauthorLeft;
use App\Services\Activity\ActivityAccess;
use App\Services\EvidenceReview;
use App\View\ManagementDashboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EvidenceCoauthorTest extends TestCase
{
    use RefreshDatabase;

    private Committee $committee;

    private User $author;

    private User $partner;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
        Notification::fake();
        Configuration::forceCreate([
            'upload_evidences_timestamp' => now()->addWeek(),
            'validate_evidences_timestamp' => now()->addWeeks(2),
        ]);
        $this->committee = Committee::create(['name' => 'Programa']);
        $this->author = $this->user('STUDENT');
        $this->partner = $this->user('STUDENT');
    }

    public function test_publishing_with_coauthors_stores_them_and_notifies_them(): void
    {
        $this->actingAs($this->author)->post(route('evidence.publish'), $this->form([$this->partner->id]))
            ->assertRedirect(route('evidence.list'));

        $evidence = Evidence::sole();
        $this->assertEquals([$this->partner->id], $evidence->coauthors->pluck('id')->all());
        $this->assertSame([$this->partner->id], ActivityLog::where('action', 'evidence.published')->sole()->details['coauthor_ids']);
        Notification::assertSentTo($this->partner, EvidenceCoauthored::class);
        Notification::assertNotSentTo($this->author, EvidenceCoauthored::class);
    }

    public function test_only_other_students_can_be_coauthors(): void
    {
        $lecturer = $this->user('LECTURE');

        $this->actingAs($this->author)->post(route('evidence.publish'), $this->form([$this->author->id]))->assertSessionHasErrors('coauthors.0');
        $this->post(route('evidence.publish'), $this->form([$lecturer->id]))->assertSessionHasErrors('coauthors.0');
        $this->assertSame(0, Evidence::count());
    }

    public function test_an_accepted_evidence_adds_its_full_hours_to_every_coauthor(): void
    {
        $other = $this->user('STUDENT');
        $this->actingAs($this->author)->post(route('evidence.publish'), $this->form([$this->partner->id, $other->id]));
        $evidence = Evidence::sole();
        $this->assertEquals(0, $this->partner->fresh()->evidences_accepted_hours());

        $this->actingAs($this->user('COORDINATOR'));
        app(EvidenceReview::class)->accept($evidence, 'COORDINATOR');

        foreach ([$this->author, $this->partner, $other] as $student) {
            $this->assertEquals(3.5, $student->fresh()->evidences_accepted_hours());
            $this->assertSame(1, $student->fresh()->evidences_accepted_count());
        }
        Notification::assertSentTo($this->partner, EvidenceAccepted::class);
        Notification::assertSentTo($this->author, EvidenceAccepted::class);
    }

    public function test_editing_keeps_the_coauthors_per_version_and_only_the_last_one_counts(): void
    {
        $other = $this->user('STUDENT');
        $this->actingAs($this->author)->post(route('evidence.draft'), $this->form([$this->partner->id]));
        $draft = Evidence::sole();

        $this->post(route('evidence.publish.edit'), ['_id' => $draft->id] + $this->form([$other->id]));

        $last = Evidence::where('last', true)->sole();
        $this->assertEquals([$other->id], $last->coauthors->pluck('id')->all());
        $this->assertEquals([$this->partner->id], $draft->fresh()->coauthors->pluck('id')->all());
        $last->update(['status' => 'ACCEPTED']);
        $this->assertEquals(0, $this->partner->fresh()->evidences_accepted_hours());
        $this->assertEquals(3.5, $other->fresh()->evidences_accepted_hours());
    }

    public function test_a_coauthor_reads_a_sent_evidence_and_its_proofs_but_cannot_change_it(): void
    {
        $this->actingAs($this->author)->post(route('evidence.draft'), $this->form([$this->partner->id]));
        $evidence = Evidence::sole();

        $this->actingAs($this->partner)->get(route('evidence.view', ['id' => $evidence->id]))->assertNotFound();

        $evidence->update(['status' => 'PENDING']);
        $this->get(route('evidence.view', ['id' => $evidence->id]))->assertOk()
            ->assertSee('Coautores')->assertDontSee('Escribir al coordinador');
        $this->get(route('evidence.list'))->assertOk()->assertSee('Evidencias en coautoría')->assertSee('Talleres de robótica');
        $this->get(route('evidence.edit', ['id' => $evidence->id]))->assertNotFound();
        $this->post(route('evidence.remove'), ['_id' => $evidence->id])->assertNotFound();
        $this->assertTrue(app(ActivityAccess::class)->canViewEvidence($this->partner, $evidence));
        $this->assertFalse(app(ActivityAccess::class)->canViewEvidence($this->user('STUDENT'), $evidence));
    }

    public function test_the_capped_and_total_hours_include_coauthored_evidences(): void
    {
        $evidence = Evidence::create(['title' => 'Talleres', 'hours' => 4, 'status' => 'ACCEPTED', 'last' => true, 'user_id' => $this->author->id, 'committee_id' => $this->committee->id]);
        $evidence->coauthors()->attach($this->partner->id);

        $this->assertEquals(4.0, $this->partner->fresh()->evidences_computed_hours());
        $this->assertEquals(4.0, $this->partner->fresh()->total_computed_hours());
    }

    public function test_a_coauthor_can_leave_a_pending_or_accepted_evidence_and_the_author_is_notified(): void
    {
        $this->actingAs($this->author)->post(route('evidence.publish'), $this->form([$this->partner->id]));
        $evidence = Evidence::sole();

        // Even with the upload deadline closed, a coauthor can leave
        Configuration::query()->update(['upload_evidences_timestamp' => now()->subDay()]);

        $this->actingAs($this->partner)->get(route('evidence.list'))
            ->assertOk()
            ->assertSee('Dejar de ser coautor')
            ->assertSee('action="'.route('evidence.coauthor.leave').'"', false);

        $this->get(route('evidence.view', ['id' => $evidence->id]))
            ->assertOk()
            ->assertSee('Dejar de ser coautor')
            ->assertSee('action="'.route('evidence.coauthor.leave').'"', false);

        $this->post(route('evidence.coauthor.leave'), ['_id' => $evidence->id])
            ->assertRedirect(route('evidence.list'))
            ->assertSessionHas('success', 'Has dejado de figurar como coautor en la evidencia.');

        $this->assertSame([], $evidence->fresh()->coauthors->pluck('id')->all());

        $log = ActivityLog::where('action', 'evidence.coauthor_left')->sole();
        $this->assertSame($this->partner->id, $log->user_id);
        $this->assertSame('STUDENT', $log->acting_role);
        $this->assertSame($this->author->id, $log->target_user_id);
        $this->assertFalse($log->flagged);
        $this->assertSame(1, $log->details['version']);

        Notification::assertSentTo($this->author, EvidenceCoauthorLeft::class, function (EvidenceCoauthorLeft $notice) use ($evidence): bool {
            $data = $notice->toArray($this->author);
            $this->assertSame('Un coautor ha dejado tu evidencia', $data['title']);
            $this->assertSame('/activity/evidence/'.$evidence->id, $data['url']);
            $this->assertStringContainsString('Talleres de robótica', $notice->mailSubject($this->author));

            return true;
        });
        Notification::assertNotSentTo($this->partner, EvidenceCoauthorLeft::class);

        $this->actingAs($this->author)->get(route('activity.evidence', ['id' => $evidence->id]))
            ->assertOk()
            ->assertSee('Coautoría abandonada');
        $this->get(route('evidence.view', ['id' => $evidence->id]))
            ->assertOk()
            ->assertSee('Coautoría abandonada');
    }

    public function test_leaving_an_accepted_evidence_recalculates_the_hours_and_the_management_dashboard(): void
    {
        $other = $this->user('STUDENT');
        $lecturer = $this->user('LECTURE');
        $this->actingAs($this->author)->post(route('evidence.publish'), $this->form([$this->partner->id, $other->id]));
        $evidence = Evidence::sole();

        $this->actingAs($this->user('COORDINATOR'));
        app(EvidenceReview::class)->accept($evidence, 'COORDINATOR');

        $dashboard = app(ManagementDashboard::class);
        $this->assertEquals(10.5, $dashboard->for($lecturer)['kpis']['hours']);
        $this->assertEquals(3.5, $this->partner->fresh()->evidences_accepted_hours());

        $this->actingAs($this->partner)->post(route('evidence.coauthor.leave'), ['_id' => $evidence->id])
            ->assertRedirect(route('evidence.list'));

        $this->assertEquals(0, $this->partner->fresh()->evidences_accepted_hours());
        $this->assertEquals(0, $this->partner->fresh()->evidences_computed_hours());
        $this->assertEquals(0, $this->partner->fresh()->total_computed_hours());
        $this->assertSame(0, $this->partner->fresh()->evidences_accepted_count());

        $this->assertEquals(3.5, $this->author->fresh()->evidences_accepted_hours());
        $this->assertEquals(3.5, $other->fresh()->evidences_accepted_hours());
        $this->assertEquals(7.0, $dashboard->for($lecturer)['kpis']['hours']);
    }

    public function test_only_a_coauthor_of_the_current_pending_or_accepted_version_can_leave(): void
    {
        $stranger = $this->user('STUDENT');
        $this->actingAs($this->author)->post(route('evidence.draft'), $this->form([$this->partner->id]));
        $first = Evidence::sole();

        // Draft cannot be left
        $this->actingAs($this->partner)->post(route('evidence.coauthor.leave'), ['_id' => $first->id])->assertNotFound();

        // Publish a second version with the same coauthor
        $this->actingAs($this->author)->post(route('evidence.publish.edit'), ['_id' => $first->id] + $this->form([$this->partner->id]));
        $current = Evidence::where('last', true)->sole();

        // Author, stranger and old version cannot leave
        $this->actingAs($this->author)->post(route('evidence.coauthor.leave'), ['_id' => $current->id])->assertNotFound();
        $this->actingAs($stranger)->post(route('evidence.coauthor.leave'), ['_id' => $current->id])->assertNotFound();
        $this->actingAs($this->partner)->post(route('evidence.coauthor.leave'), ['_id' => $first->id])->assertNotFound();

        // Rejected cannot be left either
        $current->update(['status' => 'REJECTED']);
        $this->actingAs($this->partner)->get(route('evidence.view', ['id' => $current->id]))
            ->assertOk()
            ->assertDontSee('action="'.route('evidence.coauthor.leave').'"', false);
        $this->post(route('evidence.coauthor.leave'), ['_id' => $current->id])->assertNotFound();

        // Once pending again, leaving only detaches from the current version
        $current->update(['status' => 'PENDING']);
        $this->actingAs($this->partner)->post(route('evidence.coauthor.leave'), ['_id' => $current->id])
            ->assertRedirect(route('evidence.list'));

        $this->assertSame([], $current->fresh()->coauthors->pluck('id')->all());
        $this->assertSame([$this->partner->id], $first->fresh()->coauthors->pluck('id')->all());
    }

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('rol', $role)->first() ?? Role::forceCreate(['rol' => $role, 'slug' => strtolower($role)]));
        if ($role === 'COORDINATOR') {
            $user->coordinator()->forceCreate(['committee_id' => $this->committee->id]);
        }

        return $user;
    }

    /** @param  list<int>  $coauthors */
    private function form(array $coauthors = []): array
    {
        return [
            'title' => 'Talleres de robótica',
            'hours' => 3,
            'minutes' => 30,
            'committee' => $this->committee->id,
            'description' => 'Preparación y montaje de los talleres de robótica.',
            'coauthors' => $coauthors,
        ];
    }
}
