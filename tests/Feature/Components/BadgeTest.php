<?php

namespace Tests\Feature\Components;

class BadgeTest extends ComponentTestCase
{
    public function test_a_status_badge_is_a_plain_span_with_the_tone_classes_and_a_dot(): void
    {
        $html = $this->render('<x-ui.badge tone="pending" pulse>Pendiente de revisión</x-ui.badge>');

        $this->assertStringStartsWith('<span class="', trim($html));
        $this->assertStringContainsString('bg-pending-soft text-pending-ink', $html);
        $this->assertStringContainsString('[--orb:var(--pending)]', $html);
        $this->assertStringContainsString('animate-pulse-orb', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
        $this->assertStringContainsString('Pendiente de revisión', $html);
        $this->assertStringNotContainsString('role=', $html);
        $this->assertStringNotContainsString('type=', $html);
        $this->assertStringNotContainsString('tabindex', $html);
        $this->assertStringNotContainsString('hover:', $html);
        $this->assertCspSafe($html);
    }

    public function test_every_tone_maps_to_a_verified_pair_of_classes(): void
    {
        foreach (['accepted', 'pending', 'rejected', 'draft', 'notice'] as $tone) {
            $html = $this->render('<x-ui.badge tone="'.$tone.'">x</x-ui.badge>');

            $this->assertStringContainsString('bg-'.$tone.'-soft text-'.$tone.'-ink', $html);
            $this->assertStringContainsString('[--orb:var(--'.$tone.')]', $html);
        }

        $this->assertStringContainsString('bg-brand-soft text-brand-soft-ink', $this->render('<x-ui.badge tone="brand">x</x-ui.badge>'));
        $this->assertStringContainsString('bg-draft-soft text-draft-ink', $this->render('<x-ui.badge>x</x-ui.badge>'));

        $neutral = $this->render('<x-ui.badge tone="neutral">x</x-ui.badge>');
        $this->assertStringContainsString('bg-rule text-ink', $neutral);
        $this->assertStringContainsString('[--orb:var(--draft)]', $neutral, 'The neutral dot borrows the draft colour');
    }

    public function test_every_size_maps_to_literal_classes_and_the_dot_follows(): void
    {
        $small = $this->render('<x-ui.badge size="sm">x</x-ui.badge>');
        $this->assertStringContainsString('px-2 py-0.5 text-2xs', $small);
        $this->assertStringContainsString('size-2 ', $small);

        $medium = $this->render('<x-ui.badge>x</x-ui.badge>');
        $this->assertStringContainsString('py-1.25 pr-3 pl-2 text-sm', $medium);
        $this->assertStringContainsString('size-2.25', $medium);

        $large = $this->render('<x-ui.badge size="lg">x</x-ui.badge>');
        $this->assertStringContainsString('py-2.5 pr-4.5 pl-3.5 text-base', $large);
        $this->assertStringContainsString('size-2.5', $large);

        // Without a dot or an icon the padding is symmetric
        $this->assertStringContainsString('px-3 py-1.25 text-sm', $this->render('<x-ui.badge :dot="false">x</x-ui.badge>'));
        $this->assertStringContainsString('px-4.5 py-2.5 text-base', $this->render('<x-ui.badge size="lg" :dot="false">x</x-ui.badge>'));
    }

    public function test_the_dot_appears_by_default_only_on_status_badges(): void
    {
        $this->assertStringContainsString('orb-glow', $this->render('<x-ui.badge>x</x-ui.badge>'));
        $this->assertStringNotContainsString('orb-glow', $this->render('<x-ui.badge :dot="false">x</x-ui.badge>'));
        $this->assertStringNotContainsString('orb-glow', $this->render('<x-ui.badge variant="chip">x</x-ui.badge>'));
        $this->assertStringContainsString('orb-glow', $this->render('<x-ui.badge variant="chip" dot>x</x-ui.badge>'));
        $this->assertStringNotContainsString('orb-glow', $this->render('<x-ui.badge variant="count">3</x-ui.badge>'));
        $this->assertStringNotContainsString('orb-glow', $this->render('<x-ui.badge variant="count" dot>3</x-ui.badge>'));
    }

    public function test_chips_and_counts_have_their_own_classes(): void
    {
        $chip = $this->render('<x-ui.badge variant="chip" mono>EV-0033</x-ui.badge>');
        $this->assertStringContainsString('glass-2', $chip);
        $this->assertStringContainsString('px-2.5 py-1', $chip);
        $this->assertStringContainsString('font-mono tabular-nums', $chip);
        $this->assertStringNotContainsString('-soft', $chip);
        $this->assertStringNotContainsString('whitespace-nowrap', $chip);

        $plain = $this->render('<x-ui.badge variant="chip">Estudiante</x-ui.badge>');
        $this->assertStringNotContainsString('font-mono', $plain);

        $count = $this->render('<x-ui.badge variant="count">12</x-ui.badge>');
        $this->assertStringContainsString('bg-rule px-1.75 py-px font-mono text-2xs font-semibold tabular-nums text-ink', $count);
        $this->assertStringNotContainsString('text-ink-muted', $count, 'ink-muted over rule stays under AA in the light theme');
        $this->assertStringContainsString('>12</span>', preg_replace('/\s+/', '', $count));
    }

    public function test_a_hue_paints_a_committee_dot_in_the_chip(): void
    {
        $html = $this->render('<x-ui.badge variant="chip" :hue="150">Logística</x-ui.badge>');

        $this->assertStringContainsString('committee-dot hue-150', $html);
        $this->assertStringContainsString('py-1 pr-2.5 pl-2', $html);
        $this->assertStringNotContainsString('orb-glow', $html);
        $this->assertStringNotContainsString('animate-pulse-orb', $this->render('<x-ui.badge variant="chip" :hue="150" pulse>x</x-ui.badge>'));
    }

    public function test_an_icon_is_decorative_and_small(): void
    {
        $html = $this->render('<x-ui.badge tone="accepted" icon="shield-check" :dot="false">Íntegra</x-ui.badge>');

        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('size-3.5', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
        $this->assertStringNotContainsString('orb-glow', $html);
        $this->assertStringContainsString('py-1.25 pr-3 pl-2 text-sm', $html);
    }

    public function test_with_href_it_becomes_a_link_whose_only_highlight_is_the_ink(): void
    {
        $html = $this->render('<x-ui.badge tone="rejected" href="/evidencias/3">Rechazada</x-ui.badge>');

        $this->assertStringStartsWith('<a href="/evidencias/3" class="', trim($html));
        $this->assertStringEndsWith('</a>', trim($html));
        $this->assertStringContainsString('hover:text-ink', $html);
        $this->assertStringContainsString('bg-rejected-soft text-rejected-ink', $html);
        $this->assertStringNotContainsString('<span class="inline-flex', $html);
        $this->assertStringNotContainsString('<a', $this->render('<x-ui.badge>x</x-ui.badge>'));
        $this->assertCspSafe($html);
    }

    public function test_the_slot_is_escaped_by_the_caller_and_consumer_classes_are_kept(): void
    {
        $html = $this->render('<x-ui.badge class="ml-2">{{ $text }}</x-ui.badge>', ['text' => '<b>x</b>']);

        $this->assertStringContainsString('&lt;b&gt;x&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringContainsString('ml-2', $html);
        $this->assertStringContainsString('href="/x?a=1&amp;b=&lt;2&gt;"', $this->render('<x-ui.badge href="/x?a=1&b=<2>">x</x-ui.badge>'));
    }

    public function test_the_dot_is_hidden_and_coloured_by_its_tone(): void
    {
        $html = $this->render('<x-ui.dot tone="accepted" />');

        $this->assertStringContainsString('aria-hidden="true"', $html);
        $this->assertStringContainsString('bg-(--orb) orb-glow [--orb:var(--accepted)]', $html);
        $this->assertStringContainsString('inline-block shrink-0 rounded-full', $html);
        $this->assertStringContainsString('size-2.25', $html);
        $this->assertStringNotContainsString('animate-pulse-orb', $html);
        $this->assertStringContainsString('[--orb:var(--draft)]', $this->render('<x-ui.dot />'));
        $this->assertStringContainsString('[--orb:var(--brand)]', $this->render('<x-ui.dot tone="brand" />'));
        $this->assertCspSafe($html);
    }

    public function test_the_dot_pulses_and_sizes_on_demand(): void
    {
        $this->assertStringContainsString('animate-pulse-orb', $this->render('<x-ui.dot tone="pending" pulse />'));
        $this->assertStringContainsString('size-2 ', $this->render('<x-ui.dot size="sm" />'));
        $this->assertStringContainsString('size-2.5', $this->render('<x-ui.dot size="lg" />'));
        $this->assertStringContainsString('mt-1.25', $this->render('<x-ui.dot class="mt-1.25" />'));
    }

    public function test_the_dot_normalises_the_hue_to_a_multiple_of_thirty(): void
    {
        $this->assertStringContainsString('committee-dot hue-150', $this->render('<x-ui.dot :hue="155" />'));
        $this->assertStringContainsString('committee-dot hue-0', $this->render('<x-ui.dot :hue="345" />'));
        $this->assertStringContainsString('committee-dot hue-330', $this->render('<x-ui.dot :hue="-30" />'));
        $this->assertStringContainsString('committee-dot hue-270', $this->render('<x-ui.dot :hue="990" />'));

        $html = $this->render('<x-ui.dot :hue="150" pulse />');
        $this->assertStringNotContainsString('orb-glow', $html);
        $this->assertStringNotContainsString('animate-pulse-orb', $html);
    }
}
