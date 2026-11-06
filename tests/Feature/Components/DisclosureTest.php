<?php

namespace Tests\Feature\Components;

use Illuminate\View\ViewException;
use InvalidArgumentException;

class DisclosureTest extends ComponentTestCase
{
    public function test_it_is_a_native_details_with_the_title_in_the_summary(): void
    {
        $html = $this->render('<x-ui.disclosure title="Comité de Programa">Contenido</x-ui.disclosure>');

        $this->assertMatchesRegularExpression('/^<details class="group glass rounded-lg open:shadow-raised"\s*>/', trim($html));
        $this->assertMatchesRegularExpression('/<summary\s+class="flex cursor-pointer list-none items-center rounded-lg \[&amp;::-webkit-details-marker\]:hidden min-h-12 gap-3 p-5">/', $html);
        $this->assertStringContainsString('<span class="min-w-0 text-md font-semibold">Comité de Programa</span>', $html);
        $this->assertStringContainsString('group-open:rotate-180', $html);
        $this->assertStringContainsString('<div class="px-5 pb-5">Contenido</div>', $html);
        $this->assertStringEndsWith('</details>', trim($html));
        $this->assertDoesNotMatchRegularExpression('/<details[^>]*\sopen\s*>/', $html);
        $this->assertStringNotContainsString('name=', $html);
        $this->assertSame(1, substr_count($html, '<svg'), 'Only the chevron');
        $this->assertCspSafe($html);
        $this->assertNoAutofocus($html);
    }

    public function test_it_needs_a_title(): void
    {
        try {
            $this->render('<x-ui.disclosure>Contenido</x-ui.disclosure>');
        } catch (ViewException $exception) {
            while ($exception->getPrevious() !== null) {
                $exception = $exception->getPrevious();
            }

            $this->assertInstanceOf(InvalidArgumentException::class, $exception);

            return;
        }

        $this->fail('A disclosure without a title was rendered');
    }

    public function test_open_and_name_are_printed_when_given(): void
    {
        $html = $this->render('<x-ui.disclosure title="Programa" name="comites" open>Contenido</x-ui.disclosure>');

        $this->assertMatchesRegularExpression('/^<details name="comites" class="group glass rounded-lg open:shadow-raised"\s+open\s*>/', trim($html));

        $closed = $this->render('<x-ui.disclosure title="Programa" :open="false">Contenido</x-ui.disclosure>');

        $this->assertDoesNotMatchRegularExpression('/<details[^>]*\sopen\s*>/', $closed);
    }

    public function test_level_turns_the_title_into_a_heading(): void
    {
        $this->assertStringContainsString('<h3 class="min-w-0 text-md font-semibold">Programa</h3>', $this->render('<x-ui.disclosure title="Programa" :level="3">Contenido</x-ui.disclosure>'));
        $this->assertStringContainsString('<h6 class="min-w-0 text-md font-semibold">Programa</h6>', $this->render('<x-ui.disclosure title="Programa" :level="9">Contenido</x-ui.disclosure>'), 'The level is clamped');
        $this->assertStringNotContainsString('<h', str_replace('</details>', '', $this->render('<x-ui.disclosure title="Programa">Contenido</x-ui.disclosure>')));
    }

    public function test_the_icon_and_the_extra_slot_are_optional(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-ui.disclosure title="Programa" icon="scroll-text">
                <x-slot:extra><span>4 actas</span></x-slot:extra>
                Contenido
            </x-ui.disclosure>
            BLADE);

        $this->assertStringContainsString(svg('lucide-scroll-text', 'shrink-0 size-4.5 text-ink-muted', ['aria-hidden' => 'true'])->toHtml(), $html);
        $this->assertStringContainsString('<span>4 actas</span>', $html);
        $this->assertLessThan(strpos($html, 'group-open:rotate-180'), strpos($html, '4 actas'), 'The extra content comes before the chevron');
        $this->assertSame(2, substr_count($html, '<svg'));
    }

    public function test_size_and_variant_map_to_literal_classes(): void
    {
        $small = $this->render('<x-ui.disclosure title="Programa" size="sm">Contenido</x-ui.disclosure>');

        $this->assertStringContainsString('min-h-11 gap-2.5 p-4', $small);
        $this->assertStringContainsString('<div class="px-4 pb-4">', $small);
        $this->assertStringContainsString('text-sm font-semibold', $small);

        $plain = $this->render('<x-ui.disclosure title="Programa" variant="plain" class="mt-2">Contenido</x-ui.disclosure>');

        $this->assertMatchesRegularExpression('/^<details class="group rounded-lg mt-2"\s*>/', trim($plain));
        $this->assertStringNotContainsString('glass', $plain);
        $this->assertStringNotContainsString('open:shadow-raised', $plain);
    }

    public function test_the_title_is_escaped(): void
    {
        $html = $this->render('<x-ui.disclosure :title="$title">Contenido</x-ui.disclosure>', ['title' => '<b>Programa</b>']);

        $this->assertStringContainsString('&lt;b&gt;Programa&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }
}
