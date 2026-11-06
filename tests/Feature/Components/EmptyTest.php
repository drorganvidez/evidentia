<?php

namespace Tests\Feature\Components;

use Illuminate\View\ViewException;
use InvalidArgumentException;

class EmptyTest extends ComponentTestCase
{
    private const INBOX = 'points="22 12 16 12 14 15 10 15 8 12 2 12"';

    private const LOCK = '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"';

    public function test_it_announces_the_absence_with_a_real_heading_and_the_default_icon(): void
    {
        $html = $this->render('<x-ui.empty title="No hay evidencias" class="mt-4" />');

        $this->assertStringContainsString('<div class="grid justify-items-center gap-3 text-center py-12 mt-4">', $html);
        $this->assertMatchesRegularExpression('/<h3 class="text-base font-bold">\s*No hay evidencias\s*<\/h3>/', $html);
        $this->assertStringContainsString('<span aria-hidden="true" class="grid size-12 place-items-center rounded-md bg-brand-soft text-brand-soft-ink">', $html);
        $this->assertStringContainsString(self::INBOX, $html);
        $this->assertStringContainsString('class="shrink-0 size-6"', $html);
        $this->assertStringNotContainsString('<p class=', $html);
        $this->assertStringNotContainsString('justify-center gap-3 pt-1', $html);
        $this->assertNoAutofocus($html);
        $this->assertCspSafe($html);
    }

    public function test_the_text_and_the_actions_are_optional(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-ui.empty title="No hay evidencias" text="Cuando subas una aparecerá aquí.">
                <x-slot:actions><a href="/nueva">Nueva evidencia</a></x-slot:actions>
            </x-ui.empty>
            BLADE);

        $this->assertStringContainsString('<p class="max-w-md text-md text-ink-muted">Cuando subas una aparecerá aquí.</p>', $html);
        $this->assertStringContainsString('<div class="flex flex-wrap justify-center gap-3 pt-1"><a href="/nueva">Nueva evidencia</a></div>', $html);
    }

    public function test_a_code_replaces_the_icon_bubble(): void
    {
        $html = $this->render('<x-ui.empty code="404" title="No encontramos la página" :level="1" />');

        $this->assertStringContainsString('<span class="font-mono text-6xl font-bold tracking-tighter text-brand-ink">404</span>', $html);
        $this->assertStringContainsString('<h1 class="text-base font-bold">', $html);
        $this->assertStringNotContainsString('bg-brand-soft', $html);
        $this->assertStringNotContainsString(self::INBOX, $html);
    }

    public function test_the_icon_the_size_and_the_level_follow_the_props(): void
    {
        $html = $this->render('<x-ui.empty icon="lock" title="Cuenta bloqueada" size="sm" :level="2" />');

        $this->assertStringContainsString(self::LOCK, $html);
        $this->assertStringNotContainsString(self::INBOX, $html);
        $this->assertStringContainsString('text-center py-6"', $html);
        $this->assertStringContainsString('<h2 class="text-base font-bold">', $html);
        $this->assertStringNotContainsString('py-12', $html);
    }

    public function test_the_texts_are_escaped(): void
    {
        $html = $this->render('<x-ui.empty :title="$title" :text="$text" />', ['title' => '<b>Nada</b>', 'text' => '<i>Vacío</i>']);

        $this->assertStringContainsString('&lt;b&gt;Nada&lt;/b&gt;', $html);
        $this->assertStringContainsString('&lt;i&gt;Vacío&lt;/i&gt;', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }

    public function test_the_title_is_required(): void
    {
        try {
            $this->render('<x-ui.empty />');
        } catch (ViewException $exception) {
            while ($exception->getPrevious() !== null) {
                $exception = $exception->getPrevious();
            }

            $this->assertInstanceOf(InvalidArgumentException::class, $exception);
            $this->assertSame('The empty component needs a title.', $exception->getMessage());

            return;
        }

        $this->fail('The component rendered without a title');
    }
}
