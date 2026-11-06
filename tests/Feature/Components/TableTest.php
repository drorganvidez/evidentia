<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Fluent;
use Illuminate\View\ViewException;
use InvalidArgumentException;

class TableTest extends ComponentTestCase
{
    private const COLUMNS = <<<'PHP'
        [
            ['label' => 'Evidencia', 'key' => 'title', 'sort' => ['href' => '?sort=title', 'dir' => null]],
            ['label' => 'Comité', 'key' => 'committee', 'hide' => true],
            ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
            ['label' => 'Actualizada', 'key' => 'updated', 'sort' => ['href' => '?sort=updated&dir=asc', 'dir' => 'desc']],
            ['label' => 'Abrir', 'srOnly' => true],
        ]
        PHP;

    private function table(string $props = '', string $body = ''): string
    {
        return $this->render('<x-ui.table caption="Mis evidencias" :columns="'.self::COLUMNS.'" '.$props.'>'.$body.'</x-ui.table>');
    }

    public function test_it_is_a_named_region_with_a_hidden_caption_and_explicit_roles(): void
    {
        $html = $this->table('class="mt-4"', '<x-ui.table.row><x-ui.table.cell>Hola</x-ui.table.cell></x-ui.table.row>');

        $this->assertMatchesRegularExpression('/<section\s+aria-label="Mis evidencias" class="glass overflow-hidden rounded-xl \[--row-h:4\.25rem\] mt-4">/', $html);
        $this->assertMatchesRegularExpression('/<caption class="sr-only">\s*Mis evidencias<\/caption>/', $html);
        $this->assertMatchesRegularExpression('/<table role="table" class="w-full border-collapse text-md block md:table"\s+data-cards\s*>/', $html);
        $this->assertMatchesRegularExpression('/<thead role="rowgroup"\s+class="hidden md:table-header-group"\s*>/', $html);
        $this->assertMatchesRegularExpression('/<tbody role="rowgroup"\s+class="block md:table-row-group"\s*>/', $html);
        $this->assertStringContainsString('<tr role="row"', $html);
        $this->assertStringContainsString('<td role="cell"', $html);
        $this->assertStringContainsString('x-data="scrollRegion" x-bind:tabindex="tabindex" x-bind:role="role" x-bind:aria-label="label"', $html);
        $this->assertDoesNotMatchRegularExpression('/\stabindex="/', $html);
        $this->assertStringNotContainsString('x-data="tableFilter"', $html);
        $this->assertStringNotContainsString('role="status"', $html);
        $this->assertUniqueIds($html);
        $this->assertNoAutofocus($html);
        $this->assertCspSafe($html);
    }

    public function test_the_caption_is_required_and_may_be_shown(): void
    {
        $this->assertStringContainsString('<caption class="px-5 pt-4 pb-2 text-left text-lg font-bold tracking-tight block md:table-caption">', $this->table('show-caption'));
        $this->assertStringContainsString('<caption class="px-5 pt-4 pb-2 text-left text-lg font-bold tracking-tight">', $this->table('show-caption :cards="false"'));

        try {
            $this->render('<x-ui.table />');
        } catch (ViewException $exception) {
            while ($exception->getPrevious() !== null) {
                $exception = $exception->getPrevious();
            }

            $this->assertInstanceOf(InvalidArgumentException::class, $exception);

            return;
        }

        $this->fail('A table without a caption was rendered');
    }

