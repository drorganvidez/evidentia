<?php

namespace Tests\Feature\Screens\Management;

use App\Models\Committee;
use App\Models\Meeting;

/** The meetings of the course in pages, searched on the server. */
class MeetingListPagesTest extends ScreenTestCase
{
    public function test_the_meetings_page_keep_their_filters_and_search(): void
    {
        $committee = Committee::where('name', 'Comunicación')->sole();
        foreach (range(1, 30) as $index) {
            Meeting::forceCreate([
                'title' => sprintf('Reunión %02d', $index), 'datetime' => now()->subHours($index), 'place' => 'Sala de juntas',
                'type' => '1', 'modality' => '1', 'hours' => 1, 'committee_id' => $committee->id,
            ]);
        }

        $html = $this->screen('presidente', route('president.meeting.list'))
            ->assertSee('31 reuniones')
            ->assertSee('De la 1 a la 25 de 31')
            ->assertSeeInOrder(['Reunión 01', 'Reunión 25'])
            ->assertDontSee('Reunión de logística')
            ->getContent();

        $this->assertSame(25, substr_count($html, '<tr role="row"') - 1);

        $url = route('president.meeting.list', ['comite' => $committee->id, 'acta' => 'sin', 'q' => 'juntas']);
        $filtered = $this->screen('presidente', $url)->assertSee('30 reuniones')->getContent();

        $this->assertStringContainsString('href="'.e($url.'&page=2').'" rel="next"', $filtered);
        $this->assertStringContainsString('<input type="hidden" name="q" value="juntas">', $filtered);
        $this->assertStringContainsString('<input type="hidden" name="acta" value="sin">', $filtered);

        $this->screen('presidente', route('president.meeting.list', ['q' => 'logística a3']))
            ->assertSee('1 reunión')
            ->assertSee('Reunión de logística');
        $this->screen('profesor', route('lecture.meeting.list', ['q' => 'nada parecido']))
            ->assertSee('Ninguna reunión coincide con la búsqueda');
    }
}
