<?php

namespace Tests\Feature\Screens\Meetings;

use App\Models\Configuration;

class MeetingManageScreenTest extends MeetingScreenTestCase
{
    public function test_the_summary_shows_the_figures_the_next_call_and_the_last_minutes(): void
    {
        $html = $this->assertScreen($this->as('secretario')->get(route('secretary.meeting.manage')));

        $this->assertStringContainsString('aria-label="Secciones de las reuniones"', $html);
        $this->assertStringContainsString('Próximas convocatorias', $html);
        $this->assertStringContainsString('Convocatoria de logística', $html);
        $this->assertStringContainsString('Reunión de logística', $html);
        $this->assertStringContainsString('2,00 h registradas', $html);
        $this->assertStringContainsString(route('secretary.meeting.manage.minutes.create'), $html);
    }

    public function test_new_minutes_are_not_offered_after_the_meetings_deadline(): void
    {
        Configuration::query()->update(['meetings_timestamp' => now()->subDay()]);

        $html = $this->assertScreen($this->as('secretario')->get(route('secretary.meeting.manage')));

        $this->assertStringNotContainsString(route('secretary.meeting.manage.minutes.create'), $html);
    }
}
