<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Evidence;
use App\Models\MeetingRequest;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EscapedOutputTest extends TestCase
{
    use RefreshDatabase;

    private const SCRIPT = '<script>alert(1)</script>';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
        Configuration::forceCreate([
            'upload_evidences_timestamp' => now()->addWeek(),
            'meetings_timestamp' => now()->addWeek(),
        ]);
    }

    public function test_meeting_request_titles_are_printed_as_text(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']));
        $committee = Committee::create(['name' => 'Logística']);
        $secretary = Secretary::forceCreate(['user_id' => $user->id, 'committee_id' => $committee->id]);
        MeetingRequest::forceCreate([
            'title' => 'Reunión '.self::SCRIPT,
            'committee_id' => $committee->id,
            'secretary_id' => $secretary->id,
        ]);

        $this->actingAs($user)
            ->get(route('secretary.meeting.manage.signaturesheet.create'))
            ->assertOk()
            ->assertSee('Reunión '.self::SCRIPT)
            ->assertDontSee(self::SCRIPT, false);
    }

    public function test_an_evidence_description_stays_inside_its_editor(): void
    {
        $student = User::factory()->create();
        $student->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));
        $evidence = Evidence::create([
            'title' => 'Organización de mesas',
            'description' => '<p>Mesas</p></textarea>'.self::SCRIPT,
            'hours' => 2,
            'status' => 'DRAFT',
            'user_id' => $student->id,
            'committee_id' => Committee::create(['name' => 'Programa'])->id,
        ]);

        $this->actingAs($student)
            ->get(route('evidence.edit', ['id' => $evidence->id]))
            ->assertOk()
            ->assertDontSee(self::SCRIPT, false);
    }
}
