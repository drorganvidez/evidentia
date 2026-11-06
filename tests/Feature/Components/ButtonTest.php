<?php

namespace Tests\Feature\Components;

use Illuminate\View\ViewException;
use InvalidArgumentException;

class ButtonTest extends ComponentTestCase
{
    public function test_it_is_a_link_with_href_and_a_button_without(): void
    {
        $link = $this->render('<x-ui.button href="/evidencias">Ver todas</x-ui.button>');

        $this->assertMatchesRegularExpression('/^<a /', trim($link));
        $this->assertStringContainsString('href="/evidencias"', $link);
        $this->assertStringNotContainsString('type=', $link);
        $this->assertStringContainsString('Ver todas', $link);
        $this->assertStringEndsWith('</a>', trim($link));

        $button = $this->render('<x-ui.button>Guardar</x-ui.button>');

        $this->assertMatchesRegularExpression('/^<button /', trim($button));
        $this->assertStringContainsString('type="button"', $button);
        $this->assertStringNotContainsString('href', $button);
        $this->assertStringEndsWith('</button>', trim($button));
        $this->assertCspSafe($button);
        $this->assertCspSafe($link);
    }

    public function test_the_submit_and_reset_types_are_honoured(): void
    {
        $this->assertStringContainsString('type="submit"', $this->render('<x-ui.button type="submit">Guardar</x-ui.button>'));
        $this->assertStringContainsString('type="reset"', $this->render('<x-ui.button type="reset">Limpiar</x-ui.button>'));
    }

    public function test_an_icon_only_button_needs_a_label(): void
    {
        try {
            $this->render('<x-ui.button icon-only icon="eye" />');
        } catch (ViewException $exception) {
            // The view engine wraps whatever a component throws
            while ($exception->getPrevious() !== null) {
                $exception = $exception->getPrevious();
            }

            $this->assertInstanceOf(InvalidArgumentException::class, $exception);
            $this->assertSame('An icon-only button needs a label.', $exception->getMessage());

            return;
        }

        $this->fail('An icon-only button without a label was rendered');
    }

    public function test_an_icon_only_button_carries_its_label_as_name_and_title_and_hides_the_slot(): void
    {
        $html = $this->render('<x-ui.button icon-only icon="eye" label="Abrir Montaje del photocall">Texto</x-ui.button>');

        $this->assertStringContainsString('aria-label="Abrir Montaje del photocall"', $html);
        $this->assertStringContainsString('title="Abrir Montaje del photocall"', $html);
        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('size-9 justify-center', $html);
        $this->assertStringNotContainsString('Texto', $html);

        $text = $this->render('<x-ui.button icon="eye" label="Abrir">Texto</x-ui.button>');

        $this->assertStringNotContainsString('aria-label', $text);
        $this->assertStringContainsString('Texto', $text);
    }

    public function test_busy_announces_itself_keeps_the_text_and_does_not_disable_the_control(): void
    {
        $html = $this->render('<x-ui.button icon="download" busy>Descargar</x-ui.button>');

        $this->assertStringContainsString('aria-busy="true"', $html);
        $this->assertStringContainsString('aria-disabled="true"', $html);
        $this->assertDoesNotMatchRegularExpression('/\sdisabled[\s>]/', $html);
        $this->assertStringContainsString('animate-spin', $html);
        $this->assertSame(1, substr_count($html, '<svg'), 'The spinner replaces the icon');
        $this->assertStringContainsString('Descargar', $html);
    }

    public function test_disabled_turns_off_a_button_and_marks_a_link(): void
    {
        $button = $this->render('<x-ui.button disabled>Guardar</x-ui.button>');
        $link = $this->render('<x-ui.button href="/evidencias" disabled>Ver todas</x-ui.button>');

        $this->assertMatchesRegularExpression('/\sdisabled[\s>]/', $button);
        $this->assertStringNotContainsString('aria-disabled="true"', $button);

        $this->assertStringContainsString('aria-disabled="true"', $link);
        $this->assertStringContainsString('tabindex="-1"', $link);
        $this->assertDoesNotMatchRegularExpression('/\sdisabled[\s>]/', $link);
    }

