<?php

namespace Tests\Feature\Screens\Management;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\User;

/** The attendances of the course in pages, searched on the server, with the counts and hours of the whole result. */
class AttendanceListPagesTest extends ScreenTestCase
{
    public function test_the_attendances_page_search_and_count_the_whole_result(): void
    {
        // A newer event with 30 attendances, 20 of them attended
        $talk = Event::forceCreate(['name' => 'Charla de <b>IA</b>', 'start_datetime' => now()->subDay(), 'hours' => 2]);
        User::factory()->count(30)
            ->sequence(fn ($sequence) => [
                'name' => 'Ana', 'surname' => sprintf('Zubiri %02d', $sequence->index + 1),
                'username' => sprintf('anazub%02d', $sequence->index + 1), 'email' => sprintf('ana.zubiri%02d@alum.us.es', $sequence->index + 1),
            ])
            ->create()
            ->each(fn (User $user, int $index) => Attendee::forceCreate([
                'event_id' => $talk->id, 'user_id' => $user->id, 'status' => $index < 20 ? Attendee::ATTENDED : 'Attending',
            ]));

        $html = $this->screen('profesor', route('lecture.attendee.list'))
            ->assertSee('De la 1 a la 25 de 32')
            ->assertSee('32 asistencias')
            // 20 × 2 h of the talk and 2 × 1,5 h of the workshop
            ->assertSee('43,00 h')
            ->assertDontSee('Taller de Git</td>', false)
            ->getContent();

        $attendances = substr($html, strpos($html, 'id="attendances"'), strpos($html, 'id="events"') - strpos($html, 'id="attendances"'));
        $this->assertSame(25, substr_count($attendances, '<tr role="row"') - 1, 'The header row aside');
        $this->assertMatchesRegularExpression('/Asistidas\s*<span[^>]*>\s*22\s*</', $html);
        $this->assertMatchesRegularExpression('/Pendientes\s*<span[^>]*>\s*10\s*</', $html);

        // the second page holds the older workshop, the tabs keep the event and the search
        $this->screen('profesor', route('lecture.attendee.list', ['page' => 2]))->assertSee('Taller de Git')->assertSee('De la 26 a la 32 de 32');

        $found = $this->screen('profesor', route('lecture.attendee.list', ['evento' => $talk->id, 'q' => 'zubiri 1']))
            ->assertSee('Zubiri 10')->assertDontSee('Zubiri 20')
            ->getContent();

        // Zubiri 01, 10 to 19 and 21
        $this->assertMatchesRegularExpression('/Todas\s*<span[^>]*>\s*12\s*</', $found);
        $this->assertStringContainsString('href="'.e(route('lecture.attendee.list', ['evento' => $talk->id, 'q' => 'zubiri 1', 'estado' => 'asistidas'])).'"', $found);
        $this->assertStringContainsString('<input type="hidden" name="q" value="zubiri 1">', $found);

        $this->screen('profesor', route('lecture.attendee.list', ['q' => 'taller git']))
            ->assertSee('2 asistencias')
            ->assertSee('3,00 h');
        $this->screen('profesor', route('lecture.attendee.list', ['q' => 'nada parecido']))
            ->assertSee('Ninguna asistencia coincide con la búsqueda');
    }
}
