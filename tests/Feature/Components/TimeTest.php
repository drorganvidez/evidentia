<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Carbon;

class TimeTest extends ComponentTestCase
{
    private string $carbonLocale;

    protected function setUp(): void
    {
        parent::setUp();

        $this->carbonLocale = Carbon::getLocale();
        Carbon::setTestNow(Carbon::parse('2026-11-05 22:40:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Carbon::setLocale($this->carbonLocale);

        parent::tearDown();
    }

    public function test_it_prints_the_relative_form_the_absolute_one_and_an_iso_datetime(): void
    {
        $html = $this->render('<x-ui.time :value="$value" />', ['value' => '2026-11-03 22:40:00']);

        $this->assertMatchesRegularExpression('/<time\s+datetime="2026-11-03T22:40:00\+01:00" title="03\/11\/2026 22:40">/', $html);
        $this->assertStringContainsString('hace 2 días', $html);
        $this->assertMatchesRegularExpression('/<span class="[^"]*\bblock\b[^"]*">03\/11\/2026 22:40<\/span>/', $html);
        $this->assertMatchesRegularExpression('/<span class="[^"]*\bfont-mono tabular-nums\b[^"]*text-ink-muted[^"]*">/', $html);
        $this->assertCspSafe($html);
    }

    public function test_the_relative_form_is_spanish_whatever_the_application_locale(): void
    {
        app()->setLocale('en');
        Carbon::setLocale('en');
        $value = Carbon::parse('2026-11-03 22:40:00');

        $html = $this->render('<x-ui.time :value="$value" />', ['value' => $value]);

        $this->assertSame('en', app()->getLocale());
        $this->assertStringContainsString('hace 2 días', $html);
        $this->assertStringNotContainsString('2 days ago', $html);
        $this->assertSame('en', $value->locale(), 'The date given by the caller keeps its own locale');
    }

    public function test_the_relative_format_keeps_the_absolute_in_the_title_only(): void
    {
        $html = $this->render('<x-ui.time :value="$value" format="relative" />', ['value' => '2026-11-03 22:40:00']);

        $this->assertStringContainsString('title="03/11/2026 22:40"', $html);
        $this->assertMatchesRegularExpression('/>hace 2 días\s*<\/time>/', $html);
        $this->assertStringNotContainsString('<span', $html);
    }

    public function test_the_absolute_format_follows_the_pattern_in_spanish(): void
    {
        $html = $this->render('<x-ui.time :value="$value" format="absolute" pattern="d/m/Y" />', ['value' => '2026-11-03 22:40:00']);

        $this->assertMatchesRegularExpression('/<time\s+class="font-mono tabular-nums" datetime="2026-11-03T22:40:00\+01:00">03\/11\/2026<\/time>/', $html);
        $this->assertStringNotContainsString('hace', $html);
        $this->assertStringNotContainsString('title=', $html);

        $long = $this->render('<x-ui.time :value="$value" format="absolute" pattern="l j \d\e F \d\e Y" :mono="false" />', ['value' => '2026-11-03 22:40:00']);

        $this->assertStringContainsString('>martes 3 de noviembre de 2026</time>', $long);
        $this->assertStringNotContainsString('font-mono', $long);
    }

    public function test_inline_puts_the_absolute_next_to_the_relative(): void
    {
        $html = $this->render('<x-ui.time :value="$value" inline />', ['value' => '2026-11-03 22:40:00']);

        $this->assertMatchesRegularExpression('/<span class="[^"]*\bml-1\.5\b[^"]*">03\/11\/2026 22:40<\/span>/', $html);
        $this->assertStringNotContainsString('block', $html);
    }

    public function test_short_drops_the_year_only_within_the_current_one(): void
    {
        $thisYear = $this->render('<x-ui.time :value="$value" short />', ['value' => '2026-11-03 22:40:00']);
        $otherYear = $this->render('<x-ui.time :value="$value" short />', ['value' => '2025-11-03 22:40:00']);

        $this->assertStringContainsString('>03/11 22:40</span>', $thisYear);
        $this->assertStringContainsString('title="03/11/2026 22:40"', $thisYear);
        $this->assertStringContainsString('>03/11/2025 22:40</span>', $otherYear);
    }

    public function test_a_missing_date_is_a_dash_with_a_text_alternative(): void
    {
        $html = $this->render('<x-ui.time :value="null" class="ml-2" />');

        $this->assertMatchesRegularExpression('/<span class="text-ink-faint ml-2">\s*—<span class="sr-only">Sin fecha<\/span><\/span>/', $html);
        $this->assertStringNotContainsString('<time', $html);
        $this->assertCspSafe($html);
    }
}
