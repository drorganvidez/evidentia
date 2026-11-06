<?php

namespace Tests\Feature\Components;

class AuroraTest extends ComponentTestCase
{
    public function test_by_default_it_is_fixed_calm_and_hidden_from_print_and_assistive_technology(): void
    {
        $html = $this->render('<x-ui.aurora />');

        $this->assertStringContainsString('aria-hidden="true"', $html);
        $this->assertStringContainsString('pointer-events-none', $html);
        $this->assertStringContainsString('print-hidden', $html);
        $this->assertStringContainsString('fixed inset-0', $html);
        $this->assertStringNotContainsString('absolute inset-0', $html);
        $this->assertStringNotContainsString('--blob-opacity:.8', $html);
        $this->assertStringNotContainsString('animation-duration', $html);
        $this->assertSame(3, substr_count($html, 'opacity-(--blob-opacity)'));
        $this->assertCspSafe($html);
    }

    public function test_intense_raises_the_opacity_and_speeds_up_every_blob(): void
    {
        $html = $this->render('<x-ui.aurora intense />');

        $this->assertStringContainsString('[--blob-opacity:.8] dark:[--blob-opacity:.7]', $html);
        $this->assertStringContainsString('[animation-duration:18s]', $html);
        $this->assertStringContainsString('[animation-duration:22s]', $html);
        $this->assertStringContainsString('[animation-duration:26s]', $html);
        $this->assertCspSafe($html);
    }

    public function test_static_places_it_inside_a_frame_instead_of_behind_the_page(): void
    {
        $html = $this->render('<x-ui.aurora intense static class="rounded-xl" />');

        $this->assertStringContainsString('absolute inset-0', $html);
        $this->assertStringNotContainsString('fixed inset-0', $html);
        $this->assertStringContainsString('rounded-xl', $html);
        $this->assertCspSafe($html);
    }
}
