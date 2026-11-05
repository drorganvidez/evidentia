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
