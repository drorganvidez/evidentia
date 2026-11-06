<?php

namespace Tests\Feature\Components;

class FooterTest extends ComponentTestCase
{
    public function test_it_is_a_footer_with_the_brand_the_links_and_the_institution(): void
    {
        $html = $this->render('<x-ui.footer :version="false" class="mt-8" />');

        $this->assertStringStartsWith('<footer', trim($html));
        $this->assertStringContainsString('print-hidden glass mt-2 flex min-h-15 flex-wrap items-center gap-3 rounded-lg p-4 text-sm md:gap-6 md:px-5 md:py-3 mt-8', $html);
        $this->assertStringContainsString('aria-label="Evidentia, inicio"', $html);
        $this->assertStringContainsString('<span>Evidentia</span>', $html);
        $this->assertStringContainsString('<nav aria-label="Enlaces del pie">', $html);
        $this->assertSame(4, substr_count($html, '<li>'));
        $this->assertStringContainsString('Universidad de Sevilla · ETSII', $html);
        $this->assertStringContainsString('md:ml-auto md:w-auto', $html);
        $this->assertStringEndsWith('</footer>', trim($html));
        $this->assertCspSafe($html);
    }

    public function test_the_help_links_keep_their_order_and_the_external_ones_open_in_a_new_tab(): void
    {
        $html = $this->render('<x-ui.footer :version="false" />');

        $labels = [];
        preg_match_all('/<a href="([^"]+)"[^>]*>(.*?)<\/a>/s', $html, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            if (str_contains($match[0], 'aria-label="Evidentia, inicio"')) {
                continue;
            }

            $labels[] = trim(strip_tags(preg_replace('/<span class="sr-only">.*?<\/span>/s', '', $match[2])));
        }

        $this->assertSame(['Repositorio', 'GPL-3.0', 'Ayuda', 'Sugerencias'], $labels);
        $this->assertSame(2, substr_count($html, 'target="_blank" rel="noopener noreferrer"'));
        $this->assertSame(2, substr_count($html, '<span class="sr-only">(se abre en una pestaña nueva)</span>'));
        $this->assertMatchesRegularExpression('#<a href="https://github.com/drorganvidez/evidentia"[^>]*target="_blank"#', $html);
        $this->assertDoesNotMatchRegularExpression('#<a href="[^"]*suggestionsmailbox"[^>]*target#', $html);
        $this->assertSame(4, substr_count($html, 'class="shrink-0 size-4"'), 'One icon per link');
    }

    public function test_the_version_pill_is_printed_only_with_a_version_and_links_to_the_updates_when_asked(): void
    {
        $plain = $this->render('<x-ui.footer version="4.0.0" />');

        $this->assertStringContainsString('v4.0.0', $plain);

        $this->assertStringContainsString('<span class="inline-flex min-h-6 items-center rounded-full bg-brand-soft px-2 font-mono text-2xs font-semibold text-brand-soft-ink no-underline">v4.0.0</span>', $plain);
        $this->assertStringNotContainsString('title=', $plain);

        $linked = $this->render('<x-ui.footer version="v4.0.0" version-href="/novedades" />');

        $this->assertMatchesRegularExpression('#<a href="/novedades" title="Ver las novedades de la versión 4.0.0"\s+class="inline-flex min-h-6 items-center rounded-full bg-brand-soft#', $linked);
        $this->assertStringContainsString('>v4.0.0</a>', $linked);

        $hidden = $this->render('<x-ui.footer :version="false" version-href="/novedades" />');

        $this->assertStringNotContainsString('bg-brand-soft', $hidden);
        $this->assertStringNotContainsString('/novedades', $hidden);
    }

    public function test_the_default_version_comes_from_the_version_file(): void
    {
        $path = base_path('.version');
        $existed = is_file($path);
        $previous = $existed ? file_get_contents($path) : null;

        try {
            file_put_contents($path, "4.2.1\n");
            $this->assertStringContainsString('>v4.2.1</span>', $this->render('<x-ui.footer />'));

            file_put_contents($path, '');
            $this->assertStringNotContainsString('bg-brand-soft', $this->render('<x-ui.footer />'));

            unlink($path);
            $this->assertStringNotContainsString('bg-brand-soft', $this->render('<x-ui.footer />'));
        } finally {
            if ($existed) {
                file_put_contents($path, (string) $previous);
            } elseif (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_compact_is_one_centred_line_without_glass_nor_mark(): void
    {
        $html = $this->render('<x-ui.footer compact version="4.0.0" />');

        $this->assertStringContainsString('print-hidden flex flex-wrap items-center justify-center gap-x-4 gap-y-1 px-4 py-4 text-xs text-ink', $html);
        $this->assertStringNotContainsString('glass', $html);
        $this->assertStringNotContainsString('aria-label="Evidentia, inicio"', $html);
        $this->assertStringNotContainsString('<svg', $html);
        $this->assertStringContainsString('<nav aria-label="Enlaces del pie">', $html);
        $this->assertStringContainsString('>v4.0.0</span>', $html);
        $this->assertStringContainsString('Universidad de Sevilla · ETSII', $html);
        $this->assertStringNotContainsString('md:ml-auto', $html);
        $this->assertCspSafe($html);
    }

    public function test_links_and_meta_can_be_replaced_or_removed_and_are_escaped(): void
    {
        $html = $this->render('<x-ui.footer :version="false" :links="$links" meta="<b>ETSII</b>" />', [
            'links' => [['label' => '<i>Ayuda</i>', 'href' => '/ayuda?a=1&b=2', 'icon' => 'circle-help']],
        ]);

        $this->assertSame(1, substr_count($html, '<li>'));
        $this->assertStringContainsString('&lt;i&gt;Ayuda&lt;/i&gt;', $html);
        $this->assertStringContainsString('href="/ayuda?a=1&amp;b=2"', $html);
        $this->assertStringContainsString('&lt;b&gt;ETSII&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringNotContainsString('target=', $html);

        $bare = $this->render('<x-ui.footer :version="false" :links="[]" :meta="false" />');

        $this->assertStringNotContainsString('<nav', $bare);
        $this->assertStringNotContainsString('Universidad', $bare);
    }
}
