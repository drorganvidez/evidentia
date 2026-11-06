<?php

namespace Tests\Feature\Screens\Management;

use App\Models\Attendee;
use App\Models\Event;

/** The events and the attendances of the register coordinator in pages, searched on the server. */
class EventbritePagesTest extends ScreenTestCase
{
    /** 30 newer events with one attendance each of the student. */
    private function volume(): void
    {
        foreach (range(1, 30) as $index) {
            $event = Event::forceCreate([
                'name' => sprintf('Charla %02d', $index), 'start_datetime' => now()->subHours($index), 'hours' => 1, 'status' => 'live',
            ]);
            Attendee::forceCreate(['event_id' => $event->id, 'user_id' => $this->person('estudiante')->id, 'status' => 'Attending']);
        }
    }

    public function test_the_events_page_newest_first_and_search_by_name(): void
    {
        $this->volume();

        $html = $this->screen('registro', route('registercoordinator.event.list'))
            ->assertSee('31 eventos')
            ->assertSee('De la 1 a la 10 de 31')
            ->assertSeeInOrder(['Charla 01', 'Charla 02', 'Charla 10'])
            ->assertDontSee('Charla 11')
            ->assertDontSee('Taller de Git')
            ->getContent();

        $this->assertSame(10, substr_count($html, '<tr role="row"') - 1);

        $this->screen('registro', route('registercoordinator.event.list', ['page' => 2, 'per_page' => 25]))
            ->assertSee('Charla 30')
            ->assertSee('Taller de Git');
        $this->screen('registro', route('registercoordinator.event.list', ['q' => 'git']))
            ->assertSee('1 evento')
            ->assertSee('Taller de Git')
            ->assertDontSee('Charla 01');
        $this->screen('registro', route('registercoordinator.event.list', ['q' => 'nada parecido']))
            ->assertSee('Ningún evento coincide con la búsqueda');
    }

    public function test_the_attendances_page_newest_event_first_and_search_by_person_or_event(): void
    {
        $this->volume();

        $html = $this->screen('registro', route('registercoordinator.attendee.list'))
            ->assertSee('32 asistencias')
            ->assertSee('De la 1 a la 10 de 32')
            ->assertSeeInOrder(['Charla 01', 'Charla 02'])
            ->assertDontSee('Taller de Git')
            ->getContent();

        $this->assertSame(10, substr_count($html, '<tr role="row"') - 1);

        $this->screen('registro', route('registercoordinator.attendee.list', ['page' => 2, 'per_page' => 25]))
            ->assertSee('Taller de Git')
            ->assertSee('De la 26 a la 32 de 32');
        $this->screen('registro', route('registercoordinator.attendee.list', ['q' => 'torres']))
            ->assertSee('1 asistencia')
            ->assertSee('Torres Gómez, Pablo')
            ->assertDontSee('Pérez Romero, Lucía');
        // Charla 01, 10 to 19 and 21
        $this->screen('registro', route('registercoordinator.attendee.list', ['q' => 'pérez charla 1']))
            ->assertSee('12 asistencias');
    }
}
