<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Fluent;

class BreadcrumbsTest extends ComponentTestCase
{
    /**
     * @return list<array<string, mixed>>
     */
    private function items(): array
    {
        return [
            ['label' => 'Inicio', 'href' => '/'],
            ['label' => 'Reuniones', 'href' => '/reuniones'],
            ['label' => 'Convocatorias', 'href' => '/convocatorias'],
            ['label' => 'Nueva convocatoria'],
        ];
    }

    public function test_it_is_one_link_back_to_the_parent(): void
    {
        $html = $this->render('<x-ui.breadcrumbs :items="$items" class="flex-1" />', ['items' => $this->items()]);

        $this->assertStringStartsWith('<div class="min-w-0 flex-1">', trim($html));
        $this->assertSame(1, substr_count($html, '<a '));
        $this->assertStringContainsString('href="/convocatorias" data-back', $html);
        $this->assertStringContainsString('<span class="truncate"><span class="sr-only">Volver a </span>Convocatorias</span>', $html);
        $this->assertStringContainsString('pointer-coarse:min-h-11', $html);
        $this->assertSame(1, substr_count($html, 'aria-hidden="true"'), 'The arrow is decorative');
        $this->assertStringNotContainsString('Inicio', $html);
        $this->assertStringNotContainsString('Nueva convocatoria', $html, 'The h1 already names the page');
        $this->assertStringNotContainsString('aria-current', $html);
        $this->assertCspSafe($html);
    }

    public function test_a_page_of_the_menu_has_no_link(): void
    {
        foreach ([[['label' => 'Inicio', 'href' => '/'], ['label' => 'Mis evidencias']], [['label' => 'Panel del curso']], []] as $items) {
            $html = $this->render('<x-ui.breadcrumbs :items="$items" />', ['items' => $items]);

            $this->assertStringNotContainsString('<a', $html);
            $this->assertStringContainsString('<div class="min-w-0">', $html);
        }
    }

    public function test_an_item_without_a_link_is_skipped(): void
    {
        $html = $this->render('<x-ui.breadcrumbs :items="$items" />', ['items' => [
            ['label' => 'Inicio', 'href' => '/'],
            ['label' => 'Reuniones', 'href' => '/reuniones'],
            ['label' => 'Actas'],
            ['label' => 'Acta de logística'],
        ]]);

        $this->assertStringContainsString('href="/reuniones"', $html);
        $this->assertStringNotContainsString('Actas', $html);
    }

    public function test_a_collection_of_arrayables_works_as_the_items(): void
    {
        $html = $this->render('<x-ui.breadcrumbs :items="$items" />', [
            'items' => collect([new Fluent(['label' => 'Inicio', 'href' => '/']), new Fluent(['label' => 'Mis evidencias', 'href' => '/e']), new Fluent(['label' => 'Photocall'])])->keyBy('label'),
        ]);

        $this->assertStringContainsString('href="/e"', $html);
        $this->assertStringContainsString('</span>Mis evidencias</span>', $html);
    }

    public function test_the_label_and_the_link_are_escaped(): void
    {
        $html = $this->render('<x-ui.breadcrumbs :items="$items" />', [
            'items' => [['label' => 'Inicio', 'href' => '/'], ['label' => '<b>Lista</b>', 'href' => '/x?a=1&b=2'], ['label' => 'Actual']],
        ]);

        $this->assertStringContainsString('&lt;b&gt;Lista&lt;/b&gt;', $html);
        $this->assertStringContainsString('href="/x?a=1&amp;b=2"', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }
}
