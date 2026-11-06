<?php

namespace Tests\Feature\Components;

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

        $this->assertStringContainsString('<fieldset x-data="checkboxGroup" data-name="roles[]"', $html);
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

    public function test_old_input_wins_over_the_selected_prop(): void
    {
        $this->withOldInput(['roles' => ['SECRETARY']]);

        $html = $this->render('<x-ui.checkbox-group name="roles" legend="Roles" :options="'.self::ROLES.'" :selected="[\'STUDENT\']" />');

        $this->assertMatchesRegularExpression('/<input id="field-roles-secretary"[^>]*\schecked[\s>]/', $html);
        $this->assertDoesNotMatchRegularExpression('/<input id="field-roles-student"[^>]*\schecked[\s>]/', $html);
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
