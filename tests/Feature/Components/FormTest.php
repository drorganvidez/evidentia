<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;

class FormTest extends ComponentTestCase
{
    protected function tearDown(): void
    {
        View::share('errors', new ViewErrorBag);

        parent::tearDown();
    }

    public function test_a_post_form_carries_the_token_and_the_busy_behaviour(): void
    {
        $html = $this->render('<x-ui.form action="/evidence">Campos</x-ui.form>');

        $this->assertStringContainsString('<form method="post" action="/evidence"', $html);
        $this->assertStringContainsString('name="_token"', $html);
        $this->assertStringContainsString(' novalidate', $html);
        $this->assertStringContainsString('x-data="busyForm"', $html);
        $this->assertStringContainsString('data-busy-label="Guardando"', $html);
        $this->assertStringContainsString('x-on:submit="submit"', $html);
        $this->assertStringContainsString('x-bind:aria-busy="busy ? \'true\' : \'false\'"', $html);
        $this->assertStringContainsString('<div class="grid grid-cols-[minmax(0,1fr)] gap-5">Campos</div>', $html);
        $this->assertStringNotContainsString('enctype', $html);
        $this->assertStringNotContainsString('_method', $html);
        $this->assertStringNotContainsString('data-download', $html);
        $this->assertStringNotContainsString('role="group"', $html);
        $this->assertNoAutofocus($html);
        $this->assertCspSafe($html);
    }

    public function test_put_patch_and_delete_are_spoofed_over_post(): void
    {
        foreach (['put' => 'PUT', 'patch' => 'PATCH', 'delete' => 'DELETE'] as $method => $spoofed) {
            $html = $this->render('<x-ui.form action="/evidence/3" method="'.$method.'">x</x-ui.form>');

            $this->assertStringContainsString('<form method="post"', $html);
            $this->assertStringContainsString('name="_method" value="'.$spoofed.'"', $html);
            $this->assertStringContainsString('name="_token"', $html);
        }
    }

    public function test_a_get_form_has_neither_token_nor_method(): void
    {
        $html = $this->render('<x-ui.form action="/search" method="get">x</x-ui.form>');

        $this->assertStringContainsString('<form method="get" action="/search"', $html);
        $this->assertStringNotContainsString('_token', $html);
        $this->assertStringNotContainsString('_method', $html);
    }

    public function test_files_download_label_and_novalidate_follow_the_props(): void
    {
        $html = $this->render('<x-ui.form action="/x" files download busy-label="Preparando" :novalidate="false" id="export">x</x-ui.form>');

        $this->assertStringContainsString('enctype="multipart/form-data"', $html);
        $this->assertStringContainsString(' data-download', $html);
        $this->assertStringContainsString('data-busy-label="Preparando"', $html);
        $this->assertStringContainsString(' id="export"', $html);
        $this->assertStringNotContainsString('novalidate', $html);
    }

    public function test_the_error_summary_links_every_field_and_takes_the_focus(): void
    {
        $this->withViewErrors([
            'title' => 'El título es obligatorio',
            'hours' => 'Indica las horas',
            'a.b' => 'Dato anidado',
            'users.0' => 'Elige al menos una persona',
        ]);

        $html = $this->render(<<<'BLADE'
            <x-ui.form action="/evidence" :fields="['title' => 'Título', 'users[]' => 'Asistentes']">x</x-ui.form>
            BLADE);

        $this->assertMatchesRegularExpression('/<div role="group" aria-labelledby="form-errors-title" tabindex="-1"\s+autofocus/', $html);
        $this->assertStringContainsString('<p id="form-errors-title" class="font-bold">Revisa el formulario</p>', $html);
        $this->assertMatchesRegularExpression('/<a href="#field-title" class="underline underline-offset-2">Título ·\s+El título es obligatorio<\/a>/', $html);
        $this->assertMatchesRegularExpression('/<a href="#field-hours" class="[^"]+">hours ·\s+Indica las horas<\/a>/', $html);
        $this->assertStringContainsString('href="#field-a-b"', $html);
        $this->assertMatchesRegularExpression('/<a href="#field-users" class="[^"]+">Asistentes ·\s+Elige al menos una persona<\/a>/', $html);
        $this->assertSame(1, substr_count($html, 'autofocus'));
        $this->assertUniqueIds($html);
        $this->assertCspSafe($html);
    }

    public function test_the_summary_can_leave_the_focus_alone(): void
    {
        $this->withViewErrors(['title' => 'El título es obligatorio']);

        $html = $this->render('<x-ui.form action="/x" :focus-summary="false">x</x-ui.form>');

        $this->assertStringContainsString('<div role="group" aria-labelledby="form-errors-title" tabindex="-1"', $html);
        $this->assertStringContainsString('href="#field-title"', $html);
        $this->assertNoAutofocus($html);
    }

    public function test_the_summary_can_be_renamed_and_turned_off(): void
    {
        $this->withViewErrors(['title' => 'El título es obligatorio']);

        $named = $this->render('<x-ui.form action="/x" id="evidence-form" summary-title="Hay errores">x</x-ui.form>');

        $this->assertStringContainsString('aria-labelledby="evidence-form-errors-title"', $named);
        $this->assertStringContainsString('<p id="evidence-form-errors-title" class="font-bold">Hay errores</p>', $named);

        $off = $this->render('<x-ui.form action="/x" :summary="false">x</x-ui.form>');

        $this->assertStringNotContainsString('role="group"', $off);
        $this->assertNoAutofocus($off);
    }

    public function test_without_errors_there_is_no_summary(): void
    {
        $this->withViewErrors([]);

        $html = $this->render('<x-ui.form action="/x">x</x-ui.form>');

        $this->assertStringNotContainsString('role="group"', $html);
        $this->assertNoAutofocus($html);
    }

    public function test_the_actions_slot_puts_the_primary_action_first(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-ui.form action="/x" class="mt-4">
                Campos
                <x-slot:actions class="lg:pt-6">
                    <x-ui.button type="submit" variant="primary">Guardar</x-ui.button>
                    <x-ui.button href="/back">Cancelar</x-ui.button>
                </x-slot:actions>
            </x-ui.form>
            BLADE);

        $this->assertStringContainsString('class="grid grid-cols-[minmax(0,1fr)] gap-6 mt-4"', $html);
        $this->assertStringContainsString('class="flex flex-col gap-3 border-t border-rule pt-5 *:justify-center md:flex-row-reverse md:items-center lg:pt-6"', $html);
        $this->assertLessThan(strpos($html, 'Cancelar'), strpos($html, 'Guardar'));
        $this->assertStringNotContainsString('border-t', $this->render('<x-ui.form action="/x">x</x-ui.form>'));
    }

    public function test_the_summary_escapes_the_labels_and_the_messages(): void
    {
        $this->withViewErrors(['title' => '<b>malo</b>']);

        $html = $this->render('<x-ui.form action="/x" :fields="$fields">x</x-ui.form>', ['fields' => ['title' => '<i>Título</i>']]);

        $this->assertStringContainsString('&lt;i&gt;Título&lt;/i&gt;', $html);
        $this->assertStringContainsString('&lt;b&gt;malo&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }
}
