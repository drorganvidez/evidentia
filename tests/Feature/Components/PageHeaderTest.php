<?php

namespace Tests\Feature\Components;

use Illuminate\View\ViewException;
use InvalidArgumentException;

class PageHeaderTest extends ComponentTestCase
{
    public function test_it_prints_the_h1_of_the_page_with_the_id_the_layout_points_at(): void
    {
        $html = $this->render('<x-ui.page-header title="Hola, Juan" class="mt-4" />');

        $this->assertStringStartsWith('<header', trim($html));
        $this->assertStringContainsString('flex flex-col items-stretch gap-4 px-2 pt-3 md:flex-row md:items-end md:justify-between md:gap-8 md:px-3 md:pt-6 mt-4', $html);
        $this->assertStringContainsString('<h1 id="page-title" class="text-4xl font-bold tracking-tighter md:text-5xl">Hola, Juan</h1>', $html);
        $this->assertStringNotContainsString('<p', $html);
        $this->assertStringNotContainsString('lg:hidden', $html);
        $this->assertStringNotContainsString('md:justify-end', $html);
        $this->assertStringEndsWith('</header>', trim($html));
        $this->assertNoAutofocus($html);
        $this->assertCspSafe($html);
    }

    public function test_the_level_and_the_id_can_change(): void
    {
        $html = $this->render('<x-ui.page-header title="Acta" :level="2" id="minutes-title" />');

        $this->assertStringContainsString('<h2 id="minutes-title" class="text-4xl font-bold tracking-tighter md:text-5xl">Acta</h2>', $html);
        $this->assertStringNotContainsString('page-title', $html);
        $this->assertStringContainsString('<h6 id="page-title"', $this->render('<x-ui.page-header title="Acta" :level="9" />'));
    }

    public function test_the_title_is_required(): void
    {
        try {
            $this->render('<x-ui.page-header />');
        } catch (ViewException $exception) {
            while ($exception->getPrevious() !== null) {
                $exception = $exception->getPrevious();
            }

            $this->assertInstanceOf(InvalidArgumentException::class, $exception);
            $this->assertSame('The page header needs a title.', $exception->getMessage());

            return;
        }

        $this->fail('A page header without a title was rendered');
    }

    public function test_the_lede_is_optional_and_escaped(): void
    {
        $html = $this->render('<x-ui.page-header :title="$title" :lede="$lede" />', ['title' => '<b>Hola</b>', 'lede' => '<i>Dos</i> pendientes']);

        $this->assertStringContainsString('>&lt;b&gt;Hola&lt;/b&gt;</h1>', $html);
        $this->assertStringContainsString('<p class="mt-2 max-w-[56ch] text-md text-ink md:text-base">&lt;i&gt;Dos&lt;/i&gt; pendientes</p>', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }

    public function test_the_slots_land_in_their_places_in_dom_order(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-ui.page-header title="Montaje del photocall">
                <x-slot:eyebrow>EV-0033</x-slot:eyebrow>
                <x-slot:meta class="text-sm">14 días</x-slot:meta>
                <x-slot:actions><a href="/editar">Editar</a></x-slot:actions>
            </x-ui.page-header>
            BLADE);

        $this->assertMatchesRegularExpression('/<div\s+class="mb-2 flex flex-wrap items-center gap-2 text-sm font-medium text-ink">\s*EV-0033<\/div>/', $html);
        $this->assertStringContainsString('<div class="mt-3 lg:hidden text-sm">14 días</div>', $html);
        $this->assertMatchesRegularExpression('/<div\s+class="flex flex-col gap-3 md:shrink-0 md:flex-row md:items-center md:justify-end">\s*<a href="\/editar">Editar<\/a><\/div>/', $html);

        $eyebrow = strpos($html, 'EV-0033');
        $title = strpos($html, '<h1');
        $meta = strpos($html, '14 días');
        $actions = strpos($html, '/editar');

        $this->assertLessThan($title, $eyebrow, 'The eyebrow goes above the title');
        $this->assertLessThan($meta, $title, 'The meta line goes below the title');
        $this->assertLessThan($actions, $meta, 'The actions come after the h1 in the DOM');
    }
}
