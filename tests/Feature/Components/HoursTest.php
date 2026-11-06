<?php

namespace Tests\Feature\Components;

class HoursTest extends ComponentTestCase
{
    public function test_it_formats_the_hours_with_the_spanish_separators(): void
    {
        $this->assertStringContainsString('>1,75 h</span>', $this->render('<x-ui.hours :value="1.75" />'));
        $this->assertStringContainsString('>4,00 h</span>', $this->render('<x-ui.hours :value="4" />'));
        $this->assertStringContainsString('>1.204,50 h</span>', $this->render('<x-ui.hours :value="1204.5" />'));
        $this->assertStringContainsString('>2,50 h</span>', $this->render('<x-ui.hours value="2.50" />'));
        $this->assertStringContainsString('>1.204,5 h</span>', $this->render('<x-ui.hours :value="1204.5" :decimals="1" />'));
        $this->assertStringContainsString('>12</span>', $this->render('<x-ui.hours :value="12" unit="" :decimals="0" />'));
    }

    public function test_the_figure_is_monospaced_and_tabular(): void
    {
        $html = $this->render('<x-ui.hours :value="1.75" class="ml-2" />');

        $this->assertStringContainsString('class="font-mono tabular-nums whitespace-nowrap text-md ml-2"', $html);
        $this->assertStringNotContainsString('title=', $html);
        $this->assertCspSafe($html);
    }

    public function test_the_exact_value_is_read_out_and_shown_on_hover_when_the_rounding_hides_decimals(): void
    {
        $rounded = $this->render('<x-ui.hours :value="1.755" class="ml-2" />');

        // Screen readers get the exact figure as text; the rounded one is only painted, with the exact one on hover
        $this->assertMatchesRegularExpression('/<span\s+class="font-mono tabular-nums whitespace-nowrap text-md ml-2"\s+data-sort-value="1.755">\s*<span aria-hidden="true" title="1,755 h">1,76 h<\/span>\s*<span class="sr-only">1,755 h<\/span>\s*<\/span>/', $rounded);
        $this->assertSame(1, substr_count($rounded, 'title='));
        $this->assertCspSafe($rounded);

        $string = $this->render('<x-ui.hours value="0.333" />');

        $this->assertStringContainsString('<span aria-hidden="true" title="0,333 h">0,33 h</span>', $string);
        $this->assertStringContainsString('<span class="sr-only">0,333 h</span>', $string);

        // At most four decimals of the exact value, with the Spanish separators
        $long = $this->render('<x-ui.hours :value="1234.833333" />');

        $this->assertStringContainsString('title="1.234,8333 h">1.234,83 h</span>', $long);
        $this->assertStringContainsString('<span class="sr-only">1.234,8333 h</span>', $long);

        foreach (['1.75', '4', '1.75000001', '2.5'] as $exact) {
            $html = $this->render('<x-ui.hours :value="'.$exact.'" />');

            $this->assertStringNotContainsString('title=', $html, $exact);
            $this->assertStringNotContainsString('sr-only', $html, $exact);
            $this->assertStringNotContainsString('aria-hidden', $html, $exact);
        }
    }

    public function test_the_figure_is_the_value_the_browser_order_reads(): void
    {
        $this->assertStringContainsString('data-sort-value="2.5">2,50 h</span>', $this->render('<x-ui.hours :value="2.5" />'));
        $this->assertStringContainsString('data-sort-value="10">10,00 h</span>', $this->render('<x-ui.hours value="10" />'));
    }

    public function test_a_missing_value_prints_the_null_text(): void
    {
        $this->assertMatchesRegularExpression('/<span class="font-mono tabular-nums text-ink-muted text-md"\s+data-sort-value="">—<\/span>/', $this->render('<x-ui.hours :value="null" />'));
        $this->assertStringContainsString('>Sin horas</span>', $this->render('<x-ui.hours :value="null" null-text="Sin horas" />'));
        $this->assertStringContainsString('>—</span>', $this->render('<x-ui.hours value="" />'));
    }

    public function test_sizes_and_muted_map_to_literal_classes(): void
    {
        $this->assertStringContainsString('text-sm', $this->render('<x-ui.hours :value="1" size="sm" />'));
        $this->assertStringContainsString('text-md', $this->render('<x-ui.hours :value="1" />'));
        $this->assertStringContainsString('text-lg', $this->render('<x-ui.hours :value="1" size="lg" />'));
        $this->assertStringContainsString('text-4xl', $this->render('<x-ui.hours :value="1" size="xl" />'));
        $this->assertStringContainsString('text-ink-muted', $this->render('<x-ui.hours :value="1" muted />'));
        $this->assertStringNotContainsString('text-ink-muted', $this->render('<x-ui.hours :value="1" />'));
    }
}
