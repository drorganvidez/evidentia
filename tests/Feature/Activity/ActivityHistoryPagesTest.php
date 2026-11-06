<?php

namespace Tests\Feature\Activity;

use App\Enums\ActivityAction;
use App\Facades\Activity;
use App\Models\Evidence;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Screens\Management\ScreenTestCase;

/** The history pages of an evidence, a minutes and a meeting request, and what each person sees and does there. */
class ActivityHistoryPagesTest extends ScreenTestCase
{
    private function evidence(string $title): Evidence
    {
        return Evidence::where('title', $title)->sole();
    }

    public function test_the_author_sees_the_whole_story_of_a_rejected_evidence(): void
    {
        $rejected = $this->evidence('Recogida de material');

        $html = $this->screen('estudiante', route('activity.evidence', ['id' => $rejected->id]))
            ->assertSee('Recogida de material')
            ->assertSee('Faltan las fotos del montaje terminado.')
            ->assertSee('Motivo del rechazo')
            ->assertSee('Evidencia rechazada')
            ->assertSee('Escribir al coordinador')
            ->assertSee('Cambiar de comité')
            ->assertSee('Corregir y volver a enviar')
            ->assertSee(route('evidence.view', ['id' => $rejected->id]), false)
            ->assertSee(route('messages.create', ['evidence' => $rejected->id]), false)
            ->assertDontSee('Aceptar o rechazar')
            ->assertDontSee('data-open', false)
            ->getContent();
        $this->assertNoAutofocus($html);

        $this->screen('estudiante', route('activity.evidence', ['id' => $rejected->id, 'move' => 1]))->assertSee('data-open', false);
    }

    public function test_the_coordinator_moves_a_pending_evidence_of_another_student(): void
    {
        $pending = $this->evidence('Atención en el registro');

        $this->screen('coordinador', route('activity.evidence', ['id' => $pending->id]))
            ->assertSee('Mover a otro comité')
            ->assertSee('action="'.route('coordinator.evidence.move').'"', false)
            ->assertSee('Comunicación')
            ->assertSee('Autorrevisión bloqueada')
            ->assertSee(route('coordinator.evidence.view', ['id' => $pending->id]), false)
            ->assertDontSee('Escribir al coordinador')
            ->assertDontSee('Aceptar o rechazar');
    }

    public function test_the_management_moves_and_reviews(): void
    {
        $pending = $this->evidence('Montaje del photocall');

        $this->screen('profesor', route('activity.evidence', ['id' => $pending->id]))
            ->assertSee('Mover a otro comité')
            ->assertSee('Aceptar o rechazar')
            ->assertSee('action="'.route('management.evidence.move').'"', false)
            ->assertSee('action="'.route('management.evidence.review').'"', false)
            ->assertSee('Duda sobre el photocall');
    }

    public function test_drafts_are_only_for_their_author(): void
    {
        $draft = $this->evidence('Borrador de la memoria');

        $this->screen('estudiante', route('activity.evidence', ['id' => $draft->id]))->assertSee('Cambiar de comité');
        foreach (['coordinador', 'secretario', 'profesor', 'companero'] as $who) {
            $this->actingAs($this->person($who))->get(route('activity.evidence', ['id' => $draft->id]))->assertNotFound();
        }
        $this->actingAs($this->person('companero'))->get(route('activity.evidence', ['id' => $this->evidence('Montaje del photocall')->id]))->assertNotFound();
    }

    public function test_the_committee_does_not_see_the_draft_annotations_nor_the_notices_of_others(): void
    {
        // Only the annotations of this test: the end-to-end seed also leaves a history of its own
        DB::table('activity_log_targets')->delete();
        DB::table('activity_log')->delete();
        $evidence = $this->evidence('Montaje del photocall');
        $student = $this->person('estudiante');
        Activity::record(ActivityAction::EvidenceCreated, $evidence, actor: $student, actingRole: 'STUDENT', committeeId: false);
        Activity::record(ActivityAction::EvidencePublished, $evidence, actor: $student, actingRole: 'STUDENT');
        Activity::system(ActivityAction::NotificationSent, $evidence, $this->person('coordinador'), details: ['title' => 'Evidencia recibida']);

        $this->screen('secretario', route('activity.evidence', ['id' => $evidence->id]))
            ->assertSee('Evidencia enviada')
            ->assertDontSee('Evidencia creada')
            ->assertDontSee('Aviso enviado')
            ->assertDontSee('Mover a otro comité');

        $this->screen('estudiante', route('activity.evidence', ['id' => $evidence->id]))
            ->assertSee('Evidencia creada')
            ->assertSee('Aviso enviado');
    }

    public function test_every_version_shares_the_history_of_the_last_one(): void
    {
        $first = $this->evidence('Montaje del photocall');
        $first->forceFill(['last' => false])->save();
        $second = Evidence::forceCreate([
            'title' => 'Montaje del photocall v2', 'description' => '<p>v2</p>', 'hours' => 2, 'status' => 'PENDING',
            'user_id' => $first->user_id, 'committee_id' => $first->committee_id, 'points_to' => $first->id, 'last' => true,
        ]);

        $this->screen('estudiante', route('activity.evidence', ['id' => $first->id]))
            ->assertSee('Montaje del photocall v2')
            ->assertSee('Versión 2')
            ->assertSee('Versión 1')
            ->assertSee('Evidencia enviada');
        $this->assertSame($second->id, $first->headEvidence()->id);
    }

    public function test_minutes_are_open_to_everybody_and_their_history_to_who_took_part(): void
    {
        // Only the annotations of this test: the end-to-end seed also leaves a history of its own
        DB::table('activity_log_targets')->delete();
        DB::table('activity_log')->delete();
        $minutes = MeetingMinutes::sole();

        $this->screen('secretario', route('activity.minutes', ['id' => $minutes->id]))
            ->assertSee('Reunión de logística')
            ->assertSee('Editar')
            ->assertSee('Cada equipo nombra un responsable.')
            ->assertSee('ISD-1')
            ->assertSee('Asistentes')
            ->assertSee('Historial')
            ->assertDontSee('Escribir al secretario');

        $this->screen('estudiante', route('activity.minutes', ['id' => $minutes->id, 'tab' => 'historial']))
            ->assertSee('Sin anotaciones')
            ->assertDontSee('Puntos y acuerdos')
            ->assertDontSee('Editar');

        // without the history, the tab falls back to the detail
        $this->screen('companero', route('activity.minutes', ['id' => $minutes->id, 'tab' => 'historial']))
            ->assertSee('Puntos y acuerdos')
            ->assertSee('Escribir al secretario')
            ->assertDontSee('Secciones del acta');
    }

    public function test_meeting_requests_name_who_signed_only_to_their_secretary(): void
    {
        $request = MeetingRequest::sole();

        $this->screen('secretario', route('activity.request', ['id' => $request->id]))
            ->assertSee('Convocatoria de logística')
            ->assertSee('Reparto de tareas')
            ->assertSee('Firmas')
            ->assertSee('Lucía')
            ->assertSee('Hoja de firmas')
            ->assertSee('Programada');

        $this->screen('secretario', route('activity.request', ['id' => $request->id, 'tab' => 'historial']))
            ->assertSee('Convocatoria creada')
            ->assertSee('Hoja de firmas creada');

        $this->screen('companero', route('activity.request', ['id' => $request->id]))
            ->assertSee('Reparto de tareas')
            ->assertSee('Escribir al secretario')
            ->assertDontSee('Lucía')
            ->assertDontSee('Secciones de la convocatoria');
    }
}
