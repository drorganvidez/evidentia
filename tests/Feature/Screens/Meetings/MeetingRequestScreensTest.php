<?php

namespace Tests\Feature\Screens\Meetings;

use App\Models\MeetingRequest;

class MeetingRequestScreensTest extends MeetingScreenTestCase
{
    public function test_the_list_links_each_call_to_its_edition_pdf_and_removal(): void
    {
        $request = MeetingRequest::sole();

        $html = $this->assertScreen($this->as('secretario')->get(route('secretary.meeting.manage.request.list')));

        $this->assertStringContainsString('Convocatoria de logística', $html);
        $this->assertStringContainsString(route('secretary.meeting.manage.request.edit', ['id' => $request->id]), $html);
        $this->assertStringContainsString(route('secretary.meeting.manage.request.download', ['id' => $request->id]), $html);
        $this->assertStringContainsString('action="'.route('secretary.meeting.manage.request.remove').'"', $html);
        $this->assertStringContainsString('role="alertdialog"', $html);
    }

    public function test_the_new_call_has_the_agenda_editor_and_creates_its_signature_sheet_by_default(): void
    {
        $html = $this->assertScreen($this->as('secretario')->get(route('secretary.meeting.manage.request.create')));

        $this->assertStringContainsString('x-data="agendaEditor"', $html);
        $this->assertMatchesRegularExpression('/<input type="hidden" name="points_list"/', $html);
        $this->assertMatchesRegularExpression('/name="create_signature_sheet"[^>]*value="on"[^>]*checked/s', $html);
        $this->assertMatchesRegularExpression('/name="type"[^>]*value="1"[^>]*checked/s', $html);
        $this->assertStringNotContainsString('name="_id"', $html);
    }

    public function test_the_edition_loads_the_call_and_its_agenda(): void
    {
        $request = MeetingRequest::sole();

        $html = $this->assertScreen($this->as('secretario')->get(route('secretary.meeting.manage.request.edit', ['id' => $request->id])));

        $this->assertStringContainsString('name="_id" value="'.$request->id.'"', $html);
        $this->assertStringContainsString('value="Convocatoria de logística"', $html);
        $this->assertStringContainsString('Reparto de tareas', $html);
        $this->assertStringNotContainsString('name="create_signature_sheet"', $html);
    }

    public function test_an_invalid_call_comes_back_with_its_agenda(): void
    {
        $this->as('secretario')
            ->from(route('secretary.meeting.manage.request.create'))
            ->followingRedirects()
            ->post(route('secretary.meeting.manage.request.new'), [
                'title' => 'Mal', 'points_list' => json_encode(['Bienvenida', 'Turno de preguntas']),
            ])
            ->assertOk()
            ->assertSee('Hay errores en el formulario')
            ->assertSee('Bienvenida')
            ->assertSee('Turno de preguntas');
    }
}
