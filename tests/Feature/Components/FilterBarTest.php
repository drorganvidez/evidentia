<?php

namespace Tests\Feature\Components;

use Illuminate\View\ViewException;
use InvalidArgumentException;

class FilterBarTest extends ComponentTestCase
{
    public function test_it_is_a_closed_details_with_a_get_form_by_default(): void
    {
        $html = $this->render('<x-ui.filter-bar action="/activity"><p>Campos</p></x-ui.filter-bar>');

        $this->assertMatchesRegularExpression('/^<details class="group glass rounded-xl"\s*>/', trim($html));
        $this->assertDoesNotMatchRegularExpression('/<details [^>]*\sopen[\s>]/', $html);
        $this->assertStringContainsString('<summary', $html);
        $this->assertStringContainsString('Filtros', $html);
        $this->assertStringContainsString('group-open:rotate-180', $html);
        $this->assertStringContainsString('<form method="get" action="/activity"', $html);
        $this->assertStringContainsString('x-data="busyForm"', $html);
        $this->assertStringContainsString('data-busy-label="Aplicando"', $html);
        $this->assertStringContainsString('<div class="grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-2 lg:grid-cols-4"><p>Campos</p></div>', $html);
        $this->assertStringContainsString('type="submit"', $html);
        $this->assertStringContainsString('Aplicar', $html);
        $this->assertStringNotContainsString('Limpiar', $html);
        $this->assertStringNotContainsString('Descargar', $html);
        $this->assertStringNotContainsString('formaction', $html);
        $this->assertStringNotContainsString('data-download', $html);
        $this->assertStringNotContainsString('_token', $html);
        $this->assertStringNotContainsString('bg-rule px-1.75', $html, 'No counter without active filters');
        $this->assertStringEndsWith('</details>', trim($html));
        $this->assertNoAutofocus($html);
        $this->assertUniqueIds($html);
        $this->assertCspSafe($html);
    }

    public function test_open_counts_the_active_filters_and_offers_reset_and_download(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-ui.filter-bar action="/activity" open :active="2" reset-href="/activity" id="activity-filters"
                :download="['href' => '/activity/export.csv', 'label' => 'Descargar CSV']" class="mt-4">
                <p>Campos</p>
            </x-ui.filter-bar>
            BLADE);

        $this->assertMatchesRegularExpression('/<details class="group glass rounded-xl mt-4"\s+open\s+id="activity-filters"\s*>/', $html);
        $this->assertStringContainsString('bg-rule px-1.75', $html);
        $this->assertMatchesRegularExpression('/>\s*2\s*<\/span>\s*<span class="sr-only">filtros activos<\/span>/', $html);
        $this->assertMatchesRegularExpression('/<a [^>]*href="\/activity"[^>]*>\s*Limpiar\s*<\/a>/', $html);
        $this->assertMatchesRegularExpression('/<button [^>]*formaction="\/activity\/export.csv"[^>]*>/', $html);
        $this->assertMatchesRegularExpression('/<button [^>]*data-busy-label="Preparando"[^>]*>/', $html);
        $this->assertStringContainsString('md:ml-auto', $html);
        $this->assertStringContainsString('Descargar CSV', $html);
        $this->assertStringContainsString(' data-download', $html);
        $this->assertLessThan(strpos($html, 'Limpiar'), strpos($html, 'Aplicar'));
        $this->assertLessThan(strpos($html, 'Descargar CSV'), strpos($html, 'Limpiar'));
        $this->assertUniqueIds($html);
        $this->assertCspSafe($html);
    }

    public function test_one_active_filter_is_announced_in_singular_and_the_label_is_escaped(): void
    {
        $html = $this->render('<x-ui.filter-bar action="/activity" :active="1" :label="$label">x</x-ui.filter-bar>', ['label' => '<b>Filtros</b>']);

        $this->assertMatchesRegularExpression('/>\s*1\s*<\/span>\s*<span class="sr-only">filtro activo<\/span>/', $html);
        $this->assertStringContainsString('&lt;b&gt;Filtros&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }

    public function test_the_download_label_falls_back_and_an_action_is_required(): void
    {
        $html = $this->render('<x-ui.filter-bar action="/activity" :download="[\'href\' => \'/export\']">x</x-ui.filter-bar>');

        $this->assertStringContainsString('Descargar CSV', $html);
        $this->assertStringContainsString('formaction="/export"', $html);

        try {
            $this->render('<x-ui.filter-bar>x</x-ui.filter-bar>');
            $this->fail('Rendered without an action');
        } catch (ViewException $exception) {
            while ($exception->getPrevious() !== null) {
                $exception = $exception->getPrevious();
            }

            $this->assertInstanceOf(InvalidArgumentException::class, $exception);
            $this->assertSame('The filter bar needs an action.', $exception->getMessage());
        }
    }
}
