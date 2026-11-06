<?php

namespace Tests\Feature\Screens\Management;

use App\Models\Committee;
use App\Models\Evidence;
use Illuminate\Support\Facades\DB;

/**
 * What the president and the lecturers see of each committee: its page with the signals and the way to nudge, the
 * list of committees with their figures, and the evidences and meetings filtered by committee, date and state.
 */
class CommitteeOverviewScreenTest extends ScreenTestCase
{
    private function committee(string $name): Committee
    {
        return Committee::where('name', $name)->sole();
    }

    public function test_the_management_opens_the_page_of_a_committee(): void
    {
        $logistics = $this->committee('Logística');

        foreach (['profesor' => 'lecture', 'presidente' => 'president'] as $who => $prefix) {
            $html = $this->screen($who, route("$prefix.committee.view", ['id' => $logistics->id]))
                ->assertSee('Alertas')
                ->assertSee('Todo en orden')
                ->assertSeeInOrder(['Equipo', 'Carlos Navarro Ruiz', 'Coordinación', 'Último acceso', 'Última revisión', 'Marta Ruiz Díaz', 'Secretaría'])
                ->assertSee('Escribir a la coordinación')
                ->assertSee('Escribir a la secretaría')
                ->assertSeeInOrder(['Pendientes', '2', 'Horas acreditadas', '9,00'])
                ->assertSee('Horas por origen')
                ->assertSee('Miembros de Logística')
                ->assertSee('href="'.route('profiles.view', ['id' => $this->person('estudiante')->id]).'"', false)
                ->assertSee('Últimas evidencias de Logística')
                ->assertSee('Montaje del photocall')
                ->assertDontSee('Borrador de la memoria')
                ->assertSee('Convocatoria de logística')
                ->assertSee('Reunión de logística')
                ->assertSee('Mensajes al comité')
                ->assertSee('Cambio de turno en el registro')
                ->assertSee('Actividad del comité')
                ->assertSee('href="'.route("$prefix.evidence.list", ['comite' => $logistics->id]).'"', false)
                ->getContent();
            $this->assertNoAutofocus($html);
        }
    }

    public function test_the_signals_of_a_committee_carry_their_nudge(): void
    {
        DB::table('evidences')->where('title', 'Atención en el registro')->update(['updated_at' => now()->subDays(12)]);
        $logistics = $this->committee('Logística');

        $this->screen('presidente', route('president.committee.view', ['id' => $logistics->id]))
            ->assertSee('data-signal="late-evidences"', false)
            ->assertSee('1 evidencia espera más de 7 días')
            ->assertSee('La más antigua lleva 12 días en la cola')
            ->assertDontSee('Todo en orden');

        $this->screen('profesor', route('lecture.committee.view', ['id' => $this->committee('Comunicación')->id]))
            ->assertSee('Sin coordinación')
            ->assertSee('Sin secretaría')
            ->assertSee('Sin reuniones todavía')
            ->assertSee('Asignar en Alumnos')
            ->assertSee('Sin coordinación ni secretaría')
            ->assertSee('Sin miembros todavía');
    }

    public function test_only_the_management_opens_it(): void
    {
        $id = $this->committee('Logística')->id;
        foreach (['estudiante', 'coordinador', 'secretario', 'registro'] as $who) {
            $this->actingAs($this->person($who))->get(route('lecture.committee.view', ['id' => $id]))->assertNotFound();
            $this->actingAs($this->person($who))->get(route('president.committee.view', ['id' => $id]))->assertNotFound();
        }
        $this->actingAs($this->person('profesor'))->get(route('lecture.committee.view', ['id' => 999]))->assertNotFound();
    }

    public function test_the_list_of_committees_shows_their_figures_and_signals(): void
    {
        $logistics = $this->committee('Logística');
        $communication = $this->committee('Comunicación');

        $this->screen('profesor', route('lecture.committee.list'))
            ->assertSee('href="'.route('lecture.committee.view', ['id' => $logistics->id]).'"', false)
            ->assertSee('href="'.route('lecture.committee.view', ['id' => $communication->id]).'"', false)
            ->assertSeeInOrder(['Comunicación', 'Sin coordinación', 'Sin secretaría', '3 alertas'])
            ->assertSeeInOrder(['Logística', 'Carlos Navarro Ruiz', 'coordinación', 'Marta Ruiz Díaz', 'secretaría', 'Todo en orden'])
            ->assertSee('Última actividad');
    }

    public function test_the_evidences_filter_by_committee_dates_and_late_ones(): void
    {
        $logistics = $this->committee('Logística');
        Evidence::create([
            'title' => 'Notas de prensa', 'description' => '<p>Notas.</p>', 'hours' => 5, 'status' => 'PENDING',
            'user_id' => $this->person('registro')->id, 'committee_id' => $this->committee('Comunicación')->id,
        ]);
        DB::table('evidences')->where('title', 'Atención en el registro')->update(['updated_at' => now()->subDays(8), 'created_at' => now()->subDays(8)]);

        $this->screen('profesor', route('lecture.evidence.list', ['comite' => $logistics->id]))
            ->assertSee('Montaje del photocall')
            ->assertDontSee('Notas de prensa')
            ->assertSee('Filtros de evidencias')
            ->assertSee('href="'.e(route('lecture.evidence.list', ['estado' => 'pendientes', 'comite' => $logistics->id])).'"', false);

        $this->screen('presidente', route('president.evidence.list', ['estado' => 'atrasadas']))
            ->assertSee('Atención en el registro')
            ->assertSee('en cola 8 días')
            ->assertDontSee('Montaje del photocall')
            ->assertDontSee('Notas de prensa');

        $this->screen('profesor', route('lecture.evidence.list', ['desde' => now()->subDays(2)->format('Y-m-d')]))
            ->assertSee('Notas de prensa')
            ->assertDontSee('Atención en el registro');

        // a malformed filter is ignored
        $this->screen('profesor', route('lecture.evidence.list', ['comite' => 'x', 'desde' => '31/12/2026']))
            ->assertSee('Notas de prensa')
            ->assertSee('Atención en el registro');
    }

    public function test_the_meetings_filter_by_committee_and_minutes(): void
    {
        $logistics = $this->committee('Logística');
        $communication = $this->committee('Comunicación');

        $this->screen('profesor', route('lecture.meeting.list', ['comite' => $logistics->id, 'acta' => 'con']))
            ->assertSee('Reunión de logística')
            ->assertSee('Filtros de reuniones');
        $this->screen('presidente', route('president.meeting.list', ['comite' => $communication->id]))
            ->assertDontSee('Reunión de logística')
            ->assertSee('No hay reuniones');
        $this->screen('presidente', route('president.meeting.list', ['acta' => 'sin']))
            ->assertDontSee('Reunión de logística');
    }

    public function test_a_nudge_opens_the_message_already_written(): void
    {
        $logistics = $this->committee('Logística');

        $this->screen('presidente', route('messages.create', [
            'to' => 'COORDINATOR:'.$logistics->id,
            'subject' => 'Evidencias pendientes de revisar en Logística',
            'body' => 'Hay evidencias esperando revisión.',
        ]))
            ->assertSee('value="Evidencias pendientes de revisar en Logística"', false)
            ->assertSee('Hay evidencias esperando revisión.</textarea>', false)
            ->assertSee('value="COORDINATOR:'.$logistics->id.'" selected', false);
    }
}
