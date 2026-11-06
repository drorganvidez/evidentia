<?php

namespace Tests\Feature\Components;

class BrandTest extends ComponentTestCase
{
    public function test_it_links_home_with_an_accessible_label_and_a_decorative_mark(): void
    {
        $html = $this->render('<x-ui.brand />');

        $this->assertStringContainsString('<a href="'.url('/').'" aria-label="Evidentia, inicio"', $html);
        $this->assertMatchesRegularExpression('/<span aria-hidden="true"\s+class="[^"]*before:content-\[\'e\'\]"><\/span>/', $html);
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
        $this->assertStringContainsString('text-ink-muted">Jornadas &lt;b&gt;InnoSoft&lt;/b&gt; 2026</span>', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }

    public function test_each_size_scales_the_mark_and_the_name(): void
    {
        $sm = $this->render('<x-ui.brand size="sm" />');
        $md = $this->render('<x-ui.brand />');
        $lg = $this->render('<x-ui.brand size="lg" />');

        $this->assertStringContainsString('size-6.5 rounded-xs text-md', $sm);
        $this->assertStringContainsString('text-sm block', $sm);

        $this->assertStringContainsString('size-10.5 rounded-[0.8125rem] text-2xl', $md);
        $this->assertStringContainsString('text-xl block', $md);

        $this->assertStringContainsString('size-12 rounded-md text-3xl', $lg);
        $this->assertStringContainsString('text-2xl block', $lg);
    }

    public function test_mark_only_keeps_the_label_and_drops_the_name_and_the_subtitle(): void
    {
        $html = $this->render('<x-ui.brand mark-only subtitle="Jornadas InnoSoft 2026" />');

        $this->assertStringContainsString('aria-label="Evidentia, inicio"', $html);
        $this->assertStringContainsString('before:content-[\'e\']', $html);
        $this->assertStringNotContainsString('Evidentia</span>', $html);
        $this->assertStringNotContainsString('Jornadas InnoSoft 2026', $html);
    }
}
