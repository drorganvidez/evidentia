<?php

namespace Tests\Feature\Screens\Meetings;

class MyMeetingsScreenTest extends MeetingScreenTestCase
{
    public function test_a_student_sees_the_meetings_attended_with_their_hours(): void
    {
        $html = $this->assertScreen($this->as('estudiante')->get(route('meeting.list')));

        $this->assertStringContainsString('Reunión de logística', $html);
        $this->assertStringContainsString('Registro de reuniones', $html);
        $this->assertStringContainsString('1 reunión', $html);
    }

    public function test_a_person_without_meetings_sees_the_empty_state(): void
    {
        $html = $this->assertScreen($this->as('companero')->get(route('meeting.list')));

        $this->assertStringContainsString('Sin reuniones', $html);
    }
}
