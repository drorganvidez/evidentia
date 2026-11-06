<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/**
 * Base of the component tests: renders a Blade snippet and checks what every component must honour
 * under the strict Content Security Policy.
 */
abstract class ComponentTestCase extends TestCase
{
    /**
     * @param  array<string, mixed>  $data
     */
    protected function render(string $blade, array $data = []): string
    {
        return Blade::render($blade, $data);
    }

    /** No inline script without a nonce, no style attribute, no inline event handler. */
    protected function assertCspSafe(string $html): void
    {
        $this->assertDoesNotMatchRegularExpression('/<script(?![^>]*\snonce=)/i', $html, 'Inline script without nonce');
        $this->assertDoesNotMatchRegularExpression('/\sstyle=/i', $html, 'style attribute');
        $this->assertDoesNotMatchRegularExpression('/\son[a-z]+=/i', $html, 'Inline event handler');
    }

    /** Every id attribute appears once. */
    protected function assertUniqueIds(string $html): void
    {
        preg_match_all('/\sid="([^"]+)"/', $html, $matches);

        $repeated = array_keys(array_filter(array_count_values($matches[1]), fn (int $count): bool => $count > 1));

        $this->assertSame([], $repeated, 'Repeated ids');
    }

    /** The error summary of a form is the only element allowed to take the focus on load. */
    protected function assertNoAutofocus(string $html): void
    {
        $this->assertStringNotContainsString('autofocus', $html);
    }
}
