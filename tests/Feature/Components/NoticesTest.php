<?php

namespace Tests\Feature\Components;

class NoticesTest extends ComponentTestCase
{
    public function test_the_live_regions_and_the_stack_exist_even_without_messages(): void
    {
        $html = $this->render('<x-ui.notices class="print-hidden" />');

        $this->assertMatchesRegularExpression('/^<div x-data="notices" data-messages="\[\]"\s+x-on:ui-notice.window="receive"\s+class="print-hidden">/', trim($html));
        $this->assertStringContainsString('<div role="status" class="sr-only" x-text="statusText"></div>', $html);
        $this->assertStringContainsString('<div role="alert" class="sr-only" x-text="alertText"></div>', $html);
        $this->assertStringContainsString('<div role="region" aria-label="Avisos" class="fixed right-5 bottom-5 z-40 grid w-96 max-w-[calc(100vw-1.5rem)] gap-3 max-md:inset-x-3 max-md:right-3 max-md:bottom-24 max-md:w-auto">', $html);
        $this->assertStringContainsString('<template x-for="item in items" x-bind:key="item.id">', $html);
        $this->assertMatchesRegularExpression('/<div role="group" x-bind:data-tone="item.tone" x-on:mouseenter="pause\(item.id\)"\s+x-on:mouseleave="resume\(item.id\)" x-on:focusin="pause\(item.id\)" x-on:focusout="resume\(item.id\)"/', $html);
        $this->assertStringContainsString('animate-rise motion-reduce:animate-none', $html);
        $this->assertStringContainsString('data-[tone=rejected]:bg-rejected-glass', $html);
        $this->assertStringContainsString('<p class="min-w-0 flex-1 pt-0.5" x-text="item.text"></p>', $html);
        $this->assertStringContainsString('aria-label="Cerrar aviso" title="Cerrar aviso"', $html);
        $this->assertStringContainsString('x-on:click="dismiss(item.id)"', $html);
        $this->assertSame(4, substr_count($html, 'x-show="item.tone ==='), 'One icon per tone');
        $this->assertStringNotContainsString('aria-live', $html, 'The visible notices are not live: the hidden regions announce');
        $this->assertUniqueIds($html);
        $this->assertNoAutofocus($html);
        $this->assertCspSafe($html);
    }

    public function test_the_messages_travel_escaped_in_a_data_attribute_never_in_a_script(): void
    {
        $html = $this->render('<x-ui.notices :messages="$messages" />', [
            'messages' => [['tone' => 'accepted', 'text' => '<b>Guardada</b> & "lista"']],
        ]);

        $this->assertStringContainsString('data-messages="[{&quot;tone&quot;:&quot;accepted&quot;,&quot;text&quot;:&quot;&lt;b&gt;Guardada&lt;\/b&gt; &amp; \&quot;lista\&quot;&quot;}]"', $html);
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertCspSafe($html);
    }

    public function test_the_session_keys_map_to_the_tones_in_a_fixed_order(): void
    {
        $this->withSession(['success' => 'Guardada', 'error' => 'Fallo', 'warning' => 'Ojo', 'light' => 'Nota']);

        $html = $this->render('<x-ui.notices />');

        $expected = e(json_encode([
            ['tone' => 'accepted', 'text' => 'Guardada'],
            ['tone' => 'rejected', 'text' => 'Fallo'],
            ['tone' => 'pending', 'text' => 'Ojo'],
            ['tone' => 'notice', 'text' => 'Nota'],
        ]));

        $this->assertStringContainsString('data-messages="'.$expected.'"', $html);
    }

    public function test_only_the_present_session_keys_are_included_and_the_prop_wins(): void
    {
        $this->withSession(['error' => 'Fallo']);

        $this->assertStringContainsString('data-messages="'.e(json_encode([['tone' => 'rejected', 'text' => 'Fallo']])).'"', $this->render('<x-ui.notices />'));
        $this->assertStringContainsString('data-messages="[]"', $this->render('<x-ui.notices :messages="[]" />'));
    }

    public function test_the_top_position_hangs_from_the_header(): void
    {
        $html = $this->render('<x-ui.notices position="top" />');

        $this->assertStringContainsString('class="fixed inset-x-3 top-3 z-40 grid gap-3 md:inset-x-auto md:top-5 md:left-1/2 md:w-96 md:-translate-x-1/2"', $html);
        $this->assertStringNotContainsString('bottom-5', $html);
    }
}
