<?php

namespace Tests\Feature\Activity;

use App\Enums\ActivityAction;
use App\Facades\Activity;
use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Screens\Management\ScreenTestCase;

/** The activity log by role and committee, its filters and grouping, and the activity of each person. */
class ActivityPagesTest extends ScreenTestCase
{
    public function test_the_management_sees_the_whole_log_without_accesses(): void
    {
        $this->login('estudiante');

        // 10 per page by default: the whole seed fits in one page of 50
        $response = $this->screen('profesor', route('activity.index', ['per_page' => 50]))
            ->assertSee('Registro de actividad')
            ->assertSee('Descargar CSV');
        $this->assertNoAutofocus($response->getContent());
        $this->assertRows($response, ['Comité editado', 'Evidencia rechazada'], ['Inicio de sesión']);

        $this->assertRows($this->screen('profesor', route('activity.index', ['all' => 1])), ['Inicio de sesión']);
    }

    public function test_a_coordinator_only_sees_the_committee(): void
    {
        $communication = Committee::where('name', 'Comunicación')->sole();

        $response = $this->screen('coordinador', route('activity.index'))
            ->assertSee('Actividad de Logística')
            ->assertDontSee('Descargar CSV');
        $this->assertMatchesRegularExpression('/<select[^>]*name="committee"[^>]*disabled/s', $response->getContent());
        $this->assertRows($response, ['Evidencia rechazada'], ['Comité editado']);

        // the committee of the query string is ignored out of the scope
        $this->assertRows($this->screen('coordinador', route('activity.index', ['committee' => $communication->id])), ['Evidencia rechazada'], ['Comité editado']);
    }

    public function test_students_and_the_register_have_no_log(): void
    {
        foreach (['estudiante', 'registro'] as $who) {
            $this->actingAs($this->person($who))->get(route('activity.index'))->assertNotFound();
        }
    }

    public function test_the_filters_narrow_the_log(): void
    {
        $this->assertRows($this->screen('profesor', route('activity.index', ['type' => 'evidence.rejected'])), ['Recogida de material'], ['Comité editado']);
        $this->assertRows($this->screen('profesor', route('activity.index', ['type' => 'group:management'])), ['Comité editado'], ['Evidencia rechazada']);
        $this->assertRows($this->screen('profesor', route('activity.index', ['role' => 'COORDINATOR'])), ['Evidencia rechazada'], ['Comité editado']);

        $flagged = $this->screen('profesor', route('activity.index', ['flagged' => 1]))->assertSee('Señalada, Autorrevisión', false);
        $this->assertRows($flagged, ['Autorrevisión bloqueada'], ['Comité editado']);
        $this->assertStringContainsString('1 anotación', $flagged->getContent());

        // invalid values are ignored, never an error
        $this->assertRows($this->screen('profesor', route('activity.index', ['type' => 'nada', 'from' => '2026-02-31', 'role' => 'NADIE', 'per_page' => 50])), ['Comité editado']);
    }

    public function test_one_action_groups_its_annotations(): void
    {
        $request = ActivityLog::where('action', 'sheet.created')->sole()->request_id;

        $this->screen('profesor', route('activity.index', ['per_page' => 50]))
            ->assertSee('+1 en la misma acción')
            ->assertSee(route('activity.index', ['request' => $request]), false);

        $grouped = $this->screen('profesor', route('activity.index', ['request' => $request]))->assertSee('Volver al registro');
        $this->assertRows($grouped, ['Convocatoria creada', 'Hoja de firmas creada'], ['Evidencia rechazada']);
    }

    public function test_the_log_is_paginated(): void
    {
        $lecturer = $this->person('profesor');
        foreach (range(1, 55) as $n) {
            Activity::record(ActivityAction::ConfigChanged, actor: $lecturer, actingRole: 'LECTURE');
        }

        $this->screen('profesor', route('activity.index'))
            ->assertSee('<nav aria-label="Paginación del registro"', false)
            ->assertSee('aria-label="Filas por página"', false);
        $this->assertRows($this->screen('profesor', route('activity.index', ['page' => 2, 'per_page' => 50])), ['Comité editado']);
        // a page past the last shows the last; the size of the query string stays in the filters
        $this->assertRows($this->screen('profesor', route('activity.index', ['page' => 40, 'per_page' => 50])), ['Comité editado']);
        $smaller = $this->screen('profesor', route('activity.index', ['per_page' => 10]))
            ->assertSee('<nav aria-label="Paginación del registro"', false)
            ->assertSee('<input type="hidden" name="per_page" value="10">', false)
            ->getContent();
        $this->assertMatchesRegularExpression('/per_page=10"\s+aria-current="true"/', $smaller);
        // 100 is no longer a size: the log keeps its own 10
        $this->assertMatchesRegularExpression('/per_page=10"\s+aria-current="true"/', $this->screen('profesor', route('activity.index', ['per_page' => 100]))->getContent());
    }

    public function test_each_person_sees_their_own_activity(): void
    {
        $this->login('estudiante');
        $student = $this->person('estudiante');
        // an annotation of the system about somebody else never shows up
        Activity::system(ActivityAction::NotificationFailed, target: $this->person('companero'), subjectTitle: 'Ajeno de Juan');
        Activity::system(ActivityAction::LoginFailed, target: $student, details: ['context' => 'login']);

        $this->screen('estudiante', route('activity.mine'))
            ->assertSee('Mi actividad')
            ->assertSee('Lo que he hecho')
            ->assertSee('Evidencia enviada')
            ->assertSee('Evidencia rechazada')
            ->assertDontSee('Ajeno de Juan');

        $this->screen('estudiante', route('activity.mine', ['scope' => 'about']))
            ->assertSee('Evidencia rechazada')
            ->assertDontSee('Evidencia enviada');

        $this->screen('estudiante', route('activity.mine', ['scope' => 'own']))
            ->assertSee('Evidencia enviada')
            ->assertDontSee('Evidencia rechazada');

        $this->screen('estudiante', route('activity.mine', ['scope' => 'access']))
            ->assertSee('Inicio de sesión')
            ->assertSee('Acceso fallido')
            ->assertSee('Desde 127.0.0.1')
            ->assertDontSee('Evidencia enviada');

        $this->screen('registro', route('activity.mine'))->assertSee('Todavía no hay actividad');
    }

    /** What the rows of the table say (the filters list every verb, so the page as a whole cannot tell). */
    private function assertRows(TestResponse $response, array $present, array $absent = []): void
    {
        $html = $response->getContent();
        $start = strpos($html, '<tbody');
        $table = $start === false ? '' : substr($html, $start, strpos($html, '</tbody>', $start) - $start);
        foreach ($present as $text) {
            $this->assertStringContainsString($text, $table);
        }
        foreach ($absent as $text) {
            $this->assertStringNotContainsString($text, $table);
        }
    }

    private function login(string $who): void
    {
        Activity::record(ActivityAction::Login, $this->person($who), actor: User::where('username', $who)->sole(), details: ['via' => 'form']);
    }
}
