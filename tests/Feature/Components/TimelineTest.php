<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Carbon;
use Illuminate\View\ViewException;
use InvalidArgumentException;

class TimelineTest extends ComponentTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-11-05 22:40:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function steps(string $props = ''): string
    {
        return $this->render(<<<BLADE
            <x-ui.timeline variant="steps" label="Recorrido de la evidencia" {$props}>
                <x-ui.timeline.item state="done" title="Borrador" :at="'2026-11-01 10:00:00'" />
                <x-ui.timeline.item state="done" title="Enviada" :at="'2026-11-02 11:30:00'" href="/evidencias/33/enviada" />
                <x-ui.timeline.item state="error" title="Rechazada" :at="'2026-11-04 09:15:00'" />
                <x-ui.timeline.item title="Aceptada" hint="Pendiente de corregir" />
            </x-ui.timeline>
            BLADE);
    }

    public function test_steps_are_a_labelled_ordered_list_with_the_current_step_marked(): void
    {
        $html = $this->steps(':columns="4" class="mt-4"');

        $this->assertStringContainsString('<ol aria-label="Recorrido de la evidencia" class="glass m-0 grid list-none gap-0 rounded-xl px-5 py-4 md:gap-3 md:px-8 md:py-6 md:grid-cols-4 mt-4">', $html);
        $this->assertSame(4, substr_count($html, '<li'));
        $this->assertSame(1, substr_count($html, 'aria-current="step"'));
        $this->assertMatchesRegularExpression('/<li\s+aria-current="step"\s+class="[^"]*before:bg-rule">\s*<span aria-hidden="true"\s+class="[^"]*bg-rejected ring-3 ring-rejected\/25 shadow-glow-sm">/', $html);
        $this->assertStringContainsString('</ol>', $html);
        $this->assertStringNotContainsString('hover:', str_replace('hover:text-brand-ink', '', $html));
        $this->assertUniqueIds($html);
        $this->assertCspSafe($html);
    }

    public function test_every_state_has_its_node_its_connector_and_a_hidden_text(): void
    {
        $html = $this->steps();

        $this->assertSame(2, substr_count($html, 'bg-aurora-500 ring-3 ring-aurora-500/25 shadow-glow-sm'));
        $this->assertSame(2, substr_count($html, 'before:bg-linear-to-r before:from-aurora-500 before:to-cyan-400'));
        $this->assertSame(2, substr_count($html, '<span class="sr-only">, completado</span>'));
        $this->assertStringContainsString('<span class="sr-only">, con incidencia, paso actual</span>', $html);
        $this->assertStringContainsString('<span class="sr-only">, pendiente</span>', $html);
        $this->assertMatchesRegularExpression('/<span class="font-semibold text-rejected-ink">\s*Rechazada\s*<span class="sr-only">/', $html);
        $this->assertMatchesRegularExpression('/<span class="font-semibold text-ink-muted">\s*Aceptada\s*<span class="sr-only">/', $html);
        $this->assertStringContainsString('md:top-0 bg-rule"', $html);

        $current = $this->render('<x-ui.timeline variant="steps" label="x"><x-ui.timeline.item state="current" title="Enviada" /></x-ui.timeline>');

        $this->assertStringContainsString('aria-current="step"', $current);
        $this->assertStringContainsString('<span class="sr-only">, paso actual</span>', $current);
        $this->assertStringContainsString('bg-aurora-500', $current);
    }

    public function test_a_step_with_href_is_a_link_and_the_hint_replaces_the_date(): void
    {
        $html = $this->steps();

        $this->assertMatchesRegularExpression('/<a href="\/evidencias\/33\/enviada"\s+class="text-inherit no-underline hover:text-brand-ink">Enviada<\/a>/', $html);
        $this->assertSame(1, substr_count($html, '<a '));
        $this->assertStringContainsString('<span class="text-xs text-ink-muted">Pendiente de corregir</span>', $html);
        $this->assertMatchesRegularExpression('/<time\s+class="font-mono tabular-nums text-xs text-ink-muted" datetime="2026-11-04T09:15:00\+01:00">04\/11 09:15<\/time>/', $html);
        $this->assertSame(3, substr_count($html, '<time'));
    }

    public function test_the_columns_map_to_literal_classes(): void
    {
        $this->assertStringContainsString('md:grid-cols-3', $this->steps(':columns="3"'));
        $this->assertStringContainsString('md:grid-flow-col md:auto-cols-fr', $this->steps());
        $this->assertStringNotContainsString('md:grid-cols-', $this->steps());
    }

    public function test_the_log_prints_a_dot_per_tone_and_the_slot_after_the_title(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-ui.timeline label="Historial">
                <x-ui.timeline.item tone="rejected" title="Rechazada" :at="'2026-11-04 09:15:00'">por <a href="/u/4">Carlos Navarro</a></x-ui.timeline.item>
                <x-ui.timeline.item title="Creada" hint="Versión 1" />
            </x-ui.timeline>
            BLADE);

        $this->assertStringContainsString('<ol aria-label="Historial" class="m-0 grid list-none gap-2">', $html);
        $this->assertSame(2, substr_count($html, '<li class="grid grid-cols-[1.25rem_minmax(0,1fr)] gap-3 py-2 text-sm">'));
        $this->assertStringContainsString('[--orb:var(--rejected)]', $html);
        $this->assertStringContainsString('[--orb:var(--draft)]', $html);
        $this->assertStringContainsString('mt-1.25 justify-self-center', $html);
        $this->assertMatchesRegularExpression('/<strong class="font-semibold">Rechazada<\/strong>\s*por <a href="\/u\/4">Carlos Navarro<\/a>/', $html);
        $this->assertMatchesRegularExpression('/<time\s+class="font-mono tabular-nums block text-xs text-ink-muted" datetime="2026-11-04T09:15:00\+01:00">04\/11\/2026 09:15<\/time>/', $html);
        $this->assertStringContainsString('<span class="block text-xs text-ink-muted">Versión 1</span>', $html);
        $this->assertStringNotContainsString('aria-current', $html);
        $this->assertStringNotContainsString('sr-only', $html);
        $this->assertStringNotContainsString('glass', $html);
        $this->assertCspSafe($html);
    }

    public function test_the_label_and_the_title_are_required(): void
    {
        foreach (['<x-ui.timeline />', '<x-ui.timeline label="x"><x-ui.timeline.item /></x-ui.timeline>'] as $blade) {
            try {
                $this->render($blade);
            } catch (ViewException $exception) {
                while ($exception->getPrevious() !== null) {
                    $exception = $exception->getPrevious();
                }

                $this->assertInstanceOf(InvalidArgumentException::class, $exception);

                continue;
            }

            $this->fail('A timeline without a label or an item without a title was rendered');
        }
    }

    public function test_the_texts_are_escaped(): void
    {
        $html = $this->render('<x-ui.timeline variant="steps" :label="$label"><x-ui.timeline.item :title="$title" :hint="$hint" /></x-ui.timeline>', [
            'label' => '<b>Recorrido</b>',
            'title' => '<i>Enviada</i>',
            'hint' => '<u>Pista</u>',
        ]);

        $this->assertStringContainsString('aria-label="&lt;b&gt;Recorrido&lt;/b&gt;"', $html);
        $this->assertStringContainsString('&lt;i&gt;Enviada&lt;/i&gt;', $html);
        $this->assertStringContainsString('&lt;u&gt;Pista&lt;/u&gt;', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }
}
