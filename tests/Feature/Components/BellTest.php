<?php

namespace Tests\Feature\Components;

use Illuminate\View\ViewException;
use InvalidArgumentException;

class BellTest extends ComponentTestCase
{
    public function test_without_unread_notices_it_is_a_plain_link_named_avisos(): void
    {
        $html = $this->render('<x-ui.bell href="/avisos" class="ml-2" />');

        $this->assertMatchesRegularExpression('#^<a href="/avisos" title="Avisos"\s+class="relative grid size-9 place-items-center rounded-full text-ink-muted transition hover:bg-glass-2 hover:text-ink pointer-coarse:size-11 ml-2">#', trim($html));
        $this->assertStringContainsString('<span class="sr-only">Avisos</span>', $html);
        $this->assertSame(1, substr_count($html, '<svg'));
        $this->assertStringNotContainsString('bg-brand', $html, 'No counter without unread notices');
        $this->assertStringNotContainsString('aria-label', $html, 'The hidden text names the link so axe never sees a mismatch');
        $this->assertStringEndsWith('</a>', trim($html));
        $this->assertCspSafe($html);
    }

    public function test_the_unread_count_goes_into_the_name_and_a_decorative_badge(): void
    {
        $html = $this->render('<x-ui.bell :count="3" href="/avisos" />');

        $this->assertStringContainsString('title="Avisos, 3 sin leer"', $html);
        $this->assertStringContainsString('<span class="sr-only">Avisos, 3 sin leer</span>', $html);
        $this->assertMatchesRegularExpression('/<span aria-hidden="true"\s+class="absolute -top-0.5 -right-0.5 grid min-w-4.5 place-items-center rounded-full bg-brand px-1 font-mono text-2xs font-bold text-on-brand tabular-nums">3<\/span>/', $html);
    }

    public function test_past_ninety_nine_the_badge_shows_99_plus_but_the_name_keeps_the_figure(): void
    {
        $html = $this->render('<x-ui.bell :count="120" href="/avisos" label="Notificaciones" />');

        $this->assertStringContainsString('>99+</span>', $html);
        $this->assertStringContainsString('<span class="sr-only">Notificaciones, 120 sin leer</span>', $html);
        $this->assertStringContainsString('title="Notificaciones, 120 sin leer"', $html);
    }

    public function test_negative_counts_are_treated_as_zero_and_the_href_is_required(): void
    {
        $this->assertStringNotContainsString('bg-brand', $this->render('<x-ui.bell :count="-2" href="/avisos" />'));

        try {
            $this->render('<x-ui.bell :count="3" />');
        } catch (ViewException $exception) {
            while ($exception->getPrevious() !== null) {
                $exception = $exception->getPrevious();
            }

            $this->assertInstanceOf(InvalidArgumentException::class, $exception);
            $this->assertSame('The bell needs a href.', $exception->getMessage());

            return;
        }

        $this->fail('A bell without a href was rendered');
    }
}
