<?php

namespace Tests\Feature\Components;

class BrandTest extends ComponentTestCase
{
    public function test_it_links_home_with_an_accessible_label_and_a_decorative_mark(): void
    {
        $html = $this->render('<x-ui.brand />');

        $this->assertStringContainsString('<a href="'.url('/').'" aria-label="Evidentia, inicio"', $html);
        // the mark is the drawing of the favicon, with a gradient of its own
        $this->assertMatchesRegularExpression('/<svg aria-hidden="true" focusable="false" viewBox="0 0 64 64"\s+class="size-10\.5 shrink-0 rounded-\[28%\] shadow-glow">/', $html);
        $this->assertMatchesRegularExpression('/<linearGradient id="(brand-mark-\w{6})"[^>]*>.*fill="url\(#\1\)"/s', $html);
        $this->assertStringContainsString('stop-color="#7d6af5"', $html);
        $this->assertStringContainsString('stop-color="#4fc3e8"', $html);
        $this->assertStringContainsString('Evidentia</span>', $html);
        $this->assertStringNotContainsString('text-ink-muted', $html);
        $this->assertCspSafe($html);
    }

    public function test_the_link_the_label_and_the_layout_classes_can_be_changed(): void
    {
        $html = $this->render('<x-ui.brand href="/inicio" label="Inicio de Evidentia" class="hidden md:flex" />');

        $this->assertStringContainsString('href="/inicio"', $html);
        $this->assertStringContainsString('aria-label="Inicio de Evidentia"', $html);
        $this->assertStringContainsString('hidden md:flex', $html);
    }

    public function test_the_subtitle_is_printed_only_when_given_escaped_and_joins_the_name_of_the_link(): void
    {
        $html = $this->render('<x-ui.brand subtitle="Jornadas <b>InnoSoft</b> 2026" />');

        $this->assertStringContainsString('aria-label="Evidentia, Jornadas &lt;b&gt;InnoSoft&lt;/b&gt; 2026, inicio"', $html);
        $this->assertStringContainsString('text-ink">Jornadas &lt;b&gt;InnoSoft&lt;/b&gt; 2026</span>', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }

    public function test_each_size_scales_the_mark_and_the_name(): void
    {
        $sm = $this->render('<x-ui.brand size="sm" />');
        $md = $this->render('<x-ui.brand />');
        $lg = $this->render('<x-ui.brand size="lg" />');

        $this->assertStringContainsString('class="size-6.5 shrink-0', $sm);
        $this->assertStringContainsString('text-sm block', $sm);

        $this->assertStringContainsString('class="size-10.5 shrink-0', $md);
        $this->assertStringContainsString('text-xl block', $md);

        $this->assertStringContainsString('class="size-12 shrink-0', $lg);
        $this->assertStringContainsString('text-2xl block', $lg);
    }

    public function test_mark_only_keeps_the_label_and_drops_the_name_and_the_subtitle(): void
    {
        $html = $this->render('<x-ui.brand mark-only subtitle="Jornadas InnoSoft 2026" />');

        $this->assertStringContainsString('aria-label="Evidentia, inicio"', $html);
        $this->assertStringContainsString('<svg aria-hidden="true"', $html);
        $this->assertStringNotContainsString('Evidentia</span>', $html);
        $this->assertStringNotContainsString('Jornadas InnoSoft 2026', $html);
    }
}
