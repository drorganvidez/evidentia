<?php

namespace Tests\Feature\Components;

use App\Models\User;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;

class PickerTest extends ComponentTestCase
{
    private const PEOPLE = "[['value' => 1, 'label' => 'Pérez, Juan', 'hint' => 'uvus001'], ['value' => 2, 'label' => 'López, Ana'], ['value' => 3, 'label' => 'Ruiz, Sara']]";

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

    public function test_the_native_select_is_the_source_of_truth_under_the_combobox(): void
    {
        $html = $this->render('<x-ui.picker name="users[]" label="Asistentes" :options="'.self::PEOPLE.'" :selected="[2]" />');

        $this->assertStringContainsString('x-data="picker"', $html);
        $this->assertStringContainsString('data-presets="[]"', $html);
        $this->assertMatchesRegularExpression('/<label for="field-users"\s+id="field-users-label"\s+class="text-sm font-semibold">Asistentes<\/label>/', $html);
        $this->assertMatchesRegularExpression('/<select name="users\[\]"\s+id="field-users-native"\s+x-ref="native"\s+tabindex="-1"\s+aria-labelledby="field-users-label"\s+x-bind:aria-hidden="ready \? \'true\' : null"\s+class="sr-only"\s+multiple\s*>/', $html);
        $this->assertMatchesRegularExpression('/<option value="1"\s+data-hint="uvus001"\s*>Pérez, Juan<\/option>/', $html);
        $this->assertMatchesRegularExpression('/<option value="2"\s+selected\s*>López, Ana<\/option>/', $html);
        $this->assertDoesNotMatchRegularExpression('/<option value="3"[^>]*selected/', $html);
        $this->assertStringNotContainsString('Sin selección', $html);
        $this->assertMatchesRegularExpression('/<input id="field-users"\s+type="text"\s+role="combobox"\s+aria-autocomplete="list"\s+aria-controls="field-users-list"/', $html);
        $this->assertStringContainsString('x-bind:aria-expanded="open ? \'true\' : \'false\'"', $html);
        $this->assertStringContainsString('x-bind:aria-activedescendant="activeId"', $html);
        $this->assertStringContainsString('x-model="query"', $html);
        $this->assertStringContainsString('inputmode="search"', $html);
        $this->assertStringContainsString('placeholder="Buscar por nombre o apellidos"', $html);
        $this->assertMatchesRegularExpression('/<ul id="field-users-list"\s+role="listbox"\s+aria-labelledby="field-users-label"\s+aria-multiselectable="true"/', $html);
        $this->assertStringContainsString('<template x-for="option in filtered" x-bind:key="option.value">', $html);
        $this->assertStringContainsString('x-on:mousedown.prevent="toggle(option)"', $html);
        $this->assertStringContainsString('aria-label="Seleccionados"', $html);
        $this->assertStringContainsString('x-on:click="remove(option)"', $html);
        $this->assertStringContainsString('<p role="status" class="sr-only" x-text="status"></p>', $html);
        $this->assertStringNotContainsString('Lista predeterminada', $html);
        $this->assertStringNotContainsString('aria-describedby', $html);
        $this->assertNoAutofocus($html);
        $this->assertUniqueIds($html);
        $this->assertCspSafe($html);
    }

    public function test_selected_takes_collections_of_values_or_models(): void
    {
        $empty = $this->render('<x-ui.picker name="users[]" label="Asistentes" :options="'.self::PEOPLE.'" :selected="collect()" />');

        $this->assertDoesNotMatchRegularExpression('/<option value="\d"[^>]*selected/', $empty);

        $values = $this->render('<x-ui.picker name="users[]" label="Asistentes" :options="'.self::PEOPLE.'" :selected="$selected" />', [
            'selected' => collect([1, 3]),
        ]);

        $this->assertMatchesRegularExpression('/<option value="1"[^>]*\sselected/', $values);
        $this->assertMatchesRegularExpression('/<option value="3"\s+selected/', $values);
        $this->assertDoesNotMatchRegularExpression('/<option value="2"[^>]*selected/', $values);

        // Models count by their key, a single model too
        $models = $this->render('<x-ui.picker name="users[]" label="Asistentes" :options="'.self::PEOPLE.'" :selected="$selected" />', [
            'selected' => collect([(new User)->forceFill(['id' => 2])]),
        ]);

        $this->assertMatchesRegularExpression('/<option value="2"\s+selected/', $models);
        $this->assertDoesNotMatchRegularExpression('/<option value="1"[^>]*selected/', $models);

        $one = $this->render('<x-ui.picker name="student" label="Alumno" :options="'.self::PEOPLE.'" :max="1" :selected="$selected" />', [
            'selected' => (new User)->forceFill(['id' => 3]),
        ]);

        $this->assertMatchesRegularExpression('/<option value="3"\s+selected/', $one);
        $this->assertMatchesRegularExpression('/<option value=""\s*>Sin selección<\/option>/', $one);
    }

    public function test_old_input_wins_over_the_selected_prop(): void
    {
        $this->withOldInput(['users' => ['3']]);

        $html = $this->render('<x-ui.picker name="users[]" label="Asistentes" :options="'.self::PEOPLE.'" :selected="[2]" />');

        $this->assertMatchesRegularExpression('/<option value="3"\s+selected/', $html);
        $this->assertDoesNotMatchRegularExpression('/<option value="2"[^>]*selected/', $html);
    }

    public function test_old_input_without_the_key_empties_what_the_prop_selects(): void
    {
        // Nobody was left: the key does not travel, only the rest of the form came back
        $this->withOldInput(['_token' => 'x', 'title' => 'Reunión 3']);

        $html = $this->render('<x-ui.picker name="users[]" label="Asistentes" :options="'.self::PEOPLE.'" :selected="[1, 2]" />');

        $this->assertDoesNotMatchRegularExpression('/<option value="\d"[^>]*selected/', $html);
    }

