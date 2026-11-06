<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Carbon;
use Illuminate\View\ViewException;
use InvalidArgumentException;

class MessageTest extends ComponentTestCase
{
    private const AUTHOR = "['name' => 'Lucía', 'surname' => 'Pérez', 'role' => 'como estudiante', 'href' => '/usuarios/4']";

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

    private function message(string $props = '', string $body = 'Hola'): string
    {
        return $this->render('<x-ui.message :author="'.self::AUTHOR.'" :at="\'2026-11-05 16:40:00\'" '.$props.'>'.$body.'</x-ui.message>');
    }

    public function test_it_is_an_article_named_after_its_author_with_the_person_the_time_and_the_body(): void
    {
        $html = $this->message('class="mt-2"');

        $this->assertStringContainsString('<article aria-label="Mensaje de Lucía Pérez"', $html);
        $this->assertMatchesRegularExpression('/<article aria-label="Mensaje de Lucía Pérez"\s+class="glass-2 grid gap-3 rounded-lg p-4 text-md mt-2">/', $html);
        $this->assertStringContainsString('<header class="flex flex-wrap items-center justify-between gap-2">', $html);
        $this->assertMatchesRegularExpression('/<a href="\/usuarios\/4"\s+class="text-sm font-semibold text-ink transition hover:text-brand-ink">Lucía Pérez<\/a>/', $html);
        $this->assertStringContainsString('<span class="text-xs text-ink-muted">como estudiante</span>', $html);
        $this->assertStringContainsString('size-6', $html, 'The small avatar');
        $this->assertStringContainsString('datetime="2026-11-05T16:40:00+01:00"', $html);
        $this->assertStringContainsString('hace 6 horas', $html);
        $this->assertStringContainsString('<p class="whitespace-pre-line">Hola</p>', $html);
        $this->assertStringContainsString('</article>', $html);
        $this->assertStringNotContainsString('border-l-2', $html);
        $this->assertStringNotContainsString('bg-pending-soft', $html);
        $this->assertUniqueIds($html);
        $this->assertNoAutofocus($html);
        $this->assertCspSafe($html);
    }

    public function test_mine_adds_the_left_rule_only(): void
    {
        $html = $this->message('mine');

        $this->assertStringContainsString('text-md border-l-2 border-l-brand/40"', $html);
        $this->assertStringNotContainsString('border-brand/40', $html, 'Only the left edge changes colour');
    }

    public function test_the_status_is_a_badge(): void
    {
        $open = $this->message('status="open"');

        $this->assertStringContainsString('bg-pending-soft text-pending-ink', $open);
        $this->assertMatchesRegularExpression('/>\s*Abierto\s*<\/span>/', $open);

        $resolved = $this->message('status="resolved"');

        $this->assertStringContainsString('bg-accepted-soft text-accepted-ink', $resolved);
        $this->assertMatchesRegularExpression('/>\s*Resuelto\s*<\/span>/', $resolved);

        $this->assertStringNotContainsString('-soft', $this->message('status="otro"'));
    }

    public function test_the_body_keeps_its_line_breaks_and_is_escaped_by_the_caller(): void
    {
        $html = $this->render('<x-ui.message :author="'.self::AUTHOR.'" :at="\'2026-11-05 16:40:00\'">{{ $body }}</x-ui.message>', [
            'body' => "Hola, <b>Carlos</b>.\nFaltan las fotos.",
        ]);

        $this->assertStringContainsString("<p class=\"whitespace-pre-line\">Hola, &lt;b&gt;Carlos&lt;/b&gt;.\nFaltan las fotos.</p>", $html);
        $this->assertStringNotContainsString('<b>', $html);
    }

    public function test_the_author_without_a_link_is_plain_text_and_the_surname_is_optional(): void
    {
        $html = $this->render('<x-ui.message :author="[\'name\' => \'Secretaría\']" :at="\'2026-11-05 16:40:00\'">Hola</x-ui.message>');

        $this->assertStringContainsString('aria-label="Mensaje de Secretaría"', $html);
        $this->assertStringContainsString('<span class="text-sm font-semibold text-ink">Secretaría</span>', $html);
        $this->assertStringNotContainsString('<a ', $html);
        $this->assertStringNotContainsString('text-xs text-ink-muted">como', $html);
    }

    public function test_the_author_and_the_time_are_required(): void
    {
        foreach ([
            '<x-ui.message :at="\'2026-11-05 16:40:00\'">Hola</x-ui.message>',
            '<x-ui.message :author="'.self::AUTHOR.'">Hola</x-ui.message>',
        ] as $blade) {
            try {
                $this->render($blade);
            } catch (ViewException $exception) {
                while ($exception->getPrevious() !== null) {
                    $exception = $exception->getPrevious();
                }

                $this->assertInstanceOf(InvalidArgumentException::class, $exception);

                continue;
            }

            $this->fail('A message without an author or a time was rendered');
        }
    }

    public function test_the_author_name_is_escaped(): void
    {
        $html = $this->render('<x-ui.message :author="[\'name\' => $name, \'surname\' => \'Pérez\']" :at="\'2026-11-05 16:40:00\'">Hola</x-ui.message>', ['name' => '<b>Lucía</b>']);

        $this->assertStringContainsString('aria-label="Mensaje de &lt;b&gt;Lucía&lt;/b&gt; Pérez"', $html);
        $this->assertStringContainsString('&lt;b&gt;Lucía&lt;/b&gt; Pérez</span>', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }
}
