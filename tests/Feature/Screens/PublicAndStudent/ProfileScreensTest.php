<?php

namespace Tests\Feature\Screens\PublicAndStudent;

use App\Models\Evidence;

class ProfileScreensTest extends ScreenTestCase
{
    public function test_my_profile_shows_the_summary_and_the_three_forms(): void
    {
        $html = $this->assertAppScreen($this->signedAs('estudiante')->get(route('profile.view')));

        $this->assertStringContainsString('Lucía Pérez Romero', $html);
        $this->assertStringContainsString('Horas computadas', $html);
        $this->assertStringContainsString('Sin resumen del trabajo', $html);
        foreach (['profile.upload.info', 'profile.upload.biography', 'profile.upload.pass'] as $route) {
            $this->assertStringContainsString('action="'.route($route).'"', $html);
        }
        foreach (['name', 'surname', 'email', 'biography', 'current_password', 'password', 'password_confirmation'] as $name) {
            $this->assertStringContainsString('name="'.$name.'"', $html);
        }
        $this->assertNoAutofocus($html);
    }

    public function test_the_lecturer_has_no_summary_of_work_to_write(): void
    {
        $html = $this->assertAppScreen($this->signedAs('profesor')->get(route('profile.view')));

        $this->assertStringNotContainsString('action="'.route('profile.upload.biography').'"', $html);
        $this->assertStringContainsString('action="'.route('profile.upload.pass').'"', $html);
    }

    public function test_the_lecturer_computes_no_hours_and_the_president_does(): void
    {
        $html = $this->assertAppScreen($this->signedAs('profesor')->get(route('profile.view')));

        $this->assertStringContainsString('David Romero Organvídez', $html);
        $this->assertStringNotContainsString('Horas computadas', $html);
        $this->assertStringNotContainsString('Resumen del trabajo', $html);

        $html = $this->assertAppScreen($this->signedAs('presidente')->get(route('profile.view')));

        $this->assertStringContainsString('Horas computadas', $html);
        $this->assertStringContainsString('Resumen del trabajo', $html);
    }

    public function test_the_biography_reaches_the_textarea_as_text_and_the_summary_as_paragraphs(): void
    {
        $student = $this->person('estudiante');
        $student->update(['biography' => '<p>Montaje del <b>photocall</b>.</p><p>Registro.</p>']);

        $html = $this->assertAppScreen($this->signedAs('estudiante')->get(route('profile.view')));

        $this->assertStringContainsString("Montaje del photocall.\n\nRegistro.</textarea>", $html);
        $this->assertStringContainsString('<p>Montaje del <b>photocall</b>.</p><p>Registro.</p>', $html);
        $this->assertStringNotContainsString('Nivel de implicación', $html, 'The participation level no longer exists');
    }

    public function test_the_management_reads_the_profile_and_the_evidences_of_a_student(): void
    {
        $student = $this->person('estudiante');
        $accepted = Evidence::where('title', 'Cartelería del vestíbulo')->sole();
        $draft = Evidence::where('title', 'Borrador de la memoria')->sole();

        foreach (['presidente' => 'president', 'profesor' => 'lecture'] as $who => $prefix) {
            $html = $this->assertAppScreen($this->signedAs($who)->get(route('profiles.view', ['id' => $student->id])));

            $this->assertStringContainsString(route('profiles.view.evidence', ['id_user' => $student->id, 'id_evidence' => $accepted->id]), $html);
            $this->assertStringNotContainsString('Borrador de la memoria', $html);
            $this->assertStringContainsString(route("$prefix.user.list"), $html);

            $html = $this->assertAppScreen($this->get(route('profiles.view.evidence', ['id_user' => $student->id, 'id_evidence' => $accepted->id])));

            $this->assertStringContainsString('Cartelería del vestíbulo durante las jornadas.', $html);
            $this->assertStringContainsString('Integridad', $html);
            $this->assertStringContainsString(route('profiles.view', ['id' => $student->id]), $html);
        }

        $this->get(route('profiles.view.evidence', ['id_user' => $student->id, 'id_evidence' => $draft->id]))->assertRedirect(route('home'));
    }
}
