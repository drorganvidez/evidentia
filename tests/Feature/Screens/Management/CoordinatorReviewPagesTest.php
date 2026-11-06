<?php

namespace Tests\Feature\Screens\Management;

use App\Models\Committee;
use App\Models\Evidence;

/** The review lists of a coordinator in pages: volume from the factory over the seed of the end-to-end tests. */
class CoordinatorReviewPagesTest extends ScreenTestCase
{
    private function volume(int $count = 30): void
    {
        Evidence::factory()->count($count)
            ->for($this->person('companero'), 'user')
            ->for(Committee::where('name', 'Logística')->sole())
            ->sequence(fn ($sequence) => ['title' => sprintf('Volumen %02d', $sequence->index + 1), 'updated_at' => now()->subDay()->subMinutes($sequence->index)])
            ->create();
        // another committee stays out of the counts
        Evidence::factory()->count(3)->for($this->person('companero'), 'user')->for(Committee::where('name', 'Comunicación')->sole())->create();
    }

    public function test_the_queue_holds_25_with_the_counts_and_the_hours_of_the_whole_committee(): void
    {
        $this->volume();

        $html = $this->screen('coordinador', route('coordinator.evidence.list.pending'))
            ->assertSee('De la 1 a la 25 de 32')
            ->assertSee('32 evidencias')
            ->assertSee('34,75 h')
            ->assertSeeInOrder(['Atención en el registro', 'Volumen 01'])
            ->assertDontSee('Volumen 24')
            ->getContent();

        $this->assertSame(25, substr_count($html, ' data-href="'));
        $this->assertMatchesRegularExpression('/Todas\s*<span[^>]*>\s*34\s*</', $html);
        $this->assertMatchesRegularExpression('/Pendientes\s*<span[^>]*>\s*32\s*</', $html);
        $this->assertStringContainsString('href="'.route('coordinator.evidence.list.pending', ['page' => 2]).'" rel="next"', $html);

        $this->screen('coordinador', route('coordinator.evidence.list.pending', ['page' => 5]))
            ->assertSee('De la 26 a la 32 de 32')
            ->assertSee('Volumen 30');
    }

    public function test_the_search_narrows_the_counts_and_the_tabs_keep_it(): void
    {
        $this->volume();

        $html = $this->screen('coordinador', route('coordinator.evidence.list.all', ['q' => 'volumen 1', 'per_page' => 50]))
            ->assertSee('Volumen 10')
            ->assertSee('Volumen 01')
            ->assertDontSee('Volumen 22')
            ->assertDontSee('Montaje del photocall')
            ->getContent();

        // Volumen 01, 10 to 19, 21
        $this->assertMatchesRegularExpression('/Todas\s*<span[^>]*>\s*12\s*</', $html);
        $this->assertMatchesRegularExpression('/Aceptadas\s*<span[^>]*>\s*0\s*</', $html);
        $this->assertStringContainsString('href="'.e(route('coordinator.evidence.list.rejected', ['q' => 'volumen 1', 'per_page' => 50])).'"', $html);

        $this->screen('coordinador', route('coordinator.evidence.list.accepted', ['q' => 'nada parecido']))
            ->assertSee('Ninguna evidencia coincide con la búsqueda');
    }
}
