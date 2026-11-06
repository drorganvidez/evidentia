<?php

namespace Tests\Feature\Components;

class PdfTableTest extends ComponentTestCase
{
    public function test_columns_become_a_header_row_and_rows_become_cells(): void
    {
        $html = $this->render('<x-pdf.table :columns="$columns" :rows="$rows" />', [
            'columns' => ['Rol', 'Apellidos <b>y</b> nombre'],
            'rows' => [['Coordinación', 'Pérez García, Juan'], ['Secretaría', 'Pérez Romero, <i>Lucía</i>']],
        ]);

        $this->assertStringContainsString('<table class="pdf-table pdf-table--brand">', $html);
        $this->assertMatchesRegularExpression('/<thead>\s*<tr>\s*<th scope="col">Rol<\/th>\s*<th scope="col">Apellidos &lt;b&gt;y&lt;\/b&gt; nombre<\/th>\s*<\/tr>\s*<\/thead>/', $html);
        $this->assertMatchesRegularExpression('/<tbody>\s*<tr>\s*<td>Coordinación<\/td>\s*<td>Pérez García, Juan<\/td>\s*<\/tr>/', $html);
        $this->assertStringContainsString('<td>Pérez Romero, &lt;i&gt;Lucía&lt;/i&gt;</td>', $html);
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringNotContainsString('scope="row"', $html);
        $this->assertCspSafe($html);
    }

    public function test_key_value_prints_a_row_header_per_pair_and_no_column_header(): void
    {
        $assoc = $this->render('<x-pdf.table key-value :rows="$rows" />', [
            'rows' => ['Fecha' => '21/10/2026', 'Lugar <b>x</b>' => 'Aula <i>A2.11</i>'],
        ]);

        $this->assertStringContainsString('<table class="pdf-table pdf-table--key-value pdf-table--brand">', $assoc);
        $this->assertStringNotContainsString('<thead>', $assoc);
        $this->assertMatchesRegularExpression('/<tr>\s*<th scope="row">Fecha<\/th>\s*<td>21\/10\/2026<\/td>\s*<\/tr>/', $assoc);
        $this->assertStringContainsString('<th scope="row">Lugar &lt;b&gt;x&lt;/b&gt;</th>', $assoc);
        $this->assertStringContainsString('<td>Aula &lt;i&gt;A2.11&lt;/i&gt;</td>', $assoc);

        $pairs = $this->render('<x-pdf.table key-value :rows="$rows" />', ['rows' => [['Fecha', '21/10/2026'], ['Lugar']]]);

        $this->assertMatchesRegularExpression('/<th scope="row">Fecha<\/th>\s*<td>21\/10\/2026<\/td>/', $pairs);
        $this->assertMatchesRegularExpression('/<th scope="row">Lugar<\/th>\s*<td><\/td>/', $pairs);
    }

    public function test_compact_and_the_header_tone_map_to_classes(): void
    {
        $this->assertStringContainsString('<table class="pdf-table pdf-table--compact pdf-table--plain">', $this->render('<x-pdf.table compact header-tone="plain" />'));
        $this->assertStringContainsString('<table class="pdf-table pdf-table--brand mt-2">', $this->render('<x-pdf.table class="mt-2" />'));
        $this->assertStringNotContainsString('pdf-table--compact', $this->render('<x-pdf.table />'));
    }

    public function test_the_slot_adds_rows_to_the_body_and_rows_may_be_objects(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-pdf.table :columns="['Identificador', 'Acuerdo']" :rows="$rows">
                <tr><td>ISD-2</td><td>A mano</td></tr>
            </x-pdf.table>
            BLADE, ['rows' => [(object) ['id' => 'ISD-1', 'text' => 'Generado']]]);

        $this->assertMatchesRegularExpression('/<tbody>\s*<tr>\s*<td>ISD-1<\/td>\s*<td>Generado<\/td>\s*<\/tr>\s*<tr><td>ISD-2<\/td><td>A mano<\/td><\/tr>\s*<\/tbody>/', $html);
        $this->assertSame(1, substr_count($html, '<thead>'));

        $empty = $this->render('<x-pdf.table />');

        $this->assertMatchesRegularExpression('/<tbody>\s*<\/tbody>/', $empty);
        $this->assertStringNotContainsString('<thead>', $empty);
    }
}
