<?php

namespace Tests\Feature\Messages;

use App\Models\Committee;
use App\Models\Evidence;
use App\Models\MeetingRequest;
use App\Models\Message;
use App\Models\MessageThread;
use App\Services\Messaging\Audience;
use App\Services\Messaging\Messaging;
use Tests\Feature\Screens\Management\ScreenTestCase;

/** The inbox, «Mis mensajes», the form of a new message and the thread, by role. */
class MessagesPagesTest extends ScreenTestCase
{
    private function thread(string $subject): MessageThread
    {
        return MessageThread::where('subject', $subject)->sole();
    }

    public function test_the_coordinator_reads_the_threads_of_the_committee(): void
    {
        $html = $this->screen('coordinador', route('messages.inbox'))
            ->assertSee('Mensajes')
            ->assertSee('Cambio de turno en el registro')
            ->assertSee('Duda sobre el photocall')
            ->assertSee('Montaje del photocall')
            ->assertSee('Coordinación de Logística')
            ->assertSee('Sin leer.')
            ->assertSee('1 sin leer')
            ->assertDontSee('Propuesta para el cierre')
            ->getContent();
        $this->assertNoAutofocus($html);

        $this->screen('coordinador', route('messages.inbox', ['state' => 'resolved']))->assertSee('No hay mensajes');
    }

    public function test_the_search_never_widens_the_inbox(): void
    {
        $this->screen('coordinador', route('messages.inbox', ['q' => 'Juan', 'state' => 'all']))
            ->assertSee('Cambio de turno en el registro')
            ->assertDontSee('Propuesta para el cierre');
        $this->screen('coordinador', route('messages.inbox', ['q' => '%', 'state' => 'all']))->assertSee('No hay mensajes');
    }

    public function test_the_lecturers_can_read_every_thread(): void
    {
        $this->screen('profesor', route('messages.inbox'))
            ->assertSee('Todos los hilos')
            ->assertSee('Propuesta para el cierre')
            ->assertDontSee('Cambio de turno en el registro');

        $this->screen('profesor', route('messages.inbox', ['to' => 'all']))
            ->assertSee('Propuesta para el cierre')
            ->assertSee('Cambio de turno en el registro');
    }

    public function test_the_inbox_is_only_for_the_people_in_charge(): void
    {
        $this->actingAs($this->person('estudiante'))->get(route('messages.inbox'))->assertNotFound();
        $this->screen('registro', route('messages.inbox'))->assertSee('No hay mensajes');
    }

    public function test_each_person_sees_the_threads_they_wrote(): void
    {
        $this->screen('estudiante', route('messages.mine'))
            ->assertSee('Mis mensajes')
            ->assertSee('Duda sobre el photocall')
            ->assertSee('Sin leer.')
            ->assertDontSee('Cambio de turno en el registro');

        $this->screen('presidente', route('messages.mine'))
            ->assertSee('Todavía no has escrito ningún mensaje')
            ->assertSee(route('messages.create'), false);
    }

    public function test_the_threads_come_in_pages_that_keep_the_filters(): void
    {
        $student = $this->person('estudiante');
        $this->actingAs($student);
        foreach (range(1, 30) as $index) {
            app(Messaging::class)->open($student, new Audience('LECTURE'), sprintf('Consulta %02d', $index), 'Una consulta más para el profesorado.', null);
        }

        $mine = $this->screen('estudiante', route('messages.mine'))
            ->assertSee('<nav aria-label="Paginación de mis mensajes"', false)
            ->assertSee('De la 1 a la 25 de 31')
            ->assertSee('aria-label="Filas por página"', false)
            ->getContent();
        $this->assertStringContainsString('href="'.route('messages.mine', ['page' => 2]).'" rel="next"', $mine);

        $this->screen('estudiante', route('messages.mine', ['state' => 'open', 'page' => 9]))
            ->assertSee('De la 26 a la 31 de 31')
            ->assertSee('Duda sobre el photocall');

        $inbox = $this->screen('profesor', route('messages.inbox', ['q' => 'consulta', 'per_page' => 25]))
            ->assertSee('<nav aria-label="Paginación de los mensajes"', false)
            ->assertSee('De la 1 a la 25 de 30')
            ->getContent();
        $this->assertStringContainsString('href="'.e(route('messages.inbox', ['q' => 'consulta', 'per_page' => 25, 'page' => 2])).'" rel="next"', $inbox);
    }

    public function test_a_new_message_about_an_evidence_suggests_its_coordination(): void
    {
        $evidence = Evidence::where('title', 'Recogida de material')->sole();
        $logistics = Committee::where('name', 'Logística')->sole();

        $this->screen('estudiante', route('messages.create', ['evidence' => $evidence->id]))
            ->assertSee('Nuevo mensaje')
            ->assertSee('Sobre «Recogida de material»', false)
            ->assertSee('<option value="COORDINATOR:'.$logistics->id.'" selected', false)
            ->assertSee('name="about_type" value="evidence"', false)
            ->assertSee(route('activity.evidence', ['id' => $evidence->id]), false);

        // an evidence out of reach is not found
        $this->actingAs($this->person('companero'))->get(route('messages.create', ['evidence' => $evidence->id]))->assertNotFound();
        $this->actingAs($this->person('companero'))->post(route('messages.store'), [
            'recipient' => 'COORDINATOR:'.$logistics->id, 'subject' => 'Sobre otra', 'body' => 'Una evidencia ajena.',
            'about_type' => 'evidence', 'about_id' => $evidence->id,
        ])->assertNotFound();
    }

