<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;

class IconPickerTest extends ComponentTestCase
{
    protected function tearDown(): void
    {
        View::share('errors', new ViewErrorBag);

        parent::tearDown();
    }

    /** old() reads the session of the current request, which Blade::render does not start by itself. */
    private function withOldInput(array $input): void
    {
        $this->withSession(['_old_input' => $input]);
        $this->app['request']->setLaravelSession($this->app['session.store']);
    }

    /** @return list<string> */
    private function radioValues(string $html): array
    {
        preg_match_all('/<input type="radio" name="icon" value="([^"]+)"/', $html, $matches);

        return $matches[1];
    }

    public function test_every_curated_icon_exists_and_renders_as_a_radio_with_its_aliases(): void
    {
        $html = $this->render('<x-ui.icon-picker />');
        $names = $this->radioValues($html);

        $this->assertGreaterThanOrEqual(96, count($names));
        $this->assertSame($names, array_unique($names));

        foreach ($names as $name) {
            $this->assertFileExists(base_path('vendor/mallardduck/blade-lucide-icons/resources/svg/icons/'.$name.'.svg'));
            $this->assertStringContainsString('<svg', svg('lucide-'.$name)->toHtml());
        }

        $this->assertStringContainsString('<fieldset x-data="iconPicker"', $html);
        $this->assertStringContainsString('<legend class="text-sm font-semibold">Icono', $html);
        $this->assertStringContainsString('<span class="sr-only">euro, euro, dinero, finanzas</span>', $html);
        $this->assertStringContainsString('&quot;euro&quot;:&quot;euro, dinero, finanzas&quot;', $html);
        $this->assertStringContainsString('x-show="matches(\'euro\')"', $html);
        $this->assertStringContainsString('x-on:change="choose(\'euro\')"', $html);
        $this->assertStringContainsString('title="euro"', $html);
        $this->assertStringContainsString('grid-cols-5 md:grid-cols-6', $html);
        $this->assertStringContainsString('<p role="status" class="sr-only" x-text="countText"></p>', $html);
        $this->assertStringContainsString('x-text="value || \'Sin icono\'">Sin icono</span>', $html);
        $this->assertSame(count($names) + 1, substr_count($html, '<svg'), 'One SVG per radio and the search icon, none in the preview');
        $this->assertDoesNotMatchRegularExpression('/\schecked[\s>]/', $html);
        $this->assertNoAutofocus($html);
        $this->assertUniqueIds($html);
        $this->assertCspSafe($html);
    }

    public function test_the_search_box_is_a_field_that_filters_and_announces(): void
    {
        $html = $this->render('<x-ui.icon-picker name="committee_icon" />');

        $this->assertStringContainsString('<label for="field-committee-icon-search" class="sr-only">Buscar icono', $html);
        $this->assertMatchesRegularExpression('/<input id="field-committee-icon-search" name="committee_icon_search" type="search"/', $html);
        $this->assertMatchesRegularExpression('/<input [^>]*x-model="query"[^>]*>/', $html);
        $this->assertMatchesRegularExpression('/<input [^>]*x-on:input\.debounce\.400ms="announceCount"[^>]*>/', $html);
        $this->assertStringContainsString('sticky top-0', $html);
        $this->assertStringContainsString('<input type="radio" name="committee_icon" value="euro"', $html);
    }

    public function test_the_value_checks_its_radio_and_fills_the_preview_from_the_prop_or_old_input(): void
    {
        $html = $this->render('<x-ui.icon-picker value="euro" />');

        $this->assertMatchesRegularExpression('/<input type="radio" name="icon" value="euro"\s+class="[^"]+"\s+checked/', $html);
        $this->assertStringContainsString('x-text="value || \'Sin icono\'">euro</span>', $html);
        $this->assertStringContainsString('x-ref="preview"', $html);
        $this->assertSame(count($this->radioValues($html)) + 2, substr_count($html, '<svg'), 'The preview holds the chosen icon');

        $unknown = $this->render('<x-ui.icon-picker value="<b>no</b>" />');

        $this->assertDoesNotMatchRegularExpression('/\schecked[\s>]/', $unknown);
        $this->assertStringContainsString('>Sin icono</span>', $unknown);
        $this->assertStringNotContainsString('<b>', $unknown);

        $this->withOldInput(['icon' => 'star']);
        $old = $this->render('<x-ui.icon-picker value="euro" />');

        $this->assertMatchesRegularExpression('/value="star"\s+class="[^"]+"\s+checked/', $old);
        $this->assertDoesNotMatchRegularExpression('/value="euro"\s+class="[^"]+"\s+checked/', $old);
        $this->assertStringContainsString('>star</span>', $old);
    }

    public function test_a_custom_list_columns_required_hint_and_error(): void
    {
        $html = $this->render('<x-ui.icon-picker name="badge_icon" label="Insignia" :icons="[\'users\', \'calendar\', \'users\']" :columns="4" required hint="Elige uno" error="Falta el icono" />');

        $this->assertStringContainsString('<input type="radio" name="badge_icon" value="users"', $html);
        $this->assertStringContainsString('<input type="radio" name="badge_icon" value="calendar"', $html);
        $this->assertSame(2, substr_count($html, 'type="radio"'), 'Duplicates are dropped');
        $this->assertStringContainsString('grid gap-1 p-1 grid-cols-4', $html);
        $this->assertMatchesRegularExpression('/<input type="radio" [^>]*\srequired[\s>]/', $html);
        $this->assertStringContainsString('<span class="sr-only">(obligatorio)</span>', $html);
        $this->assertMatchesRegularExpression('/<fieldset [^>]*aria-describedby="field-badge-icon-error field-badge-icon-hint"/', $html);
        $this->assertStringContainsString('<p id="field-badge-icon-hint" class="text-sm text-ink-muted">Elige uno</p>', $html);
        $this->assertStringContainsString('Falta el icono', $html);
        $this->assertStringContainsString('<span class="sr-only">users, personas, grupo, equipo</span>', $html);
        $this->assertUniqueIds($html);

        $this->withViewErrors(['badge_icon' => 'Obligatorio']);

        $this->assertStringContainsString('Obligatorio', $this->render('<x-ui.icon-picker name="badge_icon" :icons="[\'users\']" />'));
    }

    public function test_the_label_is_escaped_and_the_consumer_class_reaches_the_fieldset(): void
    {
        $html = $this->render('<x-ui.icon-picker :label="$label" :icons="[\'star\']" class="lg:col-span-2" />', ['label' => '<b>Icono</b>']);

        $this->assertStringContainsString('&lt;b&gt;Icono&lt;/b&gt;', $html);
        $this->assertStringContainsString('class="grid min-w-0 grid-cols-[minmax(0,1fr)] gap-3 lg:col-span-2"', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }
}
