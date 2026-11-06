<?php

namespace Tests\Feature\Components;

use Html;
use Illuminate\Support\HtmlString;

class RichTextTest extends ComponentTestCase
{
    private const PNG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAYAAAAGCAIAAABvrngfAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAFElEQVQImWNM8rrMgAqYGDAANYUAqj0Bi085lBwAAAAASUVORK5CYII=';

    public function test_it_prints_the_purified_html_without_escaping_it_again(): void
    {
        $html = $this->render('<x-ui.rich-text :html="$html" />', ['html' => '<p>Hola <b>mundo</b> &amp; adiós</p>']);

        $this->assertMatchesRegularExpression('/^<div class="prose-ui text-base">\s*<p>Hola <b>mundo<\/b> &amp; adiós<\/p><\/div>$/', trim($html));
        $this->assertStringNotContainsString('&lt;', $html);
        $this->assertCspSafe($html);
        $this->assertNoAutofocus($html);
    }

    public function test_size_and_clamp_map_to_literal_classes(): void
    {
        $this->assertStringContainsString('<div class="prose-ui text-sm">', $this->render('<x-ui.rich-text html="<p>Hola</p>" size="sm" />'));
        $this->assertStringContainsString('<div class="prose-ui text-base line-clamp-3">', $this->render('<x-ui.rich-text html="<p>Hola</p>" :clamp="3" />'));
        $this->assertStringContainsString('<div class="prose-ui text-base line-clamp-6">', $this->render('<x-ui.rich-text html="<p>Hola</p>" :clamp="6" />'));
        $this->assertStringContainsString('<div class="prose-ui text-base">', $this->render('<x-ui.rich-text html="<p>Hola</p>" :clamp="7" />'), 'Only 2 to 6 exist as literal classes');
        $this->assertStringNotContainsString('line-clamp', $this->render('<x-ui.rich-text html="<p>Hola</p>" />'));
    }

    public function test_the_consumer_classes_are_merged(): void
    {
        $html = $this->render('<x-ui.rich-text html="<p>Hola</p>" class="mt-4" data-test="bio" />');

        $this->assertStringContainsString('<div class="prose-ui text-base mt-4" data-test="bio">', $html);
    }

    public function test_it_purifies_the_stored_html_itself_so_a_view_cannot_print_it_raw(): void
    {
        $dirty = '<h2 onclick="steal()">Título</h2><p>Hola<script>alert(1)</script> <a href="javascript:alert(1)">enlace</a></p>'
            .'<img src="'.self::PNG.'" alt="Lona"><table><tr><td>1</td></tr></table><iframe src="https://example.com"></iframe>';

        $html = $this->render('<x-ui.rich-text :html="$dirty" />', ['dirty' => $dirty]);

        $this->assertStringContainsString('<h2>Título</h2>', $html);
        $this->assertStringContainsString('<img src="'.self::PNG.'" alt="Lona"', $html);
        $this->assertStringContainsString('<td>1</td>', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('<iframe', $html);
        $this->assertCspSafe($html);

        $twice = $this->render('<x-ui.rich-text :html="Html::clean($dirty)" />', ['dirty' => $dirty]);

        $this->assertSame($html, $twice, 'Purifying the output of Html::clean again changes nothing');
    }

    public function test_a_trusted_htmlable_is_printed_as_it_comes(): void
    {
        $html = $this->render('<x-ui.rich-text :html="$trusted" />', [
            'trusted' => new HtmlString('<p data-source="seeder">Texto <mark>propio</mark></p>'),
        ]);

        $this->assertStringContainsString('<p data-source="seeder">Texto <mark>propio</mark></p>', $html);
    }

    public function test_a_missing_text_renders_an_empty_prose_block(): void
    {
        $this->assertMatchesRegularExpression('/^<div class="prose-ui text-base">\s*<\/div>$/', trim($this->render('<x-ui.rich-text :html="null" />')));
        $this->assertSame('', Html::clean(null));
    }
}
