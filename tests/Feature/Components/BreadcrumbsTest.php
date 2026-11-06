<?php

namespace Tests\Feature\Components;

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
        $this->assertStringContainsString('<ol class="flex items-center gap-2 overflow-hidden text-md font-medium whitespace-nowrap text-ink-muted">', $html);
        $this->assertSame(1, substr_count($html, 'aria-current="page"'));
        $this->assertStringContainsString('<li aria-current="page" class="truncate font-semibold text-ink">Montaje del photocall</li>', $html);
        $this->assertSame(2, substr_count($html, '<a href='), 'Only the previous items are links');
        $this->assertStringNotContainsString('href="/evidencias" aria-current', $html);
        $this->assertCspSafe($html);
    }

    public function test_the_previous_items_link_back_with_a_decorative_separator_and_hide_on_the_phone(): void
    {
        $html = $this->render('<x-ui.breadcrumbs :items="$items" />', ['items' => $this->items()]);

        $this->assertSame(2, substr_count($html, '<li class="hidden items-center gap-2 md:inline-flex">'));
        $this->assertMatchesRegularExpression('#<a href="/"\s+class="inline-flex min-h-6 items-center text-ink-muted no-underline transition hover:text-ink">Inicio</a>#', $html);
        $this->assertSame(2, substr_count($html, '<svg'));
        $this->assertSame(2, substr_count($html, 'aria-hidden="true"'));
        $this->assertStringNotContainsString('›', $html);
        $this->assertStringNotContainsString('/', strip_tags(preg_replace('/<svg.*?<\/svg>/s', '', $html)), 'No text separator');
    }

    public function test_a_single_item_is_just_the_current_page_and_the_label_can_change(): void
    {
        $html = $this->render('<x-ui.breadcrumbs :items="$items" label="Ruta" class="flex-1" />', ['items' => [['label' => 'Inicio']]]);

        $this->assertStringContainsString('<nav aria-label="Ruta" class="min-w-0 flex-1">', $html);
        $this->assertStringContainsString('<li aria-current="page" class="truncate font-semibold text-ink">Inicio</li>', $html);
        $this->assertStringNotContainsString('<a', $html);
        $this->assertStringNotContainsString('<svg', $html);
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
