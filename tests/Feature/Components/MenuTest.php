<?php

namespace Tests\Feature\Components;

use Illuminate\View\ViewException;
use InvalidArgumentException;

class MenuTest extends ComponentTestCase
{
    public function test_it_is_an_apg_menu_button_with_a_list_of_menu_items(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-ui.menu label="Acciones" id="actions" class="ml-2">
                <x-ui.menu.item href="/importar" icon="import">Importación masiva</x-ui.menu.item>
                <x-ui.menu.item icon="download">Exportar</x-ui.menu.item>
            </x-ui.menu>
            BLADE);

        $this->assertMatchesRegularExpression('/^<div x-data="menu" x-on:keydown.escape.prevent="close\(true\)"\s+class="relative inline-block ml-2">/', trim($html));

        preg_match('/<button [^>]*>/s', $html, $trigger);
        $this->assertNotEmpty($trigger, 'The trigger button is generated');
        foreach (['x-ref="button"', 'aria-haspopup="menu"', 'aria-controls="actions"', 'aria-expanded="false"', 'x-bind:aria-expanded="open ? \'true\' : \'false\'"', 'x-on:click="toggle"', 'x-on:keydown.down.prevent="openAndFocusFirst"', 'x-on:keydown.up.prevent="openAndFocusLast"', 'type="button"'] as $attribute) {
            $this->assertStringContainsString($attribute, $trigger[0]);
        }
        $this->assertStringNotContainsString('aria-label', $trigger[0], 'The visible text names the button');
        $this->assertMatchesRegularExpression('/Acciones\s*<\/button>/', $html);

        $this->assertMatchesRegularExpression('/<ul id="actions" role="menu" aria-label="Acciones" x-ref="menu" hidden\s+x-bind:hidden="!open"\s+x-on:keydown="onKeydown"\s+x-on:click.outside="onOutsideClick"\s+x-on:click="onItemClick"\s+x-on:focusout="onFocusOut"\s+class="glass absolute top-\[calc\(100%\+0.5rem\)\] z-20 min-w-56 max-w-\[calc\(100vw-1.5rem\)\] rounded-lg p-1.5 right-0">/', $html);
        $this->assertSame(2, substr_count($html, '<li role="none">'));
        $this->assertMatchesRegularExpression('#<a href="/importar" role="menuitem" tabindex="-1" class="[^"]*text-ink hover:bg-glass-2 focus:bg-glass-2">#', $html);
        $this->assertMatchesRegularExpression('#<button type="button" role="menuitem" tabindex="-1" class="[^"]*">#', $html);
        $this->assertSame(2, substr_count($html, 'class="shrink-0 size-4.5"'), 'One decorative icon per item');
        $this->assertStringContainsString('pointer-coarse:min-h-11', $html);
        $this->assertUniqueIds($html);
        $this->assertNoAutofocus($html);
        $this->assertCspSafe($html);
    }

    public function test_the_id_is_random_by_default_and_the_label_is_required(): void
    {
        $html = $this->render('<x-ui.menu label="Acciones"><x-ui.menu.item>Uno</x-ui.menu.item></x-ui.menu>');

        preg_match('/aria-controls="(menu-[A-Za-z0-9]{6})"/', $html, $matches);
        $this->assertNotEmpty($matches, 'A random menu id');
        $this->assertStringContainsString('<ul id="'.$matches[1].'"', $html);

        try {
            $this->render('<x-ui.menu><x-ui.menu.item>Uno</x-ui.menu.item></x-ui.menu>');
        } catch (ViewException $exception) {
            while ($exception->getPrevious() !== null) {
                $exception = $exception->getPrevious();
            }

            $this->assertInstanceOf(InvalidArgumentException::class, $exception);
            $this->assertSame('The menu needs a label.', $exception->getMessage());

            return;
        }

        $this->fail('A menu without a label was rendered');
    }

    public function test_icon_only_uses_the_ellipsis_and_the_label_as_name(): void
    {
        $html = $this->render('<x-ui.menu label="Acciones" icon-only id="m"><x-ui.menu.item>Uno</x-ui.menu.item></x-ui.menu>');

        $this->assertStringContainsString('aria-label="Acciones" title="Acciones"', $html);
        $this->assertStringContainsString('<circle cx="12" cy="12" r="1"/>', $html, 'The ellipsis icon');
        $this->assertStringContainsString('size-9 justify-center', $html);

        $custom = $this->render('<x-ui.menu label="Acciones" icon-only icon="settings" id="m"><x-ui.menu.item>Uno</x-ui.menu.item></x-ui.menu>');

        $this->assertStringNotContainsString('<circle cx="12" cy="12" r="1"/>', $custom);
    }

    public function test_a_divider_is_a_separator_and_a_method_wraps_the_item_in_a_form(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-ui.menu label="Acciones" id="m">
                <x-ui.menu.item divider />
                <x-ui.menu.item tone="danger" icon="trash-2" method="delete" action="/usuarios">Borrar todos los usuarios</x-ui.menu.item>
                <x-ui.menu.item icon="log-out" method="post" href="/salir">Salir</x-ui.menu.item>
            </x-ui.menu>
            BLADE);

        $this->assertStringContainsString('<li role="separator" class="my-1 border-t border-rule"></li>', $html);
        $this->assertMatchesRegularExpression('#<form method="post" action="/usuarios">\s*<input type="hidden" name="_token" value="[^"]*" autocomplete="off">\s*<input type="hidden" name="_method" value="DELETE">#', $html);
        $this->assertMatchesRegularExpression('#<button type="submit" role="menuitem" tabindex="-1" class="[^"]*text-rejected-ink hover:bg-rejected-soft focus:bg-rejected-soft">#', $html);
        $this->assertMatchesRegularExpression('#<form method="post" action="/salir">\s*<input type="hidden" name="_token"#', $html, 'Without action the href is the target');
        $this->assertSame(1, substr_count($html, 'name="_method"'), 'A post form needs no method override');
        $this->assertStringContainsString('Borrar todos los usuarios', $html);
        $this->assertCspSafe($html);
    }

    public function test_the_trigger_slot_replaces_the_button_and_the_popup_can_open_to_the_start(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-ui.menu label="Menú de usuario" id="user" align="start">
                <x-slot:trigger><button type="button" x-ref="button" x-on:click="toggle">JP</button></x-slot:trigger>
                <x-ui.menu.item href="/perfil">Mi perfil</x-ui.menu.item>
            </x-ui.menu>
            BLADE);

        $this->assertStringContainsString('<button type="button" x-ref="button" x-on:click="toggle">JP</button>', $html);
        $this->assertSame(1, substr_count($html, 'x-ref="button"'));
        $this->assertStringNotContainsString('aria-haspopup', $html);
        $this->assertStringContainsString('rounded-lg p-1.5 left-0', $html);
        $this->assertStringNotContainsString('right-0', $html);
    }

    public function test_the_texts_and_links_are_escaped_and_extra_attributes_reach_the_item(): void
    {
        $html = $this->render('<x-ui.menu label="<b>Acciones</b>" id="m"><x-ui.menu.item href="/x?a=1&b=2" data-test="go">{{ $text }}</x-ui.menu.item></x-ui.menu>', ['text' => '<i>Uno</i>']);

        $this->assertStringContainsString('aria-label="&lt;b&gt;Acciones&lt;/b&gt;"', $html);
        $this->assertStringContainsString('&lt;i&gt;Uno&lt;/i&gt;', $html);
        $this->assertStringContainsString('href="/x?a=1&amp;b=2"', $html);
        $this->assertStringContainsString('data-test="go"', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }
}
