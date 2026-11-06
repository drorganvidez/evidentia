<?php

namespace Tests\Feature\Components;

class NavTest extends ComponentTestCase
{
    /**
     * @return list<array<string, mixed>>
     */
    private function items(): array
    {
        return [
            ['label' => 'Inicio', 'href' => '/inicio', 'icon' => 'layout-dashboard'],
            ['label' => 'Mis evidencias', 'href' => '/evidencias', 'icon' => 'file-text', 'current' => true, 'count' => 6, 'countLabel' => 'pendientes'],
            ['label' => 'Mis reuniones', 'href' => '/reuniones', 'icon' => 'handshake', 'count' => 0],
        ];
    }

    public function test_the_sidebar_is_a_labelled_nav_with_a_list_of_links(): void
    {
        $html = $this->render('<x-ui.nav :items="$items" />', ['items' => $this->items()]);

        $this->assertStringStartsWith('<nav aria-label="Navegación principal"', trim($html));
        $this->assertStringContainsString('<ul class="grid gap-0.75">', $html);
        $this->assertSame(3, substr_count($html, '<li>'));
        $this->assertSame(3, substr_count($html, '<svg'));
        $this->assertStringContainsString('href="/inicio"', $html);
        $this->assertStringContainsString('current:bg-brand-soft current:text-brand-soft-ink current:inset-shadow-hi', $html);
        $this->assertStringContainsString('group-current:text-brand-ink', $html);
        $this->assertStringContainsString('md:max-lg:flex-col', $html, 'The rail classes compact the sidebar on tablets');
        $this->assertStringNotContainsString('grid-cols-5', $html);
        $this->assertStringNotContainsString('md:hidden', $html);
        $this->assertCspSafe($html);
    }

    public function test_aria_current_marks_only_the_current_item(): void
    {
        $html = $this->render('<x-ui.nav :items="$items" />', ['items' => $this->items()]);

        $this->assertSame(1, substr_count($html, 'aria-current="page"'));
        $this->assertMatchesRegularExpression('/<a href="\/evidencias"\s+aria-current="page"/', $html);
        $this->assertDoesNotMatchRegularExpression('/<a href="\/inicio"\s+aria-current/', $html);
    }

    public function test_the_count_is_printed_only_when_given_with_its_hidden_label(): void
    {
        $html = $this->render('<x-ui.nav :items="$items" />', ['items' => $this->items()]);

        $this->assertSame(2, substr_count($html, 'ml-auto rounded-full bg-glass-2'));
        $this->assertStringContainsString('tabular-nums', $html);
        $this->assertStringContainsString('>6<span class="sr-only"> pendientes</span></span>', $html);
        $this->assertStringContainsString('>0</span>', $html);
    }

    public function test_a_heading_prints_the_group_label_and_the_separator(): void
    {
        $html = $this->render('<x-ui.nav :items="$items" heading="Gestión" label="Gestión" />', ['items' => $this->items()]);

        $this->assertMatchesRegularExpression('/^<nav aria-label="Gestión"\s+class="mt-2 border-t border-rule pt-4"/', trim($html));
        $this->assertMatchesRegularExpression('/<p class="mb-2 ml-3 text-2xs font-bold tracking-widest text-ink-muted uppercase md:max-lg:sr-only">\s*Gestión\s*<\/p>/', $html);

        $plain = $this->render('<x-ui.nav :items="$items" />', ['items' => $this->items()]);

        $this->assertDoesNotMatchRegularExpression('/<p[\s>]/', $plain);
        $this->assertStringNotContainsString('class=""', $plain);
        $this->assertStringNotContainsString('border-t', $plain);
    }

    public function test_the_rail_can_be_switched_off_or_forced(): void
    {
        $off = $this->render('<x-ui.nav :items="$items" heading="Gestión" :rail="false" />', ['items' => $this->items()]);

        $this->assertStringNotContainsString('md:max-lg:', $off);
        $this->assertStringContainsString('size-4.5', $off);

        $rail = $this->render('<x-ui.nav variant="rail" :items="$items" heading="Gestión" />', ['items' => $this->items()]);

        $this->assertStringNotContainsString('md:max-lg:', $rail);
        $this->assertStringContainsString('flex flex-col gap-1 rounded-sm px-1 py-2 text-center text-2xs', $rail);
        $this->assertStringContainsString('size-5.5 group-current:text-brand-ink', $rail);
        $this->assertStringContainsString('absolute top-1 right-1 rounded-full bg-glass-2', $rail);
        $this->assertMatchesRegularExpression('/<p class="[^"]*\bsr-only\b[^"]*">\s*Gestión/', $rail);
    }

    public function test_a_long_label_breaks_instead_of_widening_the_rail(): void
    {
        $items = ['items' => [['label' => 'Coordinadores', 'href' => '/c', 'icon' => 'users']]];

        // anywhere also lowers the min-content width, so the word never pushes the rail open; hyphens go first.
        // In the rail the label reaches into the side padding of the link, so a word up to 62 px stays whole.
        $this->assertStringContainsString('<span class="min-w-0 wrap-anywhere hyphens-auto md:max-lg:-mx-1">Coordinadores</span>', $this->render('<x-ui.nav :items="$items" />', $items));
        $this->assertStringContainsString('<span class="min-w-0 -mx-1 wrap-anywhere hyphens-auto">Coordinadores</span>', $this->render('<x-ui.nav variant="rail" :items="$items" />', $items));
        $this->assertStringContainsString('<span class="min-w-0 wrap-anywhere hyphens-auto">Coordinadores</span>', $this->render('<x-ui.nav :items="$items" :rail="false" />', $items));
    }

    public function test_the_bar_variant_is_the_fixed_bottom_navigation_of_the_phone(): void
    {
        $html = $this->render('<x-ui.nav variant="bar" :items="$items" class="mb-2" />', ['items' => $this->items()]);

        $this->assertStringStartsWith('<nav aria-label="Navegación principal (móvil)"', trim($html));
        $this->assertStringContainsString('glass fixed inset-x-3 bottom-safe z-30 rounded-lg p-1.5 md:hidden print-hidden mb-2', $html);
        $this->assertStringContainsString('grid grid-cols-5', $html);
        $this->assertStringContainsString('size-5.5', $html);
        $this->assertStringContainsString('pointer-coarse:min-h-11', $html);
        $this->assertStringContainsString('current:bg-brand-soft current:text-brand-ink', $html);
        $this->assertSame(1, substr_count($html, 'aria-current="page"'));
        $this->assertStringNotContainsString('bg-glass-2', $html, 'The bar shows no counters');
        $this->assertStringNotContainsString('hover:', $html);
        $this->assertCspSafe($html);
    }

    public function test_the_labels_and_the_links_are_escaped(): void
    {
        $html = $this->render('<x-ui.nav :items="$items" heading="<b>Gestión</b>" />', [
            'items' => [['label' => '<b>Inicio</b>', 'href' => '/x?a=1&b=2', 'icon' => 'layout-dashboard', 'count' => 3, 'countLabel' => '<i>x</i>']],
        ]);

        $this->assertStringContainsString('&lt;b&gt;Inicio&lt;/b&gt;', $html);
        $this->assertStringContainsString('&lt;b&gt;Gestión&lt;/b&gt;', $html);
        $this->assertStringContainsString('&lt;i&gt;x&lt;/i&gt;', $html);
        $this->assertStringContainsString('href="/x?a=1&amp;b=2"', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }
}