    public function test_the_column_headers_carry_scope_sort_state_and_alignment(): void
    {
        $html = $this->table();

        $this->assertSame(5, substr_count($html, '<th role="columnheader" scope="col"'));
        $this->assertSame(1, substr_count($html, 'aria-sort='));
        $this->assertMatchesRegularExpression('/<th role="columnheader" scope="col" class="[^"]*text-left[^"]*pointer-coarse:py-0"\s+aria-sort="descending"\s*>\s*<a href="\?sort=updated&amp;dir=asc" title="Ordenar de forma ascendente"/', $html);
        $this->assertMatchesRegularExpression('/<a href="\?sort=title" title="Ordenar de forma ascendente"\s+class="inline-flex min-h-6 items-center gap-1 text-ink pointer-coarse:min-h-11">\s*Evidencia/', $html);
        $this->assertStringNotContainsString('aria-label="Orden', $html, 'The visible text names the link; the title describes the action');
        $this->assertStringContainsString('<span class="sr-only">Abrir</span>', $html);
        $this->assertMatchesRegularExpression('/<th role="columnheader" scope="col" class="[^"]*text-right"\s*>\s*Horas/', $html);
        $this->assertSame(1, substr_count($html, 'd="M12 5v14"'), 'One arrow-down for the active descending column');
        $this->assertSame(1, substr_count($html, 'd="m21 16-4 4-4-4"'), 'One arrow-up-down for the sortable but unsorted column');
        $this->assertStringNotContainsString('d="M12 19V5"', $html, 'No arrow-up without an ascending column');

        $ascending = $this->render('<x-ui.table caption="x" :columns="[[\'label\' => \'Horas\', \'sort\' => [\'href\' => \'?dir=desc\', \'dir\' => \'asc\']]]" />');

        $this->assertStringContainsString('aria-sort="ascending"', $ascending);
        $this->assertStringContainsString('title="Ordenar de forma descendente"', $ascending);
        $this->assertStringContainsString('d="M12 19V5"', $ascending);
    }

    public function test_the_columns_may_be_a_collection_of_arrays_or_other_arrayables(): void
    {
        $html = $this->render('<x-ui.table caption="Mis evidencias" :columns="$columns" />', [
            'columns' => collect([
                3 => ['label' => 'Evidencia', 'sort' => ['href' => '?sort=title', 'dir' => 'asc']],
                8 => new Fluent(['label' => 'Horas', 'align' => 'end']),
            ]),
        ]);

        $this->assertSame(2, substr_count($html, '<th role="columnheader" scope="col"'));
        $this->assertStringContainsString('aria-sort="ascending"', $html);
        $this->assertMatchesRegularExpression('/<th role="columnheader" scope="col" class="[^"]*text-right"\s*>\s*Horas/', $html);

        $empty = $this->render('<x-ui.table caption="Mis evidencias" :columns="$columns"><x-slot:empty>Nada</x-slot:empty></x-ui.table>', ['columns' => collect()]);

        $this->assertStringNotContainsString('<thead', $empty);
        $this->assertMatchesRegularExpression('/<td role="cell" colspan="1"/', $empty);
    }

    public function test_the_density_sets_the_row_height_variable(): void
    {
        $this->assertStringContainsString('[--row-h:3.5rem]', $this->table('density="compact"'));
        $this->assertStringNotContainsString('[--row-h:3.5rem]', $this->table());
    }

    public function test_without_cards_the_table_keeps_its_display(): void
    {
        $html = $this->table(':cards="false"');

        $this->assertMatchesRegularExpression('/<table role="table" class="w-full border-collapse text-md"\s*>/', $html);
        $this->assertStringNotContainsString('data-cards', $html);
        $this->assertMatchesRegularExpression('/<thead role="rowgroup"\s*>/', $html);
        $this->assertMatchesRegularExpression('/<tbody role="rowgroup"\s*>/', $html);
        $this->assertStringNotContainsString('block md:', $html);
    }

