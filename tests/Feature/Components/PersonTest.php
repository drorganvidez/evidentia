<?php

namespace Tests\Feature\Components;

class PersonTest extends ComponentTestCase
{
    public function test_it_shows_a_decorative_avatar_and_the_name_as_plain_text(): void
    {
        $html = $this->render('<x-ui.person name="Juan" surname="Pérez" />');

        $this->assertStringContainsString('>JP</span>', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
        $this->assertStringContainsString('<span class="text-md font-semibold text-ink">Juan Pérez</span>', $html);
        $this->assertStringContainsString('inline-flex items-center gap-2', $html);
        $this->assertStringContainsString('grid leading-tight', $html);
        $this->assertStringNotContainsString('<a', $html);
        $this->assertStringNotContainsString('text-ink-muted', $html);
        $this->assertCspSafe($html);
    }

    public function test_with_href_the_name_is_a_link(): void
    {
        $html = $this->render('<x-ui.person name="Juan" surname="Pérez" href="/usuarios/4" />');

        $this->assertMatchesRegularExpression('#<a href="/usuarios/4"\s+class="text-md font-semibold text-ink transition hover:text-brand-ink pointer-coarse:flex pointer-coarse:min-h-11 pointer-coarse:min-w-11 pointer-coarse:items-center">Juan Pérez</a>#', $html);
        $this->assertStringNotContainsString('pointer-coarse', $this->render('<x-ui.person name="Juan" surname="Pérez" />'));
        $this->assertLessThan(strpos($html, '<a '), strpos($html, '>JP</span>'), 'The avatar stays outside the link');
    }

    public function test_the_role_line_is_optional(): void
    {
        $html = $this->render('<x-ui.person name="Juan" surname="Pérez" role="como coordinador de Programa" />');

        $this->assertStringContainsString('<span class="text-xs text-ink-muted">como coordinador de Programa</span>', $html);
        $this->assertStringNotContainsString('role=', $html, 'The role prop never leaks as an ARIA role');
        $this->assertStringNotContainsString('text-xs text-ink-muted', $this->render('<x-ui.person name="Juan" surname="Pérez" />'));
    }

    public function test_reverse_puts_the_surname_first(): void
    {
        $this->assertStringContainsString('>Pérez, Juan</span>', $this->render('<x-ui.person name="Juan" surname="Pérez" reverse />'));
        $this->assertStringContainsString('>Juan Pérez</span>', $this->render('<x-ui.person name="Juan Pérez" reverse />'), 'Nothing to reverse without a surname');
        $this->assertStringContainsString('>JP</span>', $this->render('<x-ui.person name="Juan" surname="Pérez" reverse />'), 'The initials keep their order');
    }

    public function test_the_small_size_shrinks_the_avatar_and_the_name(): void
    {
        $small = $this->render('<x-ui.person name="Juan" surname="Pérez" size="sm" role="como estudiante" />');

        $this->assertStringContainsString('size-6 text-[0.625rem] ring-0', $small);
        $this->assertStringContainsString('text-sm font-semibold text-ink', $small);
        $this->assertStringNotContainsString('text-md', $small);

        $medium = $this->render('<x-ui.person name="Juan" surname="Pérez" />');
        $this->assertStringContainsString('size-9 text-xs', $medium);
        $this->assertStringContainsString('text-md font-semibold text-ink', $medium);
    }

    public function test_a_picture_is_passed_to_the_avatar(): void
    {
        $html = $this->render('<x-ui.person name="Juan" surname="Pérez" src="/storage/avatars/juan.jpg" />');

        $this->assertStringContainsString('<img src="/storage/avatars/juan.jpg" alt=""', $html);
        $this->assertStringNotContainsString('JP', $html);
    }

    public function test_name_role_and_href_are_escaped(): void
    {
        $html = $this->render('<x-ui.person name="<b>Juan</b>" surname="Pérez" role="<i>rol</i>" href="/x?a=1&b=2" class="mt-1" />');

        $this->assertStringContainsString('&lt;b&gt;Juan&lt;/b&gt; Pérez', $html);
        $this->assertStringContainsString('&lt;i&gt;rol&lt;/i&gt;', $html);
        $this->assertStringContainsString('href="/x?a=1&amp;b=2"', $html);
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringNotContainsString('<i>', $html);
        $this->assertStringContainsString('inline-flex items-center gap-2 mt-1', $html);
    }
}
