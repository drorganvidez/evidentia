<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Evidence;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvidenceVersionsCountTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private Committee $committee;

    private Evidence $old;

    private Evidence $latest;

    protected function setUp(): void
    {
        parent::setUp();
        Configuration::forceCreate(['upload_evidences_timestamp' => now()->addWeek()]);
        $this->committee = Committee::create(['name' => 'Programa']);
        $this->student = User::factory()->create();
        $this->student->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));

        // una evidencia de 2 horas con dos versiones aceptadas: solo cuenta la última
        $this->old = $this->version(['last' => false]);
        $this->latest = $this->version(['points_to' => $this->old->id]);
    }

    public function test_hours_and_counts_only_include_the_latest_version(): void
    {
        $this->assertEquals(2, $this->student->evidences_accepted_hours());
        $this->assertSame(1, $this->student->evidences_accepted_count());
        $this->assertSame(1, $this->committee->evidencesAccepted()->count());
        $this->assertSame(1, $this->committee->evidencesNotDraft()->count());
        $this->assertSame([$this->latest->id], Evidence::evidencesAccepted()->modelKeys());
    }

    public function test_the_draw_picks_the_latest_version(): void
    {
        $lecture = User::factory()->create();
        $lecture->roles()->attach(Role::forceCreate(['rol' => 'LECTURE', 'slug' => 'lecture']));

        $this->actingAs($lecture)->post(route('randomize.save'))->assertRedirect(route('randomize.randomize'));

        $this->assertTrue((bool) $this->latest->fresh()->rand);
        $this->assertFalse((bool) $this->old->fresh()->rand);
    }

    private function version(array $attributes): Evidence
    {
        return Evidence::forceCreate($attributes + [
            'title' => 'Organización de mesas',
            'description' => 'Organicé las mesas del congreso durante dos horas.',
            'hours' => 2,
            'status' => 'ACCEPTED',
            'user_id' => $this->student->id,
            'committee_id' => $this->committee->id,
        ]);
    }
}
