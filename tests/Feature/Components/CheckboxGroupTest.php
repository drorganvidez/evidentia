<?php

namespace Tests\Feature\Components;

use App\Models\Committee;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;

class CheckboxGroupTest extends ComponentTestCase
{
    private const ROLES = "[['value' => 'STUDENT', 'label' => 'Estudiante', 'hint' => 'Sube evidencias'], ['value' => 'COORDINATOR', 'label' => 'Coordinador'], ['value' => 'SECRETARY', 'label' => 'Secretario']]";

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

    public function test_it_is_a_fieldset_of_array_named_boxes_wired_to_the_alpine_module(): void
    {
        $html = $this->render('<x-ui.checkbox-group name="roles" legend="Roles" :options="'.self::ROLES.'" :selected="[\'STUDENT\']" />');

        $this->assertStringContainsString('<fieldset x-data="checkboxGroup" data-name="roles[]" id="field-roles" tabindex="-1"', $html);
        $this->assertStringContainsString('<legend class="text-sm font-semibold">Roles', $html);
        $this->assertSame(3, substr_count($html, ' name="roles[]"'));
        $this->assertSame(3, substr_count($html, 'x-on:change="sync"'));
        $this->assertStringContainsString('id="field-roles-student"', $html);
        $this->assertStringContainsString('id="field-roles-coordinator"', $html);
        $this->assertStringContainsString('id="field-roles-secretary"', $html);
        $this->assertStringContainsString('Sube evidencias', $html);
        $this->assertMatchesRegularExpression('/<input id="field-roles-student"[^>]*\schecked[\s>]/', $html);
        $this->assertDoesNotMatchRegularExpression('/<input id="field-roles-coordinator"[^>]*\schecked[\s>]/', $html);
        $this->assertStringContainsString('<div class="grid gap-2">', $html);
        $this->assertStringNotContainsString('md:grid-cols-2', $html);
        $this->assertDoesNotMatchRegularExpression('/<fieldset [^>]*aria-describedby/', $html);
        $this->assertNoAutofocus($html);
        $this->assertUniqueIds($html);
        $this->assertCspSafe($html);
    }

    public function test_the_name_may_already_carry_the_brackets_and_two_columns_are_a_literal_class(): void
    {
        $html = $this->render('<x-ui.checkbox-group name="roles[]" legend="Roles" :options="'.self::ROLES.'" :columns="2" />');

        $this->assertStringContainsString('data-name="roles[]"', $html);
        $this->assertSame(3, substr_count($html, ' name="roles[]"'));
        $this->assertStringNotContainsString('roles[][]', $html);
        $this->assertStringContainsString('<div class="grid gap-2 md:grid-cols-2">', $html);
    }

    public function test_selected_takes_collections_of_values_or_models(): void
    {
        $empty = $this->render('<x-ui.checkbox-group name="roles" legend="Roles" :options="'.self::ROLES.'" :selected="collect()" />');

        $this->assertDoesNotMatchRegularExpression('/\schecked[\s>]/', $empty);

        $values = $this->render('<x-ui.checkbox-group name="roles" legend="Roles" :options="'.self::ROLES.'" :selected="$selected" />', [
            'selected' => collect(['STUDENT', 'SECRETARY']),
        ]);

        $this->assertMatchesRegularExpression('/<input id="field-roles-student"[^>]*\schecked[\s>]/', $values);
        $this->assertMatchesRegularExpression('/<input id="field-roles-secretary"[^>]*\schecked[\s>]/', $values);
        $this->assertDoesNotMatchRegularExpression('/<input id="field-roles-coordinator"[^>]*\schecked[\s>]/', $values);

        // Models count by their key
        $models = $this->render('<x-ui.checkbox-group name="committees" legend="Comités" :options="$options" :selected="$selected" />', [
            'options' => [['value' => 1, 'label' => 'Programa'], ['value' => 2, 'label' => 'Logística'], ['value' => 3, 'label' => 'Comunicación']],
            'selected' => collect([(new Committee)->forceFill(['id' => 2]), (new Committee)->forceFill(['id' => 3])]),
        ]);

        $this->assertDoesNotMatchRegularExpression('/<input id="field-committees-1"[^>]*\schecked[\s>]/', $models);
        $this->assertMatchesRegularExpression('/<input id="field-committees-2"[^>]*\schecked[\s>]/', $models);
        $this->assertMatchesRegularExpression('/<input id="field-committees-3"[^>]*\schecked[\s>]/', $models);
    }

