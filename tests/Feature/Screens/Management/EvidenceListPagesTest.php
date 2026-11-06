<?php

namespace Tests\Feature\Screens\Management;

use App\Models\Committee;
use App\Models\Evidence;
use App\Models\User;

/** The evidences of the course in pages: volume from the factory over the seed of the end-to-end tests. */
class EvidenceListPagesTest extends ScreenTestCase
{
    /** 60 pending evidences of one hour in Logística, older than the seed so its rows stay first. */
    private function volume(int $count = 60): void
    {
        Evidence::factory()->count($count)
            ->for($this->person('companero'), 'user')
            ->for(Committee::where('name', 'Logística')->sole())
            ->sequence(fn ($sequence) => ['title' => sprintf('Volumen %02d', $sequence->index + 1), 'updated_at' => now()->subDays(1)->subMinutes($sequence->index)])
            ->create();
    }

    private function rows(string $html): int
    {
        return substr_count($html, ' data-href="');
    }

    public function test_the_first_page_holds_25_with_the_totals_and_the_counts_of_the_whole_list(): void
    {
        $this->volume();

        $html = $this->screen('profesor', route('lecture.evidence.list'))
            ->assertSee('<nav aria-label="Paginación de Evidencias del curso"', false)
            ->assertSee('De la 1 a la 25 de 64')
            // 4 of the seed and 60 of the factory, 67,25 hours, newest first
            ->assertSeeInOrder(['Montaje del photocall', 'Volumen 01', 'Volumen 02'])
            ->assertDontSee('Volumen 22')
            ->assertSee('64 evidencias')
            ->assertSee('67,25 h')
            ->getContent();

        $this->assertSame(25, $this->rows($html));
        $this->assertMatchesRegularExpression('/Pendientes\s*<span[^>]*>\s*62\s*</', $html);
        $this->assertMatchesRegularExpression('/Todas\s*<span[^>]*>\s*64\s*</', $html);
        $this->assertStringContainsString('href="'.route('lecture.evidence.list', ['page' => 2]).'" rel="next"', $html);
    }

    public function test_the_links_keep_the_filters_the_tab_and_the_size(): void
    {
        $this->volume();
        $committee = Committee::where('name', 'Logística')->sole();
        $url = route('lecture.evidence.list', ['estado' => 'pendientes', 'comite' => $committee->id, 'per_page' => 50]);

        $html = $this->screen('profesor', $url)->assertSee('De la 1 a la 50 de 62')->getContent();

        $this->assertSame(50, $this->rows($html));
        $this->assertStringContainsString('href="'.e($url.'&page=2').'" rel="next"', $html);
        // the tabs and the reset of the filters keep the rest of the query string, back on the first page
        $this->assertStringContainsString('href="'.e(route('lecture.evidence.list', ['estado' => 'aceptadas', 'comite' => $committee->id, 'per_page' => 50])).'"', $html);
        $this->assertStringContainsString('href="'.e(route('lecture.evidence.list', ['estado' => 'pendientes', 'per_page' => 50])).'"', $html);
        $this->assertStringContainsString('<input type="hidden" name="estado" value="pendientes">', $html);
        $this->assertStringContainsString('<input type="hidden" name="per_page" value="50">', $html);

        $page = $this->screen('profesor', $url.'&page=2')->getContent();

        $this->assertSame(12, $this->rows($page));
        $this->assertStringContainsString('aria-label="Página 2"', $page);
    }

    public function test_the_search_runs_on_the_server_and_the_counts_follow_it(): void
    {
        $this->volume();
        $author = User::factory()->create(['name' => 'Inés', 'surname' => 'Alcalá Vidal', 'username' => 'inealcvid']);
        Evidence::factory()->for($author, 'user')->for(Committee::where('name', 'Comunicación')->sole())
            ->create(['title' => 'Hilo de difusión', 'status' => 'ACCEPTED']);

        $html = $this->screen('presidente', route('president.evidence.list', ['q' => 'photocall']))
            ->assertSee('Montaje del photocall')
            ->assertDontSee('Volumen 01')
            ->assertSee('value="photocall"', false)
            ->assertSee('1 evidencia')
            ->getContent();

        $this->assertMatchesRegularExpression('/Todas\s*<span[^>]*>\s*1\s*</', $html);
        $this->assertMatchesRegularExpression('/Aceptadas\s*<span[^>]*>\s*0\s*</', $html);
        $this->assertStringContainsString('href="'.e(route('president.evidence.list', ['q' => 'photocall', 'estado' => 'pendientes'])).'"', $html);
        $this->assertStringNotContainsString('aria-label="Paginación de', $html);

        // by author, username or committee, every word somewhere
        $this->screen('presidente', route('president.evidence.list', ['q' => 'inealcvid']))->assertSee('Hilo de difusión')->assertDontSee('Montaje del photocall');
        $this->screen('presidente', route('president.evidence.list', ['q' => 'comunicación hilo']))->assertSee('Hilo de difusión')->assertDontSee('Volumen 01');
        $this->screen('presidente', route('president.evidence.list', ['q' => 'volumen 07']))->assertSee('Volumen 07')->assertDontSee('Volumen 08');

        $this->screen('presidente', route('president.evidence.list', ['q' => 'nada parecido']))
            ->assertSee('Ninguna evidencia coincide con la búsqueda')
            ->assertSee('role="search"', false);
    }

    public function test_a_page_past_the_last_shows_the_last(): void
    {
        $this->volume();

        $html = $this->screen('profesor', route('lecture.evidence.list', ['page' => 99]))
            ->assertSee('De la 51 a la 64 de 64')
            ->assertSee('Volumen 60')
            ->getContent();

        $this->assertSame(14, $this->rows($html));
        $this->assertMatchesRegularExpression('/aria-label="Página 3"\s+aria-current="page"/', $html);
    }

    public function test_a_short_list_has_neither_pages_nor_sizes(): void
    {
        $this->screen('profesor', route('lecture.evidence.list'))
            ->assertDontSee('aria-label="Paginación de', false)
            ->assertDontSee('Filas por página')
            ->assertSee('4 evidencias')
            ->assertSee('role="search"', false);
    }
}
