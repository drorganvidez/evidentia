<?php

namespace Tests\Feature\Components;

use Illuminate\View\ViewException;
use InvalidArgumentException;

class DialogTest extends ComponentTestCase
{
    public function test_a_modal_links_its_title_and_description_and_generates_its_trigger(): void
    {
        $html = $this->render('<x-ui.dialog id="reject-33" title="Rechazar la evidencia" description="Explica el motivo." trigger="Rechazar" trigger-icon="circle-x" class="inline-block">Cuerpo</x-ui.dialog>');

        $this->assertMatchesRegularExpression('/^<div x-data="dialog"\s+class="inline-block">/', trim($html));
        $this->assertStringNotContainsString('data-open', $html);
        preg_match('/<button [^>]*>/s', $html, $trigger);
        $this->assertNotEmpty($trigger, 'The trigger button is generated');
        foreach (['aria-haspopup="dialog"', 'aria-controls="reject-33"', 'aria-expanded="false"', 'x-bind:aria-expanded="open ? \'true\' : \'false\'"', 'x-on:click="show"', 'type="button"'] as $attribute) {
            $this->assertStringContainsString($attribute, $trigger[0]);
        }
        $this->assertMatchesRegularExpression('/Rechazar\s*<\/button>/', $html);
        $this->assertStringContainsString('glass-2 text-ink', $html, 'The quiet variant is the default trigger');
        $this->assertMatchesRegularExpression('/<dialog id="reject-33"\s+aria-labelledby="reject-33-title"\s+aria-describedby="reject-33-desc"\s+x-ref="dialog"\s+x-on:close="onClose"\s+x-on:click="onBackdropClick"\s+closedby="any"\s+class="/', $html);
        $this->assertStringNotContainsString('role="alertdialog"', $html);
        $this->assertMatchesRegularExpression('/<h2 id="reject-33-title" tabindex="-1" class="text-xl font-bold tracking-tight">\s*Rechazar la evidencia\s*<\/h2>/', $html);
        $this->assertStringContainsString('aria-label="Cerrar" title="Cerrar"', $html);
        $this->assertStringContainsString('x-on:click="close"', $html);
        $this->assertMatchesRegularExpression('/<p id="reject-33-desc" class="text-md text-ink-muted px-6 pt-2">\s*Explica el motivo.<\/p>/', $html);
        $this->assertStringContainsString('<div class="p-6">Cuerpo</div>', $html);
        $this->assertStringNotContainsString('border-t border-rule', $html, 'No footer without the slot');
        $this->assertUniqueIds($html);
        $this->assertNoAutofocus($html);
        $this->assertCspSafe($html);
    }

    public function test_an_alert_is_an_alertdialog_with_a_danger_trigger_and_no_close_button(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-ui.dialog id="remove-33" variant="alert" title="Eliminar la evidencia" description="Se borrarán también sus pruebas." trigger="Eliminar" trigger-icon="trash-2" trigger-icon-only>
                <x-slot:footer><button type="button" x-on:click="close" autofocus>Cancelar</button></x-slot:footer>
            </x-ui.dialog>
            BLADE);

