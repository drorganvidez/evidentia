<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Evidence;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EvidenceEditTest extends TestCase
{
    use RefreshDatabase;

    private Role $role;

    private Committee $committee;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
        Configuration::forceCreate([
            'upload_evidences_timestamp' => now()->addWeek(),
            'validate_evidences_timestamp' => now()->addWeeks(2),
        ]);
        $this->role = Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']);
        $this->committee = Committee::create(['name' => 'Programa']);
    }

    public function test_a_student_publishes_an_edit_of_their_draft(): void
    {
        $student = $this->student();
        $draft = $this->evidence($student, 'DRAFT');

        $this->actingAs($student)
            ->post(route('evidence.publish.edit'), $this->form($draft->id))
            ->assertRedirect(route('evidence.list'));

        $this->assertFalse((bool) $draft->fresh()->last);
        $this->assertSame('PENDING', Evidence::where('last', true)->sole()->status);
    }

    public function test_a_student_cannot_edit_another_students_evidence(): void
    {
        $evidence = $this->evidence($this->student(), 'DRAFT');
        $this->actingAs($this->student());

        $this->post(route('evidence.draft.edit'), $this->form($evidence->id))->assertNotFound();
        $this->post(route('evidence.publish.edit'), $this->form($evidence->id))->assertNotFound();

        $this->assertTrue((bool) $evidence->fresh()->last);
        $this->assertSame(1, Evidence::count());
    }

    public function test_publishing_the_same_edit_twice_leaves_one_version_to_review(): void
    {
        $student = $this->student();
        $draft = $this->evidence($student, 'DRAFT');
        $this->actingAs($student);

        $this->post(route('evidence.publish.edit'), $this->form($draft->id))->assertRedirect(route('evidence.list'));
        $this->post(route('evidence.publish.edit'), $this->form($draft->id))->assertNotFound();

        $this->assertSame(1, Evidence::where('status', 'PENDING')->count());
    }

    public function test_an_old_draft_cannot_reopen_an_evidence_already_published(): void
    {
        $student = $this->student();
        $draft = $this->evidence($student, 'DRAFT');
        $this->actingAs($student)->post(route('evidence.publish.edit'), $this->form($draft->id));

        $this->get(route('evidence.edit', ['id' => $draft->id]))->assertNotFound();
        $this->post(route('evidence.draft.edit'), $this->form($draft->id))->assertNotFound();
    }

    public function test_an_accepted_evidence_cannot_be_edited(): void
    {
        $student = $this->student();
        $accepted = $this->evidence($student, 'ACCEPTED');
        $this->actingAs($student);

        $this->get(route('evidence.edit', ['id' => $accepted->id]))->assertNotFound();
        $this->post(route('evidence.publish.edit'), $this->form($accepted->id))->assertNotFound();

        $this->assertSame(1, Evidence::count());
    }

    public function test_an_evidence_needs_an_existing_committee(): void
    {
        $this->actingAs($this->student());
        $form = $this->form(0);
        unset($form['_id'], $form['committee']);

        $this->post(route('evidence.publish'), $form)->assertSessionHasErrors('committee');
        $this->post(route('evidence.publish'), ['committee' => 999] + $form)->assertSessionHasErrors('committee');

        $this->assertSame(0, Evidence::count());
        $this->get(route('evidence.list'))->assertOk();
    }

    private function student(): User
    {
        $student = User::factory()->create();
        $student->roles()->attach($this->role);

        return $student;
    }

    private function evidence(User $student, string $status): Evidence
    {
        return Evidence::create([
            'title' => 'Organización de mesas',
            'description' => 'Organicé las mesas del congreso durante dos horas.',
            'hours' => 2,
            'status' => $status,
            'user_id' => $student->id,
            'committee_id' => $this->committee->id,
        ]);
    }

    private function form(int $id): array
    {
        return [
            '_id' => $id,
            'title' => 'Organización de mesas',
            'hours' => 2,
            'minutes' => 0,
            'committee' => $this->committee->id,
            'description' => 'Organicé las mesas del congreso durante dos horas.',
        ];
    }
}
