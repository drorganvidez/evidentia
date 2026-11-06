<?php

namespace Tests\Feature\Screens\Management;

use App\Models\Bonus;
use App\Models\Committee;

/** The bonuses of the course in pages, searched on the server, with the hours of the whole search. */
class BonusListPagesTest extends ScreenTestCase
{
    public function test_the_bonuses_page_and_their_hours_count_the_whole_search(): void
    {
        $committee = Committee::where('name', 'Comunicación')->sole();
        foreach (range(1, 30) as $index) {
            $bono = Bonus::create(['reason' => sprintf('Difusión %02d', $index), 'hours' => 2]);
            $bono->committee()->associate($committee)->save();
            $bono->users()->attach([$this->person('companero')->id, $this->person('registro')->id]);
        }

        $html = $this->screen('profesor', route('lecture.bonus.list'))
            ->assertSee('31 bonos')
            ->assertSee('De la 1 a la 10 de 31')
            // 30 × 2 h × 2 people and the 3 h of the seed
            ->assertSee('123,00 h')
            ->getContent();

        $this->assertSame(10, substr_count($html, '<tr role="row"') - 1);

        $this->screen('profesor', route('lecture.bonus.list', ['q' => 'pérez']))
            ->assertSee('1 bono')
            ->assertSee('Montaje del escenario')
            ->assertSee('3,00 h')
            ->assertDontSee('Difusión 01');
        $this->screen('profesor', route('lecture.bonus.list', ['q' => 'comunicación torres', 'per_page' => 50]))
            ->assertSee('30 bonos')
            ->assertSee('120,00 h')
            ->assertSee('Difusión 30');
        $this->screen('presidente', route('president.bonus.list', ['q' => 'nada parecido']))
            ->assertSee('Ningún bono coincide con la búsqueda');
    }
}
