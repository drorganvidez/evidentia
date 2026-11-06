<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Fluent;
use Illuminate\View\ViewException;
use InvalidArgumentException;

class BarListTest extends ComponentTestCase
{
    private const ITEMS = <<<'PHP'
        [
            ['label' => 'Logística', 'value' => 12, 'href' => '/comites/7', 'hue' => 30],
            ['label' => 'Programa', 'value' => 9, 'hue' => 270],
            ['label' => 'Igualdad', 'value' => 3],
        ]
        PHP;

    private function list(string $props = ''): string
    {
        return $this->render('<x-ui.bar-list label="Evidencias pendientes por comité" :items="'.self::ITEMS.'" '.$props.' />');
    }

    public function test_it_is_a_labelled_ordered_list_with_a_bar_per_item(): void
    {
        $html = $this->list('class="mt-4"');

        $this->assertStringContainsString('<ol aria-label="Evidencias pendientes por comité" class="grid gap-3 mt-4">', $html);
        $this->assertSame(3, substr_count($html, '<li class="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-x-4 gap-y-1.5 text-md">'));
        $this->assertSame(3, substr_count($html, '<span class="col-span-full h-2 overflow-hidden rounded-full bg-rule" aria-hidden="true">'));
        $this->assertStringContainsString('bg-brand w-100/100', $html);
        $this->assertStringContainsString('bg-brand w-75/100', $html);
        $this->assertStringContainsString('bg-brand w-25/100', $html);
        $this->assertStringContainsString('</ol>', $html);
        $this->assertUniqueIds($html);
        $this->assertCspSafe($html);
    }

    public function test_an_explicit_max_scales_the_bars_and_values_above_it_are_clamped(): void
    {
        $this->assertStringContainsString('w-60/100', $this->list(':max="20"'));
        $this->assertStringContainsString('w-15/100', $this->list(':max="20"'));
        $this->assertStringContainsString('w-100/100', $this->list(':max="9"'));
        $this->assertSame(3, substr_count($this->list(':max="0"'), 'w-0/100'));
    }

    public function test_the_label_is_a_link_only_with_an_href_and_the_dot_follows_hue_or_tone(): void
    {
        $html = $this->list();

        $this->assertSame(1, substr_count($html, '<a href="/comites/7"'));
        $this->assertMatchesRegularExpression('/<a href="\/comites\/7"\s+class="flex min-w-0 items-center gap-2 font-medium text-ink no-underline transition hover:text-brand-ink pointer-coarse:min-h-11">/', $html);
        $this->assertSame(1, substr_count($html, 'pointer-coarse:min-h-11'), 'Only the linked label grows to 44 px');
        $this->assertStringContainsString('committee-dot hue-30', $html);
        $this->assertStringContainsString('committee-dot hue-270', $html);
        $this->assertSame(2, substr_count($html, 'committee-dot'));
        $this->assertStringNotContainsString('orb-glow', $html);
        $this->assertMatchesRegularExpression('/<span class="flex min-w-0 items-center gap-2 font-medium text-ink">\s*<span class="truncate">Igualdad<\/span>/', $html);

        $toned = $this->render('<x-ui.bar-list label="Horas por tipo" :items="[[\'label\' => \'Evidencias\', \'value\' => 4, \'tone\' => \'accepted\']]" />');

        $this->assertStringContainsString('[--orb:var(--accepted)]', $toned);
        $this->assertStringContainsString('bg-accepted w-100/100', $toned);
    }

    public function test_the_figure_follows_the_value_format_or_the_given_text(): void
    {
        $this->assertStringContainsString('<span class="font-mono font-semibold tabular-nums">12</span>', $this->list());
        $this->assertStringContainsString('>1.200</span>', $this->render('<x-ui.bar-list label="x" :items="[[\'label\' => \'a\', \'value\' => 1200]]" />'));
        $this->assertStringContainsString('>2,50 h</span>', $this->render('<x-ui.bar-list label="x" :items="[[\'label\' => \'a\', \'value\' => 2.5]]" value-format="hours" />'));
        $this->assertStringContainsString('>4 de 20</span>', $this->render('<x-ui.bar-list label="x" :items="[[\'label\' => \'a\', \'value\' => 4, \'text\' => \'4 de 20\']]" />'));
    }

    public function test_the_label_is_required_and_the_texts_are_escaped(): void
    {
        try {
            $this->render('<x-ui.bar-list :items="[]" />');
            $this->fail('A bar list without a label was rendered');
        } catch (ViewException $exception) {
            while ($exception->getPrevious() !== null) {
                $exception = $exception->getPrevious();
            }

            $this->assertInstanceOf(InvalidArgumentException::class, $exception);
        }

        $html = $this->render('<x-ui.bar-list :label="$label" :items="[[\'label\' => $text, \'value\' => 1, \'text\' => $text]]" />', [
            'label' => '<b>Lista</b>',
            'text' => '<i>x</i>',
        ]);

        $this->assertStringContainsString('aria-label="&lt;b&gt;Lista&lt;/b&gt;"', $html);
        $this->assertSame(2, substr_count($html, '&lt;i&gt;x&lt;/i&gt;'));
        $this->assertStringNotContainsString('<b>', $html);
    }

    public function test_an_empty_list_prints_an_empty_ol(): void
    {
        $html = $this->render('<x-ui.bar-list label="Nada" :items="[]" />');

        $this->assertMatchesRegularExpression('/<ol aria-label="Nada" class="grid gap-3">\s*<\/ol>/', $html);
    }

    public function test_it_takes_collections_of_arrays_or_other_arrayables(): void
    {
        $html = $this->render('<x-ui.bar-list label="Por comité" :items="$items" />', [
            'items' => collect([
                7 => ['label' => 'Logística', 'value' => 12, 'href' => '/comites/7', 'hue' => 30],
                3 => new Fluent(['label' => 'Programa', 'value' => 6, 'hue' => 270]),
            ]),
        ]);

        $this->assertSame(2, substr_count($html, '<li class="grid'));
        $this->assertStringContainsString('<a href="/comites/7"', $html);
        $this->assertStringContainsString('bg-brand w-100/100', $html);
        $this->assertStringContainsString('bg-brand w-50/100', $html);
        $this->assertStringContainsString('committee-dot hue-270', $html);
        $this->assertStringContainsString('<span class="truncate">Programa</span>', $html);

        $empty = $this->render('<x-ui.bar-list label="Nada" :items="$items" />', ['items' => collect()]);

        $this->assertMatchesRegularExpression('/<ol aria-label="Nada" class="grid gap-3">\s*<\/ol>/', $empty);
    }
}
