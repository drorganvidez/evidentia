<?php

namespace Tests\Feature;

use App\Models\Agreement;
use App\Models\Committee;
use App\Models\Meeting;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class MinutesEditKeepsIdentifiersTest extends TestCase
{
    use RefreshDatabase;

    public function test_editing_minutes_keeps_the_minutes_link_and_agreement_identifiers(): void
    {
        Storage::fake();
        DB::table('configuration')->insert(['id' => 1, 'meetings_timestamp' => now()->addMonth()]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']));
        $committee = Committee::create(['name' => 'Logística']);
        Secretary::forceCreate(['user_id' => $user->id, 'committee_id' => $committee->id]);
        $this->actingAs($user);
        $attendee = User::factory()->create();
        $form = [
            'title' => 'Reunión inicial de Logística',
            'type' => 1, 'modality' => 1, 'hours' => 1,
            'place' => 'Aula A3.10', 'date' => '2026-10-16', 'time' => '16:30',
            'users' => [$attendee->id],
            'points_json' => json_encode([[
                'title' => 'Reparto de tareas', 'duration' => '15', 'description' => 'Equipos',
                'agreements' => [['description' => 'Cada equipo nombra un responsable.']],
            ]]),
        ];

        Carbon::setTestNow('2026-10-16 18:00');
        $this->post(route('secretary.meeting.manage.minutes.create.step3_p'), $form);
        $meeting = Meeting::sole();
        $minutesId = $meeting->meetingMinutes->id;
        $identificator = Agreement::sole()->identificator;
        $downloadUrl = route('download.minutes', $minutesId);

        // Una semana después se corrige una errata del título, sin tocar puntos ni acuerdos
        Carbon::setTestNow('2026-10-23 10:00');
        $this->post(route('secretary.meeting.manage.minutes.save'), ['title' => 'Reunión inicial del Comité de Logística']
            + $form + ['_id' => $minutesId])
            ->assertRedirect(route('secretary.meeting.manage.minutes.list'));

        $this->assertSame($identificator, Agreement::sole()->identificator, 'El identificador del acuerdo cambia al editar');
        $this->assertSame($minutesId, $meeting->fresh()->meetingMinutes->id, 'El acta cambia de id al editar');
        ob_start(); // DownloadController cierra el búfer de salida activo
        $this->get($downloadUrl)->assertOk();
    }

    public function test_the_regenerated_pdf_shows_the_edited_points(): void
    {
        Storage::fake();
        DB::table('configuration')->insert(['id' => 1, 'meetings_timestamp' => now()->addMonth()]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']));
        Secretary::forceCreate(['user_id' => $user->id, 'committee_id' => Committee::create(['name' => 'Logística'])->id]);
        $this->actingAs($user);
        $form = [
            'title' => 'Reunión inicial de Logística', 'type' => 1, 'modality' => 1, 'hours' => 1,
            'place' => 'Aula A3.10', 'date' => '2026-10-16', 'time' => '16:30',
            'users' => [User::factory()->create()->id],
        ];
        $this->post(route('secretary.meeting.manage.minutes.create.step3_p'), $form + [
            'points_json' => json_encode([['title' => 'Punto antiguo', 'duration' => '5', 'description' => 'x', 'agreements' => []]]),
        ]);
        $minutesId = Meeting::sole()->meetingMinutes->id;

        $rendered = [];
        View::composer('meeting.minutes_template', function ($view) use (&$rendered) {
            $rendered = $view->getData()['meeting_minutes']->points->pluck('title')->all();
        });
        $this->post(route('secretary.meeting.manage.minutes.save'), $form + ['_id' => $minutesId,
            'points_json' => json_encode([['title' => 'Punto nuevo', 'duration' => '5', 'description' => 'x', 'agreements' => []]]),
        ])->assertRedirect(route('secretary.meeting.manage.minutes.list'));

        $this->assertSame(['Punto nuevo'], $rendered);
    }
}
