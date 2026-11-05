<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Coordinator;
use App\Models\Evidence;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RichTextSanitizingTest extends TestCase
{
    use RefreshDatabase;

    private const FORMATTED = '<p>Organicé las <strong>mesas</strong> del congreso.</p>';

    private const INJECTED = '<script>fetch("/management/user/delete/all")</script><img src="x" onerror="alert(1)">';

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        Configuration::forceCreate([
            'upload_evidences_timestamp' => now()->addWeek(),
            'validate_evidences_timestamp' => now()->addWeeks(2),
        ]);
        $this->student = User::factory()->create(['biography' => self::FORMATTED.self::INJECTED]);
        $this->student->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));
    }

    public function test_the_coordinator_sees_the_evidence_formatting_without_its_scripts(): void
    {
        $committee = Committee::create(['name' => 'Programa']);
        $coordinator = User::factory()->create();
        $coordinator->roles()->attach(Role::forceCreate(['rol' => 'COORDINATOR', 'slug' => 'coordinator']));
        Coordinator::forceCreate(['user_id' => $coordinator->id, 'committee_id' => $committee->id]);
        $evidence = Evidence::create([
            'title' => 'Organización de mesas',
            'description' => self::FORMATTED.self::INJECTED,
            'hours' => 2,
            'status' => 'PENDING',
            'user_id' => $this->student->id,
            'committee_id' => $committee->id,
        ]);

        $this->actingAs($coordinator)
            ->get(route('coordinator.evidence.view', ['id' => $evidence->id]))
            ->assertOk()
            ->assertSee(self::FORMATTED, false)
            ->assertDontSee('/management/user/delete/all', false)
            ->assertDontSee('onerror', false);
    }

    public function test_the_lecturer_sees_the_biography_formatting_without_its_scripts(): void
    {
        $lecture = User::factory()->create();
        $lecture->roles()->attach(Role::forceCreate(['rol' => 'LECTURE', 'slug' => 'lecture']));

        $this->actingAs($lecture)
            ->get(route('profiles.view', ['id' => $this->student->id]))
            ->assertOk()
            ->assertSee(self::FORMATTED, false)
            ->assertDontSee('/management/user/delete/all', false)
            ->assertDontSee('onerror', false);
    }
}
