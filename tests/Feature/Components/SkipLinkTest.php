<?php

namespace Tests\Feature\Components;

class SkipLinkTest extends ComponentTestCase
{
    public function test_by_default_it_jumps_to_the_main_content(): void
    {
        $html = $this->render('<x-ui.skip-link />');

        $this->assertStringContainsString('<a href="#main"', $html);
        $this->assertStringContainsString('>Saltar al contenido</a>', $html);
        $this->assertStringContainsString('sr-only font-semibold focus:not-sr-only focus:fixed', $html);
        $this->assertStringContainsString('focus:z-50', $html);
        $this->assertStringContainsString('focus:bg-brand', $html);
        $this->assertStringContainsString('focus:text-on-brand', $html);
        $this->assertCspSafe($html);
    }

    public function test_the_target_and_the_text_can_be_changed(): void
    {
        $html = $this->render('<x-ui.skip-link target="#form">Saltar al formulario</x-ui.skip-link>');

        $this->assertStringContainsString('<a href="#form"', $html);
        $this->assertStringContainsString('>Saltar al formulario</a>', $html);
        $this->assertStringNotContainsString('Saltar al contenido', $html);
    }
}
