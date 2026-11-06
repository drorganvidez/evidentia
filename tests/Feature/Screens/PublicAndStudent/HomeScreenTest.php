<?php

namespace Tests\Feature\Screens\PublicAndStudent;

use App\Models\User;

class HomeScreenTest extends ScreenTestCase
{
    public function test_the_student_sees_their_hours_their_latest_evidences_and_the_deadlines(): void
    {
        $html = $this->assertAppScreen($this->signedAs('estudiante')->get(route('home')));

        $this->assertStringContainsString('Hola, Lucía', $html);
        $this->assertStringContainsString('Horas computadas', $html);
        $this->assertStringContainsString('Mis últimas evidencias', $html);
        $this->assertStringContainsString(route('evidence.view', ['id' => 1]), $html);
        $this->assertStringContainsString('href="'.route('evidence.create').'"', $html);
        $this->assertStringContainsString('Subida de evidencias', $html);
        $this->assertStringContainsString('Convocatoria de logística', $html);
        $this->assertStringContainsString('Taller de Git', $html);
        $this->assertStringNotContainsString('Requiere atención', $html);
        $this->assertStringNotContainsString('Por revisar en', $html);
    }

    public function test_the_coordinator_sees_the_queue_of_their_committee_without_their_own_evidences(): void
    {
        $html = $this->assertAppScreen($this->signedAs('coordinador')->get(route('home')));

        $this->assertStringContainsString('Por revisar en Logística', $html);
        $this->assertStringContainsString('Montaje del photocall', $html);
        $this->assertStringContainsString('Atención en el registro', $html);
        $this->assertStringContainsString(route('coordinator.evidence.list.pending'), $html);
    }

    public function test_the_president_and_the_lecturer_see_the_figures_and_the_attention_queue_of_the_course(): void
    {
        foreach (['presidente' => 'president', 'profesor' => 'lecture'] as $who => $prefix) {
            $html = $this->assertAppScreen($this->signedAs($who)->get(route('home')));

            $this->assertStringContainsString('Evidencias del curso', $html);
            $this->assertStringContainsString('Requiere atención', $html);
            $this->assertStringContainsString('Evidencias pendientes de revisión', $html);
            $this->assertStringContainsString('Comités sin coordinador', $html);
            $this->assertStringContainsString('Actividad reciente', $html);
            $this->assertStringContainsString(route("$prefix.evidence.list"), $html);
            $this->assertStringContainsString(route("$prefix.committee.list"), $html);
            $this->assertStringNotContainsString('Mis últimas evidencias', $html);
        }
    }

    public function test_a_person_without_roles_still_reaches_the_documents_of_each_committee(): void
    {
        $html = $this->assertAppScreen($this->actingAs(User::factory()->create())->get(route('home')));

        $this->assertStringContainsString('Convocatorias y actas', $html);
        $this->assertStringNotContainsString('Horas computadas', $html);
    }
}