    public function test_filterable_adds_the_search_field_and_the_status_region_to_the_toolbar(): void
    {
        $html = $this->table('filterable count-label="evidencias" filter-placeholder="Filtrar evidencias"', '<x-slot:toolbar><span>Pestañas</span></x-slot:toolbar>');

        $this->assertMatchesRegularExpression('/<div\s+x-data="tableFilter" data-count-label="evidencias" class="flex flex-wrap items-center gap-2 border-b border-rule p-3 md:gap-3 md:px-5 md:py-4">/', $html);
        $this->assertStringContainsString('<span>Pestañas</span>', $html);
        $this->assertMatchesRegularExpression('/<input type="search" x-model="query" x-on:input\.debounce\.150ms="filter" autocomplete="off"\s+placeholder="Filtrar evidencias" aria-label="Filtrar evidencias"/', $html);
        $this->assertStringContainsString('w-0 min-w-0 flex-1', $html);
        $this->assertStringContainsString('text-control', $html);
        $this->assertStringContainsString('<span role="status" class="text-sm text-ink-muted tabular-nums" x-text="summary"></span>', $html);
        $this->assertCspSafe($html);

        $plain = $this->table('', '<x-slot:toolbar class="justify-end">Solo pestañas</x-slot:toolbar>');

        $this->assertMatchesRegularExpression('/<div\s+class="flex flex-wrap items-center gap-2 border-b border-rule p-3 md:gap-3 md:px-5 md:py-4 justify-end">/', $plain);
        $this->assertStringNotContainsString('type="search"', $plain);
        $this->assertStringNotContainsString('x-data="tableFilter"', $plain);
    }

    public function test_the_footer_and_the_empty_state_are_optional(): void
    {
        $html = $this->table('', '<x-slot:footer class="text-ink">6 evidencias</x-slot:footer><x-slot:empty>Nada</x-slot:empty>');

        $this->assertMatchesRegularExpression('/<div\s+class="flex flex-col items-start gap-2 border-t border-rule px-5 py-4 text-sm text-ink-muted md:flex-row md:items-center md:justify-between text-ink">\s*6 evidencias<\/div>/', $html);
        $this->assertMatchesRegularExpression('/<td role="cell" colspan="5"\s+class="p-0 block md:table-cell">\s*Nada<\/td>/', $html);

        $bare = $this->table();

        $this->assertStringNotContainsString('border-t border-rule px-5 py-4', $bare);
        $this->assertStringNotContainsString('colspan', $bare);
    }

    public function test_a_row_follows_its_link_only_when_it_has_one(): void
    {
        $linked = $this->render('<x-ui.table.row href="/evidencias/33" search="montaje photocall"><x-ui.table.cell>Hola</x-ui.table.cell></x-ui.table.row>');

        $this->assertStringContainsString('data-href="/evidencias/33"', $linked);
        $this->assertStringContainsString('data-search="montaje photocall"', $linked);
        $this->assertStringContainsString('x-data="rowLink" x-on:click="go" x-on:auxclick="go"', $linked);
        $this->assertStringContainsString('cursor-pointer', $linked);
        $this->assertStringNotContainsString('tabindex', $linked, 'The link inside the row is the keyboard path');
        $this->assertStringContainsString('in-data-cards:grid in-data-cards:grid-cols-[minmax(0,1fr)_auto]', $linked);
        $this->assertStringContainsString('md:table-row', $linked);
        $this->assertCspSafe($linked);

        $plain = $this->render('<x-ui.table.row><x-ui.table.cell>Hola</x-ui.table.cell></x-ui.table.row>');

        $this->assertStringNotContainsString('data-href', $plain);
        $this->assertStringNotContainsString('x-data', $plain);
        $this->assertStringNotContainsString('cursor-pointer', $plain);
    }

    public function test_a_flagged_row_paints_the_stripe_and_opens_its_first_cell_with_a_notice(): void
    {
        $html = $this->render('<x-ui.table.row :flag="$flag"><x-ui.table.cell>12:40</x-ui.table.cell><x-ui.table.cell>Firma</x-ui.table.cell></x-ui.table.row>', ['flag' => 'Fuera de <plazo>']);

        // The attribute bag escapes the class value; the browser reads [&>:first-child] back
        $this->assertStringContainsString('[&amp;&gt;:first-child]:inset-shadow-[4px_0_0_var(--pending)]', $html);
        $this->assertMatchesRegularExpression('/<td role="cell"[^>]*><span class="sr-only">Señalada, Fuera de &lt;plazo&gt;\. <\/span>12:40<\/td>/', $html);
        $this->assertSame(1, substr_count($html, 'Señalada'));
        $this->assertStringNotContainsString('<plazo>', $html);
        $this->assertStringNotContainsString('Señalada', $this->render('<x-ui.table.row><x-ui.table.cell>12:40</x-ui.table.cell></x-ui.table.row>'));
        $this->assertStringNotContainsString('inset-shadow', $this->render('<x-ui.table.row><x-ui.table.cell>12:40</x-ui.table.cell></x-ui.table.row>'));
    }

