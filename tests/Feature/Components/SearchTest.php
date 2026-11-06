<?php

namespace Tests\Feature\Components;

use Illuminate\View\ViewException;
use InvalidArgumentException;

class SearchTest extends ComponentTestCase
{
    public function test_it_is_a_get_search_form_with_a_labelled_field_and_the_shortcut(): void
    {
        $html = $this->render('<x-ui.search action="/evidencias" class="ml-auto" />');

        $this->assertMatchesRegularExpression('#^<form role="search" method="get" action="/evidencias" x-data="shortcut" data-key="/"\s+x-on:keydown.window="onKey" aria-keyshortcuts="/"\s+class="hidden md:block ml-auto">#', trim($html));
        $this->assertStringContainsString('<label for="field-q" class="sr-only">Buscar</label>', $html);
        $this->assertMatchesRegularExpression('/<input id="field-q" type="search" name="q" value=""\s+x-ref="input"\s+placeholder="Buscar evidencias"\s+autocomplete="off"\s+class="w-0 min-w-0 flex-1 bg-transparent text-control text-ink outline-none placeholder:text-ink-muted">/', $html);
        $this->assertStringContainsString('focus-within:border-aurora-500 focus-within:ring-4 focus-within:ring-aurora-500/18', $html);
        $this->assertMatchesRegularExpression('/<kbd aria-hidden="true"\s+class="[^"]+">\/<\/kbd>/', $html);
        $this->assertSame(1, substr_count($html, '<svg'));
        $this->assertStringNotContainsString('placeholder:text-ink-faint', $html, 'The placeholder keeps AA contrast');
        $this->assertStringEndsWith('</form>', trim($html));
        $this->assertUniqueIds($html);
        $this->assertNoAutofocus($html);
        $this->assertCspSafe($html);
    }

    public function test_the_texts_the_name_the_id_and_the_shortcut_can_change_and_the_phone_can_keep_it(): void
    {
        $html = $this->render('<x-ui.search action="/reuniones" placeholder="Buscar reuniones" name="search" value="acta" shortcut="." label="Buscar reuniones" id="field-search-drawer" :hide-on-phone="false" />');

        $this->assertStringContainsString('data-key="."', $html);
        $this->assertStringContainsString('aria-keyshortcuts="."', $html);
        $this->assertStringNotContainsString('hidden md:block', $html);
        $this->assertStringContainsString('<label for="field-search-drawer" class="sr-only">Buscar reuniones</label>', $html);
        $this->assertStringContainsString('<input id="field-search-drawer" type="search" name="search" value="acta"', $html);
        $this->assertStringContainsString('placeholder="Buscar reuniones"', $html);
        $this->assertStringContainsString('>.</kbd>', $html);
    }

    public function test_the_value_defaults_to_the_query_string_and_is_escaped(): void
    {
        $this->app['request']->query->set('q', '<b>acta</b>');

        $html = $this->render('<x-ui.search action="/evidencias" />');

        $this->assertStringContainsString('value="&lt;b&gt;acta&lt;/b&gt;"', $html);
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringContainsString('value="propio"', $this->render('<x-ui.search action="/evidencias" value="propio" />'));
    }

    public function test_the_action_is_required(): void
    {
        try {
            $this->render('<x-ui.search />');
        } catch (ViewException $exception) {
            while ($exception->getPrevious() !== null) {
                $exception = $exception->getPrevious();
            }

            $this->assertInstanceOf(InvalidArgumentException::class, $exception);
            $this->assertSame('The search needs an action.', $exception->getMessage());

            return;
        }

        $this->fail('A search form without an action was rendered');
    }
}
