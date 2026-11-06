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

    public function test_the_raw_value_goes_to_the_title_only_when_the_rounding_hides_decimals(): void
    {
        $rounded = $this->render('<x-ui.hours :value="1.755" />');

        $this->assertStringContainsString('title="1.755"', $rounded);
        $this->assertStringContainsString('>1,76 h</span>', $rounded);

        $exact = $this->render('<x-ui.hours :value="1.75" />');
        $this->assertStringNotContainsString('title=', $exact);

        $string = $this->render('<x-ui.hours value="0.333" />');
        $this->assertStringContainsString('title="0.333"', $string);
        $this->assertStringContainsString('>0,33 h</span>', $string);
    }

    public function test_a_missing_value_prints_the_null_text(): void
    {
        $this->assertStringContainsString('<span class="font-mono tabular-nums text-ink-muted text-md">—</span>', $this->render('<x-ui.hours :value="null" />'));
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
