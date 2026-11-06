<?php

namespace Tests\Feature\Screens\Coordinator;

use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\Evidence;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Screens\Management\ScreenTestCase;

/**
 * «Mi comité»: only the coordinator of a committee opens it, with the team, the figures, the members, the meetings and
 * the activity of their committee and nothing of another one; the export is a CSV that leaves its annotation.
 */
class CommitteeScreenTest extends ScreenTestCase
{
    public function test_the_coordinator_sees_their_committee(): void
    {
        $response = $this->screen('coordinador', route('coordinator.committee'));

        $response->assertSee('Mi comité')
            ->assertSee('Logística')
            ->assertSeeInOrder(['Equipo de Logística', 'Carlos', 'Coordinación', 'Marta', 'Secretaría'])
            ->assertSeeInOrder(['Pendientes', '2', 'la más antigua es de hoy · 1 en presidencia'])
            ->assertSeeInOrder(['Horas aceptadas', '2,00', '1 evidencia aceptada · 9,00 h acreditadas'])
            ->assertSeeInOrder(['Rechazadas sin corregir', '1'])
            ->assertSeeInOrder(['Miembros activos', '4'])
            ->assertSee('href="'.route('coordinator.evidence.list.pending').'"', false)
            ->assertSee('href="'.route('coordinator.committee.export').'"', false)
            ->assertSee('Revisar')
            ->assertSee('Exportar');
    }

    public function test_the_members_table_lists_each_member(): void
    {
        $response = $this->screen('coordinador', route('coordinator.committee'));

        $response->assertSee('Miembros de Logística')
            ->assertSeeInOrder(['Castro Vidal, Inés', '1 pendiente', '0,00'])
            ->assertSeeInOrder(['García López, Juan', '1 pendiente', '0,00'])
            ->assertSeeInOrder(['Pérez Romero, Lucía', '1 aceptada', '1 pendiente', '1 rechazada', '7,00'])
            ->assertSeeInOrder(['Ruiz Díaz, Marta', 'Sin evidencias', '2,00'])
            ->assertSee('4 miembros')
            ->assertDontSee('Navarro Ruiz, Carlos');
    }

    public function test_the_meetings_link_their_documents(): void
    {
        $request = MeetingRequest::where('title', 'Convocatoria de logística')->sole();
        $minutes = MeetingMinutes::sole();

        $this->screen('coordinador', route('coordinator.committee'))
            ->assertSee('Próximas convocatorias')
            ->assertSee('href="'.route('download.request', ['id' => $request->id]).'"', false)
            ->assertSee('href="'.route('download.minutes', ['id' => $minutes->id]).'"', false)
            ->assertSee('Reunión de logística');
    }

    public function test_the_activity_shows_the_committee_annotations(): void
    {
        $logistics = Committee::where('name', 'Logística')->sole();
        $communication = Committee::where('name', 'Comunicación')->sole();
        $coordinator = $this->person('coordinador');
        $base = ['created_at' => now(), 'user_id' => $coordinator->id, 'acting_role' => 'COORDINATOR'];
        ActivityLog::create($base + ['action' => 'evidence.accepted', 'committee_id' => $logistics->id, 'committee_name' => 'Logística']);
        ActivityLog::create($base + ['action' => 'evidence.moved', 'committee_id' => $communication->id, 'committee_name' => 'Comunicación']);

        $this->screen('coordinador', route('coordinator.committee'))
            ->assertSee('Últimas acciones del comité')
            ->assertSee('Evidencia aceptada')
            ->assertDontSee('Evidencia movida de comité');
    }

    public function test_nothing_of_another_committee_appears(): void
    {
        $communication = Committee::where('name', 'Comunicación')->sole();
        Evidence::create([
            'title' => 'Notas de prensa', 'description' => '<p>Notas.</p>', 'hours' => 5, 'status' => 'PENDING',
            'user_id' => $this->person('registro')->id, 'committee_id' => $communication->id,
        ]);
        MeetingRequest::forceCreate([
            'title' => 'Convocatoria de comunicación', 'place' => 'Aula B1', 'datetime' => now()->addDays(2),
            'type' => '1', 'modality' => '1', 'committee_id' => $communication->id,
        ]);

        $this->screen('coordinador', route('coordinator.committee'))
            ->assertDontSee('Torres Gómez, Pablo')
            ->assertDontSee('Convocatoria de comunicación')
            ->assertSeeInOrder(['Miembros activos', '4']);
    }

    public function test_an_empty_committee_says_so(): void
    {
        // Only the annotations of this test: the end-to-end seed also leaves a history of its own
        DB::table('activity_log_targets')->delete();
        DB::table('activity_log')->delete();
        $communication = Committee::where('name', 'Comunicación')->sole();
        $this->person('coordinador')->coordinator()->update(['committee_id' => $communication->id]);

        $this->screen('coordinador', route('coordinator.committee'))
            ->assertSee('Comunicación')
            ->assertSee('Sin secretaría')
            ->assertSee('Sin miembros todavía')
            ->assertSee('Sin horas acreditadas')
            ->assertSee('Sin convocatorias próximas')
            ->assertSee('Sin actas')
            ->assertSee('Sin anotaciones')
            ->assertSee('cola vacía');
    }

    public function test_only_a_coordinator_with_a_committee_opens_it(): void
    {
        foreach (['estudiante', 'secretario', 'registro', 'presidente', 'profesor'] as $who) {
            $this->actingAs($this->person($who))->get(route('coordinator.committee'))->assertNotFound();
            $this->actingAs($this->person($who))->get(route('coordinator.committee.export'))->assertNotFound();
        }

        $lonely = User::factory()->create();
        $lonely->roles()->attach(Role::where('rol', 'COORDINATOR')->sole());
        $this->actingAs($lonely)->get(route('coordinator.committee'))->assertNotFound();
        $this->actingAs($lonely)->get(route('coordinator.committee.export'))->assertNotFound();

        auth()->logout();
        $this->get(route('coordinator.committee'))->assertRedirect(route('login'));
        $this->assertSame(0, ActivityLog::where('action', 'committee.exported')->count());
    }

    public function test_the_export_is_a_csv_of_the_members_and_leaves_its_annotation(): void
    {
        $response = $this->actingAs($this->person('coordinador'))->get(route('coordinator.committee.export'));

        $response->assertOk();
        $this->assertSame('text/csv; charset=UTF-8', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename="comite-logistica-'.now()->format('Y-m-d').'.csv"', $response->headers->get('Content-Disposition'));
        $csv = $response->getContent();
        $this->assertStringStartsWith("\u{FEFF}nombre;apellidos;uvus;correo;", $csv);
        $this->assertSame(5, substr_count($csv, "\n"));
        $this->assertStringContainsString('Lucía;"Pérez Romero";estudiante;estudiante@e2e.test;1;1;1;7,00;1', $csv);

        $log = ActivityLog::where('action', 'committee.exported')->sole();
        $this->assertSame('COORDINATOR', $log->acting_role);
        $this->assertSame($this->person('coordinador')->id, $log->user_id);
        $this->assertSame(Committee::where('name', 'Logística')->value('id'), $log->committee_id);
        $this->assertSame(['rows' => 4], $log->details);
    }
}
