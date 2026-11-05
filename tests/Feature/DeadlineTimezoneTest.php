<?php

namespace Tests\Feature;

use App\Models\Configuration;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeadlineTimezoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_deadlines_close_at_the_spanish_time_they_were_set_to(): void
    {
        // el presidente fija las 20:00 (hora peninsular, UTC+2 en junio)
        Configuration::forceCreate(['upload_evidences_timestamp' => '2026-06-15 20:00:00']);
        $student = User::factory()->create();
        $student->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));
        $this->actingAs($student);

        $this->travelTo(Carbon::parse('2026-06-15 17:30:00', 'UTC'));
        $this->get(route('evidence.create'))->assertOk();

        $this->travelTo(Carbon::parse('2026-06-15 18:30:00', 'UTC'));
        $this->get(route('evidence.create'))->assertNotFound();
    }
}
