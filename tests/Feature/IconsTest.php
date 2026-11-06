<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/**
 * Lucide icons render inline through blade-icons; the <x-ui.icon> wrapper decides the accessibility
 * attributes from its label.
 */
class IconsTest extends TestCase
{
    public function test_a_lucide_icon_renders_inline_without_a_fixed_size(): void
    {
        $html = Blade::render('<x-lucide-check class="size-4" aria-hidden="true" />');

        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('class="size-4"', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
        $this->assertStringContainsString('focusable="false"', $html);
        $this->assertStringContainsString('stroke="currentColor"', $html);
        $this->assertStringNotContainsString('width="24"', $html);
    }

    public function test_the_wrapper_hides_decorative_icons_from_assistive_technology(): void
    {
        $html = Blade::render('<x-ui.icon name="check" class="size-4 text-accepted" />');

        $this->assertStringContainsString('aria-hidden="true"', $html);
        $this->assertStringContainsString('class="shrink-0 size-4 text-accepted"', $html);
        $this->assertStringNotContainsString('role="img"', $html);
    }

    public function test_the_wrapper_announces_labelled_icons(): void
    {
        $html = Blade::render('<x-ui.icon name="triangle-alert" label="Aviso" class="size-5" />');

        $this->assertStringContainsString('role="img"', $html);
        $this->assertStringContainsString('aria-label="Aviso"', $html);
        $this->assertStringNotContainsString('aria-hidden', $html);
    }

    public function test_the_catalogue_draws_editing_and_signing_with_the_icons_of_the_map(): void
    {
        $penLine = $this->drawing('pen-line');
        $pencil = $this->drawing('pencil');

        foreach (['ui.sections.button', 'ui.sections.alert'] as $view) {
            $html = view($view)->render();

            $this->assertStringContainsString($penLine, $html, $view.': edit is pen-line');
            $this->assertStringNotContainsString($pencil, $html, $view.': edit is not pencil');
        }

        $public = (string) $this->get('/_ui/public')->assertOk()->getContent();

        $this->assertStringContainsString($this->drawing('file-signature'), $public, 'Sign is file-signature');
        $this->assertStringNotContainsString($this->drawing('signature'), $public, 'Sign is not signature');
    }

    /** The paths of a Lucide icon without its svg element, so the check does not depend on the attributes. */
    private function drawing(string $name): string
    {
        return trim((string) preg_replace('/^\s*<svg[^>]*>|<\/svg>\s*$/', '', svg('lucide-'.$name)->contents()));
    }
}
