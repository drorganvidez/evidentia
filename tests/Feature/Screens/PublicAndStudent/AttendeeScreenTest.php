<?php

namespace Tests\Feature\Screens\PublicAndStudent;

/** The attendances of the student to the events of Eventbrite. */
class AttendeeScreenTest extends ScreenTestCase
{
    public function test_the_attendances_list_the_events_with_their_hours_and_status(): void
    {
        $html = $this->assertAppScreen($this->signedAs('estudiante')->get(route('attendee.list')));

        $this->assertStringContainsString('Taller de Git', $html);
        $this->assertStringContainsString('Asistido', $html);
        $this->assertStringContainsString('1 de 1 con asistencia', $html);
    }
}
