<?php

namespace Tests\Feature\Components;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\View\ViewException;
use InvalidArgumentException;

class PdfDocumentTest extends ComponentTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-11-05 22:40:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_is_a_complete_spanish_document_with_flat_colours_and_helvetica(): void
    {
        $html = $this->render('<x-pdf.document title="Acta de reunión"><p>Cuerpo</p></x-pdf.document>');

        $this->assertStringStartsWith('<!doctype html>', trim($html));
        $this->assertStringContainsString('<html lang="es">', $html);
        $this->assertStringContainsString('<meta charset="utf-8">', $html);
        $this->assertStringContainsString('<title>Acta de reunión · Jornadas InnoSoft Days 2026</title>', $html);
        $this->assertMatchesRegularExpression('/<style nonce="[^"]*">/', $html);
        $this->assertStringContainsString('font-family: Helvetica, Arial, sans-serif;', $html);
        $this->assertStringContainsString('<h1 class="pdf-title">Acta de reunión</h1>', $html);
        $this->assertStringContainsString('<p>Cuerpo</p>', $html);
        $this->assertStringContainsString('<span class="pdf-header__name">Evidentia</span>', $html);
        $this->assertStringContainsString('<td class="pdf-header__event">Jornadas InnoSoft Days 2026</td>', $html);
        $this->assertStringContainsString('@media dompdf', $html);

        foreach (['#624ad3', '#282d36', '#616875', '#d4d9e3', '#f3f7fe', '#007c45', '#8b5d00', '#bc2028'] as $hex) {
            $this->assertStringContainsString($hex, $html, $hex);
        }

        $this->assertStringNotContainsString('var(--', $html);
        $this->assertStringNotContainsString('oklch(', $html);
        $this->assertStringNotContainsString('backdrop', $html);
        $this->assertStringNotContainsString('color-mix', $html);
        $this->assertCspSafe($html);
        $this->assertNoAutofocus($html);
    }

    public function test_it_needs_a_title(): void
    {
        try {
            $this->render('<x-pdf.document>Cuerpo</x-pdf.document>');
        } catch (ViewException $exception) {
            while ($exception->getPrevious() !== null) {
                $exception = $exception->getPrevious();
            }

            $this->assertInstanceOf(InvalidArgumentException::class, $exception);

            return;
        }

        $this->fail('A document without a title was rendered');
    }

    public function test_the_brand_mark_is_embedded_as_a_data_uri_by_default(): void
    {
        $html = $this->render('<x-pdf.document title="Acta">Cuerpo</x-pdf.document>');

        $expected = 'data:image/png;base64,'.base64_encode(file_get_contents(public_path('img/brand/mark-256.png')));

        $this->assertStringContainsString('<img class="pdf-header__mark" src="'.$expected.'" alt="" width="28" height="28">', $html);

        $url = $this->render('<x-pdf.document title="Acta" logo="https://evidentia.us.es/img/brand/mark-256.png">Cuerpo</x-pdf.document>');

        $this->assertStringContainsString('src="https://evidentia.us.es/img/brand/mark-256.png"', $url);
        $this->assertStringNotContainsString('base64', $url);

        $missing = $this->render('<x-pdf.document title="Acta" logo="/no/such/file.png">Cuerpo</x-pdf.document>');

        $this->assertStringNotContainsString('<img', $missing);
        $this->assertStringContainsString('Evidentia', $missing);
    }

    public function test_event_committee_subtitle_and_footer_are_printed_and_escaped(): void
    {
        $html = $this->render('<x-pdf.document title="Acta" event="Jornadas 2027" committee="Comité de <b>Programa</b>" subtitle="Reunión 3" footer-text="Pie <i>de firma</i>">Cuerpo</x-pdf.document>');

        $this->assertStringContainsString('<td class="pdf-header__event">Jornadas 2027</td>', $html);
        $this->assertStringContainsString('<p class="pdf-committee">Comité de &lt;b&gt;Programa&lt;/b&gt;</p>', $html);
        $this->assertStringContainsString('<p class="pdf-subtitle">Reunión 3</p>', $html);
        $this->assertMatchesRegularExpression('/<footer class="pdf-footer">\s*Pie &lt;i&gt;de firma&lt;\/i&gt;\s*<\/footer>/', $html);
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringNotContainsString('<i>', $html);

        $bare = $this->render('<x-pdf.document title="Acta">Cuerpo</x-pdf.document>');

        $this->assertStringNotContainsString('<p class="pdf-committee">', $bare);
        $this->assertStringNotContainsString('<p class="pdf-subtitle">', $bare);
        $this->assertMatchesRegularExpression('/<footer class="pdf-footer">\s*<\/footer>/', $bare);
    }

    public function test_the_page_numbers_depend_on_dompdf_being_allowed_to_run_php(): void
    {
        config(['dompdf.options.enable_php' => false]);

        $this->assertStringNotContainsString('<script', $this->render('<x-pdf.document title="Acta">Cuerpo</x-pdf.document>'));

        config(['dompdf.options.enable_php' => true]);

        $numbered = $this->render('<x-pdf.document title="Acta">Cuerpo</x-pdf.document>');

        $this->assertMatchesRegularExpression('/<script type="text\/php" nonce="[^"]*">/', $numbered);
        $this->assertStringContainsString('Página {PAGE_NUM} de {PAGE_COUNT}', $numbered);
        $this->assertStringContainsString('page_text(', $numbered);
        $this->assertCspSafe($numbered);

        $this->assertStringNotContainsString('<script', $this->render('<x-pdf.document title="Acta" :page-numbers="false">Cuerpo</x-pdf.document>'));
    }

    public function test_the_example_minutes_render_to_a_pdf_and_open_in_the_browser(): void
    {
        $output = Pdf::loadView('ui.pages.pdf')->output();

        $this->assertStringStartsWith('%PDF', $output);
        $this->assertGreaterThan(10_000, strlen($output));

        $response = $this->get('/_ui/pdf')->assertOk();

        $response->assertSee('<html lang="es">', false);
        $response->assertSee('Acta de reunión', false);
        $response->assertSee('Datos de la reunión', false);
        $response->assertSee('Desarrollo de la reunión', false);
        $response->assertHeader('Content-Security-Policy');

        preg_match("/'nonce-([A-Za-z0-9]{40})'/", (string) $response->headers->get('Content-Security-Policy'), $match);

        $response->assertSee('<style nonce="'.$match[1].'">', false);
        $this->assertCspSafe($response->getContent());
    }
}
