<?php

namespace Tests\Feature\Components;

use Illuminate\View\ViewException;
use InvalidArgumentException;

class PdfSectionTest extends ComponentTestCase
{
    public function test_it_is_a_section_with_a_level_two_heading_by_default(): void
    {
        $html = $this->render('<x-pdf.section :number="1" title="Datos de la reunión"><p>Cuerpo</p></x-pdf.section>');

        $this->assertMatchesRegularExpression('/^<section class="pdf-section">/', trim($html));
        $this->assertStringContainsString('<h2 class="pdf-section__title"><span class="pdf-section__number">1</span>Datos de la reunión</h2>', $html);
        $this->assertStringContainsString('<p>Cuerpo</p>', $html);
        $this->assertStringEndsWith('</section>', trim($html));
        $this->assertStringNotContainsString('pdf-section--break', $html);
        $this->assertCspSafe($html);
    }

    public function test_level_three_number_formats_and_the_page_break(): void
    {
        $html = $this->render('<x-pdf.section number="2.3" title="Presupuesto" :level="3" page-break class="mt-2">Cuerpo</x-pdf.section>');

        $this->assertStringContainsString('<section class="pdf-section pdf-section--break mt-2">', $html);
        $this->assertStringContainsString('<h3 class="pdf-section__title"><span class="pdf-section__number">2.3</span>Presupuesto</h3>', $html);

        $this->assertStringContainsString('<h2 class="pdf-section__title">', $this->render('<x-pdf.section title="Cierre" :level="4">Cuerpo</x-pdf.section>'), 'Only levels 2 and 3 exist');
    }

    public function test_the_number_is_optional_and_the_title_is_escaped(): void
    {
        $html = $this->render('<x-pdf.section :title="$title">Cuerpo</x-pdf.section>', ['title' => 'Cierre <b>final</b>']);

        $this->assertStringContainsString('<h2 class="pdf-section__title">Cierre &lt;b&gt;final&lt;/b&gt;</h2>', $html);
        $this->assertStringNotContainsString('pdf-section__number', $html);
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringNotContainsString('pdf-section__number', $this->render('<x-pdf.section number="" title="Cierre">Cuerpo</x-pdf.section>'));
    }

    public function test_it_needs_a_title(): void
    {
        try {
            $this->render('<x-pdf.section :number="1">Cuerpo</x-pdf.section>');
        } catch (ViewException $exception) {
            while ($exception->getPrevious() !== null) {
                $exception = $exception->getPrevious();
            }

            $this->assertInstanceOf(InvalidArgumentException::class, $exception);

            return;
        }

        $this->fail('A section without a title was rendered');
    }
}