    public function test_copy_wires_the_clipboard_component_with_the_escaped_text(): void
    {
        $html = $this->render('<x-ui.button icon="copy" copy="https://evidentia.us.es/sign?a=1&b=<2>">Copiar enlace</x-ui.button>');

        $this->assertStringContainsString('x-data="clipboard"', $html);
        $this->assertStringContainsString('data-copy="https://evidentia.us.es/sign?a=1&amp;b=&lt;2&gt;"', $html);
        $this->assertStringContainsString('x-on:click="copy"', $html);
        $this->assertStringContainsString('x-show="!copied"', $html);
        $this->assertStringContainsString('x-show="copied"', $html);
        $this->assertSame(2, substr_count($html, '<svg'), 'The copy icon and the check');
        $this->assertCspSafe($html);

        $plain = $this->render('<x-ui.button icon="copy">Copiar enlace</x-ui.button>');

        $this->assertStringNotContainsString('x-data', $plain);
        $this->assertStringNotContainsString('x-show', $plain);
    }

    public function test_pressed_is_printed_only_when_given(): void
    {
        $this->assertStringContainsString('aria-pressed="true"', $this->render('<x-ui.button :pressed="true">Favorita</x-ui.button>'));
        $this->assertStringContainsString('aria-pressed="false"', $this->render('<x-ui.button :pressed="false">Favorita</x-ui.button>'));
        $this->assertStringNotContainsString('aria-pressed="', $this->render('<x-ui.button>Favorita</x-ui.button>'));
    }

    public function test_each_variant_and_size_prints_its_class_marker(): void
    {
        $this->assertStringContainsString('bg-brand text-on-brand', $this->render('<x-ui.button variant="primary">A</x-ui.button>'));
        $this->assertStringContainsString('hover:bg-brand-lo', $this->render('<x-ui.button variant="primary">A</x-ui.button>'));
        $this->assertStringContainsString('glass-2 text-ink', $this->render('<x-ui.button>A</x-ui.button>'));
        $this->assertStringContainsString('text-rejected-ink', $this->render('<x-ui.button variant="danger">A</x-ui.button>'));
        $this->assertStringContainsString('text-brand-ink', $this->render('<x-ui.button variant="ghost">A</x-ui.button>'));

        $this->assertStringContainsString('h-9 px-4 text-sm', $this->render('<x-ui.button size="sm">A</x-ui.button>'));
        $this->assertStringContainsString('h-10.5 px-5 text-md', $this->render('<x-ui.button>A</x-ui.button>'));
        $this->assertStringContainsString('h-12 px-6 text-base', $this->render('<x-ui.button size="lg">A</x-ui.button>'));

        $this->assertStringContainsString('size-8 justify-center', $this->render('<x-ui.button icon-only icon="eye" label="A" size="sm" />'));
        $this->assertStringContainsString('size-11 justify-center', $this->render('<x-ui.button icon-only icon="eye" label="A" size="lg" />'));
        $this->assertStringContainsString('pointer-coarse:min-h-11', $this->render('<x-ui.button size="sm">A</x-ui.button>'));
    }

    public function test_block_dim_and_the_icon_placement(): void
    {
        $block = $this->render('<x-ui.button block>Entrar</x-ui.button>');
        $dim = $this->render('<x-ui.button icon-only icon="eye" label="Abrir" dim />');
        $start = $this->render('<x-ui.button icon="plus">Nueva</x-ui.button>');
        $end = $this->render('<x-ui.button icon="arrow-right" icon-end>Siguiente</x-ui.button>');

        $this->assertStringContainsString('w-full justify-center', $block);
        // 70 % keeps the icon at 3:1 over the glass at rest (WCAG 1.4.11); hover and focus bring it to full
        $this->assertStringContainsString('opacity-70 group-hover:opacity-100 focus-visible:opacity-100', $dim);
        $this->assertStringNotContainsString('opacity-70', $block);

        $this->assertLessThan(strpos($start, 'Nueva'), strpos($start, '<svg'));
        $this->assertGreaterThan(strpos($end, 'Siguiente'), strpos($end, '<svg'));
    }

    public function test_the_consumer_classes_and_attributes_are_merged(): void
    {
        $html = $this->render('<x-ui.button class="ml-auto" data-test="go" x-on:click="toggle">Abrir</x-ui.button>');

        $this->assertStringContainsString('ml-auto', $html);
        $this->assertStringContainsString('inline-flex items-center', $html);
        $this->assertStringContainsString('data-test="go"', $html);
        $this->assertStringContainsString('x-on:click="toggle"', $html);
        $this->assertCspSafe($html);
    }
}
