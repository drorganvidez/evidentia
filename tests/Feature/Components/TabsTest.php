<?php

namespace Tests\Feature\Components;

use Illuminate\View\ViewException;
use InvalidArgumentException;

class TabsTest extends ComponentTestCase
{
    private const ITEMS = <<<'PHP'
        [
            ['label' => 'Todas', 'href' => '?estado=todas', 'count' => 24, 'current' => true],
            ['label' => 'Pendientes', 'href' => '?estado=pendientes', 'count' => 2],
            ['label' => 'Rechazadas', 'href' => '?estado=rechazadas', 'count' => 0],
            ['label' => 'Borradores', 'href' => '?estado=borradores'],
        ]
        PHP;

    private function tabs(string $props = ''): string
    {
        return $this->render('<x-ui.tabs label="Filtrar por estado" :items="'.self::ITEMS.'" '.$props.' />');
    }

    public function test_it_is_a_labelled_navigation_with_a_list_of_links(): void
    {
        $html = $this->tabs('class="mt-2"');

        $this->assertStringContainsString('<nav aria-label="Filtrar por estado" class="max-w-full mt-2">', $html);
        $this->assertMatchesRegularExpression('/<ul\s+class="inline-flex max-w-full gap-0\.5 overflow-x-auto rounded-full border border-glass-edge bg-field p-0\.75 no-scrollbar max-md:w-full">/', $html);
        $this->assertSame(4, substr_count($html, '<li class="shrink-0">'));
        $this->assertSame(4, substr_count($html, '<a href="?estado='));
        $this->assertStringContainsString('</nav>', $html);
        $this->assertStringNotContainsString('role=', $html);
        $this->assertStringNotContainsString('x-data', $html);
        $this->assertUniqueIds($html);
        $this->assertCspSafe($html);
    }

    public function test_only_the_current_item_carries_aria_current(): void
    {
        $html = $this->tabs();

        $this->assertSame(1, substr_count($html, 'aria-current="page"'));
        $this->assertMatchesRegularExpression('/<a href="\?estado=todas" class="[^"]*current:bg-glass-2 current:text-ink current:shadow-raised[^"]*"\s+aria-current="page"\s*>\s*Todas/', $html);
        $this->assertMatchesRegularExpression('/<a href="\?estado=pendientes" class="[^"]*"\s*>\s*Pendientes/', $html);
    }

    public function test_the_count_badge_appears_only_with_a_count(): void
    {
        $html = $this->tabs();

        $this->assertSame(3, substr_count($html, 'font-mono text-2xs font-semibold tabular-nums text-ink'));
        $this->assertMatchesRegularExpression('/Rechazadas\s*<span class="[^"]*bg-rule[^"]*">\s*0\s*<\/span>/', $html);
        $this->assertMatchesRegularExpression('/Borradores\s*<\/a>/', $html);
    }

    public function test_every_link_is_a_pill_with_a_coarse_pointer_target(): void
    {
        $small = $this->tabs();

        $this->assertStringContainsString('px-3.5 py-1.75 text-sm', $small);
        $this->assertStringContainsString('pointer-coarse:min-h-11', $small);
        $this->assertStringContainsString('scroll-ml-4', $small);
        $this->assertStringContainsString('whitespace-nowrap', $small);
        $this->assertStringNotContainsString('px-4 py-2 text-md', $small);

        $medium = $this->tabs('size="md"');

        $this->assertStringContainsString('px-4 py-2 text-md', $medium);
        $this->assertStringNotContainsString('px-3.5 py-1.75 text-sm', $medium);
    }

    public function test_the_label_is_required(): void
    {
        try {
            $this->render('<x-ui.tabs :items="[]" />');
        } catch (ViewException $exception) {
            while ($exception->getPrevious() !== null) {
                $exception = $exception->getPrevious();
            }

            $this->assertInstanceOf(InvalidArgumentException::class, $exception);

            return;
        }

        $this->fail('Tabs without a label were rendered');
    }

    public function test_the_label_and_the_items_are_escaped(): void
    {
        $html = $this->render('<x-ui.tabs :label="$label" :items="[[\'label\' => $text, \'href\' => $href]]" />', [
            'label' => '<b>Filtrar</b>',
            'text' => '<i>Todas</i>',
            'href' => '?a=1&b=<2>',
        ]);

        $this->assertStringContainsString('aria-label="&lt;b&gt;Filtrar&lt;/b&gt;"', $html);
        $this->assertStringContainsString('&lt;i&gt;Todas&lt;/i&gt;', $html);
        $this->assertStringContainsString('href="?a=1&amp;b=&lt;2&gt;"', $html);
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringNotContainsString('<i>', $html);
    }
}
