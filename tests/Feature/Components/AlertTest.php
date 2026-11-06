<?php

namespace Tests\Feature\Components;

class AlertTest extends ComponentTestCase
{
    public function test_it_is_a_note_by_default_and_takes_status_or_alert(): void
    {
        $note = $this->render('<x-ui.alert>Hola</x-ui.alert>');

        $this->assertMatchesRegularExpression('/^<div role="note"/', trim($note));
        $this->assertStringContainsString('Hola', $note);
        $this->assertCspSafe($note);
        $this->assertNoAutofocus($note);

        $this->assertStringContainsString('role="status"', $this->render('<x-ui.alert role="status">Hola</x-ui.alert>'));
        $this->assertStringContainsString('role="alert"', $this->render('<x-ui.alert role="alert">Hola</x-ui.alert>'));
        $this->assertStringContainsString('role="note"', $this->render('<x-ui.alert role="banner">Hola</x-ui.alert>'), 'Unknown roles fall back to note');
    }

    public function test_each_tone_prints_its_classes_and_its_icon(): void
    {
        $tones = [
            'notice' => ['bg-notice-glass text-notice-ink', 'bg-notice/18', 'info'],
            'accepted' => ['bg-accepted-glass text-accepted-ink', 'bg-accepted/18', 'circle-check'],
            'pending' => ['bg-pending-glass text-pending-ink', 'bg-pending/18', 'clock'],
            'rejected' => ['bg-rejected-glass text-rejected-ink', 'bg-rejected/18', 'triangle-alert'],
            'draft' => ['bg-draft-glass text-draft-ink', 'bg-draft/18', 'circle-help'],
        ];

        foreach ($tones as $tone => [$surface, $bubble, $icon]) {
            $html = $this->render('<x-ui.alert tone="'.$tone.'">Hola</x-ui.alert>');

            $this->assertStringContainsString($surface, $html, $tone);
            $this->assertStringContainsString($bubble, $html, $tone);
            $this->assertStringContainsString($this->icon($icon, 'shrink-0 size-5.5'), $html, $tone);
            $this->assertStringContainsString('<span aria-hidden="true"', $html, $tone);
        }

        $this->assertStringContainsString('bg-notice-glass', $this->render('<x-ui.alert>Hola</x-ui.alert>'), 'notice is the default tone');
    }

    public function test_the_icon_can_be_overridden(): void
    {
        $html = $this->render('<x-ui.alert tone="rejected" icon="history">Hola</x-ui.alert>');

        $this->assertStringContainsString($this->icon('history', 'shrink-0 size-5.5'), $html);
        $this->assertStringNotContainsString($this->icon('triangle-alert', 'shrink-0 size-5.5'), $html);
    }

    public function test_the_title_and_the_quote_are_escaped(): void
    {
        $html = $this->render('<x-ui.alert :title="$title" :quote="$quote">Hola</x-ui.alert>', [
            'title' => '<b>Más pruebas</b>',
            'quote' => 'Faltan <i>fotos</i>',
        ]);

        $this->assertStringContainsString('<p class="text-base font-bold">&lt;b&gt;Más pruebas&lt;/b&gt;</p>', $html);
        $this->assertMatchesRegularExpression('/<blockquote class="[^"]*bg-canvas\/55[^"]*">«Faltan &lt;i&gt;fotos&lt;\/i&gt;»\s*<\/blockquote>/', $html);
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringNotContainsString('<i>', $html);

        $bare = $this->render('<x-ui.alert>Hola</x-ui.alert>');

        $this->assertStringNotContainsString('<blockquote', $bare);
        $this->assertStringNotContainsString('text-base font-bold', $bare);
    }

    public function test_the_meta_and_actions_slots_are_optional(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-ui.alert tone="rejected" title="Más pruebas">
                <x-slot:meta>Juan Pérez</x-slot:meta>
                <x-slot:actions><x-ui.button variant="primary">Corregir</x-ui.button></x-slot:actions>
                Cuerpo
            </x-ui.alert>
            BLADE);

        $this->assertMatchesRegularExpression('/<div class="flex flex-wrap items-center gap-2 text-sm opacity-95">\s*Juan Pérez<\/div>/', $html);
        $this->assertStringContainsString('mt-1 flex flex-wrap items-center gap-3 max-md:*:w-full max-md:*:justify-center md:col-start-2', $html);
        $this->assertStringContainsString('Corregir', $html);
        $this->assertStringContainsString('Cuerpo', $html);
        $this->assertStringContainsString('md:grid-cols-[auto_minmax(0,1fr)]', $html);
        $this->assertCspSafe($html);

        $bare = $this->render('<x-ui.alert>Hola</x-ui.alert>');

        $this->assertStringNotContainsString('opacity-95', $bare);
        $this->assertStringNotContainsString('md:col-start-2', $bare);
    }

    public function test_dismissible_adds_the_alpine_component_and_the_close_button(): void
    {
        $html = $this->render('<x-ui.alert dismissible>Hola</x-ui.alert>');

        // The module, not an inline state: closing has to hand the focus on before the button disappears
        $this->assertStringContainsString('x-data="dismissible"', $html);
        $this->assertStringContainsString('x-show="open"', $html);
        $this->assertStringContainsString('x-on:click="close"', $html);
        $this->assertFileExists(resource_path('js/components/dismissible.js'));
        $this->assertStringContainsString('aria-label="Cerrar aviso"', $html);
        $this->assertStringContainsString($this->icon('x', 'shrink-0 size-4 shrink-0'), $html);
        $this->assertCspSafe($html);

        $plain = $this->render('<x-ui.alert>Hola</x-ui.alert>');

        $this->assertStringNotContainsString('x-data', $plain);
        $this->assertStringNotContainsString('Cerrar aviso', $plain);
    }

    public function test_compact_drops_the_bubble_and_the_two_columns(): void
    {
        $compact = $this->render(<<<'BLADE'
            <x-ui.alert tone="accepted" compact>
                <x-slot:actions>Acción</x-slot:actions>
                Hola
            </x-ui.alert>
            BLADE);

        $this->assertStringNotContainsString('size-11', $compact);
        $this->assertStringNotContainsString('md:grid-cols-[auto_minmax(0,1fr)]', $compact);
        $this->assertStringNotContainsString('md:col-start-2', $compact);
        $this->assertStringContainsString('gap-3 p-4', $compact);
        $this->assertStringContainsString($this->icon('circle-check', 'shrink-0 mt-0.5 size-5'), $compact, 'The icon stays inline');
        $this->assertStringContainsString('Acción', $compact);

        $regular = $this->render('<x-ui.alert tone="accepted">Hola</x-ui.alert>');

        $this->assertStringContainsString('size-11', $regular);
        $this->assertStringContainsString('md:px-8 md:py-6', $regular);
    }

    public function test_the_consumer_classes_are_merged_on_the_root(): void
    {
        $html = $this->render('<x-ui.alert class="mt-4" data-test="aviso">Hola</x-ui.alert>');

        $this->assertMatchesRegularExpression('/^<div role="note"\s+class="grid grid-cols-\[minmax\(0,1fr\)\] rounded-xl border backdrop-blur-glass [^"]* mt-4" data-test="aviso"\s*>/', trim($html));
    }

    /** The markup the icon component prints for a Lucide name, to compare without depending on path data. */
    private function icon(string $name, string $class): string
    {
        return svg('lucide-'.$name, $class, ['aria-hidden' => 'true'])->toHtml();
    }
}
