<?php

namespace Tests\Feature\Components;

use Illuminate\View\ViewException;
use InvalidArgumentException;

class StatTest extends ComponentTestCase
{
    public function test_with_href_the_whole_card_is_one_link_with_an_arrow(): void
    {
        $html = $this->render('<x-ui.stat label="Horas aceptadas" value="4,00" unit="h" foot="de 8,25 h declaradas" href="/horas" aria-label="Horas aceptadas 4,00 h de 8,25 h declaradas" class="mt-2" />');

        $this->assertStringContainsString('<a href="/horas" aria-label="Horas aceptadas 4,00 h de 8,25 h declaradas" class="glass group relative grid min-h-0 gap-4 rounded-xl p-5 text-inherit no-underline md:min-h-42 md:p-6 transition hover:-translate-y-0.5 mt-2">', $html);
        $this->assertStringContainsString('group-hover:translate-x-0.5', $html);
        $this->assertStringContainsString('d="m9 18 6-6-6-6"', $html, 'The chevron');
        $this->assertMatchesRegularExpression('/<span\s+class="font-mono text-3xl font-semibold tracking-tighter tabular-nums md:text-4xl">4,00<\/span>/', $html);
        $this->assertStringContainsString('<span class="text-lg font-medium text-ink-muted">h</span>', $html);
        $this->assertStringContainsString('<span class="block text-sm font-medium tracking-normal text-ink-muted">de 8,25 h declaradas</span>', $html);
        $this->assertStringContainsString('</a>', $html);
        $this->assertStringNotContainsString('<div class="glass', $html);
        $this->assertStringNotContainsString('orb-glow', $html);
        $this->assertNoAutofocus($html);
        $this->assertCspSafe($html);
    }

    public function test_without_href_it_is_a_plain_block(): void
    {
        $html = $this->render('<x-ui.stat label="Horas computadas" value="10,50" aria-label="Ignorado" />');

        $this->assertStringContainsString('<div class="glass group relative grid min-h-0 gap-4 rounded-xl p-5 text-inherit no-underline md:min-h-42 md:p-6">', $html);
        $this->assertStringNotContainsString('<a ', $html);
        $this->assertStringNotContainsString('aria-label', $html);
        $this->assertStringNotContainsString('hover:', $html);
        $this->assertStringNotContainsString('d="m9 18 6-6-6-6"', $html);
        $this->assertStringNotContainsString('text-lg font-medium', $html, 'No unit');
        $this->assertStringNotContainsString('tracking-normal', $html, 'No foot');
        $this->assertStringContainsString('</div>', $html);
    }

    public function test_the_dot_appears_only_with_a_tone(): void
    {
        $html = $this->render('<x-ui.stat label="Pendientes" value="3" tone="pending" />');

        $this->assertStringContainsString('[--orb:var(--pending)]', $html);
        $this->assertMatchesRegularExpression('/<span class="flex items-center gap-2 pr-6 text-sm font-semibold text-ink-muted">\s*<span aria-hidden="true"/', $html);
        $this->assertStringNotContainsString('orb-glow', $this->render('<x-ui.stat label="Pendientes" value="3" />'));
    }

    public function test_main_widens_the_grid_and_prints_the_aside_the_legend_and_the_mini_line(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-ui.stat label="Horas aceptadas" value="4,00" href="/horas" main>
                <x-slot:aside><span>Anillo</span></x-slot:aside>
                <x-slot:legend class="text-ink"><span>Aceptadas 4,00 h</span></x-slot:legend>
                <x-slot:mini><span>Pendientes 3,00 h</span></x-slot:mini>
                <span>Barra</span>
            </x-ui.stat>
            BLADE);

        $this->assertStringContainsString('md:grid-cols-[minmax(0,1fr)_auto] md:items-center', $html);
        $this->assertStringContainsString('<div class="justify-self-start md:justify-self-end"><span>Anillo</span></div>', $html);
        $this->assertMatchesRegularExpression('/<span\s+class="mt-1 hidden flex-wrap gap-x-4 gap-y-2 text-xs text-ink-muted md:flex text-ink"><span>Aceptadas 4,00 h<\/span><\/span>/', $html);
        $this->assertMatchesRegularExpression('/<span\s+class="mt-1.5 flex flex-wrap gap-x-3 gap-y-2 text-sm font-medium text-ink-muted md:hidden"><span>Pendientes 3,00 h<\/span><\/span>/', $html);
        $this->assertMatchesRegularExpression('/<div class="mt-2">\s*<span>Barra<\/span>\s*<\/div>/', $html);

        $plain = $this->render('<x-ui.stat label="Pendientes" value="3" />');

        $this->assertStringNotContainsString('md:grid-cols-[minmax(0,1fr)_auto]', $plain);
        $this->assertStringNotContainsString('justify-self-end', $plain);
        $this->assertStringNotContainsString('md:flex', $plain);
        $this->assertStringNotContainsString('<div class="mt-2">', $plain);
    }

    public function test_mobile_hide_keeps_the_card_for_md_and_up(): void
    {
        $this->assertStringContainsString('max-md:hidden', $this->render('<x-ui.stat label="Reuniones" value="2" mobile="hide" />'));
        $this->assertStringNotContainsString('max-md:hidden', $this->render('<x-ui.stat label="Reuniones" value="2" />'));
    }

    public function test_the_label_and_the_value_are_required(): void
    {
        foreach (['<x-ui.stat value="3" />', '<x-ui.stat label="Pendientes" />'] as $blade) {
            try {
                $this->render($blade);
            } catch (ViewException $exception) {
                while ($exception->getPrevious() !== null) {
                    $exception = $exception->getPrevious();
                }

                $this->assertInstanceOf(InvalidArgumentException::class, $exception);

                continue;
            }

            $this->fail('A stat without a label or a value was rendered');
        }
    }

    public function test_the_texts_are_escaped(): void
    {
        $html = $this->render('<x-ui.stat :label="$label" :value="$value" :foot="$foot" />', [
            'label' => '<b>Horas</b>',
            'value' => '<i>4</i>',
            'foot' => '<u>de 8</u>',
        ]);

        $this->assertStringContainsString('&lt;b&gt;Horas&lt;/b&gt;', $html);
        $this->assertStringContainsString('&lt;i&gt;4&lt;/i&gt;', $html);
        $this->assertStringContainsString('&lt;u&gt;de 8&lt;/u&gt;', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }
}
