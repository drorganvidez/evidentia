<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;

class ChoiceTest extends ComponentTestCase
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

    public function test_a_checkbox_lives_inside_its_label_and_covers_the_whole_target(): void
    {
        $html = $this->render('<x-ui.choice name="remember" label="Recordarme" />');

        $this->assertStringContainsString('<label for="field-remember"', $html);
        $this->assertStringContainsString('min-h-11', $html);
        $this->assertMatchesRegularExpression('/<input id="field-remember" type="checkbox" name="remember"\s+value="1"\s+class="peer absolute inset-0 size-full/', $html);
        $this->assertStringContainsString('rounded-xs', $html);
        $this->assertStringContainsString('peer-checked:bg-brand', $html);
        $this->assertStringContainsString('<svg', $html, 'The check mark');
        $this->assertStringContainsString('Recordarme', $html);
        $this->assertStringNotContainsString('role=', $html);
        $this->assertDoesNotMatchRegularExpression('/\schecked[\s>]/', $html);
        $this->assertStringNotContainsString('aria-describedby', $html);
        $this->assertStringNotContainsString('x-data', $html);
        $this->assertNoAutofocus($html);
        $this->assertUniqueIds($html);
        $this->assertCspSafe($html);
    }

    public function test_a_radio_takes_its_value_into_the_id_and_a_round_box(): void
    {
        $online = $this->render('<x-ui.choice type="radio" name="participation" value="online" label="En línea" />');
        $onsite = $this->render('<x-ui.choice type="radio" name="participation" value="onsite" label="Presencial" />');

        $this->assertStringContainsString('<label for="field-participation-online"', $online);
        $this->assertMatchesRegularExpression('/<input id="field-participation-online" type="radio" name="participation"\s+value="online"/', $online);
        $this->assertStringContainsString('rounded-full', $online);
        $this->assertStringContainsString('after:bg-on-brand', $online);
        $this->assertStringNotContainsString('<svg', $online);
        $this->assertStringContainsString('id="field-participation-onsite"', $onsite);
        $this->assertUniqueIds($online.$onsite);
    }

    public function test_a_switch_is_a_checkbox_with_the_switch_role(): void
    {
        $html = $this->render('<x-ui.choice type="switch" name="allow_access" label="Permitir acceso" />');

        $this->assertStringContainsString('type="checkbox"', $html);
        $this->assertStringContainsString('role="switch"', $html);
        $this->assertStringContainsString('peer-checked:bg-aurora-500', $html);
        $this->assertStringContainsString('peer-checked:after:translate-x-4', $html);
        $this->assertStringNotContainsString('<svg', $html);
    }

    public function test_checked_follows_the_prop_or_the_old_input(): void
    {
        $this->assertMatchesRegularExpression('/\schecked[\s>]/', $this->render('<x-ui.choice name="remember" label="Recordarme" :checked="true" />'));

        $this->withOldInput(['remember' => '1', 'roles' => ['COORDINATOR'], 'participation' => 'online']);

        $this->assertMatchesRegularExpression('/\schecked[\s>]/', $this->render('<x-ui.choice name="remember" label="Recordarme" />'));
        $this->assertMatchesRegularExpression('/\schecked[\s>]/', $this->render('<x-ui.choice name="roles[]" value="COORDINATOR" label="Coordinador" />'));
        $this->assertDoesNotMatchRegularExpression('/\schecked[\s>]/', $this->render('<x-ui.choice name="roles[]" value="SECRETARY" label="Secretario" />'));
        $this->assertMatchesRegularExpression('/\schecked[\s>]/', $this->render('<x-ui.choice type="radio" name="participation" value="online" label="En línea" />'));
        $this->assertDoesNotMatchRegularExpression('/\schecked[\s>]/', $this->render('<x-ui.choice type="radio" name="participation" value="onsite" label="Presencial" />'));
        $this->assertMatchesRegularExpression('/\schecked[\s>]/', $this->render('<x-ui.choice name="remember" label="Recordarme" :checked="false" />'), 'The input that came back wins over the prop');
    }

    public function test_without_old_input_the_prop_decides(): void
    {
        $this->assertMatchesRegularExpression('/\schecked[\s>]/', $this->render('<x-ui.choice type="switch" name="allow_access" label="Permitir acceso" :checked="true" />'));
        $this->assertDoesNotMatchRegularExpression('/\schecked[\s>]/', $this->render('<x-ui.choice type="switch" name="allow_access" label="Permitir acceso" :checked="false" />'));
    }

    public function test_old_input_without_the_key_unticks_what_the_prop_ticks(): void
    {
        // The switch was turned off: an unticked box does not travel, only the rest of the form came back
        $this->withOldInput(['_token' => 'x', 'email' => 'no-es-un-correo']);

        $this->assertDoesNotMatchRegularExpression('/\schecked[\s>]/', $this->render('<x-ui.choice type="switch" name="allow_access" label="Permitir acceso" :checked="true" />'));
        $this->assertDoesNotMatchRegularExpression('/\schecked[\s>]/', $this->render('<x-ui.form action="/users/3" method="put"><x-ui.choice type="switch" name="allow_access" label="Permitir acceso" :checked="true" /></x-ui.form>'));
    }

    public function test_old_input_with_the_key_ticks_what_the_prop_leaves_unticked(): void
    {
        $this->withOldInput(['allow_access' => '1']);

        $this->assertMatchesRegularExpression('/\schecked[\s>]/', $this->render('<x-ui.choice type="switch" name="allow_access" label="Permitir acceso" :checked="false" />'));
    }

    public function test_inside_a_get_form_the_prop_decides_even_with_old_input(): void
    {
        // A filter form keeps what the query gave it when another form of the page comes back with errors
        $this->withOldInput(['_token' => 'x', 'remember' => '1']);

        $ticked = $this->render('<x-ui.form action="/activity" method="get"><x-ui.choice type="radio" name="role" value="" label="Todos" :checked="true" /></x-ui.form>');
        $plain = $this->render('<x-ui.form action="/activity" method="get"><x-ui.choice name="remember" label="Recordarme" /></x-ui.form>');

        $this->assertMatchesRegularExpression('/\schecked[\s>]/', $ticked);
        $this->assertDoesNotMatchRegularExpression('/\schecked[\s>]/', $plain);
    }

    public function test_an_array_name_gets_the_value_in_its_id(): void
    {
        $html = $this->render('<x-ui.choice name="roles[]" value="COORDINATOR" label="Coordinador" />');

        $this->assertStringContainsString('<label for="field-roles-coordinator"', $html);
        $this->assertStringContainsString('name="roles[]"', $html);
    }

    public function test_hint_and_error_are_linked_and_the_error_may_come_from_the_bag(): void
    {
        $html = $this->render('<x-ui.choice name="terms" label="Acepto" hint="Lee las condiciones" error="Debes aceptar las condiciones" />');

        $this->assertStringContainsString('aria-describedby="field-terms-error field-terms-hint"', $html);
        $this->assertStringContainsString('aria-invalid="true"', $html);
        $this->assertStringContainsString('<span id="field-terms-hint" class="text-sm text-ink-muted">Lee las condiciones</span>', $html);
        $this->assertStringContainsString('<p id="field-terms-error" class="flex items-center gap-1.5 text-sm text-rejected-ink">', $html);
        $this->assertStringContainsString('Debes aceptar las condiciones', $html);
        $this->assertUniqueIds($html);

        $this->withViewErrors(['terms' => 'Obligatorio', 'roles.0' => 'Rol no válido']);

        $this->assertStringContainsString('Obligatorio', $this->render('<x-ui.choice name="terms" label="Acepto" />'));
        $this->assertStringContainsString('Rol no válido', $this->render('<x-ui.choice name="roles[]" value="X" label="X" />'));
        $this->assertStringNotContainsString('aria-invalid', $this->render('<x-ui.choice name="other" label="Otro" />'));
    }

    public function test_disabled_reaches_the_input_and_the_label_shows_it(): void
    {
        $html = $this->render('<x-ui.choice name="locked" label="Bloqueado" disabled />');

        $this->assertMatchesRegularExpression('/<input [^>]*\sdisabled[\s>]/', $html);
        $this->assertStringContainsString('has-[:disabled]:cursor-not-allowed', $html);
        $this->assertStringContainsString('peer-disabled:text-ink-muted', $html);
    }

    public function test_the_consumer_class_goes_to_the_root_and_the_rest_to_the_input(): void
    {
        $html = $this->render('<x-ui.choice name="remember" label="Recordarme" class="mt-2" x-on:change="sync" data-role="x" />');

        $this->assertMatchesRegularExpression('/<div class="grid grid-cols-\[minmax\(0,1fr\)\] gap-1 mt-2"\s*>/', $html);
        $this->assertMatchesRegularExpression('/<input [^>]*x-on:change="sync"[^>]*>/', $html);
        $this->assertMatchesRegularExpression('/<input [^>]*data-role="x"[^>]*>/', $html);
        $this->assertSame(1, substr_count($html, 'mt-2'));
        $this->assertCspSafe($html);
    }

    public function test_label_and_hint_are_escaped(): void
    {
        $html = $this->render('<x-ui.choice name="remember" :label="$label" :hint="$hint" />', ['label' => '<b>Sí</b>', 'hint' => '<i>pista</i>']);

        $this->assertStringContainsString('&lt;b&gt;Sí&lt;/b&gt;', $html);
        $this->assertStringContainsString('&lt;i&gt;pista&lt;/i&gt;', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }
}
