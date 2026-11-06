<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
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

        // Without fields every error of the bag is listed under its key
        $html = $this->render('<x-ui.form action="/evidence">x</x-ui.form>');

        $this->assertMatchesRegularExpression('/<div role="group" aria-labelledby="form-errors-title" tabindex="-1"\s+autofocus/', $html);
        $this->assertStringContainsString('<p id="form-errors-title" class="font-bold">Revisa el formulario</p>', $html);
        $this->assertMatchesRegularExpression('/<a href="#field-title" class="underline underline-offset-2">title ·\s+El título es obligatorio<\/a>/', $html);
        $this->assertMatchesRegularExpression('/<a href="#field-hours" class="[^"]+">hours ·\s+Indica las horas<\/a>/', $html);
        $this->assertStringContainsString('href="#field-a-b"', $html);
        $this->assertMatchesRegularExpression('/<a href="#field-users" class="[^"]+">users.0 ·\s+Elige al menos una persona<\/a>/', $html);
        $this->assertSame(1, substr_count($html, 'autofocus'));
        $this->assertUniqueIds($html);
        $this->assertCspSafe($html);

        // fields gives them readable names
        $named = $this->render(<<<'BLADE'
            <x-ui.form action="/evidence" :fields="['title' => 'Título', 'hours' => 'Horas', 'a[b]' => 'Dato', 'users[]' => 'Asistentes']">x</x-ui.form>
            BLADE);

        $this->assertMatchesRegularExpression('/<a href="#field-title" class="[^"]+">Título ·\s+El título es obligatorio<\/a>/', $named);
        $this->assertMatchesRegularExpression('/<a href="#field-hours" class="[^"]+">Horas ·\s+Indica las horas<\/a>/', $named);
        $this->assertMatchesRegularExpression('/<a href="#field-a-b" class="[^"]+">Dato ·\s+Dato anidado<\/a>/', $named);
        $this->assertMatchesRegularExpression('/<a href="#field-users" class="[^"]+">Asistentes ·\s+Elige al menos una persona<\/a>/', $named);
    }

    public function test_every_link_of_the_summary_reaches_an_element_that_takes_the_focus(): void
    {
        $this->withViewErrors([
            'title' => 'Falta el título',
            'roles' => 'Elige un rol',
            'type' => 'Elige el tipo',
            'icon' => 'Elige un icono',
            'points_list' => 'Añade un punto',
            'points_json' => 'El acta necesita un punto',
            'users.0' => 'Persona no válida',
            'evidence_files.0' => 'Tipo no admitido',
            'terms' => 'Acepta las condiciones',
            'items.0.title' => 'Falta el título del punto',
        ]);

        $html = $this->render(<<<'BLADE'
            <x-ui.form action="/evidence">
                <x-ui.field name="title" label="Título" />
                <x-ui.checkbox-group name="roles" legend="Roles" :options="[['value' => 'A', 'label' => 'A']]" />
                <x-ui.segmented name="type" legend="Tipo" :options="[['value' => 1, 'label' => 'Ordinaria']]" />
                <x-ui.icon-picker :icons="['star']" />
                <x-ui.agenda-editor />
                <x-ui.minutes-editor />
                <x-ui.picker name="users[]" label="Asistentes" :options="[['value' => 1, 'label' => 'Ana']]" />
                <x-ui.upload name="evidence_files[]" url="/upload" />
                <x-ui.choice name="terms" label="Acepto" />
                <x-ui.field name="items[0][title]" label="Título del punto" />
            </x-ui.form>
            BLADE);

        preg_match('/<div role="group" aria-labelledby="form-errors-title".*?<\/div>/s', $html, $summary);
        preg_match_all('/href="#([^"]+)"/', $summary[0], $links);

        $this->assertCount(10, $links[1]);
        // Only the last index of a key goes away
        $this->assertContains('field-users', $links[1]);
        $this->assertContains('field-evidence-files', $links[1]);
        $this->assertContains('field-items-0-title', $links[1]);

        foreach ($links[1] as $target) {
            $this->assertMatchesRegularExpression('/<[a-z]+(?:\s[^>]*)?\sid="'.preg_quote($target, '/').'"/', $html, 'Nothing to land on for #'.$target);
        }

        // The groups take the focus the link gives them
        foreach (['field-roles', 'field-type', 'field-icon', 'field-points-list', 'field-points-json'] as $group) {
            $this->assertSame(1, preg_match('/<(?:fieldset|div)(?:\s[^>]*)?\sid="'.$group.'"[^>]*>/', $html, $tag), 'No group #'.$group);
            $this->assertStringContainsString(' tabindex="-1"', $tag[0], '#'.$group.' cannot take the focus');
        }

        $this->assertSame(1, substr_count($html, 'autofocus'));
        $this->assertUniqueIds($html);
        $this->assertCspSafe($html);
    }

    public function test_the_summary_reads_the_bag_it_is_given(): void
    {
        $this->withViewErrors(['password' => 'La contraseña no coincide'], 'password');

        $default = $this->render('<x-ui.form action="/profile">x</x-ui.form>');

        $this->assertStringNotContainsString('role="group"', $default);
        $this->assertNoAutofocus($default);

        $named = $this->render('<x-ui.form action="/profile/password" id="password-form" bag="password">x</x-ui.form>');

        $this->assertStringContainsString('aria-labelledby="password-form-errors-title"', $named);
        $this->assertMatchesRegularExpression('/<a href="#field-password" class="[^"]+">password ·\s+La contraseña no coincide<\/a>/', $named);
        $this->assertSame(1, substr_count($named, 'autofocus'));
    }

    public function test_the_bag_may_be_a_message_bag_of_its_own(): void
    {
        // A demo or a custom validator brings its own errors without touching the shared ones
        $this->withViewErrors(['email' => 'Error compartido']);

        $html = $this->render('<x-ui.form action="/x" id="demo-form" :bag="$bag">x</x-ui.form>', [
            'bag' => new MessageBag(['title' => ['Falta el título']]),
        ]);

        $this->assertMatchesRegularExpression('/<a href="#field-title" class="[^"]+">title ·\s+Falta el título<\/a>/', $html);
        $this->assertStringNotContainsString('Error compartido', $html);
    }

    public function test_with_fields_each_form_of_a_page_lists_only_its_own_errors(): void
    {
        $this->withViewErrors(['password' => 'Demasiado corta', 'password_confirmation' => 'No coincide']);

        $html = $this->render(<<<'BLADE'
            <x-ui.form action="/profile" id="data-form" :fields="['name' => 'Nombre', 'email' => 'Correo']">x</x-ui.form>
            <x-ui.form action="/profile/password" id="password-form" :fields="['password' => 'Contraseña', 'password_confirmation' => 'Repetición']">y</x-ui.form>
            BLADE);

        $this->assertStringNotContainsString('data-form-errors-title', $html);
        $this->assertStringContainsString('aria-labelledby="password-form-errors-title"', $html);
        $this->assertMatchesRegularExpression('/Contraseña ·\s+Demasiado corta/', $html);
        $this->assertMatchesRegularExpression('/Repetición ·\s+No coincide/', $html);
        $this->assertSame(1, substr_count($html, 'role="group"'));
        $this->assertSame(1, substr_count($html, 'autofocus'), 'Only a summary with items takes the focus');
        $this->assertUniqueIds($html);
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
