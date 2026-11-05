<?php

namespace Tests\Feature;

use App\Exports\EvidencesExport;
use App\Models\Attendee;
use App\Models\Configuration;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinalHoursExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_final_export_includes_the_attendance_hours(): void
    {
        Configuration::forceCreate([]);
        $student = User::factory()->create();
        $student->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));
        $event = Event::forceCreate(['name' => 'Charla IA', 'id_eventbrite' => '111', 'hours' => 2]);
        Attendee::forceCreate(['event_id' => $event->id, 'user_id' => $student->id, 'status' => 'Checked In']);

        $row = (new EvidencesExport(null, null, 'on', null))->collection()->firstWhere('uvus', $student->username);

        $this->assertEquals(2, $row->horas_asistencia);
        $this->assertEquals(2, $row->horas_totales);
    }
}
