<?php

namespace Tests\Feature\Components;

use Illuminate\View\ViewException;
use InvalidArgumentException;

class CountChipTest extends ComponentTestCase
{
    public function test_it_prints_the_spanish_figure_names_the_chip_with_it_and_wires_the_count_up(): void
    {
        $html = $this->render('<x-ui.count-chip :value="1204" unit="h acreditadas" class="ml-2" />');

        $this->assertMatchesRegularExpression('/^<span role="img" aria-label="1.204 h acreditadas"\s+class="glass inline-flex items-center gap-3 rounded-full py-2.5 pr-4 pl-3 text-md font-medium text-ink max-md:py-2 max-md:pr-3 max-md:pl-2.5 max-md:text-sm animate-rise motion-reduce:animate-none ml-2">/', trim($html));
        $this->assertStringContainsString('[--orb:var(--brand)]', $html, 'The dot takes the brand tone by default');
        $this->assertStringContainsString('size-2.5', $html);
        $this->assertMatchesRegularExpression('/<span x-data="countUp" data-count="1204" data-decimals="0"\s+data-duration="1200" class="font-mono text-lg font-semibold tracking-tight tabular-nums"\s+x-text="shown">1.204<\/span>/', $html);
        $this->assertMatchesRegularExpression('/<\/span>\s*h acreditadas\s*<\/span>$/', trim($html));
        $this->assertStringNotContainsString('animation-delay', $html);
        $this->assertCspSafe($html);
    }

    public function test_decimals_tone_delay_and_duration_are_honoured(): void
    {
        $html = $this->render('<x-ui.count-chip :value="10.5" :decimals="1" unit="h por estudiante" tone="accepted" :delay="80" :duration="600" />');

        $this->assertStringContainsString('aria-label="10,5 h por estudiante"', $html);
        $this->assertStringContainsString('[--orb:var(--accepted)]', $html);
        $this->assertStringContainsString('[animation-delay:80ms]', $html);
        $this->assertStringContainsString('data-count="10.5" data-decimals="1"', $html);
        $this->assertStringContainsString('data-duration="600"', $html);
        $this->assertStringContainsString('>10,5</span>', $html);

        $this->assertStringContainsString('[animation-delay:160ms]', $this->render('<x-ui.count-chip :value="1" unit="x" :delay="160" />'));
        $this->assertStringNotContainsString('animation-delay', $this->render('<x-ui.count-chip :value="1" unit="x" :delay="50" />'), 'Only the literal delays exist');

        $still = $this->render('<x-ui.count-chip :value="1" unit="x" :delay="80" :rise="false" />');

        $this->assertStringNotContainsString('animate-rise', $still);
        $this->assertStringNotContainsString('animation-delay', $still);
        $this->assertStringContainsString('aria-label="1.234.567,89 x"', $this->render('<x-ui.count-chip :value="1234567.891" :decimals="2" unit="x" />'));
    }

    public function test_the_unit_is_escaped_and_value_and_unit_are_required(): void
    {
        $this->assertStringContainsString('&lt;b&gt;h&lt;/b&gt;', $this->render('<x-ui.count-chip :value="1" unit="<b>h</b>" />'));

        foreach (['<x-ui.count-chip unit="h" />' => 'The count chip needs a numeric value.', '<x-ui.count-chip value="abc" unit="h" />' => 'The count chip needs a numeric value.', '<x-ui.count-chip :value="1" />' => 'The count chip needs a unit.'] as $blade => $message) {
            try {
                $this->render($blade);
                $this->fail('Rendered without a required prop: '.$blade);
            } catch (ViewException $exception) {
                while ($exception->getPrevious() !== null) {
                    $exception = $exception->getPrevious();
                }

                $this->assertInstanceOf(InvalidArgumentException::class, $exception);
                $this->assertSame($message, $exception->getMessage());
            }
        }
    }
}
