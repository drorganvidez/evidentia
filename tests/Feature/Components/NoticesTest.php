<?php

namespace Tests\Feature\Components;

class NoticesTest extends ComponentTestCase
{
    public function test_the_live_regions_and_the_stack_exist_even_without_messages(): void
    {
        $html = $this->render('<x-ui.notices class="print-hidden" />');

        $this->assertMatchesRegularExpression('/^<div x-data="notices" data-messages="\[\]"\s+x-on:ui-notice.window="receive"\s+class="contents print-hidden">/', trim($html));
        $this->assertStringContainsString('<div role="status" class="sr-only" x-text="statusText"></div>', $html);
        $this->assertStringContainsString('<div role="alert" class="sr-only" x-text="alertText"></div>', $html);
        $this->assertStringContainsString('<div role="region" aria-label="Avisos" class="fixed right-5 bottom-5 z-40 grid w-96 max-w-[calc(100vw-1.5rem)] gap-3 max-md:inset-x-3 max-md:right-3 max-md:bottom-24 max-md:w-auto">', $html);
        $this->assertStringContainsString('<template x-for="item in items" x-bind:key="item.id">', $html);
        // The pointer and the focus hold the countdown apart: it runs again only when neither is on the notice
        $this->assertMatchesRegularExpression("/<div role=\"group\" x-bind:data-tone=\"item.tone\" x-on:mouseenter=\"pause\\(item.id, 'pointer'\\)\"\\s+x-on:mouseleave=\"resume\\(item.id, 'pointer'\\)\" x-on:focusin=\"pause\\(item.id, 'focus'\\)\"\\s+x-on:focusout=\"resume\\(item.id, 'focus'\\)\"/", $html);
        $this->assertStringContainsString('animate-rise motion-reduce:animate-none', $html);
        $this->assertStringContainsString('data-[tone=rejected]:bg-rejected-glass', $html);
        $this->assertStringContainsString('<p x-text="item.text"></p>', $html);
        $this->assertMatchesRegularExpression('/<a x-show="item.href" x-bind:href="item.href" x-text="item.action"/', $html);
        // The time left of a timed notice: decorative, held with the countdown, absent with reduced motion
        $this->assertMatchesRegularExpression('/<span x-show="item.timed" aria-hidden="true" x-bind:data-paused="item.paused \? \'true\' : null"/', $html);
        $this->assertStringContainsString('animate-countdown', $html);
        $this->assertStringContainsString('data-[paused=true]:[animation-play-state:paused] motion-reduce:hidden', $html);
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

    public function test_a_controller_adds_a_link_to_the_first_flash_message(): void
    {
        $this->withSession(['success' => 'Evidencia enviada', 'notice_link' => ['href' => '/evidence/view/7', 'action' => 'Ver la evidencia']]);

        $html = $this->render('<x-ui.notices />');

        $expected = e(json_encode([['tone' => 'accepted', 'text' => 'Evidencia enviada', 'href' => '/evidence/view/7', 'action' => 'Ver la evidencia']]));
        $this->assertStringContainsString('data-messages="'.$expected.'"', $html);
    }

    public function test_a_link_without_a_message_or_without_an_address_is_ignored(): void
    {
        $this->withSession(['notice_link' => ['href' => '/evidence/view/7']]);
        $this->assertStringContainsString('data-messages="[]"', $this->render('<x-ui.notices />'));

        $this->withSession(['success' => 'Guardada', 'notice_link' => ['action' => 'Ver']]);
        $this->assertStringContainsString('data-messages="'.e(json_encode([['tone' => 'accepted', 'text' => 'Guardada']])).'"', $this->render('<x-ui.notices />'));
    }
}
