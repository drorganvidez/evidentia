<?php

namespace Tests\Feature\Screens\Management;

use App\Models\Attendee;
use App\Models\Event;

/** Every attendance of the course and the events with their attendees, read only, for the lecturer and the president. */
class AttendanceListScreenTest extends ScreenTestCase
{
    private Event $talk;

    protected function setUp(): void
    {
        parent::setUp();
        // A newer event with a pending sign-up and an absence, which count no hours
        $this->talk = Event::forceCreate(['name' => 'Charla de <b>IA</b>', 'start_datetime' => now()->subDay(), 'hours' => 2, 'hidden' => true]);
        Attendee::forceCreate(['event_id' => $this->talk->id, 'user_id' => $this->person('companero')->id, 'status' => 'Attending']);
        Attendee::forceCreate(['event_id' => $this->talk->id, 'user_id' => $this->person('estudiante')->id, 'status' => 'Not Attending']);
    }

    public function test_both_roles_see_every_attendance_with_its_person_event_state_and_hours(): void
    {
        $student = $this->person('estudiante');

        foreach (['profesor' => 'lecture', 'presidente' => 'president'] as $who => $prefix) {
            $html = $this->screen($who, route($prefix.'.attendee.list'))
                ->assertSee('Asistencias del curso')
                ->assertSee('Pérez Romero, Lucía')
                ->assertSee('Torres Gómez, Pablo')
                ->assertSee('García López, Juan')
                ->assertSee('Taller de Git')
                ->assertSee('Charla de IA')
                ->assertDontSee('<b>IA</b>', false)
                ->assertSee('Asistido')
                ->assertSee('Pendiente de asistir')
                ->assertSee('No asistido')
                ->assertSee('href="'.route('profiles.view', ['id' => $student->id]).'"', false)
                ->assertSee('4 asistencias')
                ->assertSee('3,00 h')
                ->assertSee('Oculto')
                ->assertDontSee('Añadir asistencia')
                ->assertDontSee('action="'.route('attendee.store').'"', false)
                ->getContent();

            // The newest event first, in the attendances and in the events
            $attendances = substr($html, strpos($html, 'id="attendances"'), strpos($html, 'id="events"') - strpos($html, 'id="attendances"'));
            $this->assertLessThan(strpos($attendances, 'Taller de Git'), strpos($attendances, 'Charla de IA'));
            $events = substr($html, strpos($html, 'id="events"'));
            $this->assertLessThan(strpos($events, 'Taller de Git'), strpos($events, 'Charla de IA'));
        }
    }

    public function test_the_events_list_their_sign_ups_attendees_and_computed_hours(): void
    {
        $git = Event::where('name', 'Taller de Git')->sole();

        $html = $this->screen('presidente', route('president.attendee.list'))->getContent();
        $events = substr($html, strpos($html, 'id="events"'));

        $this->assertStringContainsString('href="'.route('president.attendee.list', ['evento' => $git->id]).'"', $events);
        $this->assertStringContainsString('href="'.route('president.attendee.list', ['evento' => $this->talk->id]).'"', $events);
        // Taller de Git: 2 attendees of 1,50 h; the talk: 2 sign-ups and none attended
        $this->assertMatchesRegularExpression('/>\s*2\s*<.*1,50 h.*3,00 h/s', $events);
        $this->assertStringContainsString('0,00 h', $events);
    }

    public function test_it_filters_by_event_and_by_state(): void
    {
        $byEvent = $this->screen('profesor', route('lecture.attendee.list', ['evento' => $this->talk->id]));
        $attendances = $this->section($byEvent->getContent());
        $this->assertStringContainsString('García López, Juan', $attendances);
        $this->assertStringNotContainsString('Torres Gómez, Pablo', $attendances);
        $byEvent->assertSee('selected', false)->assertSee('filtro activo');

        $attended = $this->section($this->screen('profesor', route('lecture.attendee.list', ['estado' => 'asistidas']))->getContent());
        $this->assertStringContainsString('Torres Gómez, Pablo', $attended);
        $this->assertStringNotContainsString('García López, Juan', $attended);

        $both = $this->screen('profesor', route('lecture.attendee.list', ['evento' => $this->talk->id, 'estado' => 'asistidas']));
        $both->assertSee('No hay asistencias registradas')
            ->assertSee('name="estado" value="asistidas"', false)
            ->assertSee('href="'.e(route('lecture.attendee.list', ['evento' => $this->talk->id, 'estado' => 'pendientes'])).'"', false);
    }

    public function test_unknown_filters_list_everything(): void
    {
        $attendances = $this->section($this->screen('profesor', route('lecture.attendee.list', ['evento' => 999, 'estado' => 'papelera']))->getContent());

        $this->assertStringContainsString('Torres Gómez, Pablo', $attendances);
        $this->assertStringContainsString('García López, Juan', $attendances);
    }

    public function test_nobody_else_opens_it(): void
    {
        foreach (['estudiante', 'coordinador', 'secretario', 'registro'] as $who) {
            $status = $this->actingAs($this->person($who))->get(route('president.attendee.list'))->getStatusCode();
            $this->assertContains($status, [302, 403, 404], $who);
        }
    }

    /** The table of the attendances, without the events below it. */
    private function section(string $html): string
    {
        $start = strpos($html, 'id="attendances"');

        return substr($html, $start, strpos($html, 'id="events"') - $start);
    }
}
