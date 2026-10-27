<?php

namespace Tests\Feature;

use App\Models\Agreement;
use App\Models\Committee;
use App\Models\Meeting;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MeetingMinutesAgreementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_minutes_saves_their_agreements(): void
    {
        Storage::fake();
        $user = User::factory()->create();
        $user->roles()->attach(Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']));
        $committee = Committee::create(['name' => 'Logística']);
        Secretary::forceCreate(['user_id' => $user->id, 'committee_id' => $committee->id]);
        $points = [[
            'title' => 'Reparto de tareas',
            'duration' => '15',
            'description' => 'Se organizan los equipos de montaje.',
            'agreements' => [['description' => 'Cada equipo nombra un responsable.'], ['description' => 'Se revisa el material el viernes.']],
        ]];

        $this->actingAs($user)->post(route('secretary.meeting.manage.minutes.create.step3_p'), [
            'title' => 'Desglose de tareas del Comité de Logística',
            'type' => 1,
            'modality' => 1,
            'hours' => 1,
            'place' => 'Aula A3.10',
            'date' => '2026-10-24',
            'time' => '11:40',
            'users' => [User::factory()->create()->id],
            'points_json' => json_encode($points),
        ])->assertRedirect(route('secretary.meeting.manage.minutes.list'));

        $meeting = Meeting::sole();
        $agreements = Agreement::orderBy('id')->get();
        $this->assertCount(2, $agreements);
        foreach ($agreements as $agreement) {
            $this->assertSame(
                'ISD-'.now()->format('Y-m-d').'-'.$committee->id.'-'.$meeting->id.'-'.$agreement->point_id.'-'.$agreement->id,
                $agreement->identificator
            );
        }
        Storage::assertExists('/meeting_minutes/meeting_minutes_'.$meeting->meetingMinutes->id.'.pdf');
    }
}
