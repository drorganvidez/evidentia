<?php

namespace Tests\Feature\Screens\Management;

use App\Models\MeetingMinutes;

/** The lists of every evidence and every meeting of the course, for the lecturer and the president. */
class CourseListsScreenTest extends ScreenTestCase
{
    public function test_the_evidences_of_the_course_by_state(): void
    {
        foreach (['profesor' => 'lecture', 'presidente' => 'president'] as $who => $prefix) {
            $this->screen($who, route($prefix.'.evidence.list'))
                ->assertSee('Evidencias del curso')
                ->assertSee('Montaje del photocall')
                ->assertSee('Cartelería del vestíbulo')
                ->assertDontSee('Borrador de la memoria')
                ->assertSee('Logística');
        }

        $this->screen('profesor', route('lecture.evidence.list', ['estado' => 'rechazadas']))
            ->assertSee('Recogida de material')
            ->assertDontSee('Montaje del photocall')
            ->assertSee('aria-current="page"', false);
    }

    public function test_an_unknown_state_lists_them_all(): void
    {
        $this->screen('profesor', route('lecture.evidence.list', ['estado' => 'papelera']))
            ->assertSee('Montaje del photocall')
            ->assertSee('Recogida de material');
    }

    public function test_the_meetings_of_the_course_link_their_minutes(): void
    {
        $minutes = MeetingMinutes::sole();

        foreach (['profesor' => 'lecture', 'presidente' => 'president'] as $who => $prefix) {
            $this->screen($who, route($prefix.'.meeting.list'))
                ->assertSee('Reunión de logística')
                ->assertSee('Aula A3.10')
                ->assertSee('href="'.route('download.minutes', ['id' => $minutes->id]).'"', false);
        }
    }
}