    public function test_inside_a_get_form_the_prop_decides_even_with_old_input(): void
    {
        $this->withOldInput(['_token' => 'x', 'actor' => '3']);

        $html = $this->render('<x-ui.form action="/activity" method="get"><x-ui.picker name="actor" label="Persona" :options="'.self::PEOPLE.'" :max="1" :selected="2" /></x-ui.form>');

        $this->assertMatchesRegularExpression('/<option value="2"\s+selected/', $html);
        $this->assertDoesNotMatchRegularExpression('/<option value="3"[^>]*selected/', $html);
    }

    public function test_presets_travel_escaped_and_get_their_own_controls(): void
    {
        $html = $this->render('<x-ui.picker name="users[]" label="Asistentes" :options="'.self::PEOPLE.'" :presets="$presets" />', [
            'presets' => [['label' => 'Comité de <Programa>', 'values' => [1, 2]], ['label' => 'Secretaría', 'values' => ['3']]],
        ]);

        $this->assertStringContainsString('data-presets="[{&quot;label&quot;:&quot;Comité de &lt;Programa&gt;&quot;,&quot;values&quot;:[&quot;1&quot;,&quot;2&quot;]},{&quot;label&quot;:&quot;Secretaría&quot;,&quot;values&quot;:[&quot;3&quot;]}]"', $html);
        $this->assertStringContainsString('<label for="field-users-preset" class="text-ink-muted">Lista predeterminada</label>', $html);
        $this->assertStringContainsString('<select id="field-users-preset" x-model="presetIndex"', $html);
        $this->assertStringContainsString('<option value="0">Comité de &lt;Programa&gt; (2)</option>', $html);
        $this->assertStringContainsString('<option value="1">Secretaría (1)</option>', $html);
        $this->assertStringContainsString('x-on:click="askPreset"', $html);
        $this->assertStringContainsString('x-bind:disabled="presetIndex === \'\'"', $html);
        $this->assertStringContainsString('role="group" aria-label="Confirmar la carga"', $html);
        $this->assertStringContainsString('x-on:click="applyPreset"', $html);
        $this->assertStringContainsString('x-on:click="cancelPreset" x-ref="cancel"', $html);
        $this->assertUniqueIds($html);
        $this->assertCspSafe($html);
    }

    public function test_a_single_choice_drops_multiple_and_the_chips(): void
    {
        $html = $this->render('<x-ui.picker name="student" label="Alumno" :options="'.self::PEOPLE.'" :max="1" />');

        $this->assertMatchesRegularExpression('/<select name="student"\s+id="field-student-native"/', $html);
        $this->assertDoesNotMatchRegularExpression('/<select [^>]*\smultiple[\s>]/', $html);
        $this->assertMatchesRegularExpression('/<option value=""\s+selected\s*>Sin selección<\/option>/', $html);
        $this->assertStringNotContainsString('aria-multiselectable', $html);
        $this->assertStringNotContainsString('aria-label="Seleccionados"', $html);
        $this->assertStringContainsString('data-max="1"', $html);

        $chosen = $this->render('<x-ui.picker name="student" label="Alumno" :options="'.self::PEOPLE.'" :max="1" :selected="2" />');

        $this->assertMatchesRegularExpression('/<option value=""\s*>Sin selección<\/option>/', $chosen);
        $this->assertMatchesRegularExpression('/<option value="2"\s+selected/', $chosen);
    }

    public function test_hint_and_error_describe_the_combobox(): void
    {
        $html = $this->render('<x-ui.picker name="users[]" label="Asistentes" :options="'.self::PEOPLE.'" hint="Intro añade" error="Elige a alguien" />');

        $this->assertMatchesRegularExpression('/<input id="field-users" [^>]*aria-invalid="true"/', $html);
        $this->assertMatchesRegularExpression('/<input id="field-users" [^>]*aria-describedby="field-users-error field-users-hint"/', $html);
        $this->assertStringContainsString('<p id="field-users-hint" class="text-sm text-ink-muted">Intro añade</p>', $html);
        $this->assertStringContainsString('Elige a alguien', $html);
        $this->assertUniqueIds($html);

        $this->withViewErrors(['users' => 'Falta gente', 'reviewers.0' => 'No válido']);

        $this->assertStringContainsString('Falta gente', $this->render('<x-ui.picker name="users[]" label="A" :options="'.self::PEOPLE.'" />'));
        $this->assertStringContainsString('No válido', $this->render('<x-ui.picker name="reviewers[]" label="A" :options="'.self::PEOPLE.'" />'));
    }

    public function test_max_visible_and_the_consumer_class_reach_the_root(): void
    {
        $html = $this->render('<x-ui.picker name="users[]" label="Asistentes" :options="'.self::PEOPLE.'" :max-visible="20" class="lg:col-span-2" />');

        $this->assertStringContainsString('data-max-visible="20"', $html);
        $this->assertStringContainsString('class="grid grid-cols-[minmax(0,1fr)] gap-1.5 lg:col-span-2"', $html);
    }

    public function test_labels_and_options_are_escaped(): void
    {
        $html = $this->render('<x-ui.picker name="users[]" :label="$label" :options="$options" />', [
            'label' => '<b>Gente</b>',
            'options' => [['value' => '1', 'label' => '<i>Pérez</i>', 'hint' => '"x"']],
        ]);

        $this->assertStringContainsString('&lt;b&gt;Gente&lt;/b&gt;', $html);
        $this->assertStringContainsString('&lt;i&gt;Pérez&lt;/i&gt;', $html);
        $this->assertStringContainsString('data-hint="&quot;x&quot;"', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }
}
