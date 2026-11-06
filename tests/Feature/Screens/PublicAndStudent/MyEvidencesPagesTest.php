<?php

namespace Tests\Feature\Screens\PublicAndStudent;

use App\Models\Committee;
use App\Models\Evidence;

/** The evidences of a student in pages, with a search on the server and the totals of all of them. */
class MyEvidencesPagesTest extends ScreenTestCase
{
    public function test_the_list_pages_searches_and_sums_every_evidence(): void
    {
        Evidence::factory()->count(30)
            ->for($this->person('estudiante'), 'user')
            ->for(Committee::where('name', 'Comunicación')->sole())
            ->sequence(fn ($sequence) => ['title' => sprintf('Volumen %02d', $sequence->index + 1), 'created_at' => now()->subDays(2)->subMinutes($sequence->index)])
            ->create();
        // the evidences of others stay out
        Evidence::factory()->count(5)->for($this->person('companero'), 'user')->for(Committee::where('name', 'Logística')->sole())->create();

        $html = $this->assertAppScreen($this->signedAs('estudiante')->get(route('evidence.list')));

        $this->assertSame(25, substr_count($html, ' data-href="'));
        $this->assertStringContainsString('De la 1 a la 25 de 34', $html);
        // 1,75 + 2 + 0,5 + 1 of the seed and 30 of one hour
        $this->assertStringContainsString('34 evidencias', $html);
        $this->assertStringContainsString('35,25 h', $html);
        $this->assertStringContainsString('<nav aria-label="Paginación de Mis evidencias"', $html);

        $page = $this->assertAppScreen($this->signedAs('estudiante')->get(route('evidence.list', ['page' => 2, 'per_page' => 25])));

        $this->assertSame(9, substr_count($page, ' data-href="'));
        $this->assertStringContainsString('Volumen 30', $page);

        $found = $this->assertAppScreen($this->signedAs('estudiante')->get(route('evidence.list', ['q' => 'comunicación 07'])));

        $this->assertStringContainsString('Volumen 07', $found);
        $this->assertStringNotContainsString('Montaje del photocall', $found);
        $this->assertStringContainsString('1 evidencia ·', $found);

        $none = $this->assertAppScreen($this->signedAs('estudiante')->get(route('evidence.list', ['q' => 'nada parecido'])));

        $this->assertStringContainsString('Ninguna evidencia coincide con la búsqueda', $none);
        $this->assertStringContainsString('value="nada parecido"', $none);
    }
}
