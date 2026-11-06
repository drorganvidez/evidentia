<?php

namespace Tests\Feature\Components;

class CardTest extends ComponentTestCase
{
    public function test_a_card_is_a_section_named_by_its_title(): void
    {
        $html = $this->render('<x-ui.card title="Pruebas" class="mt-4">Hola</x-ui.card>');

        $this->assertStringContainsString('<section aria-labelledby="card-pruebas" class="glass rounded-xl mt-4">', $html);
        $this->assertMatchesRegularExpression('/<h2 id="card-pruebas" class="min-w-0 text-lg font-bold tracking-tight">\s*Pruebas\s*<\/h2>/', $html);
        $this->assertStringContainsString('<div class="px-5 py-4 md:px-8 md:py-6">', $html);
        $this->assertStringContainsString('mb-4', $html);
        $this->assertStringContainsString('Hola', $html);
        $this->assertStringContainsString('</section>', $html);
        $this->assertStringNotContainsString('hover:', $html);
        $this->assertUniqueIds($html);
        $this->assertNoAutofocus($html);
        $this->assertCspSafe($html);
    }

    public function test_with_href_the_whole_card_is_one_link(): void
    {
        $labelled = $this->render('<x-ui.card href="/horas" aria-label="Ver el desglose de mis horas" arrow>10,5 h</x-ui.card>');

        $this->assertStringContainsString('<a href="/horas" aria-label="Ver el desglose de mis horas" class="glass rounded-xl relative group block text-inherit no-underline transition hover:-translate-y-0.5">', $labelled);
        $this->assertStringContainsString('group-hover:translate-x-0.5', $labelled);
        $this->assertStringContainsString('<div class="px-5 py-4 md:px-8 md:py-6 pr-10">', $labelled);
        $this->assertStringContainsString('</a>', $labelled);
        $this->assertStringNotContainsString('<section', $labelled);
        $this->assertStringNotContainsString('aria-labelledby', $labelled);

        // Named by its content, as WCAG 2.5.3 expects from a link that shows text
        $titled = $this->render('<x-ui.card href="/horas" title="Mis horas" as="div">10,5 h</x-ui.card>');

        $this->assertStringContainsString('<a href="/horas" class="glass rounded-xl group block', $titled);
        $this->assertStringContainsString('<h2 id="card-mis-horas"', $titled);
        $this->assertStringNotContainsString('aria-labelledby', $titled);
        $this->assertStringNotContainsString('chevron', $titled);
        $this->assertStringNotContainsString('pr-10', $titled);
    }

    public function test_the_element_and_the_heading_level_follow_the_props(): void
    {
        $article = $this->render('<x-ui.card as="article" title="Acta" :level="3">Hola</x-ui.card>');

        $this->assertStringContainsString('<article aria-labelledby="card-acta" class="glass rounded-xl">', $article);
        $this->assertStringContainsString('<h3 id="card-acta"', $article);
        $this->assertStringContainsString('</article>', $article);

        $div = $this->render('<x-ui.card as="div" title="Acta" id="minutes">Hola</x-ui.card>');

        $this->assertStringContainsString('<div class="glass rounded-xl">', $div);
        $this->assertStringNotContainsString('aria-labelledby', $div);
        $this->assertStringContainsString('<h2 id="minutes"', $div);

        $named = $this->render('<x-ui.card title="Acta" id="minutes" aria-label="Acta firmada">Hola</x-ui.card>');

        $this->assertStringContainsString('<section aria-label="Acta firmada" class="glass rounded-xl">', $named);
        $this->assertStringNotContainsString('aria-labelledby', $named);
        $this->assertStringContainsString('<h2 id="minutes"', $named);

        $this->assertStringContainsString('<section class="glass rounded-xl">', $this->render('<x-ui.card as="span">Hola</x-ui.card>'));
    }

    public function test_the_slots_are_optional(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-ui.card title="Pruebas">
                <x-slot:extra><span>3 archivos</span></x-slot:extra>
                <x-slot:footer class="text-sm">Pie</x-slot:footer>
                Cuerpo
            </x-ui.card>
            BLADE);

        $this->assertStringContainsString('<div class="ml-auto flex items-center gap-2"><span>3 archivos</span></div>', $html);
        $this->assertStringContainsString('<div class="border-t border-rule px-5 py-4 md:px-8 text-sm">Pie</div>', $html);

        $header = $this->render(<<<'BLADE'
            <x-ui.card title="Ignorado">
                <x-slot:header class="items-center">Cabecera propia</x-slot:header>
                Cuerpo
            </x-ui.card>
            BLADE);

        $this->assertStringContainsString('<div class="flex flex-wrap items-start justify-between gap-x-4 gap-y-1 mb-4 items-center">Cabecera propia</div>', $header);
        $this->assertStringNotContainsString('<h2', $header);

        $bare = $this->render('<x-ui.card>Solo cuerpo</x-ui.card>');

        $this->assertStringNotContainsString('<h2', $bare);
        $this->assertStringNotContainsString('ml-auto', $bare);
        $this->assertStringNotContainsString('border-t', $bare);
        $this->assertStringNotContainsString('aria-labelledby', $bare);

        $empty = $this->render('<x-ui.card title="Sin cuerpo" />');

        $this->assertStringNotContainsString('mb-4', $empty);
    }

    public function test_halo_and_padding_map_to_literal_classes(): void
    {
        $this->assertStringContainsString('class="glass rounded-xl relative halo-aurora overflow-hidden"', $this->render('<x-ui.card halo>Hola</x-ui.card>'));
        $this->assertStringContainsString('<div class="px-5 py-4">', $this->render('<x-ui.card padding="sm">Hola</x-ui.card>'));
        $this->assertStringContainsString('<div class="p-0">', $this->render('<x-ui.card padding="none">Hola</x-ui.card>'));
        $this->assertStringNotContainsString('halo-aurora', $this->render('<x-ui.card>Hola</x-ui.card>'));
    }

    public function test_the_title_is_escaped(): void
    {
        $html = $this->render('<x-ui.card :title="$title">Hola</x-ui.card>', ['title' => '<b>Pruebas</b>']);

        $this->assertStringContainsString('&lt;b&gt;Pruebas&lt;/b&gt;', $html);
        $this->assertStringContainsString('id="card-bpruebasb"', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }

    public function test_the_catalogue_annotates_every_card_demo_outside_its_content(): void
    {
        $html = view('ui.sections.card')->render();

        // Like the rest of the catalogue: a mono note over each demo, real content inside the card
        $this->assertSame(7, substr_count($html, '<span class="font-mono text-2xs text-ink-muted">'));
        $this->assertSame(7, preg_match_all('/<div class="grid content-start gap-2[^"]*" data-demo="[a-z]+">\s*<span class="font-mono text-2xs text-ink-muted">/', $html));
        $this->assertStringNotContainsString('Tarjeta compacta', $html);
        $this->assertStringNotContainsString('Cabecera propia', $html);
        $this->assertUniqueIds($html);
    }
}
