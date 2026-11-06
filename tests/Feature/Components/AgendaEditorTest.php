<?php

namespace Tests\Feature\Components;

class AgendaEditorTest extends ComponentTestCase
{
    public function test_the_hidden_input_and_the_data_attribute_carry_the_escaped_initial_json(): void
    {
        $html = $this->render('<x-ui.agenda-editor :points="$points" />', [
            'points' => ['Lectura del acta', 'Presupuesto <b>2026</b>', '  ', ''],
        ]);

        $json = '[&quot;Lectura del acta&quot;,&quot;Presupuesto &lt;b&gt;2026&lt;\/b&gt;&quot;]';

        $this->assertStringContainsString('<fieldset x-data="agendaEditor" data-points="'.$json.'"', $html);
        $this->assertMatchesRegularExpression('/<input type="hidden" name="points_list" value="'.preg_quote($json, '/').'"\s+x-bind:value="json">/', $html);
        $this->assertStringContainsString('data-max="50"', $html);
        $this->assertStringContainsString('<legend class="text-sm font-semibold">Orden del día</legend>', $html);
        $this->assertStringContainsString('<ol aria-label="Puntos del orden del día"', $html);
        $this->assertStringContainsString('<template x-for="(point, index) in points" x-bind:key="point.key">', $html);
        $this->assertStringContainsString('x-on:keydown="onItemKeydown($event, index)"', $html);
        $this->assertStringContainsString('x-on:keydown.enter.prevent="add"', $html);
        $this->assertStringContainsString('placeholder="Añade un punto"', $html);
        $this->assertStringContainsString('<p role="status" class="sr-only" x-text="announcement"></p>', $html);
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringNotContainsString('{{', $html, 'No Handlebars templates');
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertCspSafe($html);
        $this->assertUniqueIds($html);
        $this->assertNoAutofocus($html);
    }

    public function test_points_may_come_as_arrays_or_objects_with_a_point_or_title_key(): void
    {
        $html = $this->render('<x-ui.agenda-editor :points="$points" />', [
            'points' => [['point' => 'Lectura'], (object) ['title' => 'Presupuesto'], collect(['point' => 'Cierre']), 3],
        ]);

        $this->assertStringContainsString('data-points="[&quot;Lectura&quot;,&quot;Presupuesto&quot;,&quot;Cierre&quot;,&quot;3&quot;]"', $html);
    }

    public function test_the_actions_are_bound_to_the_module_with_the_loop_variables(): void
    {
        $html = $this->render('<x-ui.agenda-editor />');

        $this->assertStringContainsString('data-action="up"', $html);
        $this->assertStringContainsString('x-on:click="moveUp(index)" x-bind:disabled="isFirst(index)"', $html);
        $this->assertStringContainsString('x-bind:aria-label="actionLabel(\'up\', index)" x-bind:title="actionLabel(\'up\', index)"', $html);
        $this->assertStringContainsString('x-on:click="moveDown(index)" x-bind:disabled="isLast(index)"', $html);
        $this->assertStringContainsString('data-action="remove"', $html);
        $this->assertStringContainsString('x-show="!point.confirming" x-on:click="remove(index)"', $html);
        $this->assertStringContainsString('data-action="confirm"', $html);
        $this->assertStringContainsString('x-show="point.confirming"', $html);
        $this->assertStringContainsString('Confirmar', $html);
        $this->assertStringContainsString('x-model="point.text"', $html);
        $this->assertStringContainsString('data-field="draft"', $html);
        $this->assertStringContainsString('x-model="draft"', $html);
        $this->assertStringContainsString('x-on:click="add"', $html);
        $this->assertStringContainsString('text-control', $html);
        $this->assertStringContainsString('pointer-coarse:min-h-11', $html);
        $this->assertStringContainsString('data-points="[]"', $html);
        $this->assertMatchesRegularExpression('/<input type="hidden" name="points_list" value="\[\]"/', $html);
    }

    public function test_the_ids_follow_the_name_and_link_the_hint_and_the_error(): void
    {
        $plain = $this->render('<x-ui.agenda-editor />');

        $this->assertStringContainsString('<label for="field-points-list-draft" class="sr-only">Nuevo punto</label>', $plain);
        $this->assertStringContainsString('id="field-points-list-draft"', $plain);
        $this->assertStringNotContainsString('aria-describedby', $plain);
        $this->assertStringNotContainsString('aria-invalid', $plain);

        $described = $this->render('<x-ui.agenda-editor name="agenda[]" hint="Un punto por línea" error="El orden del día no es válido." />');

        $this->assertStringContainsString('aria-describedby="field-agenda-hint field-agenda-error"', $described);
        $this->assertStringContainsString('aria-invalid="true"', $described);
        $this->assertStringContainsString('<p id="field-agenda-hint" class="text-sm text-ink-muted">Un punto por línea</p>', $described);
        $this->assertStringContainsString('<p id="field-agenda-error" class="text-sm font-medium text-rejected-ink">El orden del día no es válido.</p>', $described);
        $this->assertStringContainsString('name="agenda[]"', $described);
        $this->assertUniqueIds($described);

        $hinted = $this->render('<x-ui.agenda-editor id="agenda-a" hint="Pista" />');

        $this->assertStringContainsString('aria-describedby="agenda-a-hint"', $hinted);
        $this->assertStringContainsString('for="agenda-a-draft"', $hinted);
        $this->assertStringNotContainsString('aria-invalid', $hinted);
    }

    public function test_the_error_falls_back_to_the_view_errors(): void
    {
        $this->withViewErrors(['points_list' => 'El orden del día no es válido.']);

        $html = $this->render('<x-ui.agenda-editor />');

        $this->assertStringContainsString('El orden del día no es válido.', $html);
        $this->assertStringContainsString('aria-describedby="field-points-list-error"', $html);
    }

    public function test_label_placeholder_max_and_slot_are_honoured(): void
    {
        $html = $this->render('<x-ui.agenda-editor label="Temas" placeholder="Otro tema" :max="0" class="mt-4"><output x-text="json"></output></x-ui.agenda-editor>');

        $this->assertStringContainsString('<legend class="text-sm font-semibold">Temas</legend>', $html);
        $this->assertStringContainsString('placeholder="Otro tema"', $html);
        $this->assertStringContainsString('data-max="1"', $html, 'The maximum is at least one');
        $this->assertStringContainsString('grid min-w-0 grid-cols-[minmax(0,1fr)] gap-3 mt-4', $html);
        $this->assertMatchesRegularExpression('/<output x-text="json"><\/output>\s*<\/fieldset>\s*$/', trim($html));
    }
}
