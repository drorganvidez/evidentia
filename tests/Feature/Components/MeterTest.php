<?php

namespace Tests\Feature\Components;

class MeterTest extends ComponentTestCase
{
    public function test_a_bar_segment_takes_its_width_and_colour_from_classes(): void
    {
        $html = $this->render('<x-ui.meter :segments="$segments" label="68 % aceptado" class="mt-2" />', [
            'segments' => [['value' => 68, 'tone' => 'accepted']],
        ]);

        $this->assertStringContainsString('role="img" aria-label="68 % aceptado"', $html);
        $this->assertStringContainsString('class="flex gap-1 overflow-hidden rounded-full h-3 bg-rule mt-2"', $html);
        $this->assertStringContainsString('w-68/100', $html);
        $this->assertStringContainsString('bg-accepted', $html);
        $this->assertStringNotContainsString('bg-linear-to-r', $html);
        $this->assertCspSafe($html);
    }

    public function test_the_gradient_dresses_a_single_segment_only(): void
    {
        $single = $this->render('<x-ui.meter :segments="$segments" gradient />', ['segments' => [['value' => 68, 'tone' => 'brand']]]);
        $stacked = $this->render('<x-ui.meter :segments="$segments" gradient />', [
            'segments' => [['value' => 40, 'tone' => 'accepted'], ['value' => 20, 'tone' => 'pending']],
        ]);

        $this->assertStringContainsString('bg-linear-to-r from-aurora-500 to-cyan-400 shadow-glow-sm', $single);
        $this->assertStringNotContainsString('bg-brand', $single);
        $this->assertStringNotContainsString('bg-linear-to-r', $stacked);
        $this->assertStringContainsString('bg-accepted', $stacked);
        $this->assertStringContainsString('bg-pending', $stacked);
    }

    public function test_the_values_are_clipped_to_100_sharing_the_remainder(): void
    {
        $over = $this->render('<x-ui.meter :segments="$segments" />', [
            'segments' => [['value' => 50, 'tone' => 'accepted'], ['value' => 30, 'tone' => 'pending'], ['value' => 40, 'tone' => 'rejected']],
        ]);

        $this->assertStringContainsString('w-42/100', $over);
        $this->assertStringContainsString('w-25/100', $over);
        $this->assertStringContainsString('w-33/100', $over);

        $halves = $this->render('<x-ui.meter :segments="$segments" />', [
            'segments' => [['value' => 60, 'tone' => 'accepted'], ['value' => 60, 'tone' => 'pending']],
        ]);

        $this->assertSame(2, substr_count($halves, 'w-50/100'));

        $fractions = $this->render('<x-ui.meter :segments="$segments" />', [
            'segments' => [['value' => 33.3, 'tone' => 'accepted'], ['value' => 33.3, 'tone' => 'pending']],
        ]);

        $this->assertStringContainsString('w-34/100', $fractions);
        $this->assertStringContainsString('w-33/100', $fractions);

        $clamped = $this->render('<x-ui.meter :segments="$segments" />', [
            'segments' => [['value' => 150, 'tone' => 'accepted'], ['value' => -5, 'tone' => 'pending']],
        ]);

        $this->assertStringContainsString('w-100/100', $clamped);
        $this->assertStringNotContainsString('bg-pending', $clamped);
    }

    public function test_without_a_label_the_meter_is_hidden_from_assistive_technology(): void
    {
        $bar = $this->render('<x-ui.meter :segments="$segments" />', ['segments' => [['value' => 10, 'tone' => 'draft']]]);
        $ring = $this->render('<x-ui.meter shape="ring" :segments="$segments" />', ['segments' => [['value' => 10, 'tone' => 'draft']]]);

        $this->assertStringContainsString('aria-hidden="true"', $bar);
        $this->assertStringNotContainsString('role="img"', $bar);
        $this->assertMatchesRegularExpression('/<svg viewBox="0 0 100 100" class="size-23"\s+aria-hidden="true">/', $ring);
        $this->assertStringNotContainsString('role="img"', $ring);
    }

