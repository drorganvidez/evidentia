<?php

namespace Tests\Feature\Components;

class MinutesEditorTest extends ComponentTestCase
{
    /** @var array<int, array<string, mixed>> */
    private array $points = [
        [
            'id' => 41,
            'title' => 'Lectura del acta <b>anterior</b>',
            'duration' => '10',
            'description' => ' Se aprueba. ',
            'agreements' => [['id' => 7, 'description' => 'Publicar el acta.'], 'Convocar la siguiente.'],
        ],
        ['point' => 'Presupuesto', 'agreements' => []],
    ];

    public function test_the_hidden_input_and_the_data_attribute_carry_the_normalised_escaped_json(): void
    {
        $html = $this->render('<x-ui.minutes-editor :points="$points" />', ['points' => $this->points]);

        $json = json_encode([
            ['id' => 41, 'title' => 'Lectura del acta <b>anterior</b>', 'duration' => 10, 'description' => 'Se aprueba.', 'agreements' => [
                ['id' => 7, 'description' => 'Publicar el acta.'],
                ['id' => null, 'description' => 'Convocar la siguiente.'],
            ]],
            ['id' => null, 'title' => 'Presupuesto', 'duration' => null, 'description' => '', 'agreements' => []],
        ]);

        $this->assertStringContainsString('<div x-data="minutesEditor" data-points="'.e($json).'"', $html);
        $this->assertMatchesRegularExpression('/<input type="hidden" name="points_json" value="'.preg_quote(e($json), '/').'"\s+x-bind:value="json">/', $html);
        $this->assertStringContainsString('&lt;b&gt;', $html);
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringContainsString('data-min="0"', $html);
        $this->assertMatchesRegularExpression('/data-id="field-points-json"\s+id="field-points-json"\s+tabindex="-1"\s+role="group"/', $html);
        $this->assertMatchesRegularExpression('/role="group"\s+aria-labelledby="field-points-json-label"/', $html);
        $this->assertStringContainsString('<p id="field-points-json-label" class="text-sm font-semibold">Puntos del acta</p>', $html);
        $this->assertStringContainsString('<p role="status" class="sr-only" x-text="announcement"></p>', $html);
        $this->assertStringNotContainsString('{{', $html, 'No Handlebars templates');
        $this->assertCspSafe($html);
        $this->assertUniqueIds($html);
        $this->assertNoAutofocus($html);
    }

    public function test_models_and_objects_are_accepted_as_points(): void
    {
        $html = $this->render('<x-ui.minutes-editor :points="$points" />', [
            'points' => [collect(['id' => '3', 'title' => 'Cierre', 'duration' => 'quince', 'agreements' => [(object) ['description' => 'Acta']]])],
        ]);

        $this->assertStringContainsString(e('[{"id":3,"title":"Cierre","duration":null,"description":"","agreements":[{"id":null,"description":"Acta"}]}]'), $html);
    }

