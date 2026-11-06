<?php

namespace Tests\Feature\Components;

use Illuminate\View\ViewException;
use InvalidArgumentException;

class DlTest extends ComponentTestCase
{
    public function test_it_is_a_real_definition_list_with_grouped_pairs(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-ui.dl class="mt-4">
                <x-ui.dl.item term="Registro" mono>EV-0033</x-ui.dl.item>
                <x-ui.dl.item term="Horas">1,75 h</x-ui.dl.item>
            </x-ui.dl>
            BLADE);

        $this->assertStringContainsString('<dl class="grid [--term-w:5.75rem] md:[--term-w:6.25rem] [--dl-py:0.5625rem] mt-4">', $html);
        $this->assertSame(2, preg_match_all('/<div\s+class="grid grid-cols-\[var\(--term-w\)_minmax\(0,1fr\)\] items-center gap-3 border-t border-rule py-\(--dl-py\) text-md">/', $html));
        $this->assertStringContainsString('<dt class="font-medium text-ink-muted">Registro</dt>', $html);
        $this->assertStringContainsString('<dd class="m-0 min-w-0 font-mono tabular-nums">EV-0033</dd>', $html);
        $this->assertStringContainsString('<dd class="m-0 min-w-0">1,75 h</dd>', $html);
        $this->assertStringContainsString('</dl>', $html);
        $this->assertStringNotContainsString('<table', $html);
        $this->assertUniqueIds($html);
        $this->assertCspSafe($html);
    }

    public function test_the_term_width_and_the_density_are_variables_on_the_list(): void
    {
        $small = $this->render('<x-ui.dl term-width="sm" dense>x</x-ui.dl>');

        $this->assertStringContainsString('<dl class="grid [--term-w:5rem] [--dl-py:0.375rem]">', $small);
        $this->assertStringNotContainsString('5.75rem', $small);
    }

    public function test_the_item_keeps_the_consumer_classes_and_needs_a_term(): void
    {
        $this->assertStringContainsString('text-md items-start"', $this->render('<x-ui.dl.item term="Nota" class="items-start">x</x-ui.dl.item>'));

        try {
            $this->render('<x-ui.dl.item>x</x-ui.dl.item>');
        } catch (ViewException $exception) {
            while ($exception->getPrevious() !== null) {
                $exception = $exception->getPrevious();
            }

            $this->assertInstanceOf(InvalidArgumentException::class, $exception);

            return;
        }

        $this->fail('A dl item without a term was rendered');
    }

    public function test_the_term_is_escaped_and_the_description_is_escaped_by_the_caller(): void
    {
        $html = $this->render('<x-ui.dl.item :term="$term">{{ $text }}</x-ui.dl.item>', ['term' => '<b>Registro</b>', 'text' => '<i>x</i>']);

        $this->assertStringContainsString('<dt class="font-medium text-ink-muted">&lt;b&gt;Registro&lt;/b&gt;</dt>', $html);
        $this->assertStringContainsString('&lt;i&gt;x&lt;/i&gt;', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }
}