        $this->assertStringContainsString('role="alertdialog"', $html);
        $this->assertStringContainsString('aria-describedby="remove-33-desc"', $html);
        // No light dismiss on a destructive confirmation: Escape (the native close request) still works
        $this->assertStringNotContainsString('closedby=', $html);
        $this->assertStringNotContainsString('onBackdropClick', $html);
        $this->assertMatchesRegularExpression('/x-ref="dialog"\s+x-on:close="onClose"\s+class="/', $html);
        $this->assertStringContainsString('aria-label="Eliminar" title="Eliminar"', $html);
        $this->assertStringContainsString('text-rejected-ink', $html, 'The alert trigger is the danger variant');
        $this->assertStringNotContainsString('aria-label="Cerrar"', $html);
        $this->assertStringNotContainsString('<div class="p-6">', $html, 'No body without the slot');
        $this->assertStringContainsString('px-6 pt-2 pb-6', $html, 'The description keeps the footer apart without a body');
        $this->assertMatchesRegularExpression('/<div\s+class="flex flex-col-reverse gap-3 border-t border-rule p-6 md:flex-row md:justify-end">\s*<button type="button" x-on:click="close" autofocus>Cancelar<\/button><\/div>/', $html);
        $this->assertUniqueIds($html);
        $this->assertCspSafe($html);
    }

    public function test_an_alert_needs_a_description(): void
    {
        try {
            $this->render('<x-ui.dialog id="remove-33" variant="alert" title="Eliminar" trigger="Eliminar" />');
        } catch (ViewException $exception) {
            while ($exception->getPrevious() !== null) {
                $exception = $exception->getPrevious();
            }

            $this->assertInstanceOf(InvalidArgumentException::class, $exception);
            $this->assertSame('An alert dialog needs a description.', $exception->getMessage());

            return;
        }

        $this->fail('An alert without a description was rendered');
    }

    public function test_the_id_and_the_title_are_required(): void
    {
        foreach (['<x-ui.dialog title="x" />' => 'The dialog needs an id.', '<x-ui.dialog id="x" />' => 'The dialog needs a title.'] as $blade => $message) {
            try {
                $this->render($blade);
                $this->fail('A dialog without id or title was rendered');
            } catch (ViewException $exception) {
                while ($exception->getPrevious() !== null) {
                    $exception = $exception->getPrevious();
                }

                $this->assertSame($message, $exception->getMessage());
            }
        }
    }

    public function test_the_trigger_slot_replaces_the_generated_button(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-ui.dialog id="sign" title="Firmar" trigger="Ignorado">
                <x-slot:trigger><button type="button" x-on:click="show" aria-controls="sign">Propio</button></x-slot:trigger>
            </x-ui.dialog>
            BLADE);

        $this->assertStringContainsString('<button type="button" x-on:click="show" aria-controls="sign">Propio</button>', $html);
        $this->assertSame(1, substr_count($html, 'x-on:click="show"'));
        $this->assertStringNotContainsString('Ignorado', $html);

        $none = $this->render('<x-ui.dialog id="sign" title="Firmar" />');

        $this->assertStringNotContainsString('x-on:click="show"', $none, 'Without trigger text nor slot there is no button');
        $this->assertStringContainsString('<dialog id="sign"', $none);
    }

    public function test_open_on_load_marks_the_root(): void
    {
        $this->assertMatchesRegularExpression('/^<div x-data="dialog"\s+data-open\s*>/', trim($this->render('<x-ui.dialog id="x" title="x" open-on-load />')));
        $this->assertMatchesRegularExpression('/^<div x-data="dialog"\s*>/', trim($this->render('<x-ui.dialog id="x" title="x" />')));
    }

    public function test_the_variants_and_sizes_map_to_literal_classes(): void
    {
        $modal = $this->render('<x-ui.dialog id="x" title="x" />');

        $this->assertStringContainsString('m-auto w-[calc(100%-2rem)] max-h-[calc(100dvh-2rem)] rounded-xl starting:open:translate-y-2 max-w-lg', $modal);
        $this->assertStringContainsString('max-md:mt-auto max-md:mb-0 max-md:max-h-[90dvh] max-md:w-full max-md:max-w-none max-md:rounded-b-none max-md:before:mx-auto', $modal);
        $this->assertStringContainsString('glass content-start overflow-y-auto p-0 text-ink transition transition-discrete backdrop:bg-slate-950/40 backdrop:backdrop-blur-glass open:grid starting:open:opacity-0', $modal);
        $this->assertStringNotContainsString('w-72', $modal);

        $this->assertStringContainsString('max-w-sm', $this->render('<x-ui.dialog id="x" title="x" size="sm" />'));
        $this->assertStringContainsString('max-w-2xl', $this->render('<x-ui.dialog id="x" title="x" size="lg" />'));
        $this->assertStringNotContainsString('max-md:mt-auto', $this->render('<x-ui.dialog id="x" title="x" :sheet="false" />'));

        $drawer = $this->render('<x-ui.dialog id="drawer" variant="drawer" title="Menú" trigger="Abrir menú" trigger-icon="menu" trigger-icon-only>Nav</x-ui.dialog>');

        $this->assertStringContainsString('m-0 h-dvh max-h-none w-72 max-w-[85vw] rounded-l-none rounded-r-xl starting:open:-translate-x-full', $drawer);
        $this->assertStringNotContainsString('m-auto', $drawer);
        $this->assertStringNotContainsString('max-md:mt-auto', $drawer);
        $this->assertStringNotContainsString('max-w-lg', $drawer);
        $this->assertStringContainsString('<div class="px-3 py-4">Nav</div>', $drawer);
        $this->assertStringContainsString('aria-label="Cerrar"', $drawer);
    }

    public function test_the_texts_are_escaped_and_the_close_label_can_change(): void
    {
        $html = $this->render('<x-ui.dialog id="x" title="<b>Título</b>" description="<i>Desc</i>" trigger="<u>Abrir</u>" close-label="Cerrar el diálogo" />');

        $this->assertStringContainsString('&lt;b&gt;Título&lt;/b&gt;', $html);
        $this->assertStringContainsString('&lt;i&gt;Desc&lt;/i&gt;', $html);
        $this->assertStringContainsString('&lt;u&gt;Abrir&lt;/u&gt;', $html);
        $this->assertStringContainsString('aria-label="Cerrar el diálogo"', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }
}