    public function test_the_ring_draws_one_circle_per_segment_with_the_dash_pattern(): void
    {
        $html = $this->render('<x-ui.meter shape="ring" :segments="$segments" value="48 %" label="48 % aceptado" />', [
            'segments' => [['value' => 48, 'tone' => 'accepted'], ['value' => 36, 'tone' => 'pending'], ['value' => 16, 'tone' => 'rejected']],
        ]);

        $this->assertMatchesRegularExpression('/<svg viewBox="0 0 100 100" class="size-23"\s+role="img" aria-label="48 % aceptado">/', $html);
        $this->assertStringContainsString('class="stroke-rule"', $html);
        $this->assertSame(3, substr_count($html, 'pathLength="100"'));
        $this->assertMatchesRegularExpression('/stroke-dasharray="48 52"\s+stroke-dashoffset="0"\s+transform="rotate\(-90 50 50\)"\s+class="stroke-accepted"/', $html);
        $this->assertMatchesRegularExpression('/stroke-dasharray="36 64"\s+stroke-dashoffset="-48"\s+transform="rotate\(-90 50 50\)"\s+class="stroke-pending"/', $html);
        $this->assertMatchesRegularExpression('/stroke-dasharray="16 84"\s+stroke-dashoffset="-84"\s+transform="rotate\(-90 50 50\)"\s+class="stroke-rejected"/', $html);
        $this->assertMatchesRegularExpression('/<span aria-hidden="true"\s+class="absolute font-mono font-bold tracking-tighter tabular-nums text-base">48 %<\/span>/', $html);
        $this->assertStringContainsString('drop-shadow-[0_6px_14px_var(--glow)]', $html);
        $this->assertCspSafe($html);
    }

    public function test_the_small_ring_shrinks_the_value_and_can_drop_the_track(): void
    {
        $html = $this->render('<x-ui.meter shape="ring" :segments="$segments" value="7" size="sm" :track="false" />', [
            'segments' => [['value' => 70, 'tone' => 'brand']],
        ]);

        $this->assertStringContainsString('class="size-19"', $html);
        $this->assertStringContainsString('tabular-nums text-sm">7</span>', $html);
        $this->assertStringContainsString('stroke-brand', $html);
        $this->assertStringNotContainsString('stroke-rule', $html);
    }

    public function test_the_legend_lists_each_segment_with_its_dot_and_value(): void
    {
        $html = $this->render('<x-ui.meter :segments="$segments" legend label="Horas por tipo" class="mt-4" />', [
            'segments' => [
                ['value' => 38, 'tone' => 'accepted', 'label' => 'Evidencias', 'text' => '4,00 h'],
                ['value' => 24, 'tone' => 'notice', 'label' => 'Reuniones'],
            ],
        ]);

        $this->assertStringContainsString('<div class="grid gap-2 mt-4">', $html);
        $this->assertStringContainsString('role="img" aria-label="Horas por tipo"', $html);
        $this->assertStringContainsString('<ul class="flex flex-wrap gap-x-4 gap-y-1 text-sm">', $html);
        $this->assertSame(2, substr_count($html, '<li class="inline-flex items-center gap-1.5 text-ink-muted">'));
        $this->assertStringContainsString('size-2 shrink-0 rounded-full bg-accepted', $html);
        $this->assertStringContainsString('size-2 shrink-0 rounded-full bg-notice', $html);
        $this->assertStringContainsString('Evidencias', $html);
        $this->assertStringContainsString('>4,00 h</span>', $html);
        $this->assertStringContainsString('>24 %</span>', $html);
        $this->assertCspSafe($html);
    }

    public function test_sizes_and_track_map_to_literal_classes(): void
    {
        $small = $this->render('<x-ui.meter :segments="$segments" size="sm" :track="false" />', ['segments' => [['value' => 10, 'tone' => 'draft']]]);

        $this->assertStringContainsString('h-1.5', $small);
        $this->assertStringNotContainsString('bg-rule', $small);
        $this->assertStringContainsString('bg-draft', $small);
    }
}
