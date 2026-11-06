<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Carbon;
use Illuminate\View\ViewException;
use InvalidArgumentException;

class DeadlineTest extends ComponentTestCase
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

    public function test_an_open_deadline_is_a_note_with_the_days_left_and_the_closing_date(): void
    {
        $html = $this->render('<x-ui.deadline label="Subida de evidencias" closes-at="2026-11-19 20:00:00" class="mt-2" />');

        $this->assertMatchesRegularExpression('/^<div class="glass-2 grid gap-2 rounded-lg p-4 text-sm mt-2" role="note" x-data="deadline" data-closes-at="2026-11-19T20:00:00\+01:00" data-warn-days="3">/', trim($html));
        $this->assertStringContainsString('Subida de evidencias', $html);
        $this->assertStringContainsString('bg-pending-soft text-pending-ink', $html);
        $this->assertStringContainsString('x-text="remaining"', $html);
        $this->assertStringContainsString('x-bind:data-tone="tone"', $html);
        $this->assertStringContainsString('data-[tone=rejected]:bg-rejected-soft data-[tone=rejected]:text-rejected-ink data-[tone=draft]:bg-draft-soft data-[tone=draft]:text-draft-ink', $html);
        $this->assertBadgeText('14 días', $html);
        $this->assertStringContainsString('<span x-text="closesVerb">Cierra el</span>', $html);
        $this->assertStringContainsString('datetime="2026-11-19T20:00:00+01:00">19/11 · 20:00</time>', $html);
        $this->assertStringNotContainsString('aria-live', $html);
        $this->assertStringNotContainsString('orb-glow', $html, 'The badge has no dot: x-text replaces its content');
        $this->assertDoesNotMatchRegularExpression('/\bw-\d+\/100\b/', $html, 'No bar without opensAt');
        $this->assertStringContainsString('data-tone="pending"', $html);
        $this->assertStringEndsWith('</div>', trim($html));
        $this->assertCspSafe($html);
    }

    public function test_the_text_rounds_to_days_hours_or_minutes_in_spanish(): void
    {
        $this->assertBadgeText('2 días', $this->render('<x-ui.deadline label="x" closes-at="2026-11-07 22:40:00" />'));
        $this->assertBadgeText('1 día', $this->render('<x-ui.deadline label="x" closes-at="2026-11-07 06:40:00" />'));
        $this->assertBadgeText('6 horas', $this->render('<x-ui.deadline label="x" closes-at="2026-11-06 04:40:00" />'));
        $this->assertBadgeText('1 hora', $this->render('<x-ui.deadline label="x" closes-at="2026-11-05 23:50:00" />'));
        $this->assertBadgeText('35 minutos', $this->render('<x-ui.deadline label="x" closes-at="2026-11-05 23:15:00" />'));
        $this->assertBadgeText('1 minuto', $this->render('<x-ui.deadline label="x" closes-at="2026-11-05 22:40:20" />'));
    }

    public function test_under_the_warning_days_the_tone_is_rejected(): void
    {
        $urgent = $this->render('<x-ui.deadline label="x" closes-at="2026-11-07 22:40:00" />');

        $this->assertStringContainsString('bg-rejected-soft text-rejected-ink', $urgent);
        $this->assertStringNotContainsString('bg-pending-soft text-pending-ink', $urgent);

        $wider = $this->render('<x-ui.deadline label="x" closes-at="2026-11-19 20:00:00" :warn-days="20" />');

        $this->assertStringContainsString('bg-rejected-soft text-rejected-ink', $wider);
        $this->assertStringContainsString('data-warn-days="20"', $wider);
    }

    public function test_a_past_deadline_is_closed_and_a_missing_one_has_no_deadline(): void
    {
        $closed = $this->render('<x-ui.deadline label="x" closes-at="2026-11-01 20:00:00" />');

        $this->assertBadgeText('Plazo cerrado', $closed);
        $this->assertStringContainsString('bg-draft-soft text-draft-ink', $closed);
        $this->assertStringContainsString('<span x-text="closesVerb">Cerró el</span>', $closed);
        $this->assertStringContainsString('01/11 · 20:00', $closed);

        $none = $this->render('<x-ui.deadline label="x" />');

        $this->assertBadgeText('Sin plazo', $none);
        $this->assertStringContainsString('bg-rule text-ink', $none);
        $this->assertStringNotContainsString('data-closes-at', $none);
        $this->assertStringNotContainsString('<time', $none);
        $this->assertStringNotContainsString('Cierra', $none);
    }

    public function test_with_both_dates_the_bar_shows_the_consumed_share(): void
    {
        $html = $this->render('<x-ui.deadline label="x" :opens-at="$opens" :closes-at="$closes" />', [
            'opens' => Carbon::now()->subHours(68),
            'closes' => Carbon::now()->addHours(32),
        ]);

        $this->assertStringContainsString('w-68/100', $html);
        $this->assertStringContainsString('bg-linear-to-r from-aurora-500 to-cyan-400', $html);
        $this->assertStringContainsString('h-1.5', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html, 'The bar is decorative: the text informs');
        $this->assertStringNotContainsString('role="img"', $html);

        $closed = $this->render('<x-ui.deadline label="x" opens-at="2026-10-01 00:00:00" closes-at="2026-11-01 00:00:00" />');

        $this->assertStringContainsString('w-100/100', $closed);
        $this->assertStringNotContainsString('w-0/100', $this->render('<x-ui.deadline label="x" opens-at="2026-12-01 00:00:00" closes-at="2026-12-10 00:00:00" />'));
    }

    public function test_the_inline_variant_is_a_sentence_with_the_verb(): void
    {
        $html = $this->render('<x-ui.deadline label="Subida de evidencias" variant="inline" verb="para subir evidencias" closes-at="2026-11-19 20:00:00" />');

        $this->assertMatchesRegularExpression('/^<p class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-ink-muted" role="note" x-data="deadline"/', trim($html));
        $this->assertBadgeText('14 días', $html);
        $this->assertStringContainsString('para subir evidencias', $html);
        $this->assertStringContainsString('<span x-text="closesVerbLower">cierra el</span>', $html);
        $this->assertStringContainsString('>19/11 a las 20:00</time>', $html);
        $this->assertStringNotContainsString('glass-2', $html);
        $this->assertStringNotContainsString('Subida de evidencias', $html, 'The verb replaces the label');
        $this->assertStringEndsWith('</p>', trim($html));

        $plain = $this->render('<x-ui.deadline label="Subida de evidencias" variant="inline" closes-at="2026-11-19 20:00:00" />');

        $this->assertStringContainsString('Subida de evidencias', $plain);
    }

    public function test_with_href_the_card_is_a_link_to_the_affected_view(): void
    {
        $html = $this->render('<x-ui.deadline label="x" closes-at="2026-11-19 20:00:00" href="/evidencias" />');

        $this->assertMatchesRegularExpression('/^<a class="glass-2 grid gap-2 rounded-lg p-4 text-sm text-ink no-underline transition hover:-translate-y-0.5" href="\/evidencias" x-data="deadline"/', trim($html));
        $this->assertStringNotContainsString('role="note"', $html);
        $this->assertStringEndsWith('</a>', trim($html));
        $this->assertCspSafe($html);
    }

    public function test_the_label_is_required_and_escaped(): void
    {
        $this->assertStringContainsString('&lt;b&gt;x&lt;/b&gt;', $this->render('<x-ui.deadline label="<b>x</b>" />'));

        try {
            $this->render('<x-ui.deadline />');
        } catch (ViewException $exception) {
            while ($exception->getPrevious() !== null) {
                $exception = $exception->getPrevious();
            }

            $this->assertInstanceOf(InvalidArgumentException::class, $exception);
            $this->assertSame('The deadline needs a label.', $exception->getMessage());

            return;
        }

        $this->fail('A deadline without a label was rendered');
    }

    /** The badge prints its slot between line breaks. */
    private function assertBadgeText(string $text, string $html): void
    {
        $this->assertMatchesRegularExpression('/>\s*'.preg_quote($text, '/').'\s*<\/span>/u', $html);
    }
}
