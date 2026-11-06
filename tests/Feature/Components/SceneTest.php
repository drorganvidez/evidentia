<?php

namespace Tests\Feature\Components;

use Illuminate\View\ViewException;
use InvalidArgumentException;

class SceneTest extends ComponentTestCase
{
    public function test_the_hero_draws_the_pieces_of_the_platform_hidden_from_assistive_technology_and_print(): void
    {
        $html = $this->render('<x-ui.scene class="lg:mt-2" />');

        $this->assertMatchesRegularExpression('/^<div aria-hidden="true" data-scene="hero"\s+class="relative grid select-none [^"]*print-hidden[^"]* lg:mt-2">/', trim($html));
        $this->assertSame(1, substr_count($html, 'aria-hidden="true" data-scene'), 'One hidden root');

        // An evidence with its status, committee and hours
        $this->assertStringContainsString('EV-0142', $html);
        $this->assertStringContainsString('Taller de accesibilidad web', $html);
        $this->assertStringContainsString('bg-accepted-soft text-accepted-ink', $html);
        $this->assertStringContainsString('Aceptada', $html);
        $this->assertStringContainsString('3,5 h', $html);

        // The hours by origin: a meter with the four origins and their legend
        $this->assertStringContainsString('Mis horas', $html);
        $this->assertStringContainsString('w-55/100 bg-accepted', $html);
        $this->assertStringContainsString('w-24/100 bg-notice', $html);
        $this->assertStringContainsString('w-14/100 bg-pending', $html);
        $this->assertStringContainsString('w-7/100 bg-draft', $html);
        foreach (['Evidencias', 'Reuniones', 'Asistencia', 'Bonos'] as $origin) {
            $this->assertStringContainsString($origin, $html);
        }

        // A meeting with its signed minutes, a bonus and committee chips
        $this->assertStringContainsString('Reunión ordinaria 3', $html);
        $this->assertStringContainsString('Acta', $html);
        $this->assertStringContainsString('Firmada', $html);
        $this->assertStringContainsString('Bono', $html);
        $this->assertStringContainsString('+2 h', $html);
        foreach (['Programa', 'Logística', 'Igualdad'] as $committee) {
            $this->assertMatchesRegularExpression('/committee-dot hue-\d+[^>]*><\/span>\s*'.$committee.'/', $html);
        }

        // Only the evidence stays on the phone, over two blank cards; the rest shows from md
        $this->assertSame(2, substr_count($html, 'rounded-lg glass-2 opacity-'), 'The deck of the phone');
        $this->assertSame(2, preg_match_all('/ md:hidden/', $html));
        $this->assertSame(2, substr_count($html, 'max-md:hidden'), 'The meeting and the right column hide on the phone');

        // The cards float only without the reduced motion preference
        $this->assertSame(4, substr_count($html, 'md:motion-safe:animate-float'));
        $this->assertDoesNotMatchRegularExpression('/(?<!motion-safe:)animate-float/', $html);

        $this->assertNothingInteractive($html);
        $this->assertUniqueIds($html);
        $this->assertCspSafe($html);
    }

    public function test_the_sides_are_pieces_without_text_in_the_margins_from_lg(): void
    {
        $html = $this->render('<x-ui.scene variant="sides" />');

        $this->assertMatchesRegularExpression('/^<div aria-hidden="true" data-scene="sides"\s+class="pointer-events-none fixed inset-0 -z-10 hidden overflow-hidden select-none print-hidden lg:block">/', trim($html));
        $this->assertSame('', trim(strip_tags($html)), 'No text at all');
        $this->assertSame(4, substr_count($html, 'glass absolute rounded-lg p-3 motion-safe:animate-float'));
        $this->assertSame(2, substr_count($html, 'right-[calc(50%+'), 'Two pieces to the left of the column');
        $this->assertSame(2, substr_count($html, 'left-[calc(50%+'), 'Two pieces to its right');
        $this->assertStringContainsString('<svg viewBox="0 0 100 100"', $html, 'The ring of the hours by origin');
        $this->assertSame(3, substr_count($html, 'committee-dot'));
        $this->assertNothingInteractive($html);
        $this->assertUniqueIds($html);
        $this->assertCspSafe($html);
    }

    public function test_an_unknown_variant_is_refused(): void
    {
        try {
            $this->render('<x-ui.scene variant="banner" />');
            $this->fail('Rendered an unknown variant');
        } catch (ViewException $exception) {
            while ($exception->getPrevious() !== null) {
                $exception = $exception->getPrevious();
            }

            $this->assertInstanceOf(InvalidArgumentException::class, $exception);
            $this->assertSame('The scene variant is hero or sides.', $exception->getMessage());
        }
    }

    /** Decoration: no heading, no landmark, no id and nothing that takes the focus or the pointer. */
    private function assertNothingInteractive(string $html): void
    {
        $this->assertDoesNotMatchRegularExpression('/<(a|button|input|select|textarea|summary|h[1-6]|img)[\s>]/', $html);
        $this->assertStringNotContainsString('tabindex', $html);
        $this->assertStringNotContainsString('href=', $html);
        $this->assertStringNotContainsString(' id="', $html);
        $this->assertStringNotContainsString('role="img"', $html, 'The meter carries no label inside the scene');
    }
}
