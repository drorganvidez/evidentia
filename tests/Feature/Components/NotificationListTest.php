<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Carbon;
use Illuminate\Support\Fluent;

class NotificationListTest extends ComponentTestCase
{
    private const BELL = 'd="M10.268 21a2 2 0 0 0 3.464 0"';

    private const ITEMS = <<<'PHP'
        [
            ['title' => 'Evidencia rechazada', 'text' => 'Montaje del photocall', 'at' => '2026-11-05 16:40:00', 'href' => '/evidencias/33', 'unread' => true, 'tone' => 'rejected'],
            ['title' => 'Evidencia aceptada', 'text' => 'Guion de la mesa redonda', 'at' => '2026-11-04 22:40:00', 'href' => '/evidencias/31', 'unread' => false, 'tone' => 'accepted'],
            ['title' => 'Plazo de evidencias', 'at' => '2026-11-02 22:40:00', 'unread' => true],
        ]
        PHP;

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

    private function list(string $props = ''): string
    {
        return $this->render('<x-ui.notification-list :items="'.self::ITEMS.'" '.$props.' />');
    }

    public function test_it_is_a_labelled_ordered_list_with_the_unread_items_marked(): void
    {
        $html = $this->list('class="mt-4"');

        $this->assertStringContainsString('<ol aria-label="Avisos" class="grid gap-2 mt-4">', $html);
        $this->assertSame(3, preg_match_all('/<li class="glass-2 grid grid-cols-\[auto_minmax\(0,1fr\)\] gap-3 rounded-lg px-4 py-3 text-md data-\[unread\]:bg-brand-soft\/40"/', $html));
        $this->assertSame(2, preg_match_all('/<li [^>]*\s+data-unread\s*>/', $html));
        $this->assertSame(2, substr_count($html, '<span class="sr-only">, sin leer</span>'));
        $this->assertMatchesRegularExpression('/<a href="\/evidencias\/33"\s+class="font-semibold text-ink no-underline transition hover:text-brand-ink">\s*Evidencia rechazada\s*<span class="sr-only">, sin leer<\/span>\s*<\/a>/', $html);
        $this->assertMatchesRegularExpression('/<a href="\/evidencias\/31"\s+class="[^"]*">\s*Evidencia aceptada\s*<\/a>/', $html);
        $this->assertStringContainsString('</ol>', $html);
        $this->assertUniqueIds($html);
        $this->assertCspSafe($html);
    }

    public function test_the_title_is_a_link_only_with_an_href_and_the_text_and_the_time_are_optional(): void
    {
        $html = $this->list();

        $this->assertSame(2, substr_count($html, '<a href='));
        $this->assertMatchesRegularExpression('/<span class="font-semibold text-ink">\s*Plazo de evidencias\s*<span class="sr-only">, sin leer<\/span>\s*<\/span>/', $html);
        $this->assertSame(2, substr_count($html, '<p class="text-sm text-ink-muted">'));
        $this->assertStringContainsString('<p class="text-sm text-ink-muted">Montaje del photocall</p>', $html);
        $this->assertSame(3, substr_count($html, '<time'));
        $this->assertStringContainsString('datetime="2026-11-05T16:40:00+01:00"', $html);
        $this->assertStringContainsString('hace 6 horas', $html);
        $this->assertStringContainsString('mt-1 block text-xs text-ink-muted', $html);
        $this->assertStringNotContainsString('<time', $this->render('<x-ui.notification-list :items="[[\'title\' => \'Sin fecha\']]" />'));
    }

    public function test_the_dot_follows_the_tone_and_falls_back_to_draft(): void
    {
        $html = $this->list();

        $this->assertStringContainsString('[--orb:var(--rejected)]', $html);
        $this->assertStringContainsString('[--orb:var(--accepted)]', $html);
        $this->assertStringContainsString('[--orb:var(--draft)]', $html);
        $this->assertSame(3, substr_count($html, 'orb-glow'));
        $this->assertStringContainsString('mt-1.5', $html);
    }

    public function test_without_items_it_prints_the_empty_state(): void
    {
        $html = $this->render('<x-ui.notification-list :items="[]" />');

        $this->assertStringContainsString(self::BELL, $html);
        $this->assertMatchesRegularExpression('/<h3 class="text-base font-bold">\s*No tienes avisos\s*<\/h3>/', $html);
        $this->assertStringNotContainsString('<ol', $html);
        $this->assertStringContainsString('Bandeja vacía', $this->render('<x-ui.notification-list :items="[]" empty-text="Bandeja vacía" />'));
    }

    public function test_it_takes_collections_of_arrays_or_other_arrayables(): void
    {
        $html = $this->render('<x-ui.notification-list :items="$items" />', [
            'items' => collect([
                5 => ['title' => 'Evidencia rechazada', 'href' => '/evidencias/33', 'unread' => true, 'tone' => 'rejected'],
                2 => new Fluent(['title' => 'Reunión convocada', 'text' => 'Programa', 'at' => '2026-11-05 16:40:00']),
            ]),
        ]);

        $this->assertSame(2, substr_count($html, '<li class="glass-2'));
        $this->assertStringContainsString('<a href="/evidencias/33"', $html);
        $this->assertMatchesRegularExpression('/<span class="font-semibold text-ink">\s*Reunión convocada\s*<\/span>/', $html);
        $this->assertStringContainsString('<p class="text-sm text-ink-muted">Programa</p>', $html);
        $this->assertStringContainsString('hace 6 horas', $html);

        $empty = $this->render('<x-ui.notification-list :items="$items" />', ['items' => collect()]);

        $this->assertStringContainsString(self::BELL, $empty);
        $this->assertStringNotContainsString('<ol', $empty);
    }

    public function test_the_texts_are_escaped(): void
    {
        $html = $this->render('<x-ui.notification-list :items="[[\'title\' => $title, \'text\' => $text, \'href\' => $href]]" :label="$label" />', [
            'title' => '<b>Aviso</b>',
            'text' => '<i>Detalle</i>',
            'href' => '/a?b=1&c=<2>',
            'label' => '<u>Avisos</u>',
        ]);

        $this->assertStringContainsString('aria-label="&lt;u&gt;Avisos&lt;/u&gt;"', $html);
        $this->assertStringContainsString('&lt;b&gt;Aviso&lt;/b&gt;', $html);
        $this->assertStringContainsString('&lt;i&gt;Detalle&lt;/i&gt;', $html);
        $this->assertStringContainsString('href="/a?b=1&amp;c=&lt;2&gt;"', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }
}