    public function test_the_flag_is_printed_as_written_even_with_replacement_tokens(): void
    {
        $html = $this->render('<x-ui.table.row :flag="$flag"><x-ui.table.cell>12:40</x-ui.table.cell><x-ui.table.cell>Firma</x-ui.table.cell></x-ui.table.row>', [
            'flag' => 'Importe $100, $0 y \\1 ${1}',
        ]);

        $this->assertStringContainsString('><span class="sr-only">Señalada, Importe $100, $0 y \\1 ${1}. </span>12:40</td>', $html);
        $this->assertSame(2, substr_count($html, '<td role="cell"'));
        $this->assertSame(1, substr_count($html, 'Señalada'));
    }

    public function test_every_cell_prop_maps_to_literal_classes(): void
    {
        $cell = fn (string $props): string => $this->render('<x-ui.table.cell '.$props.'>x</x-ui.table.cell>');

        $this->assertStringContainsString('<td role="cell" class="h-(--row-h) px-5 py-4 align-middle in-data-cards:block in-data-cards:h-auto in-data-cards:p-0 md:table-cell md:h-(--row-h) md:px-5 md:py-4 md:align-middle">x</td>', $cell(''));
        $this->assertStringContainsString('data-label="Horas"', $cell('label="Horas"'));
        $this->assertStringContainsString('text-right font-mono tabular-nums in-data-cards:text-left md:text-right', $cell('numeric'));
        $this->assertStringContainsString('in-data-cards:col-span-2 [&amp;_a]:font-semibold [&amp;_a]:tracking-tight [&amp;_a]:text-ink [&amp;_a:hover]:text-brand-ink', $cell('primary'));
        $this->assertStringContainsString('text-sm text-ink-muted', $cell('meta'));
        $this->assertStringContainsString('in-data-cards:justify-self-end', $cell('end'));
        $this->assertStringContainsString('in-data-cards:max-md:hidden', $cell('hide'));
        $this->assertStringContainsString('in-data-cards:col-span-full', $cell('full'));
        $this->assertStringContainsString('in-data-cards:max-md:hidden', $cell('actions'));
        $this->assertStringContainsString('w-[1%] whitespace-nowrap', $cell('actions'));
        $this->assertStringNotContainsString('in-data-cards:max-md:hidden', $cell('meta'));
        $this->assertStringNotContainsString('data-label', $cell(''));
    }

    public function test_a_header_cell_is_a_row_header(): void
    {
        $html = $this->render('<x-ui.table.cell header>EV-0033</x-ui.table.cell>');

        $this->assertStringStartsWith('<th role="rowheader" scope="row" class="', trim($html));
        $this->assertStringContainsString('text-left font-semibold', $html);
        $this->assertStringEndsWith('</th>', trim($html));
        $this->assertStringNotContainsString('scope=', $this->render('<x-ui.table.cell>x</x-ui.table.cell>'));
    }

    public function test_the_consumer_escapes_the_content_and_the_caption_is_escaped(): void
    {
        $html = $this->render('<x-ui.table :caption="$caption"><x-ui.table.row><x-ui.table.cell>{{ $text }}</x-ui.table.cell></x-ui.table.row></x-ui.table>', ['caption' => '<b>Mis</b>', 'text' => '<i>x</i>']);

        $this->assertStringContainsString('aria-label="&lt;b&gt;Mis&lt;/b&gt;"', $html);
        $this->assertStringContainsString('&lt;i&gt;x&lt;/i&gt;', $html);
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringNotContainsString('<i>', $html);
    }
}
