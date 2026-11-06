<?php

namespace Tests\Feature\Components;

use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\ViewException;
use InvalidArgumentException;

class FieldTest extends ComponentTestCase
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

    public function test_the_label_points_to_the_control_and_the_control_keeps_sixteen_pixels(): void
    {
        $html = $this->render('<x-ui.field name="title" label="Título" />');

        $this->assertStringContainsString('<label for="field-title" class="text-sm font-semibold">Título', $html);
        $this->assertMatchesRegularExpression('/<input id="field-title" name="title" type="text"\s+value=""/', $html);
        $this->assertStringContainsString('text-control', $html);
        $this->assertStringContainsString('w-0 min-w-0 flex-1', $html);
        $this->assertStringContainsString('grid grid-cols-[minmax(0,1fr)] gap-1.5', $html);
        $this->assertStringContainsString('h-10.5', $html);
        $this->assertStringNotContainsString('aria-describedby', $html);
        $this->assertStringNotContainsString('aria-invalid="true"', $html);
        $this->assertStringNotContainsString('x-data', $html);
        $this->assertNoAutofocus($html);
        $this->assertUniqueIds($html);
        $this->assertCspSafe($html);
    }

    public function test_hint_error_and_counter_are_linked_in_order(): void
    {
        $html = $this->render('<x-ui.field name="title" label="Título" hint="Mínimo 5 caracteres" error="El título es obligatorio" />');

        $this->assertStringContainsString('aria-describedby="field-title-error field-title-hint"', $html);
        $this->assertStringContainsString('aria-invalid="true"', $html);
        $this->assertStringContainsString('<p id="field-title-hint" class="text-sm text-ink-muted">Mínimo 5 caracteres</p>', $html);
        $this->assertMatchesRegularExpression('/<p id="field-title-error" class="flex items-center gap-1.5 text-sm text-rejected-ink">\s*<svg[^>]*>.*?<\/svg>\s*El título es obligatorio\s*<\/p>/s', $html);
        $this->assertStringContainsString('has-[[aria-invalid=true]]:border-rejected', $html);
        $this->assertUniqueIds($html);

        $counted = $this->render('<x-ui.field name="notes" label="Notas" type="textarea" hint="Pista" :maxlength="300" />');

        $this->assertStringContainsString('aria-describedby="field-notes-hint field-notes-count"', $counted);
    }

    public function test_the_error_comes_from_the_error_bag_under_the_dot_key(): void
    {
        $this->withViewErrors(['meta.city' => 'Indica la ciudad', 'users' => 'Elige a alguien']);

        $nested = $this->render('<x-ui.field name="meta[city]" label="Ciudad" />');

        $this->assertStringContainsString('id="field-meta-city"', $nested);
        $this->assertStringContainsString('aria-describedby="field-meta-city-error"', $nested);
        $this->assertStringContainsString('Indica la ciudad', $nested);

        $this->assertStringContainsString('Elige a alguien', $this->render('<x-ui.field name="users[]" label="Personas" />'));
        $this->assertStringNotContainsString('aria-invalid="true"', $this->render('<x-ui.field name="title" label="Título" />'));
    }

    public function test_required_disabled_and_readonly_reach_the_control(): void
    {
        $required = $this->render('<x-ui.field name="title" label="Título" required />');

        $this->assertMatchesRegularExpression('/<input [^>]*\srequired[\s>]/', $required);
        $this->assertStringContainsString('aria-required="true"', $required);
        $this->assertStringContainsString('<span aria-hidden="true" class="text-rejected-ink after:content-[\'*\']"></span>', $required);

        $disabled = $this->render('<x-ui.field name="title" label="Título" disabled />');

        $this->assertMatchesRegularExpression('/<input [^>]*\sdisabled[\s>]/', $disabled);
        $this->assertStringNotContainsString('aria-required', $disabled);
        $this->assertStringNotContainsString('after:content', $disabled);

        $this->assertMatchesRegularExpression('/<input [^>]*\sreadonly[\s>]/', $this->render('<x-ui.field name="title" label="Título" readonly />'));
    }

    public function test_old_input_wins_over_the_value_even_when_empty(): void
    {
        $this->withOldInput(['title' => 'Lo que escribí', 'notes' => '']);

        $this->assertStringContainsString('value="Lo que escribí"', $this->render('<x-ui.field name="title" label="Título" value="Original" />'));
        $this->assertStringContainsString('value=""', $this->render('<x-ui.field name="notes" label="Notas" value="Original" />'));
        $this->assertStringContainsString('value="Original"', $this->render('<x-ui.field name="other" label="Otro" value="Original" />'));
    }

    public function test_a_date_takes_the_format_of_a_date_input(): void
    {
        $html = $this->render('<x-ui.field name="meetings_date" label="Día" type="date" :value="$value" />', ['value' => Carbon::parse('2026-11-06 09:30')]);

        $this->assertMatchesRegularExpression('/<input id="field-meetings-date" name="meetings_date" type="date"\s+value="2026-11-06"/', $html);
    }

    public function test_a_date_takes_the_format_of_a_time_input(): void
    {
        $html = $this->render('<x-ui.field name="meetings_time" label="Hora" type="time" :value="$value" />', ['value' => Carbon::parse('2026-11-06 09:30')]);

        $this->assertMatchesRegularExpression('/type="time"\s+value="09:30"/', $html);
    }

    public function test_a_date_takes_the_format_of_a_datetime_local_input(): void
    {
        $html = $this->render('<x-ui.field name="deadline" label="Plazo" type="datetime-local" :value="$value" />', ['value' => new \DateTimeImmutable('2026-11-06 09:30')]);

        $this->assertMatchesRegularExpression('/type="datetime-local"\s+value="2026-11-06T09:30"/', $html);
    }

    public function test_a_date_in_any_other_control_reads_as_day_month_year(): void
    {
        $html = $this->render('<x-ui.field name="signed_at" label="Firmada" :value="$value" readonly />', ['value' => Carbon::parse('2026-11-06 09:30')]);

        $this->assertMatchesRegularExpression('/type="text"\s+value="06\/11\/2026 09:30"/', $html);
    }

    public function test_a_backed_enum_gives_its_value_to_an_input_and_to_a_select(): void
    {
        $input = $this->render('<x-ui.field name="committee" label="Comité" :value="$value" />', ['value' => FieldTestCommittee::Logistics]);

        $this->assertMatchesRegularExpression('/type="text"\s+value="logistica"/', $input);

        $select = $this->render('<x-ui.field name="committee" label="Comité" type="select" :options="[\'programa\' => \'Programa\', \'logistica\' => \'Logística\']" :value="$value" />', ['value' => FieldTestCommittee::Logistics]);

        $this->assertMatchesRegularExpression('/<option value="logistica"\s+selected\s*>\s*Logística<\/option>/', $select);
        $this->assertDoesNotMatchRegularExpression('/<option value="programa"[^>]*selected/', $select);
    }

    public function test_a_stringable_prints_its_text(): void
    {
        $html = $this->render('<x-ui.field name="notes" label="Notas" type="textarea" :value="$value" />', ['value' => Str::of('Montaje del photocall')]);

        $this->assertStringContainsString('>Montaje del photocall</textarea>', $html);
    }

    public function test_an_array_brought_back_by_old_input_prints_nothing(): void
    {
        $this->withOldInput(['tags' => ['a', 'b']]);

        $html = $this->render('<x-ui.field name="tags[]" label="Etiquetas" value="Original" />');

        $this->assertMatchesRegularExpression('/name="tags\[\]" type="text"\s+value=""/', $html);
    }

    public function test_any_other_object_as_the_value_fails_loudly(): void
    {
        try {
            $this->render('<x-ui.field name="committee" label="Comité" :value="$value" />', ['value' => (object) ['id' => 2]]);
            $this->fail('Rendered an object as the value');
        } catch (ViewException $exception) {
            while ($exception->getPrevious() !== null) {
                $exception = $exception->getPrevious();
            }

            $this->assertInstanceOf(InvalidArgumentException::class, $exception);
            $this->assertSame('The field value must be a scalar, a date, a backed enum or a Stringable.', $exception->getMessage());
        }
    }

    public function test_a_select_marks_the_placeholder_or_the_chosen_option_and_never_prints_a_value(): void
    {
        $blade = '<x-ui.field name="committee" label="Comité" type="select" :options="[1 => \'Programa\', 2 => \'Logística\']" placeholder="Elige un comité" />';

        $empty = $this->render($blade);

        $this->assertMatchesRegularExpression('/<select id="field-committee" name="committee" class="[^"]*appearance-none pr-8[^"]*"/', $empty);
        $this->assertStringNotContainsString('<select id="field-committee" name="committee" value=', $empty);
        $this->assertMatchesRegularExpression('/<option value="" disabled\s+selected\s*>Elige un comité<\/option>/', $empty);
        $this->assertStringContainsString('<option value="1"', $empty);
        $this->assertMatchesRegularExpression('/>\s*Logística<\/option>/', $empty);
        $this->assertStringContainsString('<svg', $empty, 'The decorative chevron');
        $this->assertStringContainsString('pointer-events-none absolute right-3', $empty);
        $this->assertDoesNotMatchRegularExpression('/selected\s*>\s*Programa/', $empty);

        $this->withOldInput(['committee' => '2']);
        $chosen = $this->render($blade);

        $this->assertMatchesRegularExpression('/<option value="" disabled\s*>Elige un comité<\/option>/', $chosen);
        $this->assertMatchesRegularExpression('/<option value="2"\s+selected\s*>\s*Logística<\/option>/', $chosen);
        $this->assertDoesNotMatchRegularExpression('/<option value="1"[^>]*selected/', $chosen);

        $list = $this->render('<x-ui.field name="kind" label="Tipo" type="select" :options="$options" />', [
            'options' => [['value' => 'a', 'label' => 'Primera'], ['value' => 'b', 'label' => 'Segunda', 'disabled' => true]],
        ]);

        $this->assertStringContainsString('<option value="a"', $list);
        $this->assertMatchesRegularExpression('/<option value="b"\s+disabled\s*>\s*Segunda<\/option>/', $list);
    }

    public function test_a_textarea_sizes_to_its_rows_and_counts_with_maxlength(): void
    {
        $html = $this->render('<x-ui.field name="notes" label="Notas" type="textarea" :rows="4" :maxlength="300" value="Hola" />');

        $this->assertStringContainsString('<textarea id="field-notes" name="notes" rows="4"', $html);
        $this->assertStringContainsString('[--rows:4]', $html);
        $this->assertStringContainsString('field-sizing-content', $html);
        $this->assertStringContainsString('maxlength="300"', $html);
        $this->assertStringContainsString('x-data="charCount"', $html);
        $this->assertStringContainsString('x-on:input="update"', $html);
        $this->assertMatchesRegularExpression('/<span id="field-notes-count"\s+x-text="text">4 de 300 caracteres<\/span>/', $html);
        $this->assertStringContainsString('>Hola</textarea>', $html);
        $this->assertStringContainsString('items-start py-2', $html);
        $this->assertStringNotContainsString('h-10.5', $html);

        $plain = $this->render('<x-ui.field name="notes" label="Notas" type="textarea" />');

        $this->assertStringContainsString('[--rows:3]', $plain);
        $this->assertStringNotContainsString('x-data', $plain);
        $this->assertStringNotContainsString('caracteres', $plain);
    }

    public function test_a_password_gets_the_show_hide_toggle_unless_turned_off(): void
    {
        $html = $this->render('<x-ui.field name="password" label="Contraseña" type="password" autocomplete="current-password" />');

        $this->assertStringContainsString('x-data="passwordField"', $html);
        $this->assertStringContainsString('type="password"', $html);
        $this->assertStringContainsString('x-bind:type="type"', $html);
        $this->assertStringContainsString('autocomplete="current-password"', $html);
        $this->assertMatchesRegularExpression('/<button type="button"\s+x-on:click="toggle"\s+aria-pressed="false"/', $html);
        $this->assertStringContainsString('x-bind:aria-pressed="visible ? \'true\' : \'false\'"', $html);
        // The name stays put: only aria-pressed tells the state
        $this->assertStringContainsString('aria-label="Mostrar la contraseña"', $html);
        $this->assertStringContainsString('title="Mostrar la contraseña"', $html);
        $this->assertStringNotContainsString('x-bind:aria-label', $html);
        $this->assertStringNotContainsString('x-bind:title', $html);
        $this->assertStringContainsString('x-show="!visible"', $html);
        $this->assertStringContainsString('x-show="visible"', $html);
        $this->assertSame(2, substr_count($html, '<svg'));
        $this->assertCspSafe($html);

        $bare = $this->render('<x-ui.field name="password" label="Contraseña" type="password" :toggle="false" />');

        $this->assertStringNotContainsString('x-data', $bare);
        $this->assertStringNotContainsString('<button', $bare);
    }

    public function test_icon_prefix_suffix_size_hidden_label_and_trailing_slot(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-ui.field name="hours" label="Horas" type="number" icon="clock" prefix="aprox." suffix="h" size="lg" hide-label>
                <x-slot:trailing><kbd>/</kbd></x-slot:trailing>
            </x-ui.field>
            BLADE);

        $this->assertStringContainsString('<label for="field-hours" class="sr-only">Horas', $html);
        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('size-4.5 text-ink-muted', $html);
        $this->assertStringContainsString('<span class="shrink-0 text-md text-ink-muted">aprox.</span>', $html);
        $this->assertStringContainsString('<span class="shrink-0 text-md text-ink-muted">h</span>', $html);
        $this->assertStringContainsString('h-12', $html);
        $this->assertStringContainsString('<kbd>/</kbd>', $html);
        $this->assertLessThan(strpos($html, '<input'), strpos($html, 'aprox.'));
        $this->assertGreaterThan(strpos($html, '<input'), strpos($html, '>h</span>'));
    }

    public function test_the_consumer_class_goes_to_the_root_and_the_rest_to_the_control(): void
    {
        $html = $this->render('<x-ui.field name="hours" label="Horas" type="number" class="md:col-span-2" min="0" step="0.25" inputmode="decimal" x-model="hours" />');

        $this->assertMatchesRegularExpression('/<div class="grid grid-cols-\[minmax\(0,1fr\)\] gap-1\.5 md:col-span-2"\s*>/', $html);
        $this->assertMatchesRegularExpression('/<input [^>]*min="0"[^>]*>/', $html);
        $this->assertMatchesRegularExpression('/<input [^>]*step="0.25"[^>]*>/', $html);
        $this->assertMatchesRegularExpression('/<input [^>]*inputmode="decimal"[^>]*>/', $html);
        $this->assertMatchesRegularExpression('/<input [^>]*x-model="hours"[^>]*>/', $html);
        $this->assertSame(1, substr_count($html, 'md:col-span-2'));
    }

    public function test_an_explicit_id_and_an_unknown_type_fall_back_safely(): void
    {
        $html = $this->render('<x-ui.field name="title" label="Título" id="second-title" type="color" hint="x" />');

        $this->assertStringContainsString('<label for="second-title"', $html);
        $this->assertStringContainsString('id="second-title" name="title" type="text"', $html);
        $this->assertStringContainsString('aria-describedby="second-title-hint"', $html);
    }

    public function test_label_hint_value_and_placeholder_are_escaped(): void
    {
        $html = $this->render('<x-ui.field name="title" :label="$label" :hint="$hint" :value="$value" :placeholder="$placeholder" />', [
            'label' => '<b>Título</b>',
            'hint' => '<i>pista</i>',
            'value' => '"><script>',
            'placeholder' => '<u>ph</u>',
        ]);

        $this->assertStringContainsString('&lt;b&gt;Título&lt;/b&gt;', $html);
        $this->assertStringContainsString('&lt;i&gt;pista&lt;/i&gt;', $html);
        $this->assertStringContainsString('value="&quot;&gt;&lt;script&gt;"', $html);
        $this->assertStringContainsString('placeholder="&lt;u&gt;ph&lt;/u&gt;"', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }
}

enum FieldTestCommittee: string
{
    case Programme = 'programa';
    case Logistics = 'logistica';
}
