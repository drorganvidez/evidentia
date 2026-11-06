<?php

namespace Tests\Feature\Components;

class ThemeSwitchTest extends ComponentTestCase
{
    public function test_it_binds_three_radios_to_the_theme_store_from_an_empty_alpine_root(): void
    {
        $html = $this->render('<x-ui.theme-switch class="hidden md:block" />');

        $this->assertMatchesRegularExpression('/^<div x-data\s+class="hidden md:block">/', trim($html));
        $this->assertStringNotContainsString('x-data="theme"', $html);
        $this->assertStringContainsString('<legend class="sr-only">Tema</legend>', $html);
        $this->assertSame(3, substr_count($html, 'type="radio" name="theme"'));

        foreach (['light' => 'Claro', 'dark' => 'Oscuro', 'system' => 'Sistema'] as $mode => $label) {
            $this->assertStringContainsString('id="field-theme-'.$mode.'"', $html);
            $this->assertStringContainsString('x-bind:checked="$store.theme.is(\''.$mode.'\')"', $html);
            $this->assertStringContainsString('x-on:change="$store.theme.select(\''.$mode.'\')"', $html);
            $this->assertStringContainsString('title="'.$label.'"', $html);
            $this->assertStringContainsString('<span class="sr-only">'.$label.'</span>', $html);
        }

        $this->assertSame(3, substr_count($html, '<svg'));
        $this->assertStringNotContainsString('checked>', $html, 'The store decides the checked radio');
        $this->assertStringNotContainsString('aria-pressed', $html);
        $this->assertUniqueIds($html);
        $this->assertNoAutofocus($html);
        $this->assertCspSafe($html);
    }

    public function test_labels_show_the_texts_and_the_name_changes_the_ids(): void
    {
        $html = $this->render('<x-ui.theme-switch name="theme-drawer" labels block />');

        $this->assertSame(3, substr_count($html, 'type="radio" name="theme-drawer"'));
        $this->assertStringContainsString('id="field-theme-drawer-dark"', $html);
        $this->assertStringNotContainsString('sr-only">Oscuro', $html);
        $this->assertStringContainsString('<span>Oscuro</span>', $html);
        $this->assertStringNotContainsString('title=', $html);
        $this->assertStringContainsString('flex w-full [&>label]:flex-1', $html);
        $this->assertUniqueIds($html);
    }

    public function test_two_instances_on_a_page_keep_their_ids_apart(): void
    {
        $html = $this->render('<x-ui.theme-switch name="theme-topbar" /><x-ui.theme-switch name="theme-drawer" labels />');

        $this->assertUniqueIds($html);
        $this->assertSame(6, substr_count($html, 'type="radio"'));
    }
}
