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
            ['label' => 'Mis evidencias', 'href' => '/evidencias'],
            ['label' => 'Montaje del photocall'],
        ];
    }

    public function test_the_last_item_is_the_current_page_without_a_link(): void
    {
        $html = $this->render('<x-ui.breadcrumbs :items="$items" />', ['items' => $this->items()]);

        $this->assertStringStartsWith('<nav aria-label="Migas de pan" class="min-w-0">', trim($html));
        // The list clips its overflow: the padding taken back by the negative margin leaves room for the focus ring
        $this->assertMatchesRegularExpression('/<ol\s+class="-m-1.5 flex items-center gap-2 overflow-hidden p-1.5 text-md font-medium whitespace-nowrap text-ink-muted">/', $html);
        $this->assertSame(1, substr_count($html, 'aria-current="page"'));
        // It takes the room the links leave and never drops below a readable minimum
        $this->assertStringContainsString('<li aria-current="page" class="min-w-16 flex-1 truncate font-semibold text-ink">Montaje del photocall</li>', $html);
        $this->assertSame(2, substr_count($html, '<a href='), 'Only the previous items are links');
        $this->assertStringNotContainsString('href="/evidencias" aria-current', $html);
        $this->assertCspSafe($html);
    }

    public function test_the_previous_items_link_back_with_a_decorative_separator_and_hide_on_the_phone(): void
    {
        $html = $this->render('<x-ui.breadcrumbs :items="$items" />', ['items' => $this->items()]);

        // Below lg only the parent of the current page shows; every previous item shrinks with an ellipsis
        $this->assertSame(1, substr_count($html, '<li class="hidden min-w-0 items-center gap-2 lg:inline-flex">'));
        $this->assertSame(1, substr_count($html, '<li class="hidden min-w-0 items-center gap-2 md:inline-flex">'));
        $this->assertLessThan(strpos($html, 'md:inline-flex'), strpos($html, 'lg:inline-flex'), 'The parent is the last link');
        $this->assertMatchesRegularExpression('#<a href="/"\s+class="inline-flex min-h-6 min-w-0 items-center text-ink-muted no-underline transition hover:text-ink"><span\s+class="truncate">Inicio</span></a>#', $html);
        $this->assertSame(2, substr_count($html, 'class="size-4 shrink-0 opacity-60"'), 'The separators never shrink');
        $this->assertSame(2, substr_count($html, '<svg'));
        $this->assertSame(2, substr_count($html, 'aria-hidden="true"'));
        $this->assertStringNotContainsString('›', $html);
        $this->assertStringNotContainsString('/', strip_tags(preg_replace('/<svg.*?<\/svg>/s', '', $html)), 'No text separator');
    }

    public function test_a_single_item_is_just_the_current_page_and_the_label_can_change(): void
    {
        $html = $this->render('<x-ui.breadcrumbs :items="$items" label="Ruta" class="flex-1" />', ['items' => [['label' => 'Inicio']]]);

        $this->assertStringContainsString('<nav aria-label="Ruta" class="min-w-0 flex-1">', $html);
        $this->assertStringContainsString('<li aria-current="page" class="min-w-16 flex-1 truncate font-semibold text-ink">Inicio</li>', $html);
        $this->assertStringNotContainsString('<a', $html);
        $this->assertStringNotContainsString('<svg', $html);
    }

    public function test_a_collection_works_as_the_items(): void
    {
        $html = $this->render('<x-ui.breadcrumbs :items="$items" />', ['items' => collect($this->items())->keyBy('label')]);

        $this->assertStringContainsString('>Montaje del photocall</li>', $html);
        $this->assertSame(1, substr_count($html, 'aria-current="page"'));
        $this->assertSame(2, substr_count($html, '<a href='));

        $empty = $this->render('<x-ui.breadcrumbs :items="$items" />', ['items' => collect()]);

        $this->assertStringNotContainsString('<li', $empty);

        // Arrayable items, as a view model or a Fluent would give
        $fluent = $this->render('<x-ui.breadcrumbs :items="$items" />', [
            'items' => collect([new Fluent(['label' => 'Inicio', 'href' => '/']), new Fluent(['label' => 'Actas'])]),
        ]);

        $this->assertMatchesRegularExpression('/<span\s+class="truncate">Inicio<\/span>/', $fluent);
        $this->assertStringContainsString('>Actas</li>', $fluent);
    }

    public function test_the_labels_and_the_links_are_escaped(): void
    {
        $html = $this->render('<x-ui.breadcrumbs :items="$items" />', [
            'items' => [['label' => '<b>Inicio</b>', 'href' => '/x?a=1&b=2'], ['label' => '<i>Actual</i>']],
        ]);

        $this->assertStringContainsString('&lt;b&gt;Inicio&lt;/b&gt;', $html);
        $this->assertStringContainsString('&lt;i&gt;Actual&lt;/i&gt;', $html);
        $this->assertStringContainsString('href="/x?a=1&amp;b=2"', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }
}