    public function test_old_input_wins_over_the_selected_prop(): void
    {
        $this->withOldInput(['roles' => ['SECRETARY']]);

        $html = $this->render('<x-ui.checkbox-group name="roles" legend="Roles" :options="'.self::ROLES.'" :selected="[\'STUDENT\']" />');

        $this->assertMatchesRegularExpression('/<input id="field-roles-secretary"[^>]*\schecked[\s>]/', $html);
        $this->assertDoesNotMatchRegularExpression('/<input id="field-roles-student"[^>]*\schecked[\s>]/', $html);
    }

    public function test_old_input_without_the_key_empties_what_the_prop_selects(): void
    {
        // Every box was unticked: the key does not travel, only the rest of the form came back
        $this->withOldInput(['_token' => 'x', 'name' => 'Juan']);

        $html = $this->render('<x-ui.checkbox-group name="roles" legend="Roles" :options="'.self::ROLES.'" :selected="[\'STUDENT\', \'SECRETARY\']" />');

        $this->assertDoesNotMatchRegularExpression('/\schecked[\s>]/', $html);
    }

    public function test_inside_a_get_form_the_prop_decides_even_with_old_input(): void
    {
        $this->withOldInput(['_token' => 'x', 'roles' => ['SECRETARY']]);

        $html = $this->render('<x-ui.form action="/users" method="get"><x-ui.checkbox-group name="roles" legend="Roles" :options="'.self::ROLES.'" :selected="[\'STUDENT\']" /></x-ui.form>');

        $this->assertMatchesRegularExpression('/<input id="field-roles-student"[^>]*\schecked[\s>]/', $html);
        $this->assertDoesNotMatchRegularExpression('/<input id="field-roles-secretary"[^>]*\schecked[\s>]/', $html);
    }

    public function test_hint_error_and_required_describe_the_group(): void
    {
        $html = $this->render('<x-ui.checkbox-group name="roles" legend="Roles" :options="'.self::ROLES.'" hint="Elige uno o varios" error="Elige al menos un rol" required />');

        $this->assertMatchesRegularExpression('/<fieldset [^>]*aria-describedby="field-roles-error field-roles-hint"/', $html);
        $this->assertStringContainsString('<p id="field-roles-hint" class="text-sm text-ink-muted">Elige uno o varios</p>', $html);
        $this->assertStringContainsString('<p id="field-roles-error" class="flex items-center gap-1.5 text-sm text-rejected-ink">', $html);
        $this->assertStringContainsString('Elige al menos un rol', $html);
        $this->assertStringContainsString('after:content-[\'*\']', $html);
        $this->assertStringContainsString('<span class="sr-only">(obligatorio)</span>', $html);
        $this->assertStringNotContainsString('aria-required', $html);
        $this->assertUniqueIds($html);

        $this->withViewErrors(['roles' => 'Obligatorio']);
        $this->assertStringContainsString('Obligatorio', $this->render('<x-ui.checkbox-group name="roles" legend="Roles" :options="'.self::ROLES.'" />'));

        $this->withViewErrors(['roles.1' => 'Rol no válido']);
        $this->assertStringContainsString('Rol no válido', $this->render('<x-ui.checkbox-group name="roles" legend="Roles" :options="'.self::ROLES.'" />'));
    }

    public function test_the_dependent_slot_renders_inside_the_fieldset(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-ui.checkbox-group name="roles" legend="Roles" :options="[['value' => 'COORDINATOR', 'label' => 'Coordinador']]" class="mt-4">
                <x-slot:dependent>
                    <div x-show="hasAny('COORDINATOR', 'SECRETARY')" x-cloak>Comité</div>
                </x-slot:dependent>
            </x-ui.checkbox-group>
            BLADE);

        $this->assertStringContainsString('class="grid min-w-0 grid-cols-[minmax(0,1fr)] gap-3 mt-4"', $html);
        $this->assertStringContainsString('<div x-show="hasAny(\'COORDINATOR\', \'SECRETARY\')" x-cloak>Comité</div>', $html);
        $this->assertLessThan(strpos($html, '</fieldset>'), strpos($html, 'Comité'));
        $this->assertGreaterThan(strpos($html, 'name="roles[]"'), strpos($html, 'Comité'));
        $this->assertCspSafe($html);
    }

    public function test_the_legend_and_the_labels_are_escaped(): void
    {
        $html = $this->render('<x-ui.checkbox-group name="roles" :legend="$legend" :options="$options" />', [
            'legend' => '<b>Roles</b>',
            'options' => [['value' => 'A', 'label' => '<i>A</i>']],
        ]);

        $this->assertStringContainsString('&lt;b&gt;Roles&lt;/b&gt;', $html);
        $this->assertStringContainsString('&lt;i&gt;A&lt;/i&gt;', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }
}