    public function test_a_new_message_about_a_meeting_request_suggests_its_secretary(): void
    {
        $request = MeetingRequest::sole();
        $logistics = Committee::where('name', 'Logística')->sole();

        $this->screen('companero', route('messages.create', ['request' => $request->id]))
            ->assertSee('<option value="SECRETARY:'.$logistics->id.'" selected', false);
    }

    public function test_the_form_validates_in_spanish_and_against_the_list(): void
    {
        $this->actingAs($this->person('estudiante'))->from(route('messages.create'))
            ->post(route('messages.store'), ['recipient' => 'LECTURE', 'subject' => '', 'body' => 'corto'])
            ->assertSessionHasErrors(['subject' => 'El campo asunto es obligatorio.', 'body' => 'El campo mensaje debe tener al menos 10 caracteres.']);

        $this->actingAs($this->person('estudiante'))
            ->post(route('messages.store'), ['recipient' => 'COORDINATOR:999', 'subject' => 'Hola a todos', 'body' => 'Un mensaje suficiente.'])
            ->assertSessionHasErrors(['recipient' => 'Elige un destinatario de la lista.']);
    }

    public function test_sending_opens_the_thread(): void
    {
        $this->actingAs($this->person('estudiante'))
            ->post(route('messages.store'), ['recipient' => 'LECTURE', 'subject' => 'Sugerencia', 'body' => 'Una sesión de dudas antes del cierre.'])
            ->assertSessionHas('success', 'Mensaje enviado.')
            ->assertRedirect(route('messages.show', $this->thread('Sugerencia')));
        $this->assertSame('LECTURE', $this->thread('Sugerencia')->recipient_role);
    }

    public function test_the_thread_marks_itself_read_and_shows_its_history(): void
    {
        $thread = $this->thread('Duda sobre el photocall');
        $student = $this->person('estudiante');
        $this->assertTrue($thread->isUnreadFor($student));

        $this->screen('estudiante', route('messages.show', $thread))
            ->assertSee('¿Cuento también el desmontaje del photocall o solo el montaje?')
            ->assertSee('Cuenta las dos cosas en la misma evidencia.')
            ->assertSee('como coordinador de Logística')
            ->assertSee('Responder')
            ->assertSee('Mensaje enviado')
            ->assertSee('Historial del mensaje')
            ->assertDontSee('Marcar como resuelto');

        $this->assertFalse($thread->fresh()->isUnreadFor($student));

        $this->screen('coordinador', route('messages.show', $thread))->assertSee('Marcar como resuelto');
        $this->actingAs($this->person('companero'))->get(route('messages.show', $thread))->assertForbidden();
        $this->screen('presidente', route('messages.show', $thread))
            ->assertSee('Solo lectura')
            ->assertSee('¿Cuento también el desmontaje del photocall o solo el montaje?')
            ->assertDontSee('Tu respuesta')
            ->assertDontSee('Marcar como resuelto')
            ->assertDontSee('action="'.route('messages.reopen', $thread).'"', false);
    }

    public function test_the_president_reads_every_thread_without_taking_part_in_those_of_others(): void
    {
        $thread = $this->thread('Cambio de turno en el registro');
        $president = $this->person('presidente');

        $this->screen('presidente', route('messages.inbox', ['to' => 'all', 'state' => 'all']))
            ->assertSee('Todos los hilos')
            ->assertSee('Cambio de turno en el registro');

        $this->actingAs($president)->post(route('messages.reply', $thread), ['body' => 'Me meto'])->assertForbidden();
        $this->actingAs($president)->post(route('messages.resolve', $thread))->assertForbidden();
        $thread->update(['state' => 'resolved']);
        $this->actingAs($president)->post(route('messages.reopen', $thread))->assertForbidden();
        $this->assertFalse(Message::where('message_thread_id', $thread->id)->where('body', 'Me meto')->exists());
    }

    public function test_reply_resolve_and_reopen(): void
    {
        $thread = $this->thread('Cambio de turno en el registro');
        $coordinator = $this->person('coordinador');

        $this->actingAs($coordinator)->post(route('messages.reply', $thread), ['body' => 'Hecho.'])
            ->assertRedirect(route('messages.show', $thread))
            ->assertSessionHas('success', 'Respuesta enviada.');
        $this->actingAs($coordinator)->post(route('messages.reply', $thread), ['body' => ''])->assertSessionHasErrors('body');

        $this->actingAs($this->person('companero'))->post(route('messages.resolve', $thread))->assertForbidden();
        $this->actingAs($coordinator)->post(route('messages.resolve', $thread))->assertSessionHas('success', 'Hilo marcado como resuelto.');

        $this->screen('companero', route('messages.show', $thread))
            ->assertSee('Reabrir')
            ->assertSee('Hilo resuelto')
            ->assertDontSee('Tu respuesta');
        $this->actingAs($coordinator)->post(route('messages.reply', $thread), ['body' => 'Otra cosa.'])->assertForbidden();

        $this->actingAs($this->person('companero'))->post(route('messages.reopen', $thread))->assertSessionHas('success', 'Hilo reabierto.');
        $this->assertSame('open', $thread->fresh()->state);
        $this->actingAs($this->person('estudiante'))->post(route('messages.reply', $thread), ['body' => 'Me apunto.'])->assertForbidden();
    }

    public function test_the_old_suggestions_mailbox_opens_a_thread_to_the_lecturers(): void
    {
        $this->actingAs($this->person('estudiante'))->get('/suggestionsmailbox')->assertRedirect('/messages/create?to=LECTURE');

        $this->screen('estudiante', '/messages/create?to=LECTURE')
            ->assertSee('Sugerencias al profesorado')
            ->assertSee('<option value="LECTURE" selected', false)
            ->assertSee('value="Sugerencia"', false);
    }
}
