<?php

namespace Tests\Feature\Screens\PublicAndStudent;

use App\Models\Evidence;

class EvidenceScreensTest extends ScreenTestCase
{
    private function evidence(string $title): Evidence
    {
        return Evidence::where('title', $title)->sole();
    }

    public function test_the_list_shows_every_evidence_of_the_student_with_its_actions(): void
    {
        $html = $this->assertAppScreen($this->signedAs('estudiante')->get(route('evidence.list')));

        foreach (['Montaje del photocall', 'Cartelería del vestíbulo', 'Recogida de material', 'Borrador de la memoria'] as $title) {
            $this->assertStringContainsString(route('evidence.view', ['id' => $this->evidence($title)->id]), $html);
        }
        $this->assertStringNotContainsString('Atención en el registro', $html);
        $this->assertStringContainsString('href="'.route('evidence.edit', ['id' => $this->evidence('Borrador de la memoria')->id]).'"', $html);
        $this->assertStringContainsString('id="reedit-'.$this->evidence('Recogida de material')->id.'"', $html);
        $this->assertSame(4, substr_count($html, 'action="'.route('evidence.remove').'"'));
        $this->assertStringContainsString('title="Faltan las fotos del montaje terminado."', $html);
    }

    public function test_the_detail_of_a_rejection_shows_the_reason_the_proofs_and_the_history(): void
    {
        $rejected = $this->evidence('Recogida de material');

        $html = $this->assertAppScreen($this->signedAs('estudiante')->get(route('evidence.view', ['id' => $rejected->id])));

        $this->assertStringContainsString('Motivo del rechazo', $html);
        $this->assertStringContainsString('Faltan las fotos del montaje terminado.', $html);
        $this->assertStringContainsString('aria-label="Recorrido de la evidencia"', $html);
        $this->assertStringContainsString('No hay pruebas adjuntas', $html);
        $this->assertStringContainsString('action="'.route('evidence.reedit').'"', $html);
        $this->assertStringNotContainsString(route('coordinator.evidence.accept', ['id' => $rejected->id]), $html);
    }

    public function test_the_coordinator_reviews_the_evidences_of_others_but_not_their_own(): void
    {
        $pending = $this->evidence('Montaje del photocall');
        $html = $this->assertAppScreen($this->signedAs('coordinador')->get(route('coordinator.evidence.view', ['id' => $pending->id])));

        $this->assertStringContainsString('action="'.route('coordinator.evidence.accept', ['id' => $pending->id]).'"', $html);
        $this->assertStringContainsString('action="'.route('coordinator.evidence.reject').'"', $html);
        $this->assertStringContainsString('name="reasonrejection"', $html);
        $this->assertStringNotContainsString('action="'.route('evidence.remove').'"', $html);

        $own = Evidence::create([
            'title' => 'Coordinación del montaje', 'description' => 'Reparto de los equipos.', 'hours' => 1,
            'status' => 'PENDING', 'user_id' => $this->person('coordinador')->id, 'committee_id' => $pending->committee_id,
        ]);
        $html = $this->assertAppScreen($this->get(route('coordinator.evidence.view', ['id' => $own->id])));

        $this->assertStringNotContainsString(route('coordinator.evidence.accept', ['id' => $own->id]), $html);
        $this->assertStringNotContainsString('name="reasonrejection"', $html);
    }

    public function test_the_form_creates_with_the_same_fields_and_both_actions(): void
    {
        $html = $this->assertAppScreen($this->signedAs('estudiante')->get(route('evidence.create')));

        $this->assertStringContainsString('action="'.route('evidence.draft').'"', $html);
        $this->assertStringContainsString('formaction="'.route('evidence.publish').'"', $html);
        foreach (['title', 'hours', 'minutes', 'committee', 'description', 'files[]'] as $name) {
            $this->assertStringContainsString('name="'.$name.'"', $html);
        }
        $this->assertStringNotContainsString('name="_id"', $html);
        $this->assertNoAutofocus(preg_replace('/<dialog.*?<\/dialog>/s', '', $html));
    }

    public function test_the_edit_of_a_draft_carries_its_id_its_text_and_its_versions(): void
    {
        $draft = $this->evidence('Borrador de la memoria');

        $html = $this->assertAppScreen($this->signedAs('estudiante')->get(route('evidence.edit', ['id' => $draft->id])));

        $this->assertStringContainsString('action="'.route('evidence.draft.edit').'"', $html);
        $this->assertStringContainsString('formaction="'.route('evidence.publish.edit').'"', $html);
        $this->assertStringContainsString('name="_id" value="'.$draft->id.'"', $html);
        $this->assertStringContainsString('Borrador de la memoria durante las jornadas.</textarea>', $html);
        $this->assertStringContainsString('Editando ahora', $html);
    }

    public function test_an_edit_saves_the_plain_text_and_the_detail_shows_its_paragraphs(): void
    {
        $draft = $this->evidence('Borrador de la memoria');
        $this->signedAs('estudiante');

        $this->post(route('evidence.draft.edit'), [
            '_id' => $draft->id, 'title' => 'Borrador de la memoria', 'hours' => 1, 'minutes' => 30,
            'committee' => $draft->committee_id, 'description' => "Primer párrafo.\nSegunda línea.\n\nSegundo párrafo.",
        ])->assertRedirect(route('evidence.list'));

        $latest = Evidence::where('title', 'Borrador de la memoria')->where('last', true)->sole();
        $html = $this->get(route('evidence.view', ['id' => $latest->id]))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<p>Primer párrafo\.<br ?/?>\s*Segunda línea\.</p><p>Segundo párrafo\.</p>#u', $html);
    }
}