    public function test_the_markup_of_a_point_binds_the_fields_and_the_errors_to_the_module(): void
    {
        $html = $this->render('<x-ui.minutes-editor />');

        $this->assertStringContainsString('<template x-for="(point, i) in points" x-bind:key="point.key">', $html);
        $this->assertStringContainsString('<legend class="sr-only" x-text="pointLegend(i)"></legend>', $html);
        $this->assertStringContainsString('x-model="point.title" data-field="title" x-on:input="clearError(i, \'title\')"', $html);
        $this->assertStringContainsString('x-bind:aria-invalid="point.errors.title ? \'true\' : null"', $html);
        $this->assertStringContainsString('x-bind:aria-describedby="describedBy(i, \'title\')"', $html);
        $this->assertStringContainsString('aria-[invalid=true]:border-rejected', $html);
        $this->assertStringContainsString('x-bind:id="errorId(i, \'title\')" x-show="point.errors.title" x-text="point.errors.title"', $html);
        $this->assertStringNotContainsString('role="alert"', $html, 'The status and the focused field announce the errors once');
        $this->assertStringContainsString('<textarea x-model="point.description" rows="3" data-field="description"', $html);
        $this->assertStringContainsString('field-sizing-content', $html);
        $this->assertStringContainsString('<input type="number" min="0" inputmode="numeric" x-model.number="point.duration"', $html);
        $this->assertStringContainsString('<template x-for="(agreement, j) in point.agreements" x-bind:key="agreement.key">', $html);
        $this->assertStringContainsString('x-model="agreement.description" rows="2" data-field="agreement"', $html);
        $this->assertStringContainsString('x-on:click="removeAgreement(i, j)"', $html);
        $this->assertStringContainsString('x-bind:aria-label="agreementAction(i, j)"', $html);
        $this->assertStringContainsString('x-on:click="addAgreement(i)"', $html);
        $this->assertStringContainsString('x-on:click="addPoint"', $html);
        $this->assertStringContainsString('x-on:click="movePoint(i, -1)" x-bind:disabled="isFirst(i)"', $html);
        $this->assertStringContainsString('x-on:click="movePoint(i, 1)" x-bind:disabled="isLast(i)"', $html);
        $this->assertStringContainsString('x-show="!point.confirming" x-on:click="removePoint(i)"', $html);
        $this->assertStringContainsString('data-action="confirm" x-show="point.confirming"', $html);
        $this->assertStringContainsString('x-on:keydown.escape.prevent="cancelRemove(i, true)"', $html);
        $this->assertStringContainsString('x-on:blur="cancelRemove(i, false)"', $html);
        $this->assertStringContainsString('Título', $html);
        $this->assertStringContainsString('Descripción', $html);
        $this->assertStringContainsString('Duración (min)', $html);
        $this->assertStringContainsString('Acuerdos', $html);
        $this->assertStringContainsString('pointer-coarse:min-h-11', $html);
        $this->assertStringContainsString('data-points="[]"', $html);
    }

    public function test_name_label_hint_error_and_minimum_are_honoured(): void
    {
        $html = $this->render('<x-ui.minutes-editor name="acta[puntos]" label="Puntos tratados" hint="Uno por tema" error="Añade un punto" :min-points="2" class="mt-4"><output x-text="json"></output></x-ui.minutes-editor>');

        $this->assertStringContainsString('name="acta[puntos]"', $html);
        $this->assertStringContainsString('data-id="field-acta-puntos"', $html);
        $this->assertStringContainsString('data-min="2"', $html);
        $this->assertStringContainsString('aria-labelledby="field-acta-puntos-label"', $html);
        $this->assertStringContainsString('aria-describedby="field-acta-puntos-hint field-acta-puntos-error"', $html);
        $this->assertStringContainsString('<p id="field-acta-puntos-label" class="text-sm font-semibold">Puntos tratados</p>', $html);
        $this->assertStringContainsString('<p id="field-acta-puntos-hint" class="text-sm text-ink-muted">Uno por tema</p>', $html);
        $this->assertStringContainsString('<p id="field-acta-puntos-error" class="text-sm font-medium text-rejected-ink">Añade un punto</p>', $html);
        $this->assertStringContainsString('grid min-w-0 grid-cols-[minmax(0,1fr)] gap-4 mt-4', $html);
        $this->assertMatchesRegularExpression('/<output x-text="json"><\/output>\s*<\/div>\s*$/', trim($html));
        $this->assertUniqueIds($html);

        $plain = $this->render('<x-ui.minutes-editor />');

        $this->assertStringNotContainsString('aria-describedby="field', $plain);
        $this->assertStringNotContainsString('-error"', $plain);
    }

    public function test_the_error_falls_back_to_the_view_errors(): void
    {
        $this->withViewErrors(['points_json' => 'El acta necesita un punto.']);

        $html = $this->render('<x-ui.minutes-editor />');

        $this->assertStringContainsString('El acta necesita un punto.', $html);
        $this->assertStringContainsString('aria-describedby="field-points-json-error"', $html);
    }
}
