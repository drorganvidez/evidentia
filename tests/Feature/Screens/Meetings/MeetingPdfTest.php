<?php

namespace Tests\Feature\Screens\Meetings;

use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;

class MeetingPdfTest extends MeetingScreenTestCase
{
    public function test_the_minutes_template_lays_out_data_attendees_agenda_and_agreements(): void
    {
        $html = view('meeting.minutes_template', ['meeting_minutes' => MeetingMinutes::sole()])->render();

        $this->assertStringContainsString('<h1 class="pdf-title">Acta de reunión</h1>', $html);
        $this->assertStringContainsString('Comité de Logística', $html);
        foreach (['Asistentes', 'Orden del día', 'Desarrollo de la reunión', 'Cierre'] as $section) {
            $this->assertStringContainsString('<h2 class="pdf-section__title">'.$section, $html);
        }
        $this->assertStringContainsString('<span class="pdf-meta__label">Horario</span>', $html);
        $this->assertMatchesRegularExpression('/class="pdf-point__number">1\.<\/span>Reparto de tareas<\/td>/', $html);
        $this->assertStringContainsString('<td class="pdf-point__time">20 min</td>', $html);
        $this->assertMatchesRegularExpression('/class="pdf-pill">ISD-1<\/span>/', $html);
        $this->assertStringContainsString('<p>Cada equipo nombra un responsable.</p>', $html);
        $this->assertStringNotContainsString('class="pdf-section pdf-section--break', $html, 'The points follow the agenda on the same page');
        $this->assertStringContainsString('Pérez Romero, Lucía', $html);
        $this->assertStringNotContainsString('Navarro Ruiz', $html, 'A coordinator not marked as attendee is not listed');
        $this->assertCspSafe($html);
    }

    public function test_the_attendees_are_the_marked_people_once_each_with_their_role(): void
    {
        $minutes = MeetingMinutes::sole();
        $meeting = $minutes->meeting;
        $secretary = $meeting->committee->secretaries->first()->user;
        $meeting->users()->syncWithoutDetaching([$secretary->id]);
        $minutes->refresh();

        $html = view('meeting.minutes_template', ['meeting_minutes' => $minutes])->render();
        $count = $meeting->users()->distinct()->count('users.id');

        $this->assertStringContainsString('Asistentes <span class="pdf-count">· '.$count.'</span>', $html);
        $this->assertSame(1, substr_count($html, e($secretary->surname.', '.$secretary->name)));
        $this->assertStringContainsString('<span class="pdf-tag">Secretaría</span>', $html);
        $this->assertStringNotContainsString('Rol', $html);
    }

    public function test_points_and_agreements_print_lists_and_bold_and_escape_html(): void
    {
        $minutes = MeetingMinutes::sole();
        $point = $minutes->points->first();
        $point->update(['description' => "Se revisa el **patrocinio**.\n\n- Contactar empresas\n- Preparar el dosier\n\nSi a < b, se avisa."]);
        $point->agreements->first()->update(['description' => "Encargar:\n1. El photocall\n2. Las acreditaciones"]);

        $html = view('meeting.minutes_template', ['meeting_minutes' => $minutes->fresh()])->render();

        $this->assertStringContainsString('<strong>patrocinio</strong>', $html);
        $this->assertMatchesRegularExpression('/<ul>\s*<li>Contactar empresas<\/li>\s*<li>Preparar el dosier<\/li>\s*<\/ul>/', $html);
        $this->assertMatchesRegularExpression('/<ol>\s*<li>El photocall<\/li>/', $html);
        $this->assertStringContainsString('Si a &lt; b, se avisa.', $html);

        $point->update(['description' => '<p>Antiguo <b>editor</b></p><script>alert(1)</script>']);
        $old = view('meeting.minutes_template', ['meeting_minutes' => $minutes->fresh()])->render();
        $this->assertStringContainsString('<p>Antiguo <b>editor</b></p>', $old);
        $this->assertStringNotContainsString('alert(1)', $old);
        $this->assertCspSafe($html);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function downloads(): array
    {
        return [
            'minutes' => ['download.minutes', 'minutes'],
            'call' => ['download.request', 'request'],
            'call for the secretary' => ['secretary.meeting.manage.request.download', 'request'],
        ];
    }

    #[DataProvider('downloads')]
    public function test_a_download_renders_the_current_template_again(string $route, string $kind): void
    {
        $id = $kind === 'minutes' ? MeetingMinutes::sole()->id : MeetingRequest::sole()->id;
        $path = $kind === 'minutes' ? '/meeting_minutes/meeting_minutes_'.$id.'.pdf' : '/meeting_requests/meeting_request_'.$id.'.pdf';
        Storage::put($path, '%PDF-1.4 old');

        ob_start(); // the download closes the active output buffer
        $this->as('secretario')->get(route($route, $id))->assertOk();

        $this->assertStringStartsWith('%PDF', Storage::get($path));
        $this->assertGreaterThan(5_000, strlen(Storage::get($path)));
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
