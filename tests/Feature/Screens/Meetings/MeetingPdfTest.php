<?php

namespace Tests\Feature\Screens\Meetings;

use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class MeetingPdfTest extends MeetingScreenTestCase
{
    public function test_the_minutes_template_lays_out_data_attendees_agenda_and_agreements(): void
    {
        $html = view('meeting.minutes_template', ['meeting_minutes' => MeetingMinutes::sole()])->render();

        $this->assertStringContainsString('<h1 class="pdf-title">Acta de reunión</h1>', $html);
        $this->assertStringContainsString('Comité de Logística', $html);
        foreach (['Datos de la reunión', 'Asistentes', 'Orden del día', 'Desarrollo de la reunión', 'Cierre de la reunión'] as $section) {
            $this->assertStringContainsString($section, $html);
        }
        $this->assertStringContainsString('Reparto de tareas (20 min)', $html);
        $this->assertStringContainsString('ISD-1', $html);
        $this->assertStringContainsString('Cada equipo nombra un responsable.', $html);
        $this->assertStringContainsString('Navarro Ruiz', $html);
        $this->assertCspSafe($html);
    }

    public function test_the_call_template_lists_its_data_and_agenda(): void
    {
        $html = view('meeting.request_template', ['meeting_request' => MeetingRequest::sole()])->render();

        $this->assertStringContainsString('<h1 class="pdf-title">Convocatoria de reunión</h1>', $html);
        $this->assertStringContainsString('Convocatoria de logística', $html);
        $this->assertStringContainsString('Aula A3.10', $html);
        $this->assertStringContainsString('<li>Reparto de tareas</li>', $html);
        $this->assertStringContainsString('Ruiz Díaz', $html);
        $this->assertCspSafe($html);
    }

    public function test_saving_minutes_and_a_call_stores_their_pdfs(): void
    {
        $student = User::where('email', 'estudiante@e2e.test')->sole();
        $form = [
            'title' => 'Reunión de cierre', 'type' => 1, 'modality' => 2, 'hours' => 1, 'minutes' => 30,
            'place' => 'Aula A3.10', 'date' => '2026-10-16', 'time' => '16:30', 'users' => [$student->id],
            'points_json' => json_encode([[
                'id' => null, 'title' => 'Balance', 'duration' => 15, 'description' => 'Repaso de las jornadas',
                'agreements' => [['id' => null, 'description' => 'Enviar la memoria']],
            ]]),
        ];

        $this->as('secretario')->post(route('secretary.meeting.manage.minutes.create.step3_p'), $form)
            ->assertRedirect(route('secretary.meeting.manage.minutes.list'));
        $minutes = MeetingMinutes::latest('id')->first();
        $this->assertStringStartsWith('%PDF', Storage::get('/meeting_minutes/meeting_minutes_'.$minutes->id.'.pdf'));

        $this->post(route('secretary.meeting.manage.request.new'), [
            'title' => 'Convocatoria de cierre', 'place' => 'Aula A3.10', 'date' => now()->addDay()->format('Y-m-d'),
            'time' => '10:00', 'type' => 1, 'modality' => 1, 'points_list' => json_encode(['Balance']),
        ])->assertRedirect(route('secretary.meeting.manage.request.list'));
        $request = MeetingRequest::latest('id')->first();
        $this->assertStringStartsWith('%PDF', Storage::get('/meeting_requests/meeting_request_'.$request->id.'.pdf'));
    }
}
